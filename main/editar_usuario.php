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
  <link rel="stylesheet" href="styleedituser.css">
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