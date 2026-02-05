<?php
// ============================================
// FILE: config/giphy_api.php
// FUNGSI: Mengakses Giphy API untuk mencari GIF
// KONSEP: API Consumption dengan cURL, JSON parsing
// ============================================

class GiphyAPI {
    // API Key Giphy (Gratis - Public Beta Key)
    // NOTE: Untuk production, dapatkan API key gratis di developers.giphy.com
    private $apiKey = 'dc6zaTOxFJmzC'; // Ini adalah public beta key Giphy
    
    // Base URL untuk API
    private $baseUrl = 'https://api.giphy.com/v1/gifs';
    
    /**
     * Mencari GIF berdasarkan keyword
     * @param string $keyword Kata kunci pencarian
     * @return array|false Data GIF atau false jika error
     */
    public function searchGif($keyword) {
        // Bersihkan keyword
        $keyword = urlencode(trim($keyword));
        
        // Jika keyword kosong, gunakan random
        if (empty($keyword)) {
            return $this->getRandomGif();
        }
        
        // Buat URL API
        $url = $this->baseUrl . "/search?api_key=" . $this->apiKey . 
               "&q=" . $keyword . "&limit=1&offset=0&rating=g&lang=en";
        
        try {
            // Gunakan cURL untuk request API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout 5 detik
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Untuk development saja
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Cek jika request berhasil
            if ($httpCode === 200 && !empty($response)) {
                $data = json_decode($response, true);
                
                // Jika ada hasil, kembalikan GIF pertama
                if (!empty($data['data'][0])) {
                    return [
                        'success' => true,
                        'url' => $data['data'][0]['images']['original']['url'],
                        'title' => $data['data'][0]['title'] ?? 'GIF',
                        'source' => 'Giphy'
                    ];
                } else {
                    // Jika tidak ada hasil
                    return [
                        'success' => false,
                        'message' => 'Tidak ditemukan GIF untuk: ' . urldecode($keyword)
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal mengakses API Giphy'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Mendapatkan random GIF
     */
    private function getRandomGif() {
        $url = $this->baseUrl . "/random?api_key=" . $this->apiKey . "&rating=g";
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $data = json_decode($response, true);
            
            if (!empty($data['data'])) {
                return [
                    'success' => true,
                    'url' => $data['data']['images']['original']['url'],
                    'title' => $data['data']['title'] ?? 'Random GIF',
                    'source' => 'Giphy Random'
                ];
            }
            
        } catch (Exception $e) {
            // Fallback ke GIF default
        }
        
        // GIF default jika semua gagal
        return [
            'success' => true,
            'url' => 'https://media.giphy.com/media/3o7abAHdYvZdBNnGZq/giphy.gif',
            'title' => 'Hello GIF!',
            'source' => 'Default'
        ];
    }
    
    /**
     * Mendapatkan trending GIF
     */
    public function getTrendingGif() {
        $url = $this->baseUrl . "/trending?api_key=" . $this->apiKey . "&limit=1&rating=g";
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $data = json_decode($response, true);
            
            if (!empty($data['data'][0])) {
                return [
                    'success' => true,
                    'url' => $data['data'][0]['images']['original']['url'],
                    'title' => $data['data'][0]['title'] ?? 'Trending GIF',
                    'source' => 'Giphy Trending'
                ];
            }
            
        } catch (Exception $e) {
            // Return false jika error
        }
        
        return false;
    }
}

// ============================================
// FUNGSI HELPER SEDERHANA
// ============================================

/**
 * Mengecek apakah pesan mengandung command GIF
 */
function isGifCommand($message) {
    $message = strtolower(trim($message));
    return strpos($message, 'gif ') === 0 || $message === 'gif';
}

/**
 * Extract keyword dari command GIF
 */
function extractGifKeyword($message) {
    $message = trim($message);
    
    // Hapus kata "gif" di awal
    if (strtolower(substr($message, 0, 4)) === 'gif ') {
        return trim(substr($message, 4));
    }
    
    return '';
}

/**
 * Generate response untuk command GIF
 */
function generateGifResponse($gifData) {
    if ($gifData['success']) {
        return [
            'text' => "Berikut GIF untuk Anda! 🎬\n" . 
                     "Judul: " . $gifData['title'] . "\n" .
                     "Sumber: " . $gifData['source'],
            'gif_url' => $gifData['url']
        ];
    } else {
        return [
            'text' => "Maaf, " . $gifData['message'] . "\n" .
                     "Coba ketik: gif [kata-kata]\n" .
                     "Contoh: gif kucing lucu",
            'gif_url' => null
        ];
    }
}
?>