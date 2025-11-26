<?php
session_start();

require_once 'conexao.php';

$msg_sucesso = '';
$msg_erro = '';

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = (int)$_POST['usuario_id'];
    $data_doacao = $_POST['data_doacao'];
    $local_doacao = trim($_POST['local_doacao']);
    $quantidade_ml = (int)$_POST['quantidade_ml'];

    // Validações
    if (empty($usuario_id) || empty($data_doacao) || empty($local_doacao)) {
        $msg_erro = "⚠️ Todos os campos são obrigatórios!";
    } elseif ($data_doacao > date('Y-m-d')) {
        $msg_erro = "❌ Data da doação não pode ser futura!";
    } elseif (!in_array($quantidade_ml, [450, 900])) {
        $msg_erro = "❌ Quantidade deve ser 450ml ou 900ml!";
    } else {
        // Busca tipo sanguíneo do usuário
        $sql_tipo = "SELECT tipo_sanguineo, fator_rh FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql_tipo);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        if (!$usuario) {
            $msg_erro = "❌ Usuário não encontrado!";
        } else {
            $tipo_sangue = $usuario['tipo_sanguineo'] . ($usuario['fator_rh'] === 'POSITIVO' ? '+' : '-');
            
            // Calcula data de validade (60 dias a partir da doação)
            $data_validade = date('Y-m-d', strtotime($data_doacao . ' + 60 days'));

            // Inicia transação para garantir consistência
            $conn->begin_transaction();

            try {
                // 1. Registra a doação
                $sql_doacao = "INSERT INTO doacoes (usuario_id, data_doacao, local_doacao, quantidade_ml) 
                               VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql_doacao);
                $stmt->bind_param("issi", $usuario_id, $data_doacao, $local_doacao, $quantidade_ml);
                $stmt->execute();

                // 2. Calcula quantidade de bolsas
                $qtd_bolsas = $quantidade_ml / 450;

                // 3. Adiciona ao estoque (incrementa quantidade existente ou cria nova linha)
                $sql_estoque = "INSERT INTO bolsas (tipo_sangue, quantidade, data_validade) 
                               VALUES (?, ?, ?)
                               ON DUPLICATE KEY UPDATE 
                               quantidade = quantidade + VALUES(quantidade)";
                
                // Como não temos PK composta, fazemos verificação manual
                $sql_estoque_check = "SELECT quantidade FROM bolsas 
                                     WHERE tipo_sangue = ? AND data_validade = ?";
                $stmt_check = $conn->prepare($sql_estoque_check);
                $stmt_check->bind_param("ss", $tipo_sangue, $data_validade);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();

                if ($result_check->num_rows > 0) {
                    // Já existe, atualiza quantidade
                    $sql_update = "UPDATE bolsas SET quantidade = quantidade + ? 
                                   WHERE tipo_sangue = ? AND data_validade = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("iss", $qtd_bolsas, $tipo_sangue, $data_validade);
                    $stmt_update->execute();
                } else {
                    // Não existe, insere novo
                    $sql_insert = "INSERT INTO bolsas (tipo_sangue, quantidade, data_validade) 
                                   VALUES (?, ?, ?)";
                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->bind_param("sis", $tipo_sangue, $qtd_bolsas, $data_validade);
                    $stmt_insert->execute();
                }

                $conn->commit();
                $msg_sucesso = "✅ Doação registrada com sucesso! " . $qtd_bolsas . " bolsa(s) adicionada(s) ao estoque.";

            } catch (Exception $e) {
                $conn->rollback();
                $msg_erro = "❌ Erro ao registrar doação: " . $e->getMessage();
            }
        }
    }
}

// Busca lista de usuários para o select
$usuarios_sql = "SELECT id, nome FROM usuarios ORDER BY nome";
$usuarios_result = $conn->query($usuarios_sql);
$usuarios = $usuarios_result ? $usuarios_result->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Doação - Gota Vermelha ADM</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styleregdoacao.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="logo">REGISTRAR DOAÇÃO</div>
      <button class="btn btn-voltar" onclick="window.location.href='dashboardADM.php'">
        <span>←</span> Voltar ao Dashboard
      </button>
    </div>

    <div class="card">
      <h2>🩸 Registro de Nova Doação</h2>

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
          <label for="usuario_id">Doador *</label>
          <select id="usuario_id" name="usuario_id" required>
            <option value="">Selecione o doador...</option>
            <?php foreach ($usuarios as $user): ?>
              <option value="<?php echo $user['id']; ?>" 
                <?php echo (isset($_POST['usuario_id']) && $_POST['usuario_id'] == $user['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($user['nome']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="data_doacao">Data da Doação *</label>
            <input type="date" id="data_doacao" name="data_doacao" 
                   value="<?php echo htmlspecialchars($_POST['data_doacao'] ?? date('Y-m-d')); ?>" 
                   max="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="form-group">
            <label for="quantidade_ml">Quantidade *</label>
            <select id="quantidade_ml" name="quantidade_ml" required>
              <option value="">Selecione...</option>
              <option value="450" <?php echo (isset($_POST['quantidade_ml']) && $_POST['quantidade_ml'] == 450) ? 'selected' : ''; ?>>
                450 ml (1 bolsa)
              </option>
              <option value="900" <?php echo (isset($_POST['quantidade_ml']) && $_POST['quantidade_ml'] == 900) ? 'selected' : ''; ?>>
                900 ml (2 bolsas - Doação dupla)
              </option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="local_doacao">Local da Doação *</label>
          <input type="text" id="local_doacao" name="local_doacao" 
                 value="<?php echo htmlspecialchars($_POST['local_doacao'] ?? ''); ?>" 
                 placeholder="Ex: Hemocentro de Mogi das Cruzes" required>
        </div>

        <div class="info-estoque">
          <p><strong>ℹ️ Sobre o Estoque:</strong></p>
          <p>Ao registrar a doação, o sistema <strong>adicionará automaticamente</strong> as bolsas ao estoque com validade de 60 dias.</p>
          <p>O tipo sanguíneo será definido conforme o cadastro do doador selecionado.</p>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-registrar">
            <span>➕</span> Registrar Doação
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