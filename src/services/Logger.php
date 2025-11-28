<?php
class Logger {
    private $logFile;
    
    public function __construct($logFile = 'app.log') {
        $this->logFile = __DIR__ . '/../logs/' . $logFile;
    }
    
    public function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}