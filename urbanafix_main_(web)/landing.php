<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>UrbanaFix — Plataforma Cidadã</title>
  <link rel="icon" type="image/x-icon" href="https://i.ibb.co/zW4c49qc/larakja.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">

  <style>
    /* Card ocupa 100% da largura do container */
    .intro .card {
      width: 100%;
      padding: 2rem;
      background: #ffffff;
      border: 1px solid #e6e6e6;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      margin-bottom: 3rem;
    }

    /* Logo */
    .logo {
      max-height: 120px; /* tamanho máximo */
    }

    .titulo {
        font-family: sans-serif;
        font-weight: 700; /* bold */
        font-size: 2.5rem;
        color: #ff7a00;
        letter-spacing: 10px;
        margin-top: 1rem;
        text-align: center;
    }
    /* Títulos das seções dentro do card */
    .section-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: #ff7a00;
      margin-bottom: 1rem;
      text-align: center;
    }

    .divider-wrapper {
      display: flex;
      justify-content: center; /* centraliza horizontalmente */
      margin: 1rem 0 1.5rem 0; /* espaçamento em cima e embaixo */
    }

    .divider {
      width: 80px;
      height: 4px;
      background-color: #ff7a00;
      border: none;
      border-radius: 2px;
    }

    /* Descrições maiores */
    .lead-description {
      font-size: 1.2rem;
      line-height: 1.8;
      color: #333;
      text-align: left;
    }

    /* CTA final gigante */
    .cta-text {
      font-size: 3rem;
      font-weight: 800;
      color: #212529;
      text-align: center;
      margin-top: 2rem;
      margin-bottom: 2rem;
    }

    /* Estilo para a pergunta da pesquisa de campo */
    .dados-section h5 {
      font-size: 2rem;
      font-weight: 700;
      color: #212529;
      text-align: center;
      margin-bottom: 1.5rem;
    }

    /* Estilo dos resultados da pesquisa */
    .dados-texto p {
      font-size: 1.1rem;
      font-weight: 600;
      color: #212529;
    }

    .percent {
      font-size: 1.2rem;
      color: #ff7a00; /* cor laranja para os números */
      font-weight: 700;
    }

    /* Tamanho do gráfico */
    .dados-grafico canvas {
      max-width: 400px;
      max-height: 400px;
      margin: 0 auto;
    }

    /* Botão final */
    .btn-laranja {
      background-color: #ff7a00;
      color: white;
      font-weight: 700;
      padding: 15px 25px;
      border-radius: 8px;
      border: none;
      font-size: 1.2rem;
      cursor: pointer;
      margin-top: 20px;
      transition: all 0.3s ease;
      text-decoration: none;
  
    }

    .btn-laranja:hover {
      background-color: #e86d00;
      transform: translateY(-4px);
      box-shadow: 0 8px 15px rgba(0,0,0,0.3);
    }

    /* Email de suporte */
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

    /* Dados grid */
    .dados-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      gap: 2rem;
    }

    .dados-texto, .dados-grafico {
      flex: 1;
      min-width: 250px;
    }

    
  </style>
</head>

<body>
  <header class="text-center mt-5">
    <img src="https://i.ibb.co/9Hp1cgbM/Lminilogo.png" alt="UrbanaFix" class="logo">
    <h1 class="titulo">UrbanaFix</h1>
    <br>
    <br>
    <br>
    <br>
  </header>

  <main>
    <section class="intro">
      <div class="card">
        <h2 class="section-title">O que somos</h2>
        <div class="divider-wrapper"><hr class="divider"></div>

        <p class="lead-description">
          A <strong>UrbanaFix</strong> é uma plataforma inovadora que conecta cidadãos diretamente à gestão pública.
          Cada usuário pode reportar problemas urbanos em tempo real — desde buracos, iluminação pública deficiente,
          riscos de segurança, até questões ambientais e de infraestrutura.
          Nosso objetivo é criar um canal transparente e eficiente, aproximando a população das decisões que impactam sua cidade.
        </p>

        <div class="divider-wrapper"><hr class="divider"></div>

        <h2 class="section-title">Nosso propósito</h2>
        <div class="divider-wrapper"><hr class="divider"></div>

        <p class="lead-description">
          Nosso propósito é fortalecer a <strong>participação cidadã</strong> e construir cidades mais eficientes, seguras e humanas.
          Acreditamos que cada cidadão tem o poder de transformar o ambiente urbano em que vive.
          Por meio da tecnologia, buscamos integrar a comunidade e a gestão pública, oferecendo ferramentas que incentivem
          a colaboração e a resolução de problemas urbanos de forma rápida e transparente.
        </p>

        <div class="divider-wrapper"><hr class="divider"></div>

        <p class="cta-text"><strong>Junte-se a nós e faça parte da transformação urbana!</strong></p>
      </div>
    </section>

    <section class="dados-section" id="dados">
      <div class="container">
        <h5>Qual tipo de violência mais te preocupa atualmente?</h5>
        <div class="divider-wrapper"><hr class="divider"></div>

        <div class="dados-grid">
          <div class="dados-texto">
            <p><span class="percent" data-value="35.9">0%</span> violência urbana (assaltos, furtos, etc.)</p>
            <p><span class="percent" data-value="19.5">0%</span> violência doméstica</p>
            <p><span class="percent" data-value="12.3">0%</span> violência contra jovens</p>
            <p><span class="percent" data-value="32.3">0%</span> todas as anteriores</p>
          </div>

          <div class="dados-grafico">
            <canvas id="grafico"></canvas>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="text-center py-4">
    <a href="login.php" class="btn-laranja">Colabore com a segurança</a>
    <br>
    <br>
    <div class="mt-3">
      <small>teve algum problema? - nosso suporte:
        <a href="mailto:suporte.urbanafix@gmail.com" class="support-email">suporte.urbanafix@gmail.com</a>
      </small>
      <br>
    </div>
    <small class="d-block mt-2">© 2025 UrbanaFix — Todos os direitos reservados.</small>
  </footer>

  <script src="assets/JS/theme.js"></script>
</body>
</html>
