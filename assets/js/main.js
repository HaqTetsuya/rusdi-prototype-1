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
            url: `https://localhost/rusdi-prototype-1/${activeController}/send`,
            type: 'POST',
            contentType: 'application/json', 
            data: JSON.stringify({ message: message }),
            dataType: 'json',
            success: function(response) {
                $('#typing-indicator').remove();
                $('#chat-container').append(`
                    <div class="flex items-start space-x-2">
                        <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0"></div>
                        <div class="bg-white p-3 rounded-lg border-2 border-black">
                            <p class="font-bold">ChatBot</p>
                            <p>${response.response}</p>
                        </div>
                    </div>
                `);
                scrollToBottom();
            },
            error: function(xhr, status, error) {
                console.log("XHR Response: ", xhr.responseText);
                console.log("Status: ", status);
                console.log("Error: ", error);
            
                $('#typing-indicator').remove();
                $('#chat-container').append(`
                    <div class="flex items-start space-x-2">
                        <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0"></div>
                        <div class="bg-white p-3 rounded-lg border-2 border-black">
                            <p class="font-bold">ChatBot</p>
                            <p>Maaf, terjadi kesalahan. Silakan coba lagi.</p>
                        </div>
                    </div>
                `);
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