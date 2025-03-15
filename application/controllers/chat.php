<?php
defined('BASEPATH') or exit('No script direct access allowed');
class Chat extends CI_Controller
{
    public function index()
    {
        $this->load->view('chatForm');
    }


    public function send()
    {
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['response' => 'Invalid request']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $message = $data['message'] ?? '';

        // Kirim data ke Flask API dengan format yang benar
        $ch = curl_init('http://localhost:5000/analyze');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["text" => $message])); // Kirim data sebagai JSON
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode == 200) {
            $responseData = json_decode($response, true);

            // Pastikan response memiliki 'intent'
            if (isset($responseData['intent'])) {
                // Handle intent di PHP
                $intent = $responseData['intent'];
                switch ($intent) {
                    case 'greeting':
                        $reply = 'Halo! Ada yang bisa saya bantu? 😊';
                        break;

                    case 'bantuan':
                        $reply = 'Ketik /book untuk pinjam buku, /search untuk cari buku, atau /schedule untuk lihat jadwal.';
                        break;

                    case 'pinjam_buku':
                        $reply = 'Untuk pinjam buku, silakan datang ke perpustakaan dan bawa kartu mahasiswa.';
                        break;

                    case 'cari_buku':
                        $reply = 'Gunakan fitur pencarian buku di website perpustakaan kami.';
                        break;

                    case 'jadwal_perpus':
                        $reply = 'Perpustakaan buka dari Senin - Jumat, jam 08:00 - 17:00.';
                        break;

                    default:
                        $reply = 'Maaf, perintah tidak dikenal.';
                        break;
                }
            } else {
                $reply = 'Terjadi kesalahan saat memproses intent.';
            }
        } else {
            $reply = 'Terjadi kesalahan saat menghubungi server. Silakan coba lagi.';
        }

        echo json_encode(['response' => $reply]);
    }
}
