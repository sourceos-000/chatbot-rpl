<?php
// ============================================
// FILE: process.php (UPDATED VERSION - WITH GIF)
// ============================================

session_start();
require_once 'config/config.php';
require_once 'config/giphy_api.php'; // Include file API GIF baru

header('Content-Type: application/json');

$response = [
    'success' => false,
    'reply' => '',
    'gif_url' => null,
    'error' => ''
];

try {
    // 1. VALIDASI METHOD
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Gunakan method POST.');
    }

    // 2. AMBIL PESAN USER
    $userMessage = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';
    
    // 3. VALIDASI PESAN TIDAK KOSONG
    if (empty($userMessage)) {
        $response['reply'] = "Halo! Silakan ketik pesan. Anda juga bisa mencari GIF dengan mengetik: gif [kata-kata]";
        $response['success'] = true;
        echo json_encode($response);
        exit;
    }

    // 4. CEK JIKA INI COMMAND GIF
    if (isGifCommand($userMessage)) {
        $keyword = extractGifKeyword($userMessage);
        
        // Cari GIF menggunakan API
        $giphy = new GiphyAPI();
        $gifData = $giphy->searchGif($keyword);
        
        // Generate response
        $gifResponse = generateGifResponse($gifData);
        
        $response['success'] = true;
        $response['reply'] = $gifResponse['text'];
        $response['gif_url'] = $gifResponse['gif_url'];
        
    } else {
        // 5. JIKA BUKAN COMMAND GIF, CARI DI DATABASE
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT replies FROM chatbot WHERE queries LIKE :query LIMIT 1";
        $stmt = $db->prepare($query);
        $searchQuery = '%' . $userMessage . '%';
        $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $botReply = $row['replies'];
            
            // Tambah saran untuk fitur GIF
            if (rand(0, 5) === 0) { // Kadang-kadang saja
                $botReply .= "\n\n💡 Tips: Coba ketik 'gif kucing' untuk melihat GIF lucu!";
            }
        } else {
            // Saran untuk menggunakan fitur GIF
            $botReply = "Maaf, saya belum paham dengan '" . $userMessage . "'.\n";
            $botReply .= "Coba tanyakan tentang pemrograman, atau ketik 'gif [kata-kata]' untuk mencari GIF.";
        }
        
        $response['success'] = true;
        $response['reply'] = $botReply;
    }

} catch (PDOException $e) {
    $response['error'] = 'Database error';
    $response['reply'] = 'Maaf, database sedang bermasalah.';
    
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    $response['reply'] = 'Maaf, terjadi kesalahan sistem.';
    
} finally {
    if (isset($database)) {
        $database->closeConnection();
    }
}

// 6. OUTPUT RESPONSE
echo json_encode($response);

// 7. SIMPAN LOG (SEDERHANA)
function saveSimpleLog($userMessage, $botReply, $hasGif = false) {
    $logFile = __DIR__ . '/chat_log_simple.txt';
    $logLine = date('Y-m-d H:i:s') . " | User: " . substr($userMessage, 0, 50) . 
               " | Bot: " . substr($botReply, 0, 50) . 
               ($hasGif ? " | [GIF]" : "") . "\n";
    
    // Simpan maksimal 1000 baris
    if (file_exists($logFile)) {
        $lines = file($logFile, FILE_IGNORE_NEW_LINES);
        if (count($lines) > 1000) {
            $lines = array_slice($lines, -900);
            file_put_contents($logFile, implode("\n", $lines) . "\n");
        }
    }
    
    file_put_contents($logFile, $logLine, FILE_APPEND);
}
?>