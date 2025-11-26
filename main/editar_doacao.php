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
  <link rel="stylesheet" href="styleeditdoacao.css">
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