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

        if (empty($message)) {
            echo json_encode(['response' => 'Message cannot be empty']);
            return;
        }

        // Kirim data ke Flask API
        $ch = curl_init('http://localhost:5000/analyze');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["text" => $message])); // Kirim data sebagai JSON
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Cek apakah respons valid dari Flask API
        if ($httpcode == 200 && $response) {
            $responseData = json_decode($response, true);
            $reply = $responseData['reply'] ?? 'Tidak ada respons dari server.';
        } else {
            $reply = 'Terjadi kesalahan saat menghubungi server. Silakan coba lagi.';
        }

        // Simpan riwayat chat
        $this->load->model('chat_model');
        $this->chat_model->saveChat($message, $reply);

        echo json_encode(['response' => $reply]);
    }
}
