<?php
// ============================================
// FILE: index.php
// FUNGSI: Tampilan utama chatbot
// KONSEP: Form handling, AJAX dengan Fetch API
// ============================================

// Include konfigurasi
require_once 'config/config.php';

// Ambil list emoji dari config
global $EMOJI_LIST;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Edukasi RPL - PHP Project</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Custom Colors sesuai palette -->
    <style>
        .bg-chat-primary { background-color: #3B4953; }
        .bg-chat-secondary { background-color: #90AB8B; }
        .bg-chat-background { background-color: #EBF4DD; }
        .text-chat-primary { color: #3B4953; }
        .text-chat-secondary { color: #90AB8B; }
        
        /* Animasi typing */
        @keyframes typing {
            0%, 60%, 100% { opacity: 0.4; }
            30% { opacity: 1; }
        }
        
        .typing-indicator span {
            animation: typing 1.5s infinite;
        }
        
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        
        /* Custom scrollbar */
        .chat-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .chat-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .chat-container::-webkit-scrollbar-thumb {
            background: #90AB8B;
            border-radius: 10px;
        }
        
        .chat-container::-webkit-scrollbar-thumb:hover {
            background: #7a9673;
        }
    </style>
    
    <!-- Font Awesome untuk icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-chat-background min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        <!-- Header -->
        <div class="bg-chat-primary text-white rounded-t-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center">
                        <i class="fas fa-robot text-chat-primary text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Chatbot Edukasi RPL</h1>
                        <p class="text-gray-200 text-sm">Belajar PHP dengan Project Based Learning</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="inline-block px-3 py-1 bg-white/20 rounded-full text-sm">
                        <i class="fas fa-circle text-green-400 mr-1"></i>
                        <span>Online</span>
                    </div>
                    <p class="text-xs mt-1 text-gray-300">PHP 8.2 • MySQL • Fetch API</p>
                </div>
            </div>
        </div>

        <!-- Chat Container -->
        <div class="bg-white rounded-b-2xl shadow-lg overflow-hidden">
            <!-- Chat Area -->
            <div class="chat-container h-96 p-4 overflow-y-auto" id="chatContainer">
                <!-- Welcome Message -->
                <div class="flex mb-6">
                    <div class="w-8 h-8 rounded-full bg-chat-primary flex items-center justify-center mr-3 flex-shrink-0">
                        <i class="fas fa-robot text-white text-sm"></i>
                    </div>
                    <div class="bg-gray-100 rounded-2xl rounded-tl-none p-4 max-w-[80%]">
                        <p class="text-gray-800">
                            <span class="font-bold text-chat-primary">Halo! 👋</span> Saya Chatbot Edukasi untuk pembelajaran RPL. 
                            Saya bisa membantu menjawab pertanyaan tentang pemrograman web. Coba tanyakan:
                        </p>
                        <div class="mt-2 text-sm">
                            <span class="inline-block bg-chat-secondary text-white px-3 py-1 rounded-full mr-2 mb-1 text-xs">
                                Apa itu PHP?
                            </span>
                            <span class="inline-block bg-chat-secondary text-white px-3 py-1 rounded-full mr-2 mb-1 text-xs">
                                Apa itu database?
                            </span>
                            <span class="inline-block bg-chat-secondary text-white px-3 py-1 rounded-full mr-2 mb-1 text-xs">
                                Apa itu SQL?
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-3">
                            <i class="fas fa-lightbulb mr-1"></i>
                            Anda juga bisa mengupload gambar atau menggunakan emoji!
                        </p>
                    </div>
                </div>

                <!-- Chat messages akan ditampilkan di sini oleh JavaScript -->
            </div>

            <!-- Typing Indicator (hidden by default) -->
            <div id="typingIndicator" class="hidden px-4 pb-2">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-chat-primary flex items-center justify-center mr-3 flex-shrink-0">
                        <i class="fas fa-robot text-white text-sm"></i>
                    </div>
                    <div class="bg-gray-100 rounded-2xl rounded-tl-none p-3">
                        <div class="typing-indicator flex space-x-1">
                            <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                            <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                            <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="border-t p-4">
                <!-- Emoji Picker -->
                <div id="emojiPicker" class="hidden mb-3 p-3 bg-gray-50 rounded-lg border">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Pilih Emoji</span>
                        <button onclick="hideEmojiPicker()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="grid grid-cols-8 gap-2">
                        <?php foreach ($EMOJI_LIST as $emoji): ?>
                            <button onclick="addEmoji('<?php echo $emoji; ?>')" 
                                    class="text-2xl hover:bg-gray-200 rounded p-1 transition">
                                <?php echo $emoji; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Upload Preview -->
                <div id="uploadPreview" class="hidden mb-3">
                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border">
                        <div class="flex items-center">
                            <i class="fas fa-image text-chat-secondary text-xl mr-3"></i>
                            <div>
                                <p id="fileName" class="font-medium text-sm"></p>
                                <p id="fileSize" class="text-xs text-gray-500"></p>
                            </div>
                        </div>
                        <button onclick="removeUpload()" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Input Form -->
                <form id="chatForm" class="space-y-3">
                    <div class="flex space-x-2">
                        <!-- File Upload -->
                        <div class="relative">
                            <input type="file" id="imageUpload" name="image" accept="image/*" class="hidden" 
                                   onchange="previewUpload(this)">
                            <button type="button" onclick="document.getElementById('imageUpload').click()" 
                                    class="w-12 h-12 flex items-center justify-center bg-chat-secondary hover:bg-[#7a9673] text-white rounded-xl transition">
                                <i class="fas fa-image text-lg"></i>
                            </button>
                        </div>

                        <!-- Emoji Button -->
                        <button type="button" onclick="toggleEmojiPicker()" 
                                class="w-12 h-12 flex items-center justify-center bg-chat-secondary hover:bg-[#7a9673] text-white rounded-xl transition">
                            <i class="fas fa-smile text-lg"></i>
                        </button>

                        <!-- Text Input -->
                        <div class="flex-1 relative">
                            <input type="text" id="messageInput" name="message" 
                                   placeholder="Ketik pesan Anda..." 
                                   class="w-full h-12 px-4 pr-20 border-2 border-gray-200 rounded-xl focus:border-chat-primary focus:ring-2 focus:ring-chat-primary/20 outline-none transition"
                                   autocomplete="off">
                            <button type="submit" 
                                    class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-chat-primary hover:bg-[#2c3740] text-white w-10 h-10 rounded-lg transition flex items-center justify-center">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Info Text -->
                    <p class="text-xs text-gray-500 text-center">
                        <i class="fas fa-info-circle mr-1"></i>
                        Tekan Enter untuk mengirim • Maksimal gambar 1MB (JPG, PNG, GIF)
                    </p>
                </form>
            </div>
        </div>

        <!-- Learning Panel -->
        <div class="mt-6 bg-white rounded-2xl shadow-lg p-6">
            <h3 class="font-bold text-lg text-chat-primary mb-4">
                <i class="fas fa-graduation-cap mr-2"></i>
                Materi Pembelajaran
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-chat-background p-4 rounded-xl border border-chat-secondary/20">
                    <h4 class="font-bold text-chat-secondary mb-2">Form Handling</h4>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>GET vs POST</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>File Upload</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Input Validation</li>
                    </ul>
                </div>
                
                <div class="bg-chat-background p-4 rounded-xl border border-chat-secondary/20">
                    <h4 class="font-bold text-chat-secondary mb-2">Database</h4>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>PDO Connection</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>SQL Queries</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Prepared Statements</li>
                    </ul>
                </div>
                
                <div class="bg-chat-background p-4 rounded-xl border border-chat-secondary/20">
                    <h4 class="font-bold text-chat-secondary mb-2">JavaScript</h4>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Fetch API (AJAX)</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>DOM Manipulation</li>
                        <li><i class="fas fa-check text-green-500 mr-500 mr-2"></i>Event Handling</li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-code mr-2"></i>
                        Project: Chatbot Edukasi RPL • Kelas XI RPL
                    </div>
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-history mr-1"></i>
                        <?php echo date('d F Y'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript untuk Chat Functionality -->
    <script>
        // ============================================
        // VARIABEL GLOBAL
        // ============================================
        let selectedFile = null;

        // ============================================
        // FUNGSI UNTUK EMOJI
        // ============================================
        function toggleEmojiPicker() {
            const picker = document.getElementById('emojiPicker');
            picker.classList.toggle('hidden');
        }

        function hideEmojiPicker() {
            document.getElementById('emojiPicker').classList.add('hidden');
        }

        function addEmoji(emoji) {
            const input = document.getElementById('messageInput');
            input.value += emoji;
            input.focus();
            hideEmojiPicker();
        }

        // ============================================
        // FUNGSI UNTUK FILE UPLOAD
        // ============================================
        function previewUpload(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const maxSize = 1 * 1024 * 1024; // 1MB
                
                // Validasi ukuran file
                if (file.size > maxSize) {
                    alert('Ukuran file terlalu besar! Maksimal 1MB.');
                    input.value = '';
                    return;
                }
                
                // Validasi tipe file
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak didukung! Gunakan JPG, PNG, atau GIF.');
                    input.value = '';
                    return;
                }
                
                selectedFile = file;
                
                // Tampilkan preview
                document.getElementById('uploadPreview').classList.remove('hidden');
                document.getElementById('fileName').textContent = file.name;
                document.getElementById('fileSize').textContent = 
                    'Size: ' + (file.size / 1024).toFixed(1) + ' KB';
            }
        }

        function removeUpload() {
            selectedFile = null;
            document.getElementById('imageUpload').value = '';
            document.getElementById('uploadPreview').classList.add('hidden');
        }

        // ============================================
        // FUNGSI UNTUK CHAT
        // ============================================
        function showTypingIndicator() {
            document.getElementById('typingIndicator').classList.remove('hidden');
        }

        function hideTypingIndicator() {
            document.getElementById('typingIndicator').classList.add('hidden');
        }

        function addMessageToChat(sender, message, isImage = false) {
            const chatContainer = document.getElementById('chatContainer');
            const time = new Date().toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            let messageHTML = '';
            
            if (sender === 'user') {
                messageHTML = `
                    <div class="flex justify-end mb-4">
                        <div class="flex flex-col items-end max-w-[80%]">
                            <div class="bg-chat-primary text-white rounded-2xl rounded-br-none p-4 mb-1">
                                ${isImage ? 
                                    `<img src="${message}" alt="Uploaded" class="max-w-full h-auto rounded-lg">` : 
                                    `<p class="break-words">${message}</p>`
                                }
                            </div>
                            <span class="text-xs text-gray-500">${time} • Anda</span>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-chat-primary flex items-center justify-center ml-3 flex-shrink-0">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    </div>
                `;
            } else {
                messageHTML = `
                    <div class="flex mb-4">
                        <div class="w-8 h-8 rounded-full bg-chat-primary flex items-center justify-center mr-3 flex-shrink-0">
                            <i class="fas fa-robot text-white text-sm"></i>
                        </div>
                        <div class="flex flex-col max-w-[80%]">
                            <div class="bg-gray-100 rounded-2xl rounded-tl-none p-4 mb-1">
                                <p class="break-words text-gray-800">${message}</p>
                            </div>
                            <span class="text-xs text-gray-500">${time} • Chatbot</span>
                        </div>
                    </div>
                `;
            }
            
            chatContainer.insertAdjacentHTML('beforeend', messageHTML);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        // ============================================
        // HANDLE FORM SUBMIT
        // ============================================
        document.getElementById('chatForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            const imageUpload = document.getElementById('imageUpload');
            
            // Validasi input
            if (!message && !selectedFile) {
                alert('Silakan ketik pesan atau pilih gambar!');
                return;
            }
            
            // Tampilkan pesan user
            if (selectedFile) {
                // Preview gambar lokal sebelum upload
                const reader = new FileReader();
                reader.onload = function(e) {
                    addMessageToChat('user', e.target.result, true);
                };
                reader.readAsDataURL(selectedFile);
            }
            
            if (message) {
                addMessageToChat('user', message);
            }
            
            // Reset form
            messageInput.value = '';
            removeUpload();
            hideEmojiPicker();
            
            // Tampilkan typing indicator
            showTypingIndicator();
            
            try {
                // Prepare form data
                const formData = new FormData();
                if (message) formData.append('message', message);
                if (selectedFile) formData.append('image', selectedFile);
                
                // Kirim ke server menggunakan Fetch API
                const response = await fetch('process.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                // Sembunyikan typing indicator
                hideTypingIndicator();
                
                if (result.success) {
                    // Tampilkan respons dari bot
                    setTimeout(() => {
                        addMessageToChat('bot', result.reply);
                    }, 500);
                } else {
                    addMessageToChat('bot', 'Maaf, terjadi kesalahan: ' + result.error);
                }
                
            } catch (error) {
                hideTypingIndicator();
                addMessageToChat('bot', 'Maaf, terjadi kesalahan koneksi. Silakan coba lagi.');
                console.error('Error:', error);
            }
            
            selectedFile = null;
        });

        // ============================================
        // EVENT LISTENER TAMBAHAN
        // ============================================
        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('chatForm').dispatchEvent(new Event('submit'));
            }
        });

        // Klik di luar emoji picker untuk menutup
        document.addEventListener('click', function(e) {
            const emojiPicker = document.getElementById('emojiPicker');
            const emojiButton = e.target.closest('button[onclick*="toggleEmojiPicker"]');
            
            if (!emojiPicker.contains(e.target) && !emojiButton && !emojiPicker.classList.contains('hidden')) {
                hideEmojiPicker();
            }
        });

        // ============================================
        // INISIALISASI
        // ============================================
        console.log('Chatbot RPL siap digunakan!');
        console.log('Teknologi: PHP 8.2, Fetch API, PDO, Tailwind CSS');
    </script>
</body>
</html>