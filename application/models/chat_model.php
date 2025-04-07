<?php
class Chat_model extends CI_Model {

    public function saveChat($table, $userMessage, $botResponse) {
        $data = [
            'user_message' => $userMessage,
            'bot_response' => $botResponse
        ];
        $this->db->insert($table, $data); // Simpan ke tabel yang ditentukan
    }

    public function getChatHistory($table) {
        $this->db->order_by('timestamp', 'ASC');
        return $this->db->get($table)->result_array(); // Ambil chat history dari tabel yang sesuai
    }

    public function clearChatHistory($table) {
        return $this->db->empty_table($table); // Hapus semua data dari tabel yang sesuai
    }
}
