<?php
// ============================================
// FILE: process.php
// FUNGSI: Menangani request dari chatbot (API)
// KONSEP: PDO, Prepared Statements, File Upload
// ============================================

// Mulai session untuk pesan error (opsional)
session_start();

// Include konfigurasi
require_once 'config/config.php';

// Set header untuk response JSON
header('Content-Type: application/json');

// Inisialisasi response array
$response = [
    'success' => false,
    'reply' => '',
    'error' => ''
];

try {
    // ============================================
    // 1. VALIDASI REQUEST METHOD
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. Use POST.');
    }

    // ============================================
    // 2. AMBIL DATA DARI REQUEST
    // ============================================
    $userMessage = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';
    $imageFile = isset($_FILES['image']) ? $_FILES['image'] : null;

    // ============================================
    // 3. HANDLE FILE UPLOAD (JIKA ADA)
    // ============================================
    $uploadedImagePath = null;
    
    if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
        // Validasi file
        $fileName = $imageFile['name'];
        $fileSize = $imageFile['size'];
        $fileTmp = $imageFile['tmp_name'];
        $fileType = mime_content_type($fileTmp);
        
        // Cek ukuran file
        if ($fileSize > MAX_FILE_SIZE) {
            throw new Exception('Ukuran file terlalu besar. Maksimal 1MB.');
        }
        
        // Cek tipe file
        if (!in_array($fileType, ALLOWED_TYPES)) {
            throw new Exception('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
        }
        
        // Generate nama file unik
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = 'upload_' . time() . '_' . uniqid() . '.' . $fileExt;
        $uploadPath = UPLOAD_PATH . $newFileName;
        
        // Pindahkan file ke folder uploads
        if (move_uploaded_file($fileTmp, $uploadPath)) {
            $uploadedImagePath = 'assets/uploads/' . $newFileName;
            
            // Simpan path di session untuk referensi (opsional)
            $_SESSION['last_upload'] = $uploadedImagePath;
        } else {
            throw new Exception('Gagal mengupload file. Coba lagi.');
        }
    }

    // ============================================
    // 4. PROSES PESAN USER
    // ============================================
    if (empty($userMessage) && !$uploadedImagePath) {
        $response['reply'] = "Halo! Anda bisa bertanya tentang pemrograman atau mengupload gambar.";
        $response['success'] = true;
        echo json_encode($response);
        exit;
    }

    // ============================================
    // 5. CARI RESPONS DI DATABASE
    // ============================================
    $database = new Database();
    $db = $database->getConnection();
    
    // Query untuk mencari pertanyaan yang mirip
    // Menggunakan LIKE dengan prepared statement untuk keamanan
    $query = "SELECT replies FROM chatbot WHERE queries LIKE :query ORDER BY id LIMIT 1";
    $stmt = $db->prepare($query);
    
    // Tambah wildcard untuk pencarian partial
    $searchQuery = '%' . $userMessage . '%';
    $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
    $stmt->execute();
    
    // ============================================
    // 6. TENTUKAN RESPONS
    // ============================================
    if ($stmt->rowCount() > 0) {
        // Jika ditemukan di database
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $botReply = $row['replies'];
        
        // Tambah info jika ada gambar
        if ($uploadedImagePath) {
            $botReply .= "\n\n📷 Terima kasih sudah mengupload gambar!";
        }
        
    } else {
        // Jika tidak ditemukan, beri respons default
        $defaultReplies = [
            "Maaf, saya belum bisa menjawab pertanyaan itu. Coba tanyakan tentang PHP, HTML, CSS, atau database.",
            "Pertanyaan menarik! Saat ini saya masih belajar. Coba tanyakan hal lain tentang pemrograman web.",
            "Saya belum mempelajari tentang itu. Tanyakan hal lain seperti: Apa itu PHP? atau Apa itu database?",
            "Hmm... saya belum tahu jawabannya. Sebagai chatbot edukasi RPL, saya fokus pada materi pemrograman web dasar."
        ];
        
        $botReply = $defaultReplies[array_rand($defaultReplies)];
        
        // Tambah info jika ada gambar
        if ($uploadedImagePath) {
            $botReply .= "\n\nGambar yang Anda upload telah diterima!";
        }
    }
    
    // ============================================
    // 7. TAMBAH INFO UPLOAD GAMBAR DI RESPONS
    // ============================================
    if ($uploadedImagePath) {
        // Catat di log (untuk debugging)
        error_log("User uploaded image: " . $uploadedImagePath);
        
        // Untuk versi lebih advanced, bisa menyimpan path gambar di database
        // dengan menambah kolom 'image_path' di tabel chatbot
    }

    // ============================================
    // 8. KIRIM RESPONSE
    // ============================================
    $response['success'] = true;
    $response['reply'] = $botReply;
    
    if ($uploadedImagePath) {
        $response['image_path'] = $uploadedImagePath;
    }

} catch (PDOException $e) {
    // Error database
    $response['error'] = 'Database error: ' . $e->getMessage();
    $response['reply'] = 'Maaf, terjadi kesalahan pada database.';
    error_log("PDO Error: " . $e->getMessage());
    
} catch (Exception $e) {
    // Error umum
    $response['error'] = $e->getMessage();
    $response['reply'] = 'Maaf, terjadi kesalahan: ' . $e->getMessage();
    error_log("Error: " . $e->getMessage());
    
} finally {
    // Pastikan koneksi database ditutup
    if (isset($database)) {
        $database->closeConnection();
    }
}

// ============================================
// 9. OUTPUT RESPONSE JSON
// ============================================
echo json_encode($response);

// ============================================
// FUNGSI TAMBAHAN
// ============================================
function logChat($userMessage, $botReply, $imagePath = null) {
    // Fungsi untuk logging (opsional, untuk debugging)
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_message' => $userMessage,
        'bot_reply' => $botReply,
        'image' => $imagePath
    ];
    
    $logFile = dirname(__DIR__) . '/chat_log.json';
    
    // Baca log yang ada
    $logs = [];
    if (file_exists($logFile)) {
        $logs = json_decode(file_get_contents($logFile), true) ?: [];
    }
    
    // Tambah log baru
    $logs[] = $logData;
    
    // Simpan (maks 100 entri)
    if (count($logs) > 100) {
        $logs = array_slice($logs, -100);
    }
    
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
}
?>