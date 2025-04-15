$(document).ready(function() {
	let waitingForRecommendation = false;

    // Function to scroll chat to bottom
    function scrollToBottom() {
        var chatContainer = document.getElementById('chat-container');
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Scroll to bottom on page load
    scrollToBottom();

    // Handle form submission
	$('#chat-form').submit(function(e) {
		
		//waitingForRecommendation=false;
		
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

		$('#message').val('');
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
		
		console.log("waitingForRecommendation status:", waitingForRecommendation); // debug log

		// Percabangan untuk input rekomendasi
		if (waitingForRecommendation) {
			$.ajax({
				url: `${baseUrl}${activeController}/sendbook`, // Gunakan endpoint sendbook di controller aktif
				type: 'POST',
				contentType: 'application/json',
				data: JSON.stringify({ message: message }), // Gunakan format message yang konsisten
				dataType: 'json',
				xhrFields: {
					withCredentials: true
				},
				success: function(response) {
					$('#typing-indicator').remove();
					
					// Tambahkan respons dari server yang sudah diformat
					$('#chat-container').append(`
						<div class="flex items-start space-x-2">
							<div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0"></div>
							<div class="bg-white p-3 rounded-lg border-2 border-black">
								<p class="font-bold">ChatBot</p>
								<div>${response.response}</div>
							</div>
						</div>
					`);
					
					// Reset state ke mode intent normal
					waitingForRecommendation = false;
					scrollToBottom();
				},
				error: function(xhr, status, error) {
					$('#typing-indicator').remove();
					$('#chat-container').append(`
						<div class="flex items-start space-x-2">
							<div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0"></div>
							<div class="bg-white p-3 rounded-lg border-2 border-black">
								<p class="font-bold">ChatBot</p>
								<p>Terjadi kesalahan saat merekomendasikan buku. Silakan coba lagi.</p>
							</div>
						</div>
					`);
					waitingForRecommendation = false;
					scrollToBottom();
				}
			});
			return; // keluar dari fungsi karena kita sudah tangani
		}

		// Jika bukan rekomendasi, lanjut ke intent biasa
		$.ajax({
			url: `${baseUrl}${activeController}/send`,
			type: 'POST',
			contentType: 'application/json',
			data: JSON.stringify({ message: message }),
			dataType: 'json',

			// 🔥 THIS IS THE FIX
			xhrFields: {
				withCredentials: true
			},

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

				if (response.next_action && response.next_action === 'wait_book_recommendation') {
					waitingForRecommendation = true;
				}
			},

			error: function(xhr, status, error) {
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