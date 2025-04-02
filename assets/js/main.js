$(document).ready(function() {
    // Function to scroll chat to bottom
    function scrollToBottom() {
        var chatContainer = document.getElementById('chat-container');
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Scroll to bottom on page load
    scrollToBottom();

    // Handle form submission
    $('#chat-form').submit(function(e) {
        e.preventDefault();

        var message = $('#message').val();
        if (message.trim() === '') return;

        // Add user message to chat
        $('#chat-container').append(`
            <div class="flex items-end justify-end space-x-2">
                <div class="bg-white p-3 rounded-lg border-2 border-black">
                    <p class="text-right font-bold">User</p>
                    <p>${message}</p>
                </div>
                <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0"></div>
            </div>
        `);

        // Clear input field
        $('#message').val('');

        // Scroll to bottom
        scrollToBottom();

        // Show typing indicator
        $('#chat-container').append(`
            <div class="flex items-start space-x-2" id="typing-indicator">
                <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0"></div>
                <div class="bg-white p-3 rounded-lg border-2 border-black">
                    <p class="font-bold">ChatBot</p>
                    <p>Mengetik<span class="typing-dots">...</span></p>
                </div>
            </div>
        `);
        scrollToBottom();

        // Send AJAX request
        $.ajax({
            url: 'https://localhost/book_recomendation/chat/send',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                message: message
            }),
            dataType: 'json',
            beforeSend: function() {
                // Show a typing indicator
                $('#chat-container').append(`
            <div id="typing-indicator" class="flex items-start space-x-2 animate-pulse">
                <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0"></div>
                <div class="bg-gray-200 p-3 rounded-lg border-2 border-gray-400">
                    <p class="font-bold text-gray-600">ChatBot</p>
                    <p class="text-gray-500">Typing...</p>
                </div>
            </div>
        `);
                scrollToBottom();
            },
            success: function(response) {
                $('#typing-indicator').remove(); // Remove typing indicator

                let botResponse = `
            <div class="flex items-start space-x-2 fade-in">
                <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0 bg-gray-300"></div>
                <div class="bg-white p-3 rounded-lg border-2 border-black shadow-md">
                    <p class="font-bold text-blue-600">📚 ChatBot</p>
                    <p class="text-gray-800">${response.response}</p>
                </div>
            </div>
        `;

                $('#chat-container').append(botResponse);
                scrollToBottom();
            },
            error: function(xhr, status, error) {
                $('#typing-indicator').remove();

                let errorResponse = `
            <div class="flex items-start space-x-2 fade-in">
                <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0 bg-red-300"></div>
                <div class="bg-white p-3 rounded-lg border-2 border-red-500 shadow-md">
                    <p class="font-bold text-red-600">⚠️ ChatBot</p>
                    <p class="text-gray-800">Maaf, terjadi kesalahan. Silakan coba lagi.</p>
                </div>
            </div>
        `;

                $('#chat-container').append(errorResponse);
                scrollToBottom();
            }
        });
        
    });

    // Animate typing dots
    setInterval(function() {
        var dots = $('.typing-dots');
        if (dots.length > 0) {
            var text = dots.text();
            dots.text(text.length >= 3 ? '' : text + '.');
        }
    }, 500);
});