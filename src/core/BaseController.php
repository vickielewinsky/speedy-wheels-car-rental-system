<?php
class BaseController {
    protected $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    protected function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
}