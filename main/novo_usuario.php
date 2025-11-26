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
  <link rel="stylesheet" href="stylenovouser.css">
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