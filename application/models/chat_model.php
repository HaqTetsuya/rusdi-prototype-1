<?php
class chat_model extends CI_Model {

    public function saveChat($userMessage, $botResponse) {
        $data = [
            'user_message' => $userMessage,
            'bot_response' => $botResponse
        ];
        $this->db->insert('chats2', $data);
    }

    public function getChatHistory() {
        $this->db->order_by('timestamp', 'ASC');
        return $this->db->get('chats2')->result_array();
    }
}
