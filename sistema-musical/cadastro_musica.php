<?php
/*
 * =============================================
 * ARQUIVO: cadastro_musica.php (versão final — + capa do álbum)
 * =============================================
 *
 * Cada música pertence a um artista (artista_id é
 * chave estrangeira que referencia artistas.id).
 *
 * O link do YouTube é armazenado como URL completa
 * e convertido para o ID do vídeo ao exibir.
 */
include('valida_sessao.php');
include('conexao.php');



// Mesma função de redimensionamento já usada em cadastro_artista.php



// -----------------------------------------------
// CREATE / UPDATE
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['delete_id'])) {
    $id           = $_POST['id']           ?? '';
    $artista_id   = $_POST['artista_id']   ?? '';
    $titulo       = $_POST['titulo']       ?? '';
    $album        = $_POST['album']        ?? '';
    $genero       = $_POST['genero']       ?? '';
    $letra        = $_POST['letra']        ?? '';
    $youtube_link = $_POST['youtube_link'] ?? '';

    $check = $conn->prepare('SELECT id FROM artistas WHERE id=?');
    $check->bind_param('i', $artista_id); $check->execute();
    if (!$check->get_result()->fetch_assoc() || trim($titulo)==='' || strlen($titulo)>100 || strlen($album)>100 || strlen($genero)>50 || strlen($youtube_link)>255) { $class='error'; $mensagem='Selecione um artista e preencha os campos corretamente.'; }
    if (empty(getYoutubeVideoId($youtube_link))) {
        $mensagem = "Insira um link válido do YouTube (ex: https://www.youtube.com/watch?v=...).";
        $class    = "error";
    } else {
        $imagem_capa = "";
        if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] == 0) {
            $upload = redimensionarESalvarImagem($_FILES['imagem_capa']);
            if (str_starts_with($upload, 'img/')) {
                $imagem_capa = $upload;
            } else {
                $mensagem = $upload; $class = "error";
            }
        }

        if (!isset($class)) {
            if ($id) {
                // UPDATE — só troca a capa se uma nova foi enviada
                $sql    = "UPDATE musicas SET artista_id=?, titulo=?, album=?, genero=?, letra=?, youtube_link=?";
                $params = [$artista_id, $titulo, $album, $genero, $letra, $youtube_link];
                if ($imagem_capa) { $sql .= ", imagem_capa=?"; $params[] = $imagem_capa; }
                $sql .= " WHERE id=?"; $params[] = intval($id);
                $stmt  = $conn->prepare($sql);
                $tipos = str_repeat('s', count($params) - 1) . 'i';
                $stmt->bind_param($tipos, ...$params);
                $mensagem = "Música atualizada com sucesso!";
            } else {
                // INSERT
                $stmt = $conn->prepare("INSERT INTO musicas (artista_id,titulo,album,genero,letra,youtube_link,imagem_capa) VALUES (?,?,?,?,?,?,?)");
                $stmt->bind_param("sssssss", $artista_id, $titulo, $album, $genero, $letra, $youtube_link, $imagem_capa);
                $mensagem = "Música cadastrada com sucesso!";
            }
            $class = $stmt->execute() ? "success" : "error";
            if ($class === "error") $mensagem = "Erro: " . $stmt->error;
            $stmt->close();
        }
    }
}


// -----------------------------------------------
// DELETE (agora também remove o arquivo da capa)
// -----------------------------------------------
if (isset($_POST['delete_id'])) {
    $id   = intval($_POST['delete_id']);
    $stmt = $conn->prepare("SELECT imagem_capa FROM musicas WHERE id = ?");
    $stmt->bind_param("i", $id); $stmt->execute();
    $arq  = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Remove o arquivo físico da imagem, se existir
    if ($arq && !empty($arq['imagem_capa']) && file_exists($arq['imagem_capa'])) {
        unlink($arq['imagem_capa']); // unlink() = apagar arquivo do servidor
    }

    $stmt = $conn->prepare("DELETE FROM musicas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $class    = $stmt->execute() ? "success" : "error";
    $mensagem = $class === "success" ? "Música excluída!" : "Erro: " . $stmt->error;
    $stmt->close();
}


// -----------------------------------------------
// READ (edição e listagem)
// -----------------------------------------------
$musica = null;
if (isset($_GET['edit_id'])) {
    $eid  = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT * FROM musicas WHERE id = ?");
    $stmt->bind_param("i", $eid); $stmt->execute();
    $musica = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$artistas          = $conn->query("SELECT id, nome FROM artistas ORDER BY nome");
$artista_id_filtro = isset($_GET['artista_id']) ? intval($_GET['artista_id'])
                   : ($musica['artista_id'] ?? null);

$musicas = null;
if ($artista_id_filtro) {
    $stmt = $conn->prepare("
        SELECT m.*, a.nome AS artista_nome
        FROM musicas m
        JOIN artistas a ON m.artista_id = a.id
        WHERE m.artista_id = ?
        ORDER BY m.titulo
    ");
    $stmt->bind_param("i", $artista_id_filtro); $stmt->execute();
    $musicas = $stmt->get_result();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $musica ? 'Editar Música' : 'Nova Música'; ?> — Sistema Musical</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><text y=%27.9em%27 font-size=%2790%27>🎵</text></svg>">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">

    <div class="page-header-bar">
        <h2><?php echo $musica ? '✏️ Editar Música' : '🎵 Nova Música'; ?></h2>
        <div class="header-actions">
            <?php if ($artista_id_filtro): ?>
                <a href="cadastro_artista.php" class="btn-back">⬅ Voltar para Artistas</a>
            <?php else: ?>
                <a href="index.php" class="btn-back">⬅ Voltar ao Menu</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($mensagem)): ?>
        <div class="message <?php echo $class; ?>">
            <?php echo $class === 'success' ? '✅' : '⚠️'; ?> <?php echo htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>

    <!-- FORMULÁRIO -->
    <form method="post" action="" enctype="multipart/form-data"><?php campoCsrf(); ?>
        <input type="hidden" name="id" value="<?php echo $musica['id'] ?? ''; ?>">

        <div class="form-grid">

            <!-- Linha 1: Artista + Título -->
            <div class="form-group">
                <label for="artista_id">Artista <span style="color:var(--red)">*</span></label>
                <select name="artista_id" id="artista_id" required
                        <?php echo isset($_GET['artista_id']) ? 'disabled' : ''; ?>>
                    <option value="">Selecione um artista</option>
                    <?php while ($row = $artistas->fetch_assoc()): ?>
                        <?php $sel = $musica['artista_id'] ?? ($_GET['artista_id'] ?? ''); ?>
                        <option value="<?php echo $row['id']; ?>"
                            <?php echo ($sel == $row['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php if (isset($_GET['artista_id'])): ?>
                    <input type="hidden" name="artista_id" value="<?php echo intval($_GET['artista_id']); ?>">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="titulo">Título <span style="color:var(--red)">*</span></label>
                <input type="text" id="titulo" name="titulo"
                       value="<?php echo htmlspecialchars($musica['titulo'] ?? ''); ?>"
                       required placeholder="Ex: Oceano, Aquarela…">
            </div>

            <!-- Linha 2: Álbum + Gênero -->
            <div class="form-group">
                <label for="album">Álbum</label>
                <input type="text" id="album" name="album"
                       value="<?php echo htmlspecialchars($musica['album'] ?? ''); ?>"
                       placeholder="Ex: Luz (opcional)">
            </div>

            <div class="form-group">
                <label for="genero">Gênero</label>
                <input type="text" id="genero" name="genero"
                       value="<?php echo htmlspecialchars($musica['genero'] ?? ''); ?>"
                       placeholder="Ex: MPB, Rock…">
            </div>

            <!-- Linha 3: YouTube + Capa -->
            <div class="form-group">
                <label for="youtube_link">Link do YouTube <span style="color:var(--red)">*</span></label>
                <input type="url" id="youtube_link" name="youtube_link"
                       value="<?php echo htmlspecialchars($musica['youtube_link'] ?? ''); ?>"
                       required placeholder="https://www.youtube.com/watch?v=…">
                <small>Cole a URL do vídeo (não do canal ou playlist).</small>
            </div>

            <div class="form-group">
                <label for="imagem_capa">Capa do Álbum</label>
                <input type="file" id="imagem_capa" name="imagem_capa" accept="image/*">
                <small>JPG ou PNG · Máximo 5 MB<?php echo $musica ? ' · Deixe vazio para manter a capa atual' : ''; ?></small>
                <?php if (!empty($musica['imagem_capa'])): ?>
                    <div class="current-image">
                        <img src="<?php echo htmlspecialchars($musica['imagem_capa']); ?>" alt="Capa atual">
                        <small>Capa atual</small>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Linha 4: Letra (ocupa as 2 colunas) -->
            <div class="form-group full">
                <label for="letra">Letra da Música</label>
                <textarea id="letra" name="letra"
                          placeholder="Cole a letra aqui (opcional)…"><?php
                    echo htmlspecialchars($musica['letra'] ?? '');
                ?></textarea>
            </div>

        </div><!-- fim .form-grid -->

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <?php echo $musica ? '💾 Atualizar' : '➕ Cadastrar'; ?>
            </button>
            <?php if ($musica): ?>
                <a href="cadastro_musica.php<?php echo $artista_id_filtro ? '?artista_id='.$artista_id_filtro : ''; ?>"
                   class="btn-back">✖ Cancelar</a>
            <?php endif; ?>
        </div>
    </form>


    <!-- -----------------------------------------------
         TABELA DE MÚSICAS DO ARTISTA SELECIONADO
    ----------------------------------------------- -->
    <?php if ($musicas && $musicas->num_rows > 0): ?>
        <h3>🎵 Músicas Cadastradas</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="col-capa">Capa</th>
                        <th>Título</th>
                        <th>Álbum</th>
                        <th class="col-youtube">YouTube</th>
                        <th class="col-acoes">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $musicas->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if ($row['imagem_capa']): ?>
                                    <img src="<?php echo htmlspecialchars($row['imagem_capa']); ?>"
                                         alt="Capa" class="table-thumbnail" loading="lazy">
                                <?php else: ?>
                                    <div class="no-image">Sem<br>capa</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($row['album']) ?: '—'; ?></td>
                            <td>
                                <?php
                                $vid = getYoutubeVideoId($row['youtube_link'] ?? '');
                                if ($vid):
                                    $watch_url = "https://www.youtube.com/watch?v={$vid}";
                                ?>
                                    <a href="<?php echo $watch_url; ?>"
                                       target="_blank" rel="noopener noreferrer"
                                       rel="noopener noreferrer"
                                       class="btn-youtube">▶ Assistir</a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?artista_id=<?php echo $artista_id_filtro; ?>&edit_id=<?php echo $row['id']; ?>"
                                       class="btn-edit">✏️ Editar</a>
                                    <form method="post" style="display:inline" onsubmit="return confirm('Excluir este registro?')"><?php campoCsrf(); ?><input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>"><button type="submit" class="btn-delete">🗑️</button></form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
