<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
	<link href="<?php echo base_url(); ?>assets/css/main.css" rel="stylesheet">
    
</head>
<body class="bg-white h-screen flex items-center justify-center">
    <div class="bg-white w-full max-w-4xl rounded-lg shadow-lg p-4 flex flex-col h-full border-2 border-black">
        <!-- Header with profile and controls -->
        <div class="flex justify-between items-center mb-4 pb-2 border-b-2 border-black">
            <div class="flex items-center">
                <div class="w-10 h-10 border-2 border-black rounded-full mr-2"></div>
                <h1 class="text-2xl font-bold">ChatBot</h1>
            </div>
            
            <div class="flex items-center space-x-4">
                <!-- Delete chat history button 
                <form method="post" action="<?php //echo site_url($active_controller.'/clear'); ?>">
                    <button type="submit" class="bg-white border-2 border-black rounded-full p-2"
                        onclick="return confirm('Yakin ingin menghapus semua riwayat chat?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
                
                <!-- Profile dropdown -->
                <div class="profile-dropdown">
                    <button class="bg-white border-2 border-black rounded-full p-2">
                        <i class="fas fa-user"></i>
                    </button>
                    <div class="dropdown-content">
                        <div class="px-4 py-2 border-b border-gray-200">
                            <p class="font-bold"><?= $user->nama; ?></p>
							<p class="font-bold"><?= $user->email; ?></p>
                            <p class="text-sm">ID: <?= $user->id; ?></p>
                        </div>
						<a href="<?php echo site_url($active_controller.'/clear'); ?>" onclick="return confirm('Yakin ingin menghapus semua riwayat chat?')">
                            <i class="fas fa-trash mr-2"></i> Hapus
                        </a>
                        <a href="<?php echo site_url('auth/logout'); ?>">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chat container -->
        <div class="flex flex-col space-y-4 flex-grow overflow-y-auto" id="chat-container">
            <!-- Welcome message -->
            <div class="flex items-start space-x-2 message-container">
                <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0"></div>
                <div class="bg-white p-3 rounded-lg border-2 border-black">
                    <p class="font-bold">ChatBot</p>
                    <p>Halo, Ada yang bisa saya bantu?</p>
                    <div class="timestamp">Today, 00:00</div>
                </div>
            </div>
            
            <?php foreach ($chats as $chat): ?>
                <div class="flex items-end justify-end space-x-2 message-container">
                    <div class="bg-white p-3 rounded-lg border-2 border-black">
                        <p class="text-right font-bold"><?= $user->nama; ?></p>
                        <p><?= $chat['user_message'] ?></p>
                        <div class="timestamp text-right"><?= $chat['timestamp'] ?></div>
                    </div>
                    <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0"></div>
                </div>
                <div class="flex items-start space-x-2 message-container">
                    <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0"></div>
                    <div class="bg-white p-3 rounded-lg border-2 border-black">
                        <p class="font-bold">ChatBot</p>
                        <p><?= $chat['bot_response'] ?></p>
                        <div class="timestamp"><?= $chat['timestamp'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Input form -->
        <hr class="border-black my-4">
        <form id="chat-form" class="flex items-center space-x-2">
            <input type="text" id="message" name="message" placeholder="Masukan Pesan........................." class="flex-grow p-2 border-2 border-black rounded-full">
            <button type="submit" class="p-2 rounded-full bg-white border-2 border-black">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>

    <script src="<?php echo base_url('assets/js/main.js'); ?>"></script>
    <script>
        const baseUrl = "<?= base_url() ?>";
        var activeController = "<?php echo $active_controller; ?>";                       
    </script>
</body>
</html>