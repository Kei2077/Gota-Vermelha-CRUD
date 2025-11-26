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
    DATE_FORMAT(criado_em, '%d/%m/%Y %H:%i') as criado
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

$agendamentos_sql = "SELECT 
    id,
    unidade,
    data,
    hora,
    status,
    DATE_FORMAT(criado_em, '%d/%m/%Y %H:%i') as criado
    FROM agendamentos 
    WHERE usuario_id = ? 
    ORDER BY data ASC, hora ASC 
    LIMIT 10";

$agendamentos_stmt = $conn->prepare($agendamentos_sql);
$agendamentos = [];
if ($agendamentos_stmt) {
    $agendamentos_stmt->bind_param("i", $_SESSION['usuario_id']);
    $agendamentos_stmt->execute();
    $agendamentos_result = $agendamentos_stmt->get_result();
    $agendamentos = $agendamentos_result->fetch_all(MYSQLI_ASSOC);
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
  <link rel="stylesheet" href="styledashboard.css">
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
        <button class="btn-agendar" onclick="window.location.href='agendamento.php'">📅 Agendar Doação</button>
        <button class="btn-sair" onclick="window.location.href='logout.php'">Sair</button>
      </div>
    </div>

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

    <div class="card" style="grid-column: 1 / -1;">
      <h2>📅 Meus Agendamentos</h2>
      
      <?php if (count($agendamentos) > 0): ?>
        <table class="tabela-historico">
          <thead>
            <tr>
              <th>Data e Hora</th>
              <th>Unidade</th>
              <th>Status</th>
              <th>Agendado em</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($agendamentos as $agendamento): ?>
              <tr>
                <td><?php echo date('d/m/Y', strtotime($agendamento['data'])) . ' às ' . $agendamento['hora']; ?></td>
                <td><?php echo htmlspecialchars($agendamento['unidade']); ?></td>
                <td>
                  <?php 
                    $badge_class = 'badge-' . strtolower($agendamento['status']);
                    echo '<span class="' . $badge_class . '">' . ucfirst($agendamento['status']) . '</span>';
                  ?>
                </td>
                <td><?php echo $agendamento['criado']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="placeholder">
          <div class="placeholder-icon">📅</div>
          <h3>Você não tem agendamentos ativos</h3>
          <p>Clique em "Agendar Doação" para marcar sua próxima doação.</p>
        </div>
      <?php endif; ?>
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