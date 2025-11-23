<?php
session_start();

require_once 'conexao.php';

$msg_sucesso = '';
$msg_erro = '';

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_sangue = $_POST['tipo_sangue'];
    $quantidade = (int)$_POST['quantidade'];
    $data_validade = $_POST['data_validade'];

    // Validações
    if (empty($tipo_sangue) || empty($quantidade) || empty($data_validade)) {
        $msg_erro = "⚠️ Todos os campos são obrigatórios!";
    } elseif ($quantidade <= 0) {
        $msg_erro = "❌ Quantidade deve ser maior que zero!";
    } elseif ($data_validade <= date('Y-m-d')) {
        $msg_erro = "❌ Data de validade deve ser futura!";
    } else {
        // Verifica se já existe bolsas desse tipo com essa validade
        $sql_check = "SELECT quantidade FROM bolsas WHERE tipo_sangue = ? AND data_validade = ?";
        $stmt = $conn->prepare($sql_check);
        $stmt->bind_param("ss", $tipo_sangue, $data_validade);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Já existe, atualiza quantidade
            $row = $result->fetch_assoc();
            $nova_quantidade = $row['quantidade'] + $quantidade;
            
            $sql_update = "UPDATE bolsas SET quantidade = ? WHERE tipo_sangue = ? AND data_validade = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("iss", $nova_quantidade, $tipo_sangue, $data_validade);
            
            if ($stmt_update->execute()) {
                $msg_sucesso = "✅ Estoque atualizado! Agora há " . $nova_quantidade . " bolsas de " . $tipo_sangue . ".";
            } else {
                $msg_erro = "❌ Erro ao atualizar estoque: " . $conn->error;
            }
        } else {
            // Não existe, insere novo registro
            $sql_insert = "INSERT INTO bolsas (tipo_sangue, quantidade, data_validade) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sis", $tipo_sangue, $quantidade, $data_validade);
            
            if ($stmt_insert->execute()) {
                $msg_sucesso = "✅ " . $quantidade . " bolsas de " . $tipo_sangue . " adicionadas ao estoque!";
            } else {
                $msg_erro = "❌ Erro ao adicionar bolsas: " . $conn->error;
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
  <title>Adicionar Bolsas - Gota Vermelha ADM</title>
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
      max-width: 600px;
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
    .btn-adicionar {
      background: linear-gradient(135deg, var(--cor-adm), var(--cor-adm-hover));
      color: white;
    }
    .btn-adicionar:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(139, 92, 246, 0.25);
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
    .alert {
      padding: 16px;
      border-radius: 10px;
      margin-bottom: 24px;
      font-size: 14px;
      font-weight: 500;
    }
    .alert-success {
      background: rgba(139, 92, 246, 0.1);
      color: var(--cor-adm);
      border: 1px solid rgba(139, 92, 246, 0.3);
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
    .info-tipo {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 8px;
      margin-top: 8px;
    }
    .tipo-item {
      background: rgba(139, 92, 246, 0.05);
      padding: 8px;
      border-radius: 6px;
      font-size: 12px;
      text-align: center;
      color: var(--cor-texto-secundario);
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
      <div class="logo">ADICIONAR BOLSAS</div>
      <button class="btn btn-voltar" onclick="window.location.href='dashboardADM.php'">
        <span>←</span> Voltar ao Dashboard
      </button>
    </div>

    <div class="card">
      <h2>📦 Adicionar Bolsas ao Estoque</h2>

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
        <div class="form-group">
          <label for="tipo_sangue">Tipo Sanguíneo *</label>
          <select id="tipo_sangue" name="tipo_sangue" required>
            <option value="">Selecione o tipo...</option>
            <option value="A+">A+</option>
            <option value="A-">A-</option>
            <option value="B+">B+</option>
            <option value="B-">B-</option>
            <option value="AB+">AB+</option>
            <option value="AB-">AB-</option>
            <option value="O+">O+</option>
            <option value="O-">O-</option>
          </select>
          <div class="info-tipo">
            <div class="tipo-item">A+</div>
            <div class="tipo-item">A-</div>
            <div class="tipo-item">B+</div>
            <div class="tipo-item">B-</div>
            <div class="tipo-item">AB+</div>
            <div class="tipo-item">AB-</div>
            <div class="tipo-item">O+</div>
            <div class="tipo-item">O-</div>
          </div>
        </div>

        <div class="form-group">
          <label for="quantidade">Quantidade de Bolsas *</label>
          <input type="number" id="quantidade" name="quantidade" min="1" 
                 value="<?php echo htmlspecialchars($_POST['quantidade'] ?? ''); ?>" 
                 placeholder="Ex: 5" required>
          <small style="color: var(--cor-texto-secundario);">Número inteiro maior que zero</small>
        </div>

        <div class="form-group">
          <label for="data_validade">Data de Validade *</label>
          <input type="date" id="data_validade" name="data_validade" 
                 value="<?php echo htmlspecialchars($_POST['data_validade'] ?? ''); ?>" 
                 min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
          <small style="color: var(--cor-texto-secundario);">Deve ser uma data futura</small>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-adicionar">
            <span>➕</span> Adicionar ao Estoque
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