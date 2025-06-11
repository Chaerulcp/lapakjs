<?php
// Konfigurasi session untuk keamanan
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set ke 1 jika menggunakan HTTPS

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
