<?php
session_start();

require_once 'conexao.php';

$msg_sucesso = '';
$msg_erro = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_sangue = $_POST['tipo_sangue'];
    $quantidade = (int)$_POST['quantidade'];
    $data_validade = $_POST['data_validade'];

 
    if (empty($tipo_sangue) || empty($quantidade) || empty($data_validade)) {
        $msg_erro = "⚠️ Todos os campos são obrigatórios!";
    } elseif ($quantidade <= 0) {
        $msg_erro = "❌ Quantidade deve ser maior que zero!";
    } elseif ($data_validade <= date('Y-m-d')) {
        $msg_erro = "❌ Data de validade deve ser futura!";
    } else {
      
        $sql_check = "SELECT quantidade FROM bolsas WHERE tipo_sangue = ? AND data_validade = ?";
        $stmt = $conn->prepare($sql_check);
        $stmt->bind_param("ss", $tipo_sangue, $data_validade);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
 
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
  <link rel="stylesheet" href="styleaddbolsa.css">
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