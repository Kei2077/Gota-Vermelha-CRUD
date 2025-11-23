<?php
session_start();

require_once 'conexao.php';

$msg_sucesso = '';
$msg_erro = '';

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $cpf = preg_replace('/\D/', '', $_POST['cpf']);
    $data_nascimento = $_POST['data_nascimento'];
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $tipo_sanguineo = $_POST['tipo_sanguineo'];
    $fator_rh = $_POST['fator_rh'];
    $telefone = preg_replace('/\D/', '', $_POST['telefone']);
    $endereco = trim($_POST['endereco']);

    // Validações
    if (empty($nome) || empty($cpf) || empty($data_nascimento) || empty($email) || 
        empty($senha) || empty($telefone) || empty($endereco)) {
        $msg_erro = "⚠️ Todos os campos são obrigatórios!";
    } elseif (strlen($cpf) != 11) {
        $msg_erro = "❌ CPF deve conter 11 dígitos!";
    } elseif (strlen($telefone) < 10 || strlen($telefone) > 11) {
        $msg_erro = "❌ Telefone inválido!";
    } elseif (strlen($senha) < 6) {
        $msg_erro = "⚠️ A senha deve ter no mínimo 6 caracteres!";
    } else {
        // Verifica CPF e email únicos
        $check_sql = "SELECT id FROM usuarios WHERE cpf = ? OR email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $cpf, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $msg_erro = "❌ CPF ou Email já cadastrados no sistema!";
        } else {
            // Insere usuário
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO usuarios 
                           (nome, cpf, data_nascimento, email, senha, tipo_sanguineo, fator_rh, telefone, endereco) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sssssssss", $nome, $cpf, $data_nascimento, $email, 
                              $senha_hash, $tipo_sanguineo, $fator_rh, $telefone, $endereco);

            if ($stmt->execute()) {
                $msg_sucesso = "✅ Usuário cadastrado com sucesso!";
                // Limpa formulário
                $_POST = [];
            } else {
                $msg_erro = "❌ Erro ao cadastrar: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Novo Usuário - Gota Vermelha ADM</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --cor-adm: #8B5CF6;
      --cor-adm-hover: #7C3AED;
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
      font-family: 'Inter', sans-serif;
      background: var(--cor-fundo);
      min-height: 100vh;
      padding: 20px;
      color: var(--cor-texto);
    }
    .container {
      max-width: 900px;
      margin: 0 auto;
    }
    .card {
      background: var(--cor-card);
      backdrop-filter: blur(10px);
      border-radius: var(--raio-borda);
      box-shadow: var(--sombra);
      padding: 32px;
      border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 32px;
      background: linear-gradient(135deg, var(--cor-adm), var(--cor-adm-hover));
      padding: 24px 32px;
      border-radius: var(--raio-borda);
      color: white;
    }
    .logo {
      font-size: 24px;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .logo::before { content: "🩸"; font-size: 28px; }
    h1 { font-size: 24px; font-weight: 700; }
    h2 {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 24px;
      color: var(--cor-adm);
    }
    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      transition: var(--transicao);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .btn-salvar {
      background: linear-gradient(135deg, var(--cor-sucesso), #059669);
      color: white;
    }
    .btn-salvar:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(16, 185, 129, 0.25);
    }
    .btn-cancelar {
      background: linear-gradient(135deg, var(--cor-texto-secundario), #4B5563);
      color: white;
    }
    .btn-voltar {
      background: white;
      color: var(--cor-adm);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .btn-voltar:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .form-group {
      margin-bottom: 20px;
    }
    label {
      display: block;
      font-weight: 600;
      font-size: 14px;
      margin-bottom: 8px;
      color: var(--cor-texto);
    }
    input, select {
      width: 100%;
      padding: 14px;
      border: 2px solid var(--cor-borda);
      border-radius: 10px;
      font-size: 14px;
      transition: var(--transicao);
      font-family: 'Inter', sans-serif;
    }
    input:focus, select:focus {
      outline: none;
      border-color: var(--cor-adm);
      box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }
    .form-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }
    .alert {
      padding: 16px;
      border-radius: 10px;
      margin-bottom: 24px;
      font-size: 14px;
      font-weight: 500;
    }
    .alert-success {
      background: rgba(16, 185, 129, 0.1);
      color: var(--cor-sucesso);
      border: 1px solid rgba(16, 185, 129, 0.3);
    }
    .alert-error {
      background: rgba(239, 68, 68, 0.1);
      color: var(--cor-erro);
      border: 1px solid rgba(239, 68, 68, 0.3);
    }
    .form-actions {
      display: flex;
      gap: 12px;
      margin-top: 32px;
      flex-wrap: wrap;
    }
    @media (max-width: 768px) {
      .form-actions { flex-direction: column; }
      .btn { width: 100%; justify-content: center; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="logo">NOVO USUÁRIO</div>
      <button class="btn btn-voltar" onclick="window.location.href='dashboardADM.php'">
        <span>←</span> Voltar ao Dashboard
      </button>
    </div>

    <div class="card">
      <h2>👤 Cadastro de Novo Doador</h2>

      <?php if ($msg_sucesso): ?>
        <div class="alert alert-success">
          <?php echo $msg_sucesso; ?>
        </div>
      <?php endif; ?>

      <?php if ($msg_erro): ?>
        <div class="alert alert-error">
          <?php echo $msg_erro; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-row">
          <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" 
                   placeholder="João da Silva" required>
          </div>
          <div class="form-group">
            <label for="cpf">CPF *</label>
            <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($_POST['cpf'] ?? ''); ?>" 
                   placeholder="12345678900" maxlength="11" required>
            <small style="color: var(--cor-texto-secundario);">Apenas números</small>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="data_nascimento">Data de Nascimento *</label>
            <input type="date" id="data_nascimento" name="data_nascimento" 
                   value="<?php echo htmlspecialchars($_POST['data_nascimento'] ?? ''); ?>" required>
          </div>
          <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                   placeholder="joao@exemplo.com" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="senha">Senha *</label>
            <input type="password" id="senha" name="senha" 
                   placeholder="Mínimo 6 caracteres" required>
          </div>
          <div class="form-group">
            <label for="telefone">Telefone *</label>
            <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>" 
                   placeholder="11987654321" maxlength="11" required>
            <small style="color: var(--cor-texto-secundario);">DDD + número (apenas dígitos)</small>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="tipo_sanguineo">Tipo Sanguíneo *</label>
            <select id="tipo_sanguineo" name="tipo_sanguineo" required>
              <option value="">Selecione...</option>
              <option value="A" <?php echo ($_POST['tipo_sanguineo'] ?? '') === 'A' ? 'selected' : ''; ?>>A</option>
              <option value="B" <?php echo ($_POST['tipo_sanguineo'] ?? '') === 'B' ? 'selected' : ''; ?>>B</option>
              <option value="AB" <?php echo ($_POST['tipo_sanguineo'] ?? '') === 'AB' ? 'selected' : ''; ?>>AB</option>
              <option value="O" <?php echo ($_POST['tipo_sanguineo'] ?? '') === 'O' ? 'selected' : ''; ?>>O</option>
            </select>
          </div>
          <div class="form-group">
            <label for="fator_rh">Fator RH *</label>
            <select id="fator_rh" name="fator_rh" required>
              <option value="">Selecione...</option>
              <option value="POSITIVO" <?php echo ($_POST['fator_rh'] ?? '') === 'POSITIVO' ? 'selected' : ''; ?>>POSITIVO (+)</option>
              <option value="NEGATIVO" <?php echo ($_POST['fator_rh'] ?? '') === 'NEGATIVO' ? 'selected' : ''; ?>>NEGATIVO (-)</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="endereco">Endereço Completo *</label>
          <input type="text" id="endereco" name="endereco" 
                 value="<?php echo htmlspecialchars($_POST['endereco'] ?? ''); ?>" 
                 placeholder="Rua Exemplo, 123 - Bairro - Cidade/UF" required>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-salvar">
            <span>➕</span> Cadastrar Usuário
          </button>
          <button type="reset" class="btn btn-cancelar">
            <span>🗑️</span> Limpar Formulário
          </button>
          <button type="button" class="btn btn-voltar" onclick="window.location.href='dashboardADM.php'">
            <span>❌</span> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>