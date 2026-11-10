<?php
$config = require __DIR__ . '/config.php';
if (!extension_loaded('mysqli')) { http_response_code(503); exit('Ative a extensão mysqli no PHP.'); }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($config['host'], $config['usuario'], $config['senha'], $config['banco'], $config['porta']);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(503);
    exit('Banco indisponível. Confira Apache e MySQL no XAMPP e config.php. <a href="instalar.php">Abrir instalação</a>');
}
if (!is_dir(__DIR__ . '/img')) mkdir(__DIR__ . '/img', 0755, true);
