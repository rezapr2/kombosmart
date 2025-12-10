<?php
// Simple router for PHP built-in server to work with WordPress.
// Serves static assets directly; routes all other requests to index.php.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path !== '/' && file_exists(__DIR__ . $path)) {
    return false; // serve the requested resource
}
require __DIR__ . '/index.php';