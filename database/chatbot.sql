-- ============================================
-- DATABASE CHATBOT UNTUK PEMBELAJARAN RPL
-- ============================================
-- File ini berisi skema database dan data sampel
-- untuk project Chatbot sederhana menggunakan PHP

-- 1. Buat database (jika belum ada)
CREATE DATABASE IF NOT EXISTS `chatbot_db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;

-- 2. Gunakan database
USE `chatbot_db`;

-- 3. Buat tabel chatbot
CREATE TABLE IF NOT EXISTS `chatbot` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `queries` VARCHAR(300) NOT NULL COMMENT 'Pertanyaan dari user',
  `replies` VARCHAR(300) NOT NULL COMMENT 'Jawaban dari bot',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Masukkan 5 data sampel edukatif untuk RPL
INSERT INTO `chatbot` (`queries`, `replies`) VALUES
('Apa itu PHP?', 'PHP adalah bahasa pemrograman server-side untuk pengembangan web. PHP singkatan dari Hypertext Preprocessor.'),
('Apa itu database?', 'Database adalah kumpulan data terstruktur yang disimpan secara elektronik. Contoh: MySQL, PostgreSQL.'),
('Apa itu SQL?', 'SQL (Structured Query Language) adalah bahasa untuk mengelola dan memanipulasi database.'),
('Apa itu HTML?', 'HTML (HyperText Markup Language) adalah bahasa markup untuk membuat struktur halaman web.'),
('Apa itu CSS?', 'CSS (Cascading Style Sheets) adalah bahasa untuk styling dan layout halaman web.');

-- 5. Tambahkan beberapa data tambahan untuk variasi
INSERT INTO `chatbot` (`queries`, `replies`) VALUES
('Halo', 'Halo! Ada yang bisa saya bantu?'),
('Hai', 'Hai! Senang berbicara dengan Anda.'),
('Terima kasih', 'Sama-sama! Semoga membantu.'),
('Siapa kamu?', 'Saya adalah chatbot edukasi untuk pembelajaran RPL.'),
('Bagaimana cara belajar pemrograman?', 'Mulailah dengan dasar-dasar HTML, CSS, PHP, dan database. Praktek adalah kunci!');

-- 6. Lihat data yang telah dimasukkan
SELECT * FROM `chatbot`;

-- ============================================
-- CATATAN UNTUK SISWA:
-- 1. Untuk import database, buka phpMyAdmin
-- 2. Pilih database 'chatbot_db'
-- 3. Klik tab 'Import'
-- 4. Pilih file ini dan klik 'Go'
-- ============================================