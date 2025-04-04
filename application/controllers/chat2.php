<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Chat2 extends CI_Controller
{
    public function index()
    {
        $this->load->model('chat_model');
        $data['chats'] = $this->chat_model->getChatHistory();
        $this->load->view('chatForm', $data);
    }

    public function send()
	{
		if (!$this->input->is_ajax_request()) {
			echo json_encode(['response' => 'Invalid request']);
			return;
		}

		$data = json_decode(file_get_contents('php://input'), true);
		$message = $data['message'] ?? '';

		$ch = curl_init('http://localhost:5000/predict_intent');
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
					case 'out_of_distribution':
						$reply = 'Maaf, saya tidak mengenali perintah ini. Bisa dijelaskan lebih lanjut? ??';
						break;
					default:
						$reply = "terjadi kesalahan, intent tidak ada";
						break;
				}
			}

			$this->load->model('chat_model');
			$this->chat_model->saveChat($message, $reply);
		} else {
			error_log("Flask API error: HTTP $httpcode");
			$reply = 'Terjadi kesalahan saat menghubungi server. Silakan coba lagi.';
		}

		echo json_encode(['response' => $reply]);
	}
	
	public function clear()
	{
		$this->load->model('chat_model');
		$this->chat_model->clearChatHistory();
		redirect('chat2'); // redirect back to chat page
	}

}
