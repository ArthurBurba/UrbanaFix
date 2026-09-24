<?php
session_start();
$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$dbname = "urbanafix";
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $conn->real_escape_string($_POST["email"]);
    $senha = $conn->real_escape_string($_POST["senha"]);

    $sql = "SELECT * FROM usuarios WHERE email='$email' AND senha='$senha' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $_SESSION["usuario"] = $email;
        header("Location: feed.php");
        exit;
    } else {
        $erro = "E-mail ou senha inválidos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Login — UrbanaFix</title>
<link rel="icon" type="image/x-icon" href="https://i.ibb.co/zW4c49qc/larakja.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/CSS/style.css">

<style>
body {
  background-color: #fff;
  font-family: 'Segoe UI', sans-serif;
  color: #333;
}

.logo {
  max-height: 100px;
}

.titulo {
  color: #ff7a00;
  font-weight: 700;
  letter-spacing: 8px;
  font-size: 2.5rem;
  margin-top: 1rem;
}

.login-container {
  max-width: 420px;
  margin: 3rem auto;
  background: #fff;
  border: 1px solid #eee;
  border-radius: 12px;
  box-shadow: 0 6px 16px rgba(0,0,0,0.08);
  padding: 2.5rem;
  text-align: center;
}

.login-container h5 {
  color: #ff7a00;
  font-weight: 700;
  margin-bottom: 1.5rem;
  font-size: 1.8rem;
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

.btn-laranja {
  background-color: #ff7a00;
  color: white;
  font-weight: bold;
  border: none;
  border-radius: 8px;
  padding: 0.8rem 1.5rem;
  width: 100%;
  transition: all 0.2s ease;
}

.btn-laranja:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 12px rgba(255,122,0,0.4);
}

.erro {
  color: red;
  font-weight: bold;
  margin-bottom: 1rem;
}

a.link-cadastro {
    display: inline-block;
    margin-top: 1rem;
    color: #ff7a00;
    text-decoration: none;
    font-weight: bold;
    font-size: 1rem;
    transition: all 0.3s ease;
}

a.link-cadastro:hover {
    color: #ffb366;
}

footer {
  text-align: center;
  margin-top: 3rem;
  padding: 1rem 0;
  color: #555;
  font-size: 0.9rem;
}

footer small {
  display: block;
  margin-top: 0.5rem;
}
.support-email {
      color: #ff7a00;
      font-weight: 600;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .support-email:hover {
      color: #e86d00;
      text-decoration: underline;
    }
</style>
</head>

<body>
  <header class="text-center mt-5">
    <img src="https://i.ibb.co/9Hp1cgbM/Lminilogo.png" alt="UrbanaFix" class="logo">
    <h1 class="titulo">URBANAFIX</h1>
  </header>

  <main>
    <div class="login-container">
      <h5>Login</h5>
      <?php if (!empty($erro)): ?>
        <p class="erro"><?php echo $erro; ?></p>
      <?php endif; ?>

      <form method="POST">
        <input class="form-control" type="email" name="email" placeholder="E-mail" required>
        <input class="form-control" type="password" name="senha" placeholder="Senha" required>
        <button type="submit" class="btn-laranja">Entrar</button>
      </form>

      <!-- Link para cadastro -->
      <a href="cadastro.php" class="link-cadastro">Não tenho conta</a>
    </div>
  </main>

  <footer>
    <small>Precisa de ajuda? Entre em contato: <a href="mailto:suporte.urbanafix@gmail.com" class="support-email">suporte.urbanafix@gmail.com</a></small>
    <small>© 2025 UrbanaFix — Todos os direitos reservados.</small>
  </footer>
</body>
</html>
