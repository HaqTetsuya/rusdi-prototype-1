<?php
defined('BASEPATH') or exit('No script direct access allowed');

class Chat extends CI_Controller
{
	public function __construct()
		{
			parent::__construct();
			$this->load->model('Chat_model', 'chatModel'); // Memuat model Chat_model
		}
	
    public function index()
		{
			$data['active_controller'] = 'chat'; // Menandai controller yang aktif
			$data['chats'] = $this->chatModel->getChatHistory('chats'); // Gunakan tabel 'chats'
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

        // Kirim data ke Flask API d
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
            $intent = $responseData['intent'] ?? null;
            $course = $responseData['course'] ?? null;
            $day = $responseData['day'] ?? null;

            // Pastikan $course dan $day menjadi string jika berupa array
            if (is_array($course)) {
                $course = implode(', ', $course);
            }
            if (is_array($day)) {
                $day = implode(', ', $day);
            }

            switch ($intent) {
                case 'greetings':
                    $reply = 'Halo! Ada yang bisa saya bantu? 😊';
                    break;

                case 'jadwal_mata_kuliah':
                    $reply = $course
                        ? sprintf('Jadwal mata kuliah %s bisa Anda cek di sistem akademik.', $course)
                        : 'Anda ingin mengecek jadwal mata kuliah apa?';
                    break;

                case 'jadwal_hari_ini':
                    $reply = 'Jadwal mata kuliah Anda pada hari ini yaitu...';
                    break;

                case 'jadwal_minggu_ini':
                    $reply = 'Jadwal mata kuliah Anda pada minggu ini yaitu...';
                    break;

                case 'nilai_mata_kuliah':
                    $reply = $course
                        ? sprintf('Silakan cek nilai mata kuliah %s di portal akademik.', $course)
                        : 'Anda ingin mengecek nilai mata kuliah apa?';
                    break;

                case 'dosen_mata_kuliah':
                    $reply = $course
                        ? sprintf('Dosen pengampu mata kuliah %s bisa dilihat di sistem akademik.', $course)
                        : 'Mata kuliah mana yang ingin Anda tanyakan dosennya?';
                    break;

                case 'ruang_kelas':
                    $reply = $course
                        ? sprintf('Ruang kelas untuk %s bisa dicek di jadwal akademik.', $course)
                        : 'Untuk mata kuliah apa Anda ingin mencari ruang kelasnya?';
                    break;

                case 'ruangan_tidak_dipakai':
                    $reply = 'Berikut adalah ruangan yang tidak terpakai.';
                    break;

                case 'jadwal_hari':
                    $reply = $day
                        ? sprintf('Jadwal kuliah hari %s bisa Anda lihat di sistem akademik.', ucfirst($day))
                        : 'Hari apa yang ingin Anda tanyakan jadwalnya?';
                    break;

                case 'ipk_kumulatif':
                    $reply = 'Nilai mata kuliah Anda yaitu...';
                    break;

                case 'unknown_intent':
                    $reply = 'Maaf, saya tidak mengerti pertanyaan Anda.';
                    break;

                default:
                    $reply = 'Maaf, saya belum bisa menangani permintaan tersebut.';
                    break;
            }

            $this->chatModel->saveChat('chats', $message, $reply);
        } else {
            $reply = 'Terjadi kesalahan saat menghubungi server. Silakan coba lagi.';
        }


        echo json_encode(['response' => $reply]);
    }
		public function clear()
		{
			$this->chatModel->clearChatHistory('chats');
			redirect('chat'); // redirect back to chat page
		}
}
