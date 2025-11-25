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
    .btn-registrar {
      background: linear-gradient(135deg, var(--cor-adm), var(--cor-adm-hover));
      color: white;
    }
    .btn-registrar:hover {
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
    .info-estoque {
      background: rgba(139, 92, 246, 0.05);
      padding: 16px;
      border-radius: 10px;
      margin-top: 24px;
      border-left: 4px solid var(--cor-adm);
    }
    .info-estoque p {
      font-size: 13px;
      color: var(--cor-texto-secundario);
      margin-bottom: 4px;
    }
    .info-estoque strong {
      color: var(--cor-adm);
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