<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$usuario_email = $_SESSION["usuario"];

// Conexão
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "urbanafix";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Like via AJAX (agora alterna like e dislike)
if (isset($_POST['like_id'])) {
    $id_post = intval($_POST['like_id']);
    $res = $conn->query("SELECT likes, users_liked FROM denuncias WHERE id = $id_post");
    if ($res && $row = $res->fetch_assoc()) {
        $users = !empty($row['users_liked']) ? json_decode($row['users_liked'], true) : [];
        if (!is_array($users)) $users = [];

        // Alterna o like
        if (in_array($usuario_email, $users)) {
            // remover like
            $users = array_values(array_diff($users, [$usuario_email]));
        } else {
            // adicionar like
            $users[] = $usuario_email;
        }

        $likes = count($users);
        $users_json = $conn->real_escape_string(json_encode($users));
        $conn->query("UPDATE denuncias SET likes = $likes, users_liked = '$users_json' WHERE id = $id_post");

        // retorna o novo número de likes e se o usuário curtiu
        echo json_encode([
            "likes" => $likes,
            "liked" => in_array($usuario_email, $users)
        ]);
    } else {
        echo json_encode(["likes" => 0, "liked" => false]);
    }
    exit;
}

// Busca denúncias
$denuncias = $conn->query("SELECT d.*, u.usuario AS nome_usuario, u.foto_perfil AS foto_usuario 
                           FROM denuncias d
                           LEFT JOIN usuarios u ON d.id_usuario = u.id
                           ORDER BY d.data_envio DESC");

// Usuário logado
$user_res = $conn->query("SELECT usuario, foto_perfil FROM usuarios WHERE email='$usuario_email'");
$user_data = $user_res->fetch_assoc();
$foto_perfil = $user_data['foto_perfil'] ?? 'https://i.ibb.co/cSRyKKDZ/LALAL.png';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Feed — UrbanaFix</title>
<link rel="icon" type="image/x-icon" href="https://i.ibb.co/zW4c49qc/larakja.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<style>
body {
    margin: 0;
    background: #ffffffff;
    font-family: 'Segoe UI', sans-serif;
    color: #333;
}

/* Cabeçalho */
.feed-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 25px;
    background: #ffffffff;
    border-bottom: 1px solid #eee;
}
.feed-header h1 {
    color: #ff7a00;
    letter-spacing: 6px;
    font-weight: 700;
    margin: 0;
}
.feed-header img {
    height: 50px;
}
.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.user-info img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #ff7a00;
    cursor: pointer;
}
.btn-sair {
    background: #ff7a00;
    color: #fff;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    padding: 8px 14px;
    cursor: pointer;
}
.btn-sair:hover {
    background: #e46f00;
}

/* Feed */
.feed-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 22px;
    padding: 25px 0 120px;
}

/* Mensagem de feed vazio */
.feed-empty {
    text-align: center;
    color: #9b9b9bff;
    font-size: 1.2rem;
    margin-top: 100px;
    font-weight: 500;
}

/* Card */
.card-denuncia {
    background: #ffefe4ff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    padding: 18px;
    width: 90%;
    max-width: 600px;
    transition: 0.2s;
}
.card-denuncia:hover {
    transform: translateY(-2px);
}

/* Usuário no post */
.user-post {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.user-post img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #ff7a00;
}

/* Mídia */
.media-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 8px;
}
.media-container img, .media-container video {
    width: 200px;
    border-radius: 10px;
    cursor: pointer;
}

/* Mapa */
.post-map {
    width: 100%;
    height: 250px;
    border-radius: 10px;
    margin-top: 10px;
}

/* Aprovo */
.aprovo-btn {
    margin-top: 10px;
    font-weight: bold;
    padding: 8px 16px;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #ffd64f, #ffb300);
    color: #4a3200;
    cursor: pointer;
    transition: 0.2s;
}
.aprovo-btn.liked {
    background: linear-gradient(135deg, #ffb347, #ff7a00);
    color: white;
}

/* Botão nova denúncia fixo */
.footer-botao {
    position: fixed;
    bottom: 20px;
    left: 0;
    width: 100%;
    display: flex;
    justify-content: center;
    z-index: 1000;
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.btn-nova-denuncia {
    background-color: #ff7a00;
    color: #fff;
    font-weight: bold;
    border: none;
    border-radius: 10px;
    padding: 12px 18px;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    transition: 0.2s;
}
.btn-nova-denuncia:hover {
    background-color: #e46f00;
    box-shadow: 0 6px 14px rgba(255,122,0,0.4);
}

/* Footer */
footer.footer-info {
    background: #fff;
    border-top: 1px solid #ddd;
    padding: 25px;
    text-align: center;
    color: #555;
    font-size: 0.9rem;
}
footer.footer-info a {
    color: #ff7a00;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}
footer.footer-info a:hover {
    color: #e86d00;
    text-decoration: underline; 
}

/* Descrição */
.descricao-preview {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    margin: 8px 0;
    transition: all 0.3s ease;
}

.descricao-preview.expandida {
    white-space: normal;
    overflow: visible;
    text-overflow: unset;
    word-wrap: break-word;
}
.ler-mais-btn {
    background: none;
    border: none;
    color: #ff7a00;
    cursor: pointer;
    font-weight: bold;
    padding: 0;
}

.btn-filtro {
    background: none;
    border: 2px solid #ff7a00;
    color: #ff7a00;
    border-radius: 8px;
    padding: 6px 12px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.2s;
    margin-left: 15px;
}
.btn-filtro:hover {
    background: #ff7a00;
    color: #fff;
}

</style>
</head>

<body>
<div class="feed-header">
    <div style="display:flex; align-items:center; gap:10px;">
        <h1>FEED</h1>
        <img src="https://i.ibb.co/9Hp1cgbM/Lminilogo.png" alt="Logo UrbanaFix">
        <button class="btn-filtro" onclick="location.href='minhas_denuncias.php'">Minhas denúncias</button>
    </div>
    <div class="user-info">
        <span><?php echo htmlspecialchars($user_data['usuario']); ?></span>
        <img src="<?php echo $foto_perfil; ?>" alt="Perfil" onclick="location.href='perfil.php'">
        <button class="btn-sair" onclick="confirmSair()">Sair</button>
    </div>
</div>

<div class="feed-container">
<?php if ($denuncias->num_rows === 0): ?>
    <div class="feed-empty">Ainda sem posts</div>
<?php else: ?>
<?php while($d = $denuncias->fetch_assoc()): 
    $users_liked = json_decode($d['users_liked'] ?? '[]', true);
if (!is_array($users_liked)) $users_liked = [];
    $liked_by_user = in_array($usuario_email, $users_liked);
    $usuario_post = $d['nome_usuario'] ?? 'Usuário Desconhecido';
    $foto_post_usuario = $d['foto_usuario'] ?? 'https://i.ibb.co/cSRyKKDZ/LALAL.png';
    $descricao = $d['descricao'] ?? '';
?>
<div class="card-denuncia">
    <div class="user-post">
        <img src="<?php echo $foto_post_usuario; ?>" alt="Perfil"> 
        <strong><?php echo htmlspecialchars($usuario_post); ?></strong> 
    </div>

    <h5><?php echo htmlspecialchars($d["titulo"]); ?></h5>
    <br>
    <p><b>Local:</b> <?php echo htmlspecialchars($d["localizacao"] ?? 'Não informado'); ?></p>
    <p><b>Data:</b> <?php echo $d["data_envio"]; ?></p>

    <?php if(!empty($descricao)): ?>
        <p class="descricao-preview" id="desc-<?php echo $d['id']; ?>">
            <?php echo htmlspecialchars($d['descricao']); ?> 
        </p>
        <span id="btn-desc-<?php echo $d['id']; ?>" style="color:#ff7a00; cursor:pointer; font-weight:bold;" onclick="toggleDescricao(<?php echo $d['id']; ?>)">
            Ler mais
        </span>
    <?php endif; ?>

    <div class="media-container">
        <?php if ($d["tipo_midia"] === "imagem"): ?>
            <img src="uploads/<?php echo $d["midia"]; ?>" class="post-media">
        <?php else: ?>
            <video controls class="post-media">
                <source src="uploads/<?php echo $d["midia"]; ?>">
            </video>
        <?php endif; ?>
    </div>

    <div id="map-<?php echo $d["id"]; ?>" class="post-map"></div>

    <button class="aprovo-btn <?php if($liked_by_user) echo 'liked'; ?>" data-id="<?php echo $d["id"]; ?>">
        Aprovo <span id="count-<?php echo $d["id"]; ?>"><?php echo $d['likes'] ?? 0; ?></span>
    </button>
</div>

<script>
let map<?php echo $d["id"]; ?> = L.map('map-<?php echo $d["id"]; ?>').setView([
    <?php echo $d["latitude"] ?? '-23.55052'; ?>,
    <?php echo $d["longitude"] ?? '-46.633308'; ?>
], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map<?php echo $d["id"]; ?>);
L.marker([<?php echo $d["latitude"] ?? '-23.55052'; ?>, <?php echo $d["longitude"] ?? '-46.633308'; ?>]).addTo(map<?php echo $d["id"]; ?>);
</script>
<?php endwhile; ?>
<?php endif; ?>
</div>

<div class="footer-botao">
  <button class="btn-nova-denuncia" onclick="location.href='formulario.php'">+ Nova denúncia</button>
</div>

<footer class="footer-info">
  <small>Precisa de ajuda? Contate: <a href="mailto:suporte.urbanafix@gmail.com">suporte.urbanafix@gmail.com</a></small><br>
  <small>© 2025 UrbanaFix — Todos os direitos reservados.</small>
</footer>

<script>
// Like AJAX — alterna like e dislike
document.querySelectorAll('.aprovo-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    let postId = btn.dataset.id;
    fetch('feed.php', {
      method: 'POST',
      headers: { 'Content-Type':'application/x-www-form-urlencoded' },
      body: 'like_id=' + postId
    })
    .then(res => res.json())
    .then(data => {
      document.getElementById('count-' + postId).textContent = data.likes;
      if (data.liked) {
        btn.classList.add('liked');
      } else {
        btn.classList.remove('liked');
      }
    });
  });
});

function confirmSair() {
  if (confirm("Tem certeza que deseja sair?")) {
    window.location.href = 'login.php';
  }
}

function toggleDescricao(id) {
  const p = document.getElementById('desc-' + id);
  const btn = document.getElementById('btn-desc-' + id);
  p.classList.toggle('expandida');
  btn.textContent = p.classList.contains('expandida') ? "Ler menos" : "Ler mais";
}

document.addEventListener("scroll", () => {
  const footer = document.querySelector(".footer-info");
  const botao = document.querySelector(".footer-botao");
  if (!footer || !botao) return;
  const footerTop = footer.getBoundingClientRect().top;
  const windowHeight = window.innerHeight;
  if (footerTop < windowHeight + 50) {
    botao.style.opacity = "0";
    botao.style.transform = "translateY(30px)";
  } else {
    botao.style.opacity = "1";
    botao.style.transform = "translateY(0)";
  }
});
</script>

<script>
// Zoom de imagem (lightbox simples)
document.querySelectorAll('.post-media').forEach(img => {
  if (img.tagName.toLowerCase() === 'img') {
    img.addEventListener('click', () => {
      const overlay = document.createElement('div');
      overlay.style.position = 'fixed';
      overlay.style.top = 0;
      overlay.style.left = 0;
      overlay.style.width = '100vw';
      overlay.style.height = '100vh';
      overlay.style.background = 'rgba(0, 0, 0, 0.8)';
      overlay.style.display = 'flex';
      overlay.style.alignItems = 'center';
      overlay.style.justifyContent = 'center';
      overlay.style.zIndex = '2000';
      overlay.style.cursor = 'zoom-out';

      const imgAmpliada = document.createElement('img');
      imgAmpliada.src = img.src;
      imgAmpliada.style.maxWidth = '90%';
      imgAmpliada.style.maxHeight = '90%';
      imgAmpliada.style.borderRadius = '12px';
      imgAmpliada.style.boxShadow = '0 0 20px rgba(255,255,255,0.3)';
      imgAmpliada.style.transition = 'transform 0.2s ease';
      imgAmpliada.style.transform = 'scale(1.05)';

      overlay.appendChild(imgAmpliada);
      document.body.appendChild(overlay);

      overlay.addEventListener('click', () => {
        overlay.remove();
      });
    });
  }
});
</script>

</body>
</html>
