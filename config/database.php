<?php
// ============================================
// FILE: config/database.php
// FUNGSI: Mengelola koneksi ke database
// KONSEP: PDO (PHP Data Objects) - Lebih aman dari mysqli
// ============================================

class Database {
    private $host = "localhost";
    private $db_name = "chatbot_db";
    private $username = "root";
    private $password = "";
    private $conn;
    
    // Method untuk koneksi database
    public function getConnection() {
        $this->conn = null;
        
        try {
            // Buat koneksi menggunakan PDO
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            
            // Set error mode ke exception (lebih mudah debugging)
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set charset ke UTF-8
            $this->conn->exec("SET NAMES utf8mb4");
            
            // Pesan sukses (hanya tampil di development)
            error_log("Database connected successfully");
            
        } catch(PDOException $exception) {
            // Tangani error koneksi
            error_log("Connection error: " . $exception->getMessage());
            echo "Database connection failed. Please check your configuration.";
            die();
        }
        
        return $this->conn;
    }
    
    // Method untuk menutup koneksi (opsional)
    public function closeConnection() {
        $this->conn = null;
    }
}

// ============================================
// CARA PENGGUNAAN DI FILE LAIN:
// 
// require_once 'config/database.php';
// $database = new Database();
// $db = $database->getConnection();
// 
// ============================================

// Contoh penggunaan langsung (untuk testing)
// $database = new Database();
// $db = $database->getConnection();
// echo "Database connected!";
?>