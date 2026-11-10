<?php
require_once __DIR__.'/comum.php';
header('Cache-Control: no-store');
if (!isset($_SESSION['usuario'])) { header('Location: login.php'); exit; }
