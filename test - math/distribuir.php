<?php
session_start();

require_once 'conexao.php';

$msg_sucesso = '';
$msg_erro = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_sangue = $_POST['tipo_sangue'];
    $quantidade = (int)$_POST['quantidade'];


    if (empty($tipo_sangue) || empty($quantidade)) {
        $msg_erro = "⚠️ Todos os campos são obrigatórios!";
    } elseif ($quantidade <= 0) {
        $msg_erro = "❌ Quantidade deve ser maior que zero!";
    } else {
        
        $sql_estoque = "SELECT SUM(quantidade) as total FROM bolsas 
                       WHERE tipo_sangue = ? AND quantidade > 0 AND data_validade > CURDATE()";
        $stmt = $conn->prepare($sql_estoque);
        $stmt->bind_param("s", $tipo_sangue);
        $stmt->execute();
        $result = $stmt->get_result();
        $estoque = $result->fetch_assoc();

        if ($estoque['total'] < $quantidade) {
            $msg_erro = "❌ Estoque insuficiente! Há apenas " . $estoque['total'] . " bolsas de " . $tipo_sangue . ".";
        } else {

            $conn->begin_transaction();
            
            try {
          
                $sql_bolsas = "SELECT id, quantidade FROM bolsas 
                              WHERE tipo_sangue = ? AND quantidade > 0 AND data_validade > CURDATE() 
                              ORDER BY data_validade ASC";
                $stmt = $conn->prepare($sql_bolsas);
                $stmt->bind_param("s", $tipo_sangue);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $quantidade_restante = $quantidade;
                
                while (($row = $result->fetch_assoc()) && $quantidade_restante > 0) {
                    $id_bolsa = $row['id'];
                    $qtd_disponivel = $row['quantidade'];
                    
                    if ($qtd_disponivel >= $quantidade_restante) {
                     
                        $nova_quantidade = $qtd_disponivel - $quantidade_restante;
                        $quantidade_restante = 0;
                    } else {
                     
                        $nova_quantidade = 0;
                        $quantidade_restante -= $qtd_disponivel;
                    }
                    
                
                    $sql_update = "UPDATE bolsas SET quantidade = ? WHERE id = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("ii", $nova_quantidade, $id_bolsa);
                    $stmt_update->execute();
                }
                
                $conn->commit();
                $msg_sucesso = "✅ " . $quantidade . " bolsas de " . $tipo_sangue . " distribuídas com sucesso!";
                
            } catch (Exception $e) {
                $conn->rollback();
                $msg_erro = "❌ Erro ao distribuir: " . $e->getMessage();
            }
        }
    }
}


$sql_estoque = "SELECT tipo_sangue, SUM(quantidade) as total 
                FROM bolsas 
                WHERE quantidade > 0 AND data_validade > CURDATE() 
                GROUP BY tipo_sangue 
                HAVING total > 0
                ORDER BY tipo_sangue";
$estoque_result = $conn->query($sql_estoque);
$estoque_tipos = $estoque_result ? $estoque_result->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Distribuir Bolsas - Gota Vermelha ADM</title>
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
    .btn-distribuir {
      background: linear-gradient(135deg, var(--cor-adm), var(--cor-adm-hover));
      color: white;
    }
    .btn-distribuir:hover {
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
      <div class="logo">DISTRIBUIR BOLSAS</div>
      <button class="btn btn-voltar" onclick="window.location.href='dashboardADM.php'">
        <span>←</span> Voltar ao Dashboard
      </button>
    </div>

    <div class="card">
      <h2>📤 Distribuição de Bolsas</h2>

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
            <?php foreach ($estoque_tipos as $item): ?>
              <option value="<?php echo $item['tipo_sangue']; ?>">
                <?php echo $item['tipo_sangue']; ?> - <?php echo $item['total']; ?> bolsas disponíveis
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="quantidade">Quantidade a Distribuir *</label>
          <input type="number" id="quantidade" name="quantidade" min="1" 
                 placeholder="Ex: 3" required>
          <small style="color: var(--cor-texto-secundario);">
            As bolsas serão retiradas do estoque (priorizando as próximas a vencer)
          </small>
        </div>

        <div class="info-estoque">
          <p><strong>ℹ️ Regra de Distribuição:</strong></p>
          <p>O sistema retirará bolsas do estoque <strong>ordenadas pela data de validade</strong> (primeiro as mais próximas de vencer).</p>
          <p>Se houver estoque suficiente, a quantidade será reduzida. Caso contrário, a distribuição falhará.</p>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-distribuir">
            <span>📤</span> Distribuir Bolsas
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