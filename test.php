<?php
// File: test.php (Untuk testing saja, hapus setelah berhasil)
require_once 'config/config.php';

echo "<h1>Testing Setup Chatbot</h1>";

// Test database connection
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "<p style='color: green;'>✅ Database connected successfully!</p>";
    
    // Test query
    $query = "SELECT COUNT(*) as total FROM chatbot";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Total data di database: <strong>" . $result['total'] . "</strong></p>";
    
    // Test config
    echo "<h3>Konfigurasi:</h3>";
    echo "<pre>";
    echo "MAX_FILE_SIZE: " . (MAX_FILE_SIZE / 1024) . " KB\n";
    echo "UPLOAD_PATH: " . UPLOAD_PATH . "\n";
    echo "Timezone: " . date_default_timezone_get() . "\n";
    echo "</pre>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test upload folder
if (is_writable(UPLOAD_PATH)) {
    echo "<p style='color: green;'>✅ Folder uploads writable!</p>";
} else {
    echo "<p style='color: red;'>❌ Folder uploads tidak writable!</p>";
}

// Test emoji list
echo "<h3>Emoji List:</h3>";
global $EMOJI_LIST;
foreach ($EMOJI_LIST as $emoji) {
    echo $emoji . " ";
}

echo "<hr>";
echo "<p>Jika semua test berhasil (✅), hapus file <code>test.php</code> ini.</p>";
?>