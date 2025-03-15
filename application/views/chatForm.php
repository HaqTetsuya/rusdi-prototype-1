<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Patrick Hand', cursive;
        }

        #chat-container {
            height: 70vh;
        }
    </style>
</head>

<body class="bg-white h-screen flex items-center justify-center">
    <div class="bg-white w-full max-w-4xl rounded-lg shadow-lg p-4 flex flex-col h-full border-2 border-black">
        <div class="flex flex-col space-y-4 flex-grow overflow-y-auto" id="chat-container">
            <div class="flex flex-col items-center">
                <div class="w-24 h-24 border-2 border-black rounded-full mb-4"></div>
                <h1 class="text-3xl font-bold mb-4">ChatBot</h1>
            </div>
            <div class="flex items-start space-x-2">
                <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0"></div>
                <div class="bg-white p-3 rounded-lg border-2 border-black">
                    <p class="font-bold">ChatBot</p>
                    <p>Halo, Ada yang bisa saya bantu?</p>
                </div>
            </div>
            <?php foreach ($chats as $chat): ?>
                <div class="flex items-end justify-end space-x-2">
                    <div class="bg-white p-3 rounded-lg border-2 border-black">
                        <p class="text-right font-bold">User</p>
                        <p><?= $chat['user_message'] ?></p>
                    </div>
                    <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0"></div>
                </div>
                <div class="flex items-start space-x-2">
                    <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0"></div>
                    <div class="bg-white p-3 rounded-lg border-2 border-black">
                        <p class="font-bold">ChatBot</p>
                        <p><?= $chat['bot_response'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
        <hr>
        <form id="chat-form" class="flex items-center space-x-2 mt-4 ">
            <input type="text" id="message" name="message" placeholder="Masukan Pesan........................." class="flex-grow p-2 border-2 border-black rounded-full">
            <button type="submit" class="p-2 rounded-full bg-white border-2 border-black">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
    <script src="<?php echo base_url('assets/js/main.js'); ?>"></script>
</body>

</html>