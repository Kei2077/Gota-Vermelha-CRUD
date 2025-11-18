<?php
session_start();
if (!isset($_SESSION['logado'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexao.php';


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

$litros_total = $estoque['total'] * 0.45;

$doacoes_sql = "SELECT 
    id,
    data_doacao,
    local_doacao,
    quantidade_ml,
    DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as criado
    FROM doacoes 
    WHERE usuario_id = ? 
    ORDER BY data_doacao DESC 
    LIMIT 15";

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

$usuario_sql = "SELECT tipo_sanguineo, fator_rh FROM usuarios WHERE id = ?";
$usuario_stmt = $conn->prepare($usuario_sql);
if ($usuario_stmt) {
    $usuario_stmt->bind_param("i", $_SESSION['usuario_id']);
    $usuario_stmt->execute();
    $usuario_result = $usuario_stmt->get_result();
    $usuario_data = $usuario_result->fetch_assoc();
} else {
    $usuario_data = ['tipo_sanguineo' => 'N/A', 'fator_rh' => 'N/A'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Gota Vermelha</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --cor-primaria: #E2230A;
      --cor-primaria-hover: #C41E0A;
      --cor-secundaria: #880202;
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
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: var(--cor-fundo);
      min-height: 100vh;
      padding: 20px;
      color: var(--cor-texto);
    }
    .container {
      max-width: 1440px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
    }
    .card {
      background: var(--cor-card);
      backdrop-filter: blur(10px);
      border-radius: var(--raio-borda);
      box-shadow: var(--sombra);
      padding: 32px;
      transition: var(--transicao);
      border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15);
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 32px;
      grid-column: 1 / -1;
    }
    .logo {
      font-size: 24px;
      font-weight: 800;
      color: var(--cor-primaria);
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .logo::before { content: "🩸"; font-size: 28px; }
    .user-info {
      display: flex;
      align-items: center;
      gap: 16px;
      background: rgba(226, 35, 10, 0.05);
      padding: 12px 20px;
      border-radius: 12px;
    }
    .avatar {
      width: 48px;
      height: 48px;
      background: linear-gradient(135deg, var(--cor-primaria), var(--cor-primaria-hover));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 700;
      font-size: 18px;
    }
    h1 { font-size: 24px; font-weight: 700; }
    h2 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .btn-sair {
      background: linear-gradient(135deg, var(--cor-erro), #DC2626);
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      transition: var(--transicao);
      box-shadow: 0 4px 6px rgba(239, 68, 68, 0.15);
    }
    .btn-sair:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(239, 68, 68, 0.25);
    }
    .btn-refresh {
      background: linear-gradient(135deg, var(--cor-primaria), var(--cor-primaria-hover));
      color: white;
      padding: 10px 18px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: var(--transicao);
    }
    .btn-refresh:hover { transform: scale(1.05); }
    .btn-refresh:active { transform: scale(0.98); }


    .info-painel {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 32px;
    }
    .info-card {
      background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
      border-radius: 12px;
      padding: 24px;
      border-left: 4px solid var(--cor-primaria);
      transition: var(--transicao);
    }
    .info-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05);
    }
    .info-card h3 {
      font-size: 13px;
      font-weight: 500;
      color: var(--cor-texto-secundario);
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .info-card p {
      font-size: 28px;
      font-weight: 700;
      color: var(--cor-texto);
      line-height: 1;
    }
    .volume-total {
      font-size: 24px !important;
      color: var(--cor-primaria) !important;
    }

   
    .estoque-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 20px;
      margin-top: 24px;
    }
    .bolsa-item {
      background: linear-gradient(135deg, #FFFFFF 0%, #F9FAFB 100%);
      border: 2px solid var(--cor-borda);
      border-radius: 12px;
      padding: 24px 16px;
      text-align: center;
      transition: var(--transicao);
      position: relative;
      overflow: hidden;
    }
    .bolsa-item::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--cor-primaria), var(--cor-primaria-hover));
      opacity: 0;
      transition: var(--transicao);
    }
    .bolsa-item:hover {
      border-color: var(--cor-primaria);
      transform: translateY(-4px);
      box-shadow: 0 10px 20px rgba(226, 35, 10, 0.1);
    }
    .bolsa-item:hover::before { opacity: 1; }
    .bolsa-tipo {
      font-weight: 700;
      font-size: 20px;
      color: var(--cor-texto);
      margin-bottom: 8px;
    }
    .bolsa-qtd {
      font-size: 32px;
      font-weight: 800;
      background: linear-gradient(135deg, var(--cor-primaria), var(--cor-primaria-hover));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin: 8px 0;
    }
    .bolsa-volume {
      font-size: 14px;
      color: var(--cor-texto-secundario);
      margin-top: 12px;
      padding-top: 12px;
      border-top: 1px solid var(--cor-borda);
    }
    .bolsa-volume strong {
      color: var(--cor-primaria);
      font-weight: 700;
    }

  
    .tabela-historico {
      width: 100%;
      border-collapse: collapse;
      margin-top: 24px;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .tabela-historico th {
      background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
      font-weight: 600;
      font-size: 13px;
      color: var(--cor-texto-secundario);
      padding: 16px 20px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .tabela-historico td {
      padding: 16px 20px;
      font-size: 14px;
      color: var(--cor-texto);
    }
    .tabela-historico tr:hover {
      background: rgba(226, 35, 10, 0.02);
    }
    
    
    .badge-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
    }
    .badge-sucesso {
      background: linear-gradient(135deg, var(--cor-sucesso), #059669);
      color: white;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      display: inline-block;
    }
    .badge-bolsas {
      font-size: 11px;
      color: var(--cor-texto-secundario);
      font-weight: 500;
      background: #F9FAFB;
      padding: 2px 8px;
      border-radius: 4px;
      border: 1px solid var(--cor-borda);
    }

  
    .placeholder {
      text-align: center;
      padding: 60px 40px;
      color: var(--cor-texto-secundario);
      background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
      border-radius: 12px;
      margin-top: 20px;
    }
    .placeholder-icon {
      font-size: 64px;
      margin-bottom: 24px;
      opacity: 0.7;
    }
    .placeholder h3 {
      font-size: 18px;
      margin-bottom: 8px;
      color: var(--cor-texto);
      font-weight: 600;
    }

    @media (max-width: 1024px) {
      .container { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
      .container { padding: 0; }
      .card { padding: 24px; }
      h1 { font-size: 20px; }
      .user-info { padding: 8px 16px; }
      .avatar { width: 40px; height: 40px; font-size: 16px; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="logo">GOTA VERMELHA</div>
      <div class="user-info">
        <div class="avatar"><?php echo substr($_SESSION['usuario_nome'], 0, 1); ?></div>
        <div>
          <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></div>
          <div style="font-size: 12px; color: var(--cor-texto-secundario);">
            <?php echo htmlspecialchars($usuario_data['tipo_sanguineo'] . ($usuario_data['fator_rh'] === 'POSITIVO' ? '+' : '-')); ?>
          </div>
        </div>
        <button class="btn-sair" onclick="window.location.href='logout.php'">Sair</button>
      </div>
    </div>

    <!-- Métricas Gerais -->
    <div class="card">
      <h2>📈 Visão Geral</h2>
      <div class="info-painel">
        <div class="info-card">
          <h3>Bolsas Disponíveis</h3>
          <p><?php echo $estoque['total']; ?></p>
        </div>
        <div class="info-card">
          <h3>Volume Total</h3>
          <p class="volume-total"><?php echo number_format($litros_total, 2, ',', '.'); ?> L</p>
        </div>
        <div class="info-card">
          <h3>Minhas Doações</h3>
          <p><?php echo count($doacoes); ?></p>
        </div>
        <div class="info-card">
          <h3>Total Doado</h3>
          <p><?php 
            $total_doado = array_sum(array_column($doacoes, 'quantidade_ml'));
            echo number_format($total_doado / 1000, 2, ',', '.');
          ?> L</p>
        </div>
      </div>
    </div>

    <!-- Estoque -->
    <div class="card" style="grid-column: 1 / -1;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2>📊 Estoque de Bolsas</h2>
        <button class="btn-refresh" onclick="location.reload()">
          <span>🔄</span> Atualizar
        </button>
      </div>
      
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

      <p style="color: var(--cor-texto-secundario); font-size: 12px; margin-top: 24px; text-align: center;">
        <strong>Última atualização:</strong> <?php echo date('H:i:s - d/m/Y'); ?>
      </p>
    </div>

    <!-- Histórico -->
    <div class="card" style="grid-column: 1 / -1;">
      <h2>📋 Histórico de Doações</h2>
      
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
            <?php foreach ($doacoes as $doacao): 
              $bolsas = $doacao['quantidade_ml'] / 450;
            ?>
              <tr>
                <td><?php echo date('d/m/Y', strtotime($doacao['data_doacao'])); ?></td>
                <td><?php echo htmlspecialchars($doacao['local_doacao']); ?></td>
                <td>
                  <div class="badge-container">
                    <span class="badge-sucesso"><?php echo $doacao['quantidade_ml']; ?>ml</span>
                    <span class="badge-bolsas">
                      <?php echo $bolsas; ?> bolsa<?php echo $bolsas > 1 ? 's' : ''; ?>
                    </span>
                  </div>
                </td>
                <td><?php echo $doacao['criado']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
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