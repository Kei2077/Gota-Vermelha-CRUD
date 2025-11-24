<?php
session_start();

require_once 'conexao.php';


$msg_sucesso = '';
$msg_erro = '';
$doacao = null;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de doação inválido!");
}

$doacao_id = (int)$_GET['id'];


$doacao_sql = "SELECT d.*, u.nome as usuario_nome 
               FROM doacoes d 
               JOIN usuarios u ON d.usuario_id = u.id 
               WHERE d.id = ?";
$stmt = $conn->prepare($doacao_sql);
$stmt->bind_param("i", $doacao_id);
$stmt->execute();
$result = $stmt->get_result();
$doacao = $result->fetch_assoc();

if (!$doacao) {
    die("Doação não encontrada!");
}


$usuarios_sql = "SELECT id, nome FROM usuarios ORDER BY nome";
$usuarios_result = $conn->query($usuarios_sql);
$usuarios = $usuarios_result ? $usuarios_result->fetch_all(MYSQLI_ASSOC) : [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = (int)$_POST['usuario_id'];
    $data_doacao = $_POST['data_doacao'];
    $local_doacao = trim($_POST['local_doacao']);
    $quantidade_ml = (int)$_POST['quantidade_ml'];

    
    if (empty($data_doacao) || empty($local_doacao) || !in_array($quantidade_ml, [450, 900])) {
        $msg_erro = "Por favor, preencha todos os campos corretamente!";
    } else {
       
        $update_sql = "UPDATE doacoes 
                       SET usuario_id = ?, data_doacao = ?, local_doacao = ?, quantidade_ml = ?
                       WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("issii", $usuario_id, $data_doacao, $local_doacao, $quantidade_ml, $doacao_id);

        if ($stmt->execute()) {
            $msg_sucesso = "Doação atualizada com sucesso!";
          
            $stmt->close();
            $stmt = $conn->prepare($doacao_sql);
            $stmt->bind_param("i", $doacao_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $doacao = $result->fetch_assoc();
        } else {
            $msg_erro = "Erro ao atualizar: " . $conn->error;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Doação - Gota Vermelha ADM</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --cor-primaria: #E2230A;
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
      max-width: 800px;
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
      grid-template-columns: 2fr 1fr;
      gap: 20px;
    }
    @media (max-width: 600px) {
      .form-row { grid-template-columns: 1fr; }
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
    .info-doacao {
      background: rgba(139, 92, 246, 0.05);
      padding: 16px;
      border-radius: 10px;
      margin-bottom: 24px;
      border-left: 4px solid var(--cor-adm);
    }
    .info-doacao p {
      font-size: 14px;
      color: var(--cor-texto-secundario);
      margin-bottom: 4px;
    }
    .info-doacao strong {
      color: var(--cor-texto);
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="logo">EDITAR DOAÇÃO</div>
      <button class="btn btn-voltar" onclick="window.location.href='dashboardADM.php'">
        <span>←</span> Voltar ao Dashboard
      </button>
    </div>

    <div class="card">
      <h2>📝 Editar Doação #<?php echo $doacao['id']; ?></h2>

 
      <div class="info-doacao">
        <p><strong>Doador atual:</strong> <?php echo htmlspecialchars($doacao['usuario_nome']); ?></p>
        <p><strong>Data atual:</strong> <?php echo date('d/m/Y', strtotime($doacao['data_doacao'])); ?></p>
        <p><strong>Local atual:</strong> <?php echo htmlspecialchars($doacao['local_doacao']); ?></p>
        <p><strong>Quantidade atual:</strong> <?php echo $doacao['quantidade_ml']; ?> ml</p>
      </div>

      
      <?php if ($msg_sucesso): ?>
        <div class="alert alert-success">
          ✅ <?php echo $msg_sucesso; ?>
        </div>
      <?php endif; ?>

      <?php if ($msg_erro): ?>
        <div class="alert alert-error">
          ❌ <?php echo $msg_erro; ?>
        </div>
      <?php endif; ?>


      <form method="POST" action="">
        <div class="form-group">
          <label for="usuario_id">Doador *</label>
          <select name="usuario_id" id="usuario_id" required>
            <option value="">Selecione o doador...</option>
            <?php foreach ($usuarios as $user): ?>
              <option value="<?php echo $user['id']; ?>" 
                <?php echo ($user['id'] == $doacao['usuario_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($user['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="data_doacao">Data da Doação *</label>
          <input type="date" name="data_doacao" id="data_doacao" 
                 value="<?php echo htmlspecialchars($doacao['data_doacao']); ?>" required>
        </div>

        <div class="form-group">
          <label for="local_doacao">Local da Doação *</label>
          <input type="text" name="local_doacao" id="local_doacao" 
                 value="<?php echo htmlspecialchars($doacao['local_doacao']); ?>" 
                 placeholder="Ex: Hemocentro de Mogi das Cruzes" required>
        </div>

        <div class="form-group">
          <label for="quantidade_ml">Quantidade (ml) *</label>
          <select name="quantidade_ml" id="quantidade_ml" required>
            <option value="450" <?php echo ($doacao['quantidade_ml'] == 450) ? 'selected' : ''; ?>>
              450 ml (1 bolsa)
            </option>
            <option value="900" <?php echo ($doacao['quantidade_ml'] == 900) ? 'selected' : ''; ?>>
              900 ml (2 bolsas - Doação dupla)
            </option>
          </select>
          <small style="color: var(--cor-texto-secundario);">
            * Valores padrão: 450ml ou 900ml (doação dupla)
          </small>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 32px;">
          <button type="submit" class="btn btn-salvar">
            <span>💾</span> Salvar Alterações
          </button>
          <button type="button" class="btn btn-cancelar" onclick="history.back()">
            <span>❌</span> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>