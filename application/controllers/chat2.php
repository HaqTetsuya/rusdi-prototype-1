<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Chat2 extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Chat_model', 'chatModel'); // Memuat model Chat_model
	}

	public function index()
	{
		$data['active_controller'] = 'chat2'; // Menandai controller yang aktif
		$data['chats'] = $this->chatModel->getChatHistory('chats2'); // Gunakan tabel 'chats2'
		$this->load->view('chatForm', $data);
	}

	public function send()
	{
		if (!$this->input->is_ajax_request()) {
			echo json_encode(['response' => 'Invalid request']);
			return;
		}
		$user_id = $this->session->userdata('id');
		$data = json_decode(file_get_contents('php://input'), true);
		$message = $data['message'] ?? '';

		$ch = curl_init('http://localhost:5000/analyze');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["text" => $message]));
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		error_log("Raw Flask Response: " . $response); // Log Flask response

		if ($httpcode == 200) {
			$responseData = json_decode($response, true);

			if (!isset($responseData['intent'])) {
				error_log("Flask API did not return an intent.");
				$reply = 'Terjadi kesalahan saat memproses intent.';
			} else {
				$intent = strtolower($responseData['intent']); // Ensure lowercase matching
				$confidence = $responseData['confidence'] ?? 0;

				error_log("Intent received: $intent, Confidence: $confidence");

				switch ($intent) {
					case 'greeting':
						$reply = 'Halo! Ada yang bisa saya bantu? ??';
						break;
					case 'goodbye':
						$reply = 'Sampai jumpa! Semoga harimu menyenangkan! ??';
						break;
					case 'confirm':
						$reply = 'Baik, saya akan memproses permintaan Anda! ?';
						break;
					case 'denied':
						$reply = 'Baik, saya tidak akan melanjutkan permintaan ini. ?';
						break;
					case 'jam_layanan':
						$reply = '';
						break;
					case 'cari_buku':
						$reply = 'Silakan masukkan deskripsi bukunya seperti genre, judul, atau kategori.';
						$action = 'wait_book_recomendation';
						break;
					case 'unknown':
						$reply = 'Maaf, saya tidak mengenali perintah ini. Bisa dijelaskan lebih lanjut? ??';
						break;
					default:
						$reply = "terjadi kesalahan";
						break;
				}
			}
			$data = [
				'user_id' => $user_id,
				'user_message' => $message,
				'bot_response' => $reply
			];

			$this->chatModel->saveChat('chats2', $data);
		} else {
			error_log("Flask API error: HTTP $httpcode");
			$reply = 'Terjadi kesalahan saat menghubungi server. Silakan coba lagi.';
		}

		echo json_encode(['response' => $reply]);
	}

	public function clear()
	{
		$this->chatModel->clearChatHistory('chats2');
		redirect('chat2'); // redirect back to chat page
	}
}
