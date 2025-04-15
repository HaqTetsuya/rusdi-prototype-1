<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Chat3 extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		// Load necessary libraries
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->model('Chat_model', 'chatModel'); // Memuat model Chat_model
	}

	/**
	 * Index page - display chat interface
	 */
	public function index()
	{
		$data['active_controller'] = 'chat3'; // Menandai controller yang aktif
		$data['chats'] = $this->chatModel->getChatHistory('chats3'); // Gunakan tabel 'chats2'
		$this->load->view('chatForm', $data);
	}

	/**
	 * Handle chat message sending
	 * Communicates with Flask API for book recommendations
	 */

	public function send()
	{
		if (!$this->input->is_ajax_request()) {
			show_error('Direct access not allowed', 403);
			return;
		}
		$user_id= $this->session->userdata('id');
		$json_data = file_get_contents('php://input');
		$post_data = json_decode($json_data, true);
		$message = isset($post_data['message']) ? trim($post_data['message']) : '';

		if (empty($message)) {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(['error' => 'No message provided']));
			return;
		}

		$api_data = [
			'query' => $message,
			'top_n' => 5
		];

		$ch = curl_init('http://localhost:5000/recommend');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

		$response = curl_exec($ch);
		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($ch);
		curl_close($ch);

		if ($response === false) {
			log_message('error', 'cURL Error: ' . $curl_error);
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(['error' => 'Failed to connect to recommendation service', 'details' => $curl_error]));
			return;
		}

		$api_response = json_decode($response, true);

		if ($status_code != 200 || !isset($api_response['results'])) {
			$error = isset($api_response['error']) ? $api_response['error'] : 'Unknown error';
			log_message('error', 'API Error: ' . $error . ' (Status: ' . $status_code . ')');
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(['error' => 'Recommendation service error', 'details' => $error]));
			return;
		}

		$formatted_response = $this->format_recommendations($api_response['results']);
		$data = [
			'user_id' => $user_id,
			'user_message' => $message,
			'bot_response' => $formatted_response
		];
		$this->chatModel->saveChat('chats3', $data);
		// ✅ Save chat to database


		// Return response
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode(['response' => $formatted_response]));
	}


	/**
	 * Format book recommendations into HTML for display
	 */
	private function format_recommendations($results)
	{
		$output = "<strong>Buku yang direkomendasikan untuk Anda:</strong><br><br>";

		foreach ($results as $index => $book) {
			$relevance_percentage = $book['relevance'] * 100;
			$year = $book['year'] ? $book['year'] : 'Tahun tidak diketahui';

			$output .= "<div class='book-recommendation'>";
			$output .= "<strong>" . ($index + 1) . ". " . htmlspecialchars($book['title']) . "</strong><br>";
			$output .= "Penulis: " . htmlspecialchars($book['author']) . "<br>";
			$output .= "Kategori: " . htmlspecialchars($book['category']) . "<br>";
			$output .= "Tahun: " . htmlspecialchars($year) . "<br>";
			$output .= "<p><em>Deskripsi:</em> " . nl2br(htmlspecialchars($book['description'])) . "</p>";
			$output .= "Relevansi: " . number_format($relevance_percentage, 0) . "%<br>";
			$output .= "</div><br>";
		}

		$output .= "<p>Apakah Anda ingin rekomendasi buku lainnya? Silakan ketik pertanyaan atau topik yang Anda minati.</p>";

		return $output;
	}
	public function clear()
	{
		$this->chatModel->clearChatHistory('chats3');
		redirect('chat'); // redirect back to chat page
	}
}
