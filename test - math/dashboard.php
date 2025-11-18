<?php
session_start();
if (!isset($_SESSION['logado'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexao.php';

// Busca estoque de bolsas
$estoque_sql = "SELECT 
    SUM(quantidade) as total,
    SUM(CASE WHEN tipo_sangue='A+' THEN quantidade ELSE 0 END) as a_pos,
    SUM(CASE WHEN tipo_sangue='A-' THEN quantidade ELSE 0 END) as a_neg,
    SUM(CASE WHEN tipo_sangue='B+' THEN quantidade ELSE 0 END) as b_pos,
    SUM(CASE WHEN tipo_sangue='B-' THEN quantidade ELSE 0 END) as b_neg,
    SUM(CASE WHEN tipo_sangue='AB+' THEN quantidade ELSE 0 END) as ab_pos,
    SUM(CASE WHEN tipo_sangue='AB-' THEN quantidade ELSE 0 END) as ab_neg,
    SUM(CASE WHEN tipo_sangue='O+' THEN quantidade ELSE 0 END) as o_pos,
    SUM(CASE WHEN tipo_sangue='O-' THEN quantidade ELSE 0 END) as o_neg
    FROM bolsas
    WHERE quantidade > 0 AND data_validade > CURDATE()";

$estoque_result = $conn->query($estoque_sql);
$estoque = $estoque_result ? $estoque_result->fetch_assoc() : array_fill_keys(
    ['total','a_pos','a_neg','b_pos','b_neg','ab_pos','ab_neg','o_pos','o_neg'], 
    0
);

// Cálculo de litros (cada bolsa = 450ml)
$litros_total = $estoque['total'] * 0.45;

// Busca histórico de doações
$doacoes_sql = "SELECT 
    id,
    data_doacao,
    local_doacao,
    quantidade_ml,
    DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as criado
    FROM doacoes 
    WHERE usuario_id = ? 
    ORDER BY data_doacao DESC 
    LIMIT 20";

$stmt = $conn->prepare($doacoes_sql);
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $doacoes_result = $stmt->get_result();
    $doacoes = $doacoes_result->fetch_all(MYSQLI_ASSOC);
} else {
    $doacoes = [];
    $erro_doacoes = "Erro ao buscar histórico: " . $conn->error;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Gota Vermelha</title>
  <style>
    :root {
      --cor-primaria: #E2230A;
      --cor-primaria-hover: #C41E0A;
      --cor-secundaria: #880202;
      --cor-fundo: #F9F9F9;
      --cor-card: #FFFFFF;
      --cor-texto: #2D2D2D;
      --cor-texto-secundario: #6B7280;
      --cor-borda: #E5E7EB;
      --cor-sucesso: #10B981;
      --cor-erro: #EF4444;
      --sombra: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      --raio-borda: 12px;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, var(--cor-fundo) 0%, #F3F4F6 100%);
      min-height: 100vh;
      padding: 20px;
    }
    .container {
      max-width: 1400px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr;
      gap: 24px;
    }
    .card {
      background: var(--cor-card);
      border-radius: var(--raio-borda);
      box-shadow: var(--sombra);
      padding: 32px;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
    }
    h1 { color: var(--cor-primaria); font-size: 28px; }
    h2 { color: var(--cor-texto); font-size: 20px; margin-bottom: 20px; }
    .btn-sair {
      background: var(--cor-erro);
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.2s;
    }
    .btn-sair:hover { background: #DC2626; transform: translateY(-1px); }
    .btn-refresh {
      background: var(--cor-primaria);
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-refresh:hover { background: var(--cor-primaria-hover); }

    /* Grid de estoque com LITROS */
    .estoque-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
      gap: 16px;
      margin-top: 20px;
    }
    .bolsa-item {
      background: #F9FAFB;
      border: 2px solid var(--cor-borda);
      border-radius: 8px;
      padding: 20px;
      text-align: center;
      transition: all 0.2s;
      position: relative;
    }
    .bolsa-item:hover { border-color: var(--cor-primaria); transform: translateY(-3px); }
    .bolsa-tipo {
      font-weight: 700;
      font-size: 20px;
      color: var(--cor-primaria);
      margin-bottom: 8px;
    }
    .bolsa-qtd {
      font-size: 28px;
      font-weight: 800;
      color: var(--cor-texto);
      margin: 8px 0;
    }
    .bolsa-volume {
      font-size: 14px;
      color: var(--cor-texto-secundario);
      margin-top: 8px;
      padding-top: 8px;
      border-top: 1px solid var(--cor-borda);
    }
    .bolsa-volume strong {
      color: var(--cor-primaria);
      font-weight: 600;
    }

    /* Painel geral */
    .info-painel {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }
    .info-card {
      background: #F9FAFB;
      border-radius: 8px;
      padding: 20px;
      border-left: 4px solid var(--cor-primaria);
    }
    .info-card h3 {
      font-size: 14px;
      color: var(--cor-texto-secundario);
      margin-bottom: 4px;
    }
    .info-card p {
      font-size: 24px;
      font-weight: 700;
      color: var(--cor-texto);
    }
    .volume-total {
      font-size: 18px !important;
      color: var(--cor-primaria) !important;
    }

    /* Tabela de doações */
    .tabela-historico {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      border-radius: 8px;
      overflow: hidden;
    }
    .tabela-historico th,
    .tabela-historico td {
      padding: 12px 16px;
      text-align: left;
      border-bottom: 1px solid var(--cor-borda);
    }
    .tabela-historico th {
      background: #F9FAFB;
      font-weight: 600;
      font-size: 14px;
      color: var(--cor-texto);
    }
    .tabela-historico tr:hover { background: #F9FAFB; }
    .tabela-historico td { font-size: 14px; color: var(--cor-texto); }
    .badge-sucesso {
      background: var(--cor-sucesso);
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
    }

    /* Placeholder */
    .placeholder {
      text-align: center;
      padding: 40px;
      color: var(--cor-texto-secundario);
    }
    .placeholder-icon { font-size: 48px; margin-bottom: 16px; }

    @media (max-width: 768px) {
      .container { padding: 0; }
      .card { padding: 24px; }
      h1 { font-size: 24px; }
      .tabela-historico { font-size: 12px; }
      .tabela-historico th,
      .tabela-historico td { padding: 8px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Boas-vindas -->
    <div class="card">
      <div class="header">
        <div>
          <h1>🩸 Dashboard Gota Vermelha</h1>
          <p style="color: var(--cor-texto-secundario); margin-top: 8px;">
            Bem-vindo(a), <strong><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></strong>
          </p>
        </div>
        <button class="btn-sair" onclick="window.location.href='logout.php'">Sair</button>
      </div>
    </div>

    <!-- Estoque -->
    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>📊 Estoque de Bolsas</h2>
        <button class="btn-refresh" onclick="location.reload()">🔄 Atualizar</button>
      </div>
      
      <!-- Total Geral -->
      <div class="info-painel">
        <div class="info-card">
          <h3>Total de Bolsas</h3>
          <p><?php echo $estoque['total']; ?></p>
        </div>
        <div class="info-card">
          <h3>Volume Total</h3>
          <p class="volume-total"><?php echo number_format($litros_total, 2, ',', '.'); ?> L</p>
        </div>
        <div class="info-card">
          <h3>Média por Bolsa</h3>
          <p>450ml</p>
        </div>
      </div>

      <!-- Grid de tipos sanguíneos com litros -->
      <div class="estoque-grid">
        <?php
        $tipos = [
            ['tipo' => 'A+', 'qtd' => $estoque['a_pos'], 'litros' => $estoque['a_pos'] * 0.45],
            ['tipo' => 'A-', 'qtd' => $estoque['a_neg'], 'litros' => $estoque['a_neg'] * 0.45],
            ['tipo' => 'B+', 'qtd' => $estoque['b_pos'], 'litros' => $estoque['b_pos'] * 0.45],
            ['tipo' => 'B-', 'qtd' => $estoque['b_neg'], 'litros' => $estoque['b_neg'] * 0.45],
            ['tipo' => 'AB+', 'qtd' => $estoque['ab_pos'], 'litros' => $estoque['ab_pos'] * 0.45],
            ['tipo' => 'AB-', 'qtd' => $estoque['ab_neg'], 'litros' => $estoque['ab_neg'] * 0.45],
            ['tipo' => 'O+', 'qtd' => $estoque['o_pos'], 'litros' => $estoque['o_pos'] * 0.45],
            ['tipo' => 'O-', 'qtd' => $estoque['o_neg'], 'litros' => $estoque['o_neg'] * 0.45],
        ];
        
        foreach ($tipos as $item):
          // Só mostra se tiver estoque
          if ($item['qtd'] > 0):
        ?>
          <div class="bolsa-item">
            <div class="bolsa-tipo"><?php echo $item['tipo']; ?></div>
            <div class="bolsa-qtd"><?php echo $item['qtd']; ?></div>
            <div style="font-size: 12px; color: var(--cor-texto-secundario);">bolsas</div>
            <div class="bolsa-volume">
              <strong><?php echo number_format($item['litros'], 2, ',', '.'); ?> L</strong>
            </div>
          </div>
        <?php 
          endif;
        endforeach; 
        ?>
      </div>

      <p style="color: var(--cor-texto-secundario); font-size: 12px; margin-top: 16px;">
        <strong>Última atualização:</strong> <?php echo date('H:i:s - d/m/Y'); ?> | 
        Cada bolsa contém <strong>450ml</strong>
      </p>
    </div>

    <!-- Histórico de Doações -->
    <div class="card">
      <h2>📋 Seu Histórico de Doações</h2>
      
      <?php if (isset($erro_doacoes)): ?>
        <div style="background: #FEE2E2; color: #991B1B; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
          <?php echo htmlspecialchars($erro_doacoes); ?>
        </div>
      <?php endif; ?>

      <?php if (count($doacoes) > 0): ?>
        <table class="tabela-historico">
          <thead>
            <tr>
              <th>Data</th>
              <th>Local</th>
              <th>Quantidade</th>
              <th>Registrado em</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($doacoes as $doacao): ?>
              <tr>
                <td><?php echo date('d/m/Y', strtotime($doacao['data_doacao'])); ?></td>
                <td><?php echo htmlspecialchars($doacao['local_doacao']); ?></td>
                <td><span class="badge-sucesso"><?php echo $doacao['quantidade_ml']; ?>ml</span></td>
                <td><?php echo $doacao['criado']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <p style="color: var(--cor-texto-secundario); font-size: 12px; margin-top: 16px;">
          Mostrando <?php echo count($doacoes); ?> doações recentes
        </p>
      <?php else: ?>
        <div class="placeholder">
          <div class="placeholder-icon">🩸</div>
          <h3>Você ainda não tem doações registradas</h3>
          <p>Quando você doar sangue, as informações aparecerão aqui.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>