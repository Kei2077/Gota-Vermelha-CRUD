<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexao.php';

$stmt = $conn->prepare("SELECT nome, email, telefone, cpf FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$conn->close();

if (!$usuario) {
    die("Erro ao buscar dados do usuário.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agendar Doação - Gota Vermelha</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --cor-primaria: #E2230A;
      --cor-primaria-hover: #C41E0A;
      --cor-fundo: linear-gradient(135deg, #F9F9F9 0%, #F3F4F6 100%);
      --cor-card: rgba(255, 255, 255, 0.95);
      --cor-texto: #1F2937;
      --cor-texto-secundario: #6B7280;
      --cor-borda: #E5E7EB;
      --cor-sucesso: #10B981;
      --cor-erro: #EF4444;
      --sombra: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
      --raio-borda: 16px;
      --transicao: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: var(--cor-fundo);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden; 
    }
    header {
      background: var(--cor-card);
      box-shadow: var(--sombra);
      padding: 20px 0;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    nav {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .logo {
      font-size: 20px;
      font-weight: 800;
      color: var(--cor-primaria);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .nav-links {
      display: flex;
      list-style: none;
      gap: 24px;
    }
    .nav-links a {
      color: var(--cor-texto);
      text-decoration: none;
      font-weight: 500;
      font-size: 14px;
      padding: 8px 12px;
      border-radius: 6px;
      transition: var(--transicao);
    }
    .nav-links a:hover {
      background: rgba(226, 35, 10, 0.05);
      color: var(--cor-primaria);
    }
    .mobile-menu { display: none; font-size: 24px; background: none; border: none; cursor: pointer; }

    main {
      flex: 1;
      max-width: 800px;
      width: 100%;
      margin: 40px auto;
      padding: 0 24px;
    }
    .section-title {
      font-size: 32px;
      font-weight: 700;
      color: var(--cor-primaria);
      margin-bottom: 32px;
      text-align: center;
    }
    .card {
      background: var(--cor-card);
      backdrop-filter: blur(10px);
      border-radius: var(--raio-borda);
      box-shadow: var(--sombra);
      padding: 40px;
      border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .alert {
      padding: 16px 20px;
      border-radius: 10px;
      margin-bottom: 24px;
      font-size: 14px;
      font-weight: 500;
      display: none;
    }
    .alert.show { display: block; }
    .alert-success {
      background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
      color: #065F46;
      border: 1px solid var(--cor-sucesso);
    }
    .alert-error {
      background: linear-gradient(135deg, #FEE2E2, #FECACA);
      color: #991B1B;
      border: 1px solid var(--cor-erro);
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }
    .form-group { 
      display: flex; 
      flex-direction: column; 
      min-width: 0;
    }
    label {
      font-size: 14px;
      font-weight: 500;
      color: var(--cor-texto);
      margin-bottom: 8px;
    }
    input, select {
      padding: 14px 16px;
      border: 2px solid var(--cor-borda);
      border-radius: 10px;
      font-size: 16px;
      font-family: inherit;
      transition: var(--transicao);
      background: #F9FAFB;
      width: 100%; 
      max-width: 100%; 
    }
    input:focus, select:focus {
      outline: none;
      border-color: var(--cor-primaria);
      background: var(--cor-card);
      box-shadow: 0 0 0 3px rgba(226, 35, 10, 0.1);
    }
    input::placeholder { color: #9CA3AF; }
    input[readonly] {
      background: #F3F4F6;
      color: var(--cor-texto-secundario);
      cursor: not-allowed;
    }

    .btn-primary {
      width: 100%;
      padding: 16px;
      background: linear-gradient(135deg, var(--cor-primaria), var(--cor-primaria-hover));
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transicao);
      box-shadow: 0 4px 6px rgba(226, 35, 10, 0.15);
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(226, 35, 10, 0.25);
    }
    .btn-primary:active { transform: translateY(0); }

    footer {
      background: var(--cor-texto);
      color: white;
      padding: 40px 0;
      margin-top: 60px;
    }
    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 24px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 32px;
    }
    .footer-section h3 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 16px;
      color: #F9FAFB;
    }
    .footer-section ul {
      list-style: none;
      font-size: 14px;
      line-height: 1.8;
      color: #D1D5DB;
    }
    .footer-bottom {
      max-width: 1200px;
      margin: 0 auto;
      padding: 24px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      margin-top: 32px;
      text-align: center;
      font-size: 13px;
      color: #9CA3AF;
    }
    .footer-bottom p { margin: 4px 0; }

    
    @media (max-width: 768px) {
      .nav-links {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--cor-card);
        flex-direction: column;
        padding: 16px;
        box-shadow: var(--sombra);
      }
      .nav-links.show { display: flex; }
      .mobile-menu { display: block; }
      
      
      main {
        margin: 20px auto;
        padding: 0 16px; 
      }
      
      .section-title { font-size: 24px; }
      .card { padding: 20px; } 
      
      .grid {
        grid-template-columns: 1fr; 
        gap: 16px; 
      }
      
   
      input, select {
        padding: 12px 14px;
        font-size: 16px; 
        width: 100% !important; 
      }
      
      
      .form-group {
        width: 100%;
        overflow: hidden; 
      }
    }
  </style>
</head>
<body>
  <header>
    <nav>
      <div class="logo">🩸 GOTA VERMELHA</div>
      <ul class="nav-links" id="navLinks">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="logout.php">Sair</a></li>
      </ul>
      <button class="mobile-menu" id="mobileMenu">☰</button>
    </nav>
  </header>

  <main>
    <h1 class="section-title">Agende Sua Doação</h1>
    <div class="card">
      <div id="alertBox" class="alert"></div>
      
      <form id="agendamentoForm" action="./processaagendamento.php" method="POST">
        <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['usuario_id']; ?>">
        
        <div class="grid">
          <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($usuario['nome']); ?>" readonly>
          </div>
          <div class="form-group">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($usuario['email']); ?>">
          </div>
          <div class="form-group">
            <label for="telefone">Telefone *</label>
            <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required value="<?php echo htmlspecialchars($usuario['telefone']); ?>">
          </div>
          <div class="form-group">
            <label for="cpf">CPF *</label>
            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required value="<?php echo htmlspecialchars($usuario['cpf']); ?>" readonly>
          </div>
        </div>
        
        <div class="grid">
          <div class="form-group">
            <label for="unidade">Unidade *</label>
            <select id="unidade" name="unidade" required>
              <option value="">Selecione uma unidade</option>
              <option value="CHHMC (Centro de Hematologia e Hemoterapia de Mogi das Cruzes)">Centro de Hematologia e Hemoterapia de Mogi das Cruzes - R. Ipiranga, 797 - Jardim Santista, Mogi das Cruzes - SP</option>
              <option value="SCMMC (Santa Casa de Misericórdia de Mogi das Cruzes)">Santa Casa de Misericórdia de Mogi das Cruzes - R. Barão de Jaceguai, 1148 - Centro, Mogi das Cruzes - SP</option>
              <option value="LMED (Laboratório Municipal de Exames Diagnósticos)">Laboratório Municipal de Exames Diagnósticos - Av. Cap. Manoel Rudge, 272 - Parque Monte Libano, Mogi das Cruzes - SP</option>
              <option value="CentrhHHMC (Centrho de Hematologia e Hemoterapia de Mogi das Cruzes)">Centrho de Hematologia e Hemoterapia de Mogi das Cruzes - R. Dona Afife Nassif Jafet, 223 - Vila Industrial, Mogi das Cruzes - SP</option>
            </select>
          </div>
          <div class="form-group">
            <label for="data">Data *</label>
            <input type="date" id="data" name="data" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
          </div>
          <div class="form-group">
            <label for="hora">Horário *</label>
            <select id="hora" name="hora" required>
              <option value="">Selecione um horário</option>
              <option value="08:00">08:00</option>
              <option value="09:00">09:00</option>
              <option value="10:00">10:00</option>
              <option value="11:00">11:00</option>
              <option value="14:00">14:00</option>
              <option value="15:00">15:00</option>
              <option value="16:00">16:00</option>
              <option value="17:00">17:00</option>
            </select>
          </div>
        </div>

        <button type="submit" class="btn-primary">Confirmar Agendamento</button>
      </form>
    </div>
  </main>

  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h3>Gota Vermelha</h3>
        <p>Salvando vidas através da solidariedade e do compromisso com a saúde da nossa comunidade.</p>
      </div>
      <div class="footer-section">
        <h3>Informações</h3>
        <ul>
          <li>📍 Av. Paulista, 1000 - São Paulo</li>
          <li>📞 (11) 3333-4000</li>
          <li>📧 contato@gotavermelha.org.br</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 Gota Vermelha. Todos os direitos reservados.</p>
    </div>
  </footer>

    <script>
        document.getElementById('mobileMenu').addEventListener('click', function() {
        document.getElementById('navLinks').classList.toggle('show');
        });

        document.getElementById('telefone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 10) {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (value.length > 6) {
            value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/(\d{2})(\d{4})/, '($1) $2');
        }
        e.target.value = value;
        });

        <?php if (isset($_SESSION['agendamento_msg'])): ?>
        document.getElementById('alertBox').classList.add('show', 'alert-<?php echo $_SESSION['agendamento_tipo']; ?>');
  
        const mensagem = <?php echo json_encode($_SESSION['agendamento_msg']); ?>;
        document.getElementById('alertBox').textContent = mensagem;
        <?php unset($_SESSION['agendamento_msg'], $_SESSION['agendamento_tipo']); ?>
        <?php endif; ?>
  </script>
</body>
</html>