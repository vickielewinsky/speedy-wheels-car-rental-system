<?php
class FileUploadService {
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    private $maxSize = 5 * 1024 * 1024; // 5MB
    
    public function upload($file, $targetDir) {
        // Validation logic here
    }
}