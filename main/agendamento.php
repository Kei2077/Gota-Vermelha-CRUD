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
  <link rel="stylesheet" href="styleagendamento.css">
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