<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$mensagem = "";

// Conexão com o banco
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "urbanafix";
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    $mensagem = "Erro ao conectar ao banco de dados.";
} else {
    $usuario_email = $_SESSION["usuario"];
    $user_res = $conn->query("SELECT id FROM usuarios WHERE email='$usuario_email'");
    $user_data = $user_res->fetch_assoc();
    $id_usuario = $user_data['id'] ?? null;

    if (!$id_usuario) {
        die("Erro: usuário não encontrado.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $titulo = $conn->real_escape_string($_POST['titulo'] ?? '');
        $descricao = $conn->real_escape_string($_POST['descricao'] ?? '');
        $localizacao = $conn->real_escape_string($_POST['localizacao'] ?? '');
        $latitude = $_POST['latitude'] ?? '';
        $longitude = $_POST['longitude'] ?? '';
        $peso = $conn->real_escape_string($_POST['peso'] ?? 'pequeno');
        $status = "em andamento"; // Padrão
        $midia_nome = '';
        $tipo_midia = '';

        if (empty($peso)) {
            $mensagem = "Por favor, selecione o peso da denúncia antes de enviar.";
        } else {
            if (!empty($_FILES['midia']['name'])) {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $midia_nome = time() . '_' . basename($_FILES['midia']['name']);
                $ext = strtolower(pathinfo($midia_nome, PATHINFO_EXTENSION));
                move_uploaded_file($_FILES['midia']['tmp_name'], $upload_dir . $midia_nome);
                $tipo_midia = in_array($ext, ['mp4','webm','ogg']) ? 'video' : 'imagem';
            }

            $sql = "INSERT INTO denuncias 
                    (id_usuario, titulo, descricao, localizacao, midia, tipo_midia, latitude, longitude, status, peso, data_envio)
                    VALUES 
                    ($id_usuario, '$titulo', '$descricao', '$localizacao', '$midia_nome', '$tipo_midia', '$latitude', '$longitude', '$status', '$peso', NOW())";

            if ($conn->query($sql)) {
                header("Location: feed.php");
                exit;
            } else {
                $mensagem = "Erro ao enviar denúncia: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Adicionar Denúncia — UrbanaFix</title>
<link rel="icon" type="image/x-icon" href="https://i.ibb.co/zW4c49qc/larakja.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    color: #333;
    background: url('https://i.ibb.co/Gf62St40/dddd.png') no-repeat center center fixed;
    background-size: cover;
    background-attachment: fixed;
}
.form-container {
  max-width: 650px;
  margin: 3rem auto;
  background: #fff;
  border: 1px solid #eee;
  border-radius: 12px;
  box-shadow: 0 6px 16px rgba(0,0,0,0.08);
  padding: 2.5rem;
}
.form-container h5 {
  color: #ff7a00;
  font-weight: 700;
  margin-bottom: 1.5rem;
  text-align: center;
  font-size: 1.8rem;
}
.form-control, select {
  border-radius: 8px;
  border: 1px solid #ccc;
  padding: 0.8rem;
  font-size: 1rem;
  margin-bottom: 1rem;
  transition: 0.2s;
  width: 100%;
  appearance: none;
  background-color: #fff;
}
.form-control:focus, select:focus {
  border-color: #ff7a00;
  box-shadow: 0 0 0 0.2rem rgba(255,122,0,0.25);
  outline: none;
}
textarea.form-control { resize: none; min-height: 120px; }
.character-counter { text-align: right; font-size: 0.85rem; color: #555; margin-top: -0.8rem; margin-bottom: 1rem; }
.btn-laranja {
  background-color: #ff7a00;
  color: white;
  font-weight: bold;
  border: none;
  border-radius: 8px;
  padding: 0.8rem 1.5rem;
  width: 100%;
  transition: all 0.2s ease;
  margin-top: 0.5rem;
}
.btn-laranja:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 12px rgba(255,122,0,0.4);
}
#map { height: 350px; border-radius: 12px; margin-bottom: 1rem; }
#resultadosBusca {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #ccc;
  border-radius: 8px;
  margin-top: 0.5rem;
  padding: 0.5rem;
  display: none;
  background: #fff;
}
.resultadoItem {
  padding: 10px 12px;
  border-bottom: 1px solid #eee;
  cursor: pointer;
  transition: background 0.2s, transform 0.1s;
}
.resultadoItem:hover {
  background: #fff4e6;
  transform: scale(1.01);
}
.erro { color: red; font-weight: bold; text-align: center; margin-bottom: 1rem; }
footer { text-align: center; margin-top: 3rem; padding: 1rem 0; color: #555; font-size: 0.9rem; }
footer small { display: block; margin-top: 0.5rem; }
.support-email { color: #ff7a00; font-weight: 600; text-decoration: none; transition: color 0.3s ease; }
.support-email:hover { color: #e86d00; text-decoration: underline; }
</style>
</head>

<body>

<main>
<div class="form-container">
  <h5>Adicionar Denúncia</h5>

  <?php if (!empty($mensagem)): ?>
    <p class="erro"><?php echo $mensagem; ?></p>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <input type="text" name="titulo" class="form-control" placeholder="Título" required>
    <textarea name="descricao" id="descricao" class="form-control" placeholder="Descrição da denúncia" maxlength="700" required></textarea>
    <div class="character-counter"><span id="contador">700</span> caracteres restantes</div>

    <label for="peso" style="font-weight:600; color:#333;">Peso da denúncia:</label>
    <select name="peso" class="form-control" required>
      <option value="">Selecione o peso...</option>
      <option value="pequeno">Pequeno</option>
      <option value="médio">Médio</option>
      <option value="grande">Grande</option>
    </select>

    <label for="buscaEndereco" style="font-weight:600; color:#333;">Pesquisa de endereço:</label>
    <input type="text" id="buscaEndereco" class="form-control" placeholder="Pesquisar endereço...">
    <button type="button" id="btnBuscar" class="btn-laranja" style="margin-bottom:0.5rem;">Buscar</button>
    <div id="resultadosBusca"></div>

    <input type="text" name="localizacao" id="localizacao" class="form-control" placeholder="Localização selecionada" required>

    <label for="midia" style="font-weight:600; color:#333;">Adicionar foto/vídeo:</label>
    <input type="file" name="midia" class="form-control" accept="image/*,video/*">

    <div id="map"></div>
    <input type="hidden" name="latitude" id="latitude">
    <input type="hidden" name="longitude" id="longitude">

    <button type="submit" class="btn-laranja">Enviar Denúncia</button>
    <button type="button" class="btn-laranja" onclick="location.href='feed.php'">Voltar</button>
  </form>
</div>
</main>

<footer style="color:white;">
  <small>Precisa de ajuda? Entre em contato: <a class="support-email" href="mailto:suporte.urbanafix@gmail.com">suporte.urbanafix@gmail.com</a></small>
  <small>© 2025 UrbanaFix — Todos os direitos reservados.</small>
</footer>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const latInicial = -23.622065;
  const lonInicial = -46.532169;

  const map = L.map('map').setView([latInicial, lonInicial], 16);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap' }).addTo(map);

  const marker = L.marker([latInicial, lonInicial], { draggable:true }).addTo(map);
  document.getElementById('latitude').value = latInicial;
  document.getElementById('longitude').value = lonInicial;

  marker.on('dragend', function() {
    const pos = marker.getLatLng();
    document.getElementById('latitude').value = pos.lat;
    document.getElementById('longitude').value = pos.lng;
  });

  const textarea = document.getElementById('descricao');
  const contador = document.getElementById('contador');
  textarea.addEventListener('input', () => {
    contador.textContent = 700 - textarea.value.length;
  });

  const btnBuscar = document.getElementById('btnBuscar');
  const buscaInput = document.getElementById('buscaEndereco');
  const resultadosDiv = document.getElementById('resultadosBusca');
  const localizacaoInput = document.getElementById('localizacao');

  btnBuscar.addEventListener('click', async () => {
    const query = buscaInput.value.trim();
    if (!query) return;
    resultadosDiv.innerHTML = '';
    resultadosDiv.style.display = 'block';

    try {
      const url = `buscar_endereco.php?q=${encodeURIComponent(query)}`;
      const res = await fetch(url);
      const data = await res.json();

      if (data.length === 0) {
        resultadosDiv.innerHTML = '<div>Nenhum resultado encontrado.</div>';
        return;
      }

      data.forEach(item => {
        const div = document.createElement('div');
        div.classList.add('resultadoItem');
        const nomePrincipal = item.display_name.split(',')[0];
        const resto = item.display_name.replace(nomePrincipal + ',', '').trim();

        div.innerHTML = `
          <div style="display:flex; align-items:center; gap:8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#ff7a00" viewBox="0 0 24 24">
              <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 
              9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 
              2.5 2.5S13.38 11.5 12 11.5z"/>
            </svg>
            <div>
              <div style="font-weight:600; color:#333;">${nomePrincipal}</div>
              <div style="font-size:0.85rem; color:#666;">${resto}</div>
            </div>
          </div>
        `;

        div.addEventListener('click', () => {
          const lat = parseFloat(item.lat);
          const lon = parseFloat(item.lon);
          map.setView([lat, lon], 17);
          marker.setLatLng([lat, lon]);
          document.getElementById('latitude').value = lat;
          document.getElementById('longitude').value = lon;
          localizacaoInput.value = item.display_name;
          resultadosDiv.style.display = 'none';
        });

        resultadosDiv.appendChild(div);
      });
    } catch (err) {
      console.error(err);
      resultadosDiv.innerHTML = '<div>Erro ao buscar endereço.</div>';
    }
  });
});
</script>

</body>
</html>
