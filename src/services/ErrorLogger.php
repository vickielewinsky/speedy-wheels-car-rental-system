<?php
// src/services/ErrorLogger.php

class ErrorLogger {

    public static function log($message, $context = [], $level = 'ERROR') {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] [$level] $message " . json_encode($context) . PHP_EOL;
        file_put_contents(__DIR__ . '/../../logs/error.log', $logMessage, FILE_APPEND);
    }

    public static function error($message, $context = []) {
        self::log($message, $context, 'ERROR');
    }

    public static function info($message, $context = []) {
        self::log($message, $context, 'INFO');
    }

    public static function debug($message, $context = []) {
        self::log($message, $context, 'DEBUG');
    }

    public static function logException($exception) {
        self::error($exception->getMessage(), ['trace' => $exception->getTrace()]);
    }
}
