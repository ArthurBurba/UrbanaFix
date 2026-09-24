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

// Busca ID do usuário logado
$user_res = $conn->query("SELECT id, usuario, foto_perfil FROM usuarios WHERE email='$usuario_email'");
$user_data = $user_res->fetch_assoc();
$id_usuario = $user_data['id'];
$foto_perfil = $user_data['foto_perfil'] ?? 'https://i.ibb.co/cSRyKKDZ/LALAL.png';

// Busca apenas denúncias do usuário logado
$denuncias = $conn->query("SELECT d.*, u.usuario AS nome_usuario, u.foto_perfil AS foto_usuario 
                           FROM denuncias d
                           LEFT JOIN usuarios u ON d.id_usuario = u.id
                           WHERE d.id_usuario = $id_usuario
                           ORDER BY d.data_envio DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Minhas Denúncias — UrbanaFix</title>
<link rel="icon" type="image/x-icon" href="https://i.ibb.co/zW4c49qc/larakja.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- CSS GLOBAL -->
<link rel="stylesheet" href="style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<style>
.feed-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 25px;
    background: #fff;
    border-bottom: 1px solid #eee;
}

.feed-header h1 {
    color: var(--cor-principal);
    letter-spacing: 4px;
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
    border: 2px solid var(--cor-principal);
    cursor: pointer;
}

.btn-sair {
    background: var(--cor-principal);
    color: #fff;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    padding: 8px 14px;
    cursor: pointer;
    transition: 0.2s;
}
.btn-sair:hover {
    background: var(--cor-botao-hover);
}

.btn-voltar {
    background: none;
    border: 2px solid var(--cor-principal);
    color: var(--cor-principal);
    border-radius: 8px;
    padding: 6px 12px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.2s;
    margin-left: 15px;
}
.btn-voltar:hover {
    background: var(--cor-principal);
    color: #ff893bff;
}

/* Feed cards */
.feed-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 22px;
    padding: 25px 0 120px;
}

.feed-empty {
    text-align: center;
    color: #9b9b9b;
    font-size: 1.2rem;
    margin-top: 100px;
    font-weight: 500;
}

.card-denuncia {
    background: #fff;
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
    border: 2px solid var(--cor-principal);
}

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

.post-map {
    width: 100%;
    height: 250px;
    border-radius: 10px;
    margin-top: 10px;
}

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
    color: var(--cor-principal);
    cursor: pointer;
    font-weight: bold;
    padding: 0;
}
footer.footer-info {
    background: #fff;
    border-top: 1px solid #ddd;
    padding: 25px;
    text-align: center;
    color: #555;
    font-size: 0.9rem;
}
footer.footer-info a {
    color: var(--cor-principal);
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}
footer.footer-info a:hover {
    color: var(--cor-botao-hover);
    text-decoration: underline; 
}
</style>
</head>

<body>
<div class="feed-header">
    <div style="display:flex; align-items:center; gap:10px;">
        <h1>MINHAS DENÚNCIAS</h1>
        <img src="https://i.ibb.co/9Hp1cgbM/Lminilogo.png" alt="Logo UrbanaFix">
        <button class="btn-voltar" onclick="location.href='feed.php'">← Voltar</button>
    </div>
    <div class="user-info">
        <span><?php echo htmlspecialchars($user_data['usuario']); ?></span>
        <img src="<?php echo $foto_perfil; ?>" alt="Perfil" onclick="location.href='perfil.php'">
        <button class="btn-sair" onclick="confirmSair()">Sair</button>
    </div>
</div>

<div class="feed-container">
<?php if ($denuncias->num_rows === 0): ?>
    <div class="feed-empty">Você ainda não fez nenhuma denúncia.</div>
<?php else: ?>
<?php while($d = $denuncias->fetch_assoc()): 
    $descricao = $d['descricao'] ?? '';
?>
<div class="card-denuncia">
    <div class="user-post">
        <img src="<?php echo $foto_perfil; ?>" alt="Perfil">
        <strong><?php echo htmlspecialchars($user_data['usuario']); ?></strong>
    </div>

    <h5><?php echo htmlspecialchars($d["titulo"]); ?></h5>
    <br>
    <p><b>Local:</b> <?php echo htmlspecialchars($d["localizacao"] ?? 'Não informado'); ?></p>
    <p><b>Data:</b> <?php echo $d["data_envio"]; ?></p>

    <?php if(!empty($descricao)): ?>
        <p class="descricao-preview" id="desc-<?php echo $d['id']; ?>">
            <?php echo htmlspecialchars($d['descricao']); ?> 
        </p>
        <span id="btn-desc-<?php echo $d['id']; ?>" style="color:var(--cor-principal); cursor:pointer; font-weight:bold;" onclick="toggleDescricao(<?php echo $d['id']; ?>)">
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

<footer class="footer-info">
  <small>Precisa de ajuda? Contate: <a href="mailto:suporte.urbanafix@gmail.com">suporte.urbanafix@gmail.com</a></small><br>
  <small>© 2025 UrbanaFix — Todos os direitos reservados.</small>
</footer>

<script>
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
</script>

<!-- Modal de zoom (lightbox) -->
<script>
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
