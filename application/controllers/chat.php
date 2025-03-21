<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Chat extends CI_Controller
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

        // Kirim ke Flask API
        $ch = curl_init('http://localhost:5000/analyze');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["text" => $message]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode == 200) {
            $responseData = json_decode($response, true);

            if (isset($responseData['intent']) && isset($responseData['confidence'])) {
                $intent = $responseData['intent'];
                $confidence = $responseData['confidence'];

                // Threshold confidence minimal untuk mengenali intent
                $threshold = 0.6;

                if ($confidence < $threshold) {
                    $reply = "Saya tidak yakin dengan perintah ini. (Confidence: " . number_format($confidence, 4) . ")";
                } else {
                    switch ($intent) {
                        case 'greeting':
                            $reply = 'Halo! Ada yang bisa saya bantu? 😊';
                            break;
                        case 'goodbye':
                            $reply = 'Sampai jumpa! Semoga harimu menyenangkan! 👋';
                            break;
                        default:
                            $reply = "Saya tidak mengenali perintah ini. (Confidence: " . number_format($confidence, 4) . ")";
                            break;
                    }
                }
            } else {
                $reply = 'Terjadi kesalahan saat memproses intent.';
            }

            $this->load->model('chat_model');
            $this->chat_model->saveChat($message, $reply);
        } else {
            $reply = 'Terjadi kesalahan saat menghubungi server. Silakan coba lagi.';
        }

        echo json_encode(['response' => $reply]);
    }
}
