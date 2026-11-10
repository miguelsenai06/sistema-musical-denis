<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly'=>true, 'samesite'=>'Lax']);
    session_start();
}
$_SESSION['csrf'] = $_SESSION['csrf'] ?? bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
    http_response_code(403); exit('Formulário expirado. Volte e atualize a página.');
}
function campoCsrf() { echo '<input type="hidden" name="csrf" value="'.htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8').'">'; }
function getYoutubeVideoId(string $url) {
    $u = parse_url(trim($url));
    if (!$u || !in_array(strtolower($u['scheme'] ?? ''), ['http','https'])) return '';
    $host = strtolower($u['host'] ?? ''); $path = $u['path'] ?? '';
    $id = '';
    if ($host === 'youtu.be') $id = trim($path, '/');
    elseif (in_array($host, ['youtube.com','www.youtube.com','m.youtube.com','youtube-nocookie.com','www.youtube-nocookie.com'])) {
        parse_str($u['query'] ?? '', $q); $id = $q['v'] ?? '';
        if (preg_match('~^/(?:embed|shorts|v)/([A-Za-z0-9_-]{11})/?$~', $path, $m)) $id = $m[1];
    }
    return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : '';
}
function redimensionarESalvarImagem(array $arquivo, $largura=300, $altura=300) {
    if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return 'Falha no envio da imagem.';
    if ($arquivo['size'] > 5000000) return 'Imagem muito grande. Máximo: 5 MB.';
    $info = @getimagesize($arquivo['tmp_name']);
    if (!$info || !in_array($info['mime'], ['image/jpeg','image/png'])) return 'Envie uma imagem JPG ou PNG válida.';
    if ($info[0]*$info[1] > 20000000) return 'Imagem com resolução muito alta.';
    $destino = 'img/'.bin2hex(random_bytes(16)).($info['mime'] === 'image/png' ? '.png' : '.jpg');
    if (!function_exists('imagecreatefromstring')) {
        return move_uploaded_file($arquivo['tmp_name'], __DIR__.'/'.$destino) ? $destino : 'Não foi possível salvar a imagem.';
    }
    $original = @imagecreatefromstring(file_get_contents($arquivo['tmp_name']));
    if (!$original) return 'Imagem inválida.';
    $ratio = min(1, $largura/$info[0], $altura/$info[1]);
    $w = max(1,(int)($info[0]*$ratio)); $h = max(1,(int)($info[1]*$ratio));
    $nova = imagecreatetruecolor($w,$h);
    imagealphablending($nova,false); imagesavealpha($nova,true);
    imagecopyresampled($nova,$original,0,0,0,0,$w,$h,$info[0],$info[1]);
    $ok = $info['mime'] === 'image/png' ? imagepng($nova,__DIR__.'/'.$destino) : imagejpeg($nova,__DIR__.'/'.$destino,90);
    imagedestroy($original); imagedestroy($nova);
    return $ok ? $destino : 'Não foi possível salvar a imagem.';
}
