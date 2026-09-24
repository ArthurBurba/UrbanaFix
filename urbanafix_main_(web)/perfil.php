<?php
session_start();

// --------------------
// Conexão direta
// --------------------
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "urbanafix";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// --------------------
// Verifica login
// --------------------
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Pega ID do usuário
$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    $usuario_email = $_SESSION['usuario'];
    $res = $conn->query("SELECT id FROM usuarios WHERE email='$usuario_email' LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        $id_usuario = $row['id'];
    } else {
        header('Location: login.php');
        exit();
    }
}

// --------------------
// Atualização de perfil
// --------------------
if (isset($_POST['atualizar'])) {
    $novo_nome = mysqli_real_escape_string($conn, $_POST['usuario']);
    if (preg_match('/^[A-Za-z0-9._-]+$/', $novo_nome)) {
        $conn->query("UPDATE usuarios SET usuario='$novo_nome' WHERE id='$id_usuario'");
    }

    if (!empty($_FILES['foto']['name'])) {
        $pasta = "pfp/";
        $nome_arquivo = uniqid() . "_" . basename($_FILES['foto']['name']);
        $caminho = $pasta . $nome_arquivo;
        if (!is_dir($pasta)) mkdir($pasta, 0777, true);
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho)) {
            $conn->query("UPDATE usuarios SET foto_perfil='$caminho' WHERE id='$id_usuario'");
        }
    }

    header("Location: perfil.php");
    exit();
}

// --------------------
// Pega dados do usuário
// --------------------
$sql = "SELECT usuario, foto_perfil FROM usuarios WHERE id='$id_usuario' LIMIT 1";
$result = $conn->query($sql);
$usuario = $result->fetch_assoc();
$foto_perfil = $usuario['foto_perfil'] ?? 'https://i.ibb.co/cSRyKKDZ/LALAL.png';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil — UrbanaFix</title>
<link rel="icon" type="image/x-icon" href="https://i.ibb.co/zW4c49qc/larakja.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: url('https://i.ibb.co/JFGSBLNz/AGORA.jpg') no-repeat center center fixed;
    background-size: cover;
    color: #333;
}

.perfil-container {
    max-width: 420px;
    margin: 3rem auto;
    background: #fff;
    border-radius: 12px;
    padding: 2.5rem;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    text-align: center;
}

.perfil-container img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ff7a00;
    margin-bottom: 1rem;
}

.perfil-container h2 {
    color: #ff7a00;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.form-control {
    border-radius: 8px;
    border: 1px solid #ccc;
    padding: 0.8rem;
    font-size: 1rem;
    margin-bottom: 1rem;
    transition: 0.2s;
}

.form-control:focus {
    border-color: #ff7a00;
    box-shadow: 0 0 0 0.2rem rgba(255,122,0,0.25);
}

.btn-urbana {
    background-color: #ff7a00;
    color: white;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    padding: 0.8rem 1.5rem;
    width: 100%;
    margin-bottom: 1rem;
    transition: all 0.2s ease;
}

.btn-urbana:hover {
    background-color: #ffb366;
}

.btn-voltar {
    display: block;
    background-color: #fff;
    color: #ff7a00;
    border: 1px solid #ff7a00;
    font-weight: bold;
    border-radius: 8px;
    padding: 0.8rem 1.5rem;
    width: 100%;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s ease;
}

.btn-voltar:hover {
    background-color: #ff7a00;
    color: #fff;
}

.logout-btn {
    padding: 6px 12px;
    border-radius: 12px;
    border: none;
    font-weight: bold;
    background-color: #ff6f00;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    position: absolute;
    top: 20px;
    right: 20px;
}

.logout-btn:hover {
    background-color: #ffb366;
}
</style>
</head>
<body>

<button class="logout-btn" onclick="confirmSair()">Sair</button>

<div class="perfil-container">
    <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil">
    <h2>@<?php echo htmlspecialchars($usuario['usuario']); ?></h2>

    <form method="POST" enctype="multipart/form-data">
        <input class="form-control" type="text" name="usuario" placeholder="Novo nome de usuário">
        <input class="form-control" type="file" name="foto" accept="image/*">
        <button type="submit" name="atualizar" class="btn-urbana">Atualizar Perfil</button>
    </form>

    <a href="feed.php" class="btn-voltar">⬅ Voltar ao Feed</a>
</div>

<script>
function confirmSair() {
    if (confirm("Tem certeza que deseja sair?")) {
        window.location.href = 'login.php';
    }
}
</script>

</body>
</html>
