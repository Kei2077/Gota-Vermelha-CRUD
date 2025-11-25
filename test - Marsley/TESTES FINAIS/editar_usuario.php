<?php
session_start();


require_once 'conexao.php';

$usuario_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$mensagem = '';
$erro = '';

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    die("Usuário não encontrado!");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $endereco = trim($_POST['endereco']);
    $tipo_sanguineo = $_POST['tipo_sanguineo'];
    $fator_rh = $_POST['fator_rh'];
    $nova_senha = $_POST['nova_senha'];

    if (empty($nome) || empty($email) || empty($telefone)) {
        $erro = "Preencha todos os campos obrigatórios!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido!";
    } else {

        $check_email = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $usuario_id);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0) {
            $erro = "Este email já está cadastrado para outro usuário!";
        } else {

            if (!empty($nova_senha)) {
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $update_sql = "UPDATE usuarios SET nome = ?, email = ?, telefone = ?, endereco = ?, tipo_sanguineo = ?, fator_rh = ?, senha = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sssssssi", $nome, $email, $telefone, $endereco, $tipo_sanguineo, $fator_rh, $senha_hash, $usuario_id);
            } else {
                $update_sql = "UPDATE usuarios SET nome = ?, email = ?, telefone = ?, endereco = ?, tipo_sanguineo = ?, fator_rh = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ssssssi", $nome, $email, $telefone, $endereco, $tipo_sanguineo, $fator_rh, $usuario_id);
            }

            if ($update_stmt->execute()) {
                $mensagem = "✅ Usuário atualizado com sucesso!";

                $stmt->execute();
                $result = $stmt->get_result();
                $usuario = $result->fetch_assoc();
            } else {
                $erro = "Erro ao atualizar usuário: " . $conn->error;
            }
        }
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Usuário - Gota Vermelha ADM</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --cor-primaria: #E2230A;
      --cor-primaria-hover: #C41E0A;
      --cor-secundaria: #880202;
      --cor-fundo: linear-gradient(135deg, #F9F9F9 0%, #F3F4F6 100%);
      --cor-card: rgba(255, 255, 255, 0.95);
      --cor-texto: #1F2937;
      --cor-texto-secundario: #6B7280;
      --cor-borda: #E5E7EB;
      --cor-sucesso: #10B981;
      --cor-erro: #EF4444;
      --cor-adm: #8B5CF6;
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
      max-width: 800px;
      margin: 0 auto;
    }
    .card {
      background: var(--cor-card);
      backdrop-filter: blur(10px);
      border-radius: var(--raio-borda);
      box-shadow: var(--sombra);
      padding: 32px;
      transition: var(--transicao);
      border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 32px;
    }
    .logo {
      font-size: 24px;
      font-weight: 800;
      color: var(--cor-adm);
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .logo::before { content: "🩸"; font-size: 28px; }
    h1 { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
    h2 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 8px;
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
      box-shadow: 0 4px 6px rgba(16, 185, 129, 0.15);
    }
    .btn-salvar:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(16, 185, 129, 0.25);
    }
    .btn-cancelar {
      background: var(--cor-borda);
      color: var(--cor-texto);
      margin-left: 12px;
    }
    .btn-cancelar:hover {
      background: #D1D5DB;
    }
    .btn-voltar {
      background: linear-gradient(135deg, var(--cor-adm), #7C3AED);
      color: white;
    }
    .btn-voltar:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(139, 92, 246, 0.25);
    }
    .form-group {
      margin-bottom: 24px;
    }
    label {
      display: block;
      font-weight: 600;
      font-size: 14px;
      margin-bottom: 8px;
      color: var(--cor-texto);
    }
    input, select, textarea {
      width: 100%;
      padding: 14px;
      border: 2px solid var(--cor-borda);
      border-radius: 10px;
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      transition: var(--transicao);
      background: white;
    }
    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: var(--cor-adm);
      box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
    }
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    .alert {
      padding: 16px 20px;
      border-radius: 10px;
      margin-bottom: 24px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .alert-success {
      background: rgba(16, 185, 129, 0.1);
      color: var(--cor-sucesso);
      border: 1px solid rgba(16, 185, 129, 0.2);
    }
    .alert-error {
      background: rgba(239, 68, 68, 0.1);
      color: var(--cor-erro);
      border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .info-box {
      background: rgba(139, 92, 246, 0.05);
      border-left: 4px solid var(--cor-adm);
      padding: 16px 20px;
      border-radius: 8px;
      margin-bottom: 24px;
      font-size: 14px;
      color: var(--cor-texto-secundario);
    }
    .info-box strong { color: var(--cor-adm); }
    .senha-info {
      font-size: 12px;
      color: var(--cor-texto-secundario);
      margin-top: 8px;
    }
    @media (max-width: 768px) {
      .form-row { grid-template-columns: 1fr; }
      .card { padding: 24px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="header">
        <div class="logo">Editar Usuário</div>
        <button class="btn btn-voltar" onclick="window.location.href='dashboardADM.php'">
          <span>←</span> Voltar ao Dashboard
        </button>
      </div>

      <?php if ($mensagem): ?>
        <div class="alert alert-success">
          <span>✅</span> <?php echo $mensagem; ?>
        </div>
      <?php endif; ?>

      <?php if ($erro): ?>
        <div class="alert alert-error">
          <span>⚠️</span> <?php echo $erro; ?>
        </div>
      <?php endif; ?>

      <div class="info-box">
        <strong>ID do Usuário:</strong> #<?php echo $usuario['id']; ?> | 
        <strong>Cadastrado em:</strong> <?php echo date('d/m/Y H:i', strtotime($usuario['criado_em'])); ?>
      </div>

      <form method="POST" action="">
        <div class="form-row">
          <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
          </div>
          <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="telefone">Telefone *</label>
            <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone']); ?>" required maxlength="11" placeholder="11987654321">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="tipo_sanguineo">Tipo Sanguíneo</label>
            <select id="tipo_sanguineo" name="tipo_sanguineo" required>
              <option value="A" <?php echo $usuario['tipo_sanguineo'] === 'A' ? 'selected' : ''; ?>>A</option>
              <option value="B" <?php echo $usuario['tipo_sanguineo'] === 'B' ? 'selected' : ''; ?>>B</option>
              <option value="AB" <?php echo $usuario['tipo_sanguineo'] === 'AB' ? 'selected' : ''; ?>>AB</option>
              <option value="O" <?php echo $usuario['tipo_sanguineo'] === 'O' ? 'selected' : ''; ?>>O</option>
            </select>
          </div>
          <div class="form-group">
            <label for="fator_rh">Fator RH</label>
            <select id="fator_rh" name="fator_rh" required>
              <option value="POSITIVO" <?php echo $usuario['fator_rh'] === 'POSITIVO' ? 'selected' : ''; ?>>Positivo (+)</option>
              <option value="NEGATIVO" <?php echo $usuario['fator_rh'] === 'NEGATIVO' ? 'selected' : ''; ?>>Negativo (-)</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="endereco">Endereço Completo</label>
          <textarea id="endereco" name="endereco" rows="3"><?php echo htmlspecialchars($usuario['endereco']); ?></textarea>
        </div>

        <div class="form-group">
          <label for="nova_senha">Nova Senha (deixe vazio para não alterar)</label>
          <input type="password" id="nova_senha" name="nova_senha" placeholder="Mínimo 6 caracteres">
          <div class="senha-info">⚠️ A senha será criptografada automaticamente</div>
        </div>

        <div style="margin-top: 32px; padding-top: 24px; border-top: 2px solid var(--cor-borda);">
          <button type="submit" class="btn btn-salvar">
            <span>💾</span> Salvar Alterações
          </button>
          <button type="button" class="btn btn-cancelar" onclick="window.location.href='dashboardADM.php'">
            Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>