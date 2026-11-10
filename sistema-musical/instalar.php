<?php
require_once __DIR__.'/comum.php';
$lock = __DIR__.'/instalado.lock';
if (file_exists($lock)) { header('Location: login.php'); exit; }
$erro = ''; $ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!extension_loaded('mysqli')) throw new RuntimeException('Ative mysqli no php.ini e reinicie o Apache.');
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $cfg = require __DIR__.'/config.php';
        $cfg['usuario'] = trim($_POST['usuario_mysql'] ?? 'root');
        $cfg['senha'] = $_POST['senha_mysql'] ?? '';
        $db = new mysqli($cfg['host'], $cfg['usuario'], $cfg['senha'], '', $cfg['porta']);
        $db->set_charset('utf8mb4');
        $db->multi_query(file_get_contents(__DIR__.'/bd.sql'));
        do { if ($r = $db->store_result()) $r->free(); } while ($db->more_results() && $db->next_result());
        $db->select_db($cfg['banco']);
        $r = $db->query("SELECT id FROM usuarios WHERE usuario='admin' LIMIT 1");
        if (!$r->num_rows) {
            $hash = password_hash('123', PASSWORD_DEFAULT);
            $q = $db->prepare('INSERT INTO usuarios(usuario,senha) VALUES (?,?)');
            $usuario = 'admin'; $q->bind_param('ss',$usuario,$hash); $q->execute();
        }
        if (!is_dir(__DIR__.'/img') && !mkdir(__DIR__.'/img',0755,true)) throw new RuntimeException('Não foi possível criar a pasta img.');
        if (file_put_contents(__DIR__.'/config.php', "<?php\nreturn ".var_export($cfg,true).";\n", LOCK_EX) === false) throw new RuntimeException('Sem permissão para salvar config.php.');
        if (file_put_contents($lock, date('c'), LOCK_EX) === false) throw new RuntimeException('Sem permissão para finalizar a instalação.');
        $ok = true;
    } catch (Throwable $e) { $erro = 'Não foi possível concluir. Confira se o MySQL está ligado e se o usuário e a senha estão corretos. Confira também as permissões da pasta e as extensões mysqli e fileinfo do PHP.'; }
}
?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Instalar Sistema Musical</title><link rel="stylesheet" href="styles.css"></head>
<body class="login-page"><div class="login-card"><h1>🎵 Instalação</h1>
<?php if ($ok): ?><div class="message success">Banco preparado! Entre com admin e senha 123. Se o usuário já existia, sua senha foi preservada.</div><a class="btn-primary" href="login.php">Abrir sistema</a>
<?php else: ?><p>Ligue Apache e MySQL no XAMPP. Esta tela cria o banco e o acesso inicial, preservando os registros existentes.</p>
<?php if ($erro): ?><div class="message error"><?php echo htmlspecialchars($erro); ?></div><?php endif; ?>
<form method="post"><?php campoCsrf(); ?><div class="form-group"><label>Usuário do MySQL</label><input name="usuario_mysql" value="root" required></div><div class="form-group"><label>Senha do MySQL</label><input type="password" name="senha_mysql"><small>No XAMPP padrão, deixe vazio. Se você cadastrou uma senha no MySQL, use essa senha.</small></div><button class="btn-primary">Preparar banco e iniciar</button></form><?php endif; ?></div></body></html>
