<?php


require_once 'conexao.php';


$stats_sql = "
        SELECT 
        (SELECT COUNT(*) FROM usuarios) as total_usuarios,
        (SELECT COUNT(*) FROM doacoes) as total_doacoes,
        (SELECT COALESCE(SUM(quantidade_ml), 0) FROM doacoes) as volume_total,
        (SELECT COUNT(*) FROM agendamentos WHERE status IN ('PENDENTE', 'CONFIRMADO')) as agendamentos_ativos,
        (SELECT COUNT(DISTINCT tipo_sangue) FROM bolsas WHERE quantidade > 0 AND data_validade > CURDATE()) as bolsas_disponiveis
";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result ? $stats_result->fetch_assoc() : [];


$estoque_sql = "SELECT 
    tipo_sangue,
    SUM(quantidade) as qtd_bolsas,
    SUM(quantidade) * 0.45 as volume_litros,
    MIN(data_validade) as proxima_validade
    FROM bolsas
    WHERE quantidade > 0 AND data_validade > CURDATE()
    GROUP BY tipo_sangue";
$estoque_result = $conn->query($estoque_sql);
$estoque_detalhado = $estoque_result ? $estoque_result->fetch_all(MYSQLI_ASSOC) : [];


$usuarios_sql = "SELECT 
    u.id,
    u.nome,
    u.email,
    u.tipo_sanguineo,
    u.fator_rh,
    COUNT(d.id) as total_doacoes,
    COALESCE(SUM(d.quantidade_ml), 0) as volume_doado,
    u.criado_em
    FROM usuarios u
    LEFT JOIN doacoes d ON u.id = d.usuario_id
    GROUP BY u.id
    ORDER BY u.nome";
$usuarios_result = $conn->query($usuarios_sql);
$usuarios = $usuarios_result ? $usuarios_result->fetch_all(MYSQLI_ASSOC) : [];


$doacoes_sql = "SELECT 
    d.id,
    u.nome as doador,
    d.data_doacao,
    d.local_doacao,
    d.quantidade_ml,
    d.criado_em
    FROM doacoes d
    JOIN usuarios u ON d.usuario_id = u.id
    ORDER BY d.data_doacao DESC";
$doacoes_result = $conn->query($doacoes_sql);
$todas_doacoes = $doacoes_result ? $doacoes_result->fetch_all(MYSQLI_ASSOC) : [];


$agendamentos_sql = "SELECT 
    a.id,
    u.nome as usuario,
    a.unidade,
    a.data,
    a.hora,
    a.status,
    a.criado_em
    FROM agendamentos a
    JOIN usuarios u ON a.usuario_id = u.id
    ORDER BY a.data ASC, a.hora ASC";
$agendamentos_result = $conn->query($agendamentos_sql);
$todos_agendamentos = $agendamentos_result ? $agendamentos_result->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Administrativo - Gota Vermelha</title>
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
      --cor-pendente: #F59E0B;
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
    .adm-info {
      display: flex;
      align-items: center;
      gap: 16px;
      background: rgba(255, 255, 255, 0.15);
      padding: 12px 20px;
      border-radius: 12px;
    }
    .avatar-adm {
      width: 48px;
      height: 48px;
      background: rgba(255, 255, 255, 0.2);
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
    .btn-sair {
      background: white;
      color: var(--cor-adm);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .btn-sair:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .btn-primary {
      background: linear-gradient(135deg, var(--cor-adm), var(--cor-adm-hover));
      color: white;
      box-shadow: 0 4px 6px rgba(139, 92, 246, 0.15);
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(139, 92, 246, 0.25);
    }
    .btn-sucesso {
      background: linear-gradient(135deg, var(--cor-sucesso), #059669);
      color: white;
      padding: 10px 18px;
      font-size: 13px;
    }
    .btn-alerta {
      background: linear-gradient(135deg, var(--cor-pendente), #D97706);
      color: white;
      padding: 10px 18px;
      font-size: 13px;
    }
    .btn-erro {
      background: linear-gradient(135deg, var(--cor-erro), #DC2626);
      color: white;
      padding: 10px 18px;
      font-size: 13px;
    }
    .info-painel {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 32px;
    }
    .info-card {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      padding: 24px;
      border-left: 4px solid rgba(255, 255, 255, 0.5);
    }
    .info-card h3 {
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      opacity: 0.9;
    }
    .info-card p {
      font-size: 28px;
      font-weight: 700;
      line-height: 1;
    }
    .section-title {
      grid-column: 1 / -1;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }
    .section-title h2 { margin-bottom: 0; }
    .tabela-historico {
      width: 100%;
      border-collapse: collapse;
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
      text-align: left;
    }
    .tabela-historico td {
      padding: 16px 20px;
      font-size: 14px;
      color: var(--cor-texto);
    }
    .tabela-historico tr:hover {
      background: rgba(139, 92, 246, 0.03);
    }
    .tabela-historico tr:nth-child(even) {
      background: #F9FAFB;
    }
    .badge {
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      display: inline-block;
    }
    .badge-adm { background: var(--cor-adm); color: white; }
    .badge-sucesso { background: var(--cor-sucesso); color: white; }
    .badge-pendente { background: var(--cor-pendente); color: white; }
    .badge-erro { background: var(--cor-erro); color: white; }
    .badge-urgente {
      background: linear-gradient(135deg, #DC2626, #B91C1C);
      color: white;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }
    .acoes {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }
    .estoque-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }
    .bolsa-item {
      background: white;
      border: 2px solid var(--cor-borda);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      transition: var(--transicao);
    }
    .bolsa-item:hover {
      border-color: var(--cor-adm);
      transform: translateY(-4px);
      box-shadow: 0 10px 20px rgba(139, 92, 246, 0.1);
    }
    .bolsa-tipo {
      font-weight: 700;
      font-size: 18px;
      margin-bottom: 8px;
    }
    .bolsa-qtd {
      font-size: 24px;
      font-weight: 800;
      color: var(--cor-adm);
    }
    .bolsa-volume {
      font-size: 12px;
      color: var(--cor-texto-secundario);
      margin-top: 8px;
    }
    .menu-lateral {
      position: fixed;
      left: 0;
      top: 0;
      height: 100vh;
      width: 280px;
      background: white;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
      padding: 24px;
      overflow-y: auto;
      z-index: 1000;
    }
    .conteudo-principal {
      margin-left: 280px;
      padding: 20px;
    }
    .menu-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px;
      border-radius: 10px;
      margin-bottom: 8px;
      cursor: pointer;
      transition: var(--transicao);
      text-decoration: none;
      color: var(--cor-texto);
    }
    .menu-item:hover {
      background: rgba(139, 92, 246, 0.1);
      color: var(--cor-adm);
    }
    .menu-item.ativo {
      background: linear-gradient(135deg, var(--cor-adm), var(--cor-adm-hover));
      color: white;
    }
    @media (max-width: 1024px) {
      .menu-lateral { transform: translateX(-100%); }
      .conteudo-principal { margin-left: 0; }
    }
  </style>
</head>
<body>

  <div class="menu-lateral">
    <div class="logo" style="margin-bottom: 32px; color: var(--cor-adm);"> GOTA VERMELHA ADM</div>
    <a href="dashboard_adm.php" class="menu-item ativo">
      <span>📊</span> Dashboard
    </a>
    <a href="novo_usuario.php" class="menu-item">
      <span>👥</span> Usuários
    </a>
    <a href="registrar_doacao.php" class="menu-item">
      <span>🩸</span> Doações
    </a>
    <a href="adicionar_bolsas.php" class="menu-item">
      <span>📦</span> Estoque
    </a>
    <hr style="margin: 24px 0; border: none; border-top: 1px solid var(--cor-borda);">
    <a href="logout_adm.php" class="menu-item">
      <span>🚪</span> Sair
    </a>
  </div>

  <div class="conteudo-principal">
    <div class="container" id="dashboard">
      <div class="header">
        <div class="logo">PAINEL ADMINISTRATIVO</div>
        <div class="adm-info">
          <div class="avatar-adm">A</div>
          <div>
            <div style="font-weight: 600; font-size: 14px;">Administrador</div>
            <div style="font-size: 12px; opacity: 0.9;"><?php echo htmlspecialchars($_SESSION['adm_email'] ?? 'admin@gotavermelha.com'); ?></div>
          </div>
          <button class="btn btn-sair" onclick="window.location.href='logout_adm.php'">Sair</button>
        </div>
      </div>

      <!-- ESTATÍSTICAS GERAIS -->
      <div class="card" style="grid-column: 1 / -1;">
        <h2>📈 Estatísticas do Sistema</h2>
        <div class="info-painel">
          <div class="info-card" style="background: linear-gradient(135deg, var(--cor-adm), var(--cor-adm-hover)); color: white;">
            <h3>Total de Usuários</h3>
            <p><?php echo $stats['total_usuarios'] ?? 0; ?></p>
          </div>
          <div class="info-card" style="background: linear-gradient(135deg, var(--cor-sucesso), #059669); color: white;">
            <h3>Doações Realizadas</h3>
            <p><?php echo $stats['total_doacoes'] ?? 0; ?></p>
          </div>
          <div class="info-card" style="background: linear-gradient(135deg, var(--cor-primaria), #DC2626); color: white;">
            <h3>Volume Total (Litros)</h3>
            <p><?php echo number_format(($stats['volume_total'] ?? 0) / 1000, 2, ',', '.'); ?> L</p>
          </div>
          <div class="info-card" style="background: linear-gradient(135deg, var(--cor-pendente), #D97706); color: white;">
            <h3>Agendamentos Ativos</h3>
            <p><?php echo $stats['agendamentos_ativos'] ?? 0; ?></p>
          </div>
          <div class="info-card" style="background: linear-gradient(135deg, #3B82F6, #2563EB); color: white;">
            <h3>Bolsas em Estoque</h3>
            <p><?php echo $stats['bolsas_disponiveis'] ?? 0; ?></p>
          </div>
        </div>
      </div>

      <!-- ESTOQUE - CORREÇÃO: Mostra todos os 8 tipos -->
      <div class="card" style="grid-column: 1 / -1;">
        <div class="section-title">
          <h2>📦 Controle de Estoque</h2>
          <div>
            <button class="btn btn-primary" onclick="window.location.href='adicionar_bolsas.php'">
              <span>➕</span> Adicionar Bolsas
            </button>
            <button class="btn btn-alerta" onclick="distribuir()" style="margin-left: 8px;">
              <span>📤</span> Distribuir
            </button>
          </div>
        </div>
        <div class="estoque-grid">
          <?php
      
          $tipos_sanguineos = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
          $estoque_completo = [];
    
          foreach ($tipos_sanguineos as $tipo) {
            $estoque_completo[$tipo] = [
              'tipo_sangue' => $tipo,
              'qtd_bolsas' => 0,
              'volume_litros' => 0,
              'proxima_validade' => null
            ];
          }
          
      
          foreach ($estoque_detalhado as $item) {
            if (isset($estoque_completo[$item['tipo_sangue']])) {
              $estoque_completo[$item['tipo_sangue']] = $item;
            }
          }
          

          ksort($estoque_completo);
          ?>
          
          <?php foreach ($estoque_completo as $item): ?>
            <div class="bolsa-item">
              <div class="bolsa-tipo"><?php echo $item['tipo_sangue']; ?></div>
              <div class="bolsa-qtd"><?php echo $item['qtd_bolsas']; ?></div>
              <div style="font-size: 12px; color: var(--cor-texto-secundario);">bolsas</div>
              <div class="bolsa-volume">
                <strong><?php echo number_format($item['volume_litros'], 2, ',', '.'); ?> L</strong>
              </div>
              <?php if ($item['proxima_validade']): ?>
                <div style="font-size: 10px; color: var(--cor-erro); margin-top: 8px;">
                  Vence: <?php echo date('d/m/Y', strtotime($item['proxima_validade'])); ?>
                </div>
              <?php else: ?>
                <div style="font-size: 10px; color: var(--cor-texto-secundario); margin-top: 8px;">
                  Sem estoque
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- USUÁRIOS -->
      <div class="card" style="grid-column: 1 / -1;">
        <div class="section-title">
          <h2>👥 Gerenciamento de Usuários</h2>
          <button class="btn btn-primary" onclick="window.location.href='novo_usuario.php'">
            <span>➕</span> Novo Usuário
          </button>
        </div>
        <div style="overflow-x: auto;">
          <table class="tabela-historico">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Tipo Sanguíneo</th>
                <th>Doações</th>
                <th>Volume Doado</th>
                <th>Cadastro</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($usuarios as $user): ?>
                <tr>
                  <td><?php echo $user['id']; ?></td>
                  <td><strong><?php echo htmlspecialchars($user['nome']); ?></strong></td>
                  <td><?php echo htmlspecialchars($user['email']); ?></td>
                  <td><?php echo $user['tipo_sanguineo'] . ($user['fator_rh'] === 'POSITIVO' ? '+' : '-'); ?></td>
                  <td><?php echo $user['total_doacoes']; ?></td>
                  <td><?php echo number_format($user['volume_doado'] / 1000, 2, ',', '.'); ?> L</td>
                  <td><?php echo date('d/m/Y', strtotime($user['criado_em'])); ?></td>
                  <td class="acoes">
                    <button class="btn btn-sucesso" onclick="editarUsuario(<?php echo $user['id']; ?>)">Editar</button>
                    <button class="btn btn-erro" onclick="excluirUsuario(<?php echo $user['id']; ?>)">Excluir</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- DOAÇÕES -->
      <div class="card" style="grid-column: 1 / -1;">
        <div class="section-title">
          <h2>🩸 Histórico de Doações</h2>
          <button class="btn btn-primary" onclick="window.location.href='registrar_doacao.php'">
            <span>➕</span> Registrar Doação
          </button>
        </div>
        <div style="overflow-x: auto;">
          <table class="tabela-historico">
            <thead>
              <tr>
                <th>ID</th>
                <th>Doador</th>
                <th>Data</th>
                <th>Local</th>
                <th>Quantidade</th>
                <th>Bolsas</th>
                <th>Registrado em</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($todas_doacoes as $doacao): 
                $bolsas = $doacao['quantidade_ml'] / 450;
              ?>
                <tr>
                  <td><?php echo $doacao['id']; ?></td>
                  <td><strong><?php echo htmlspecialchars($doacao['doador']); ?></strong></td>
                  <td><?php echo date('d/m/Y', strtotime($doacao['data_doacao'])); ?></td>
                  <td><?php echo htmlspecialchars($doacao['local_doacao']); ?></td>
                  <td><?php echo $doacao['quantidade_ml']; ?> ml</td>
                  <td><?php echo number_format($bolsas, 0); ?></td>
                  <td><?php echo date('d/m/Y H:i', strtotime($doacao['criado_em'])); ?></td>
                  <td class="acoes">
                    <button class="btn btn-sucesso" onclick="editarDoacao(<?php echo $doacao['id']; ?>)">Editar</button>
                    <button class="btn btn-erro" onclick="excluirDoacao(<?php echo $doacao['id']; ?>)">Excluir</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- AGENDAMENTOS -->
      <div class="card" style="grid-column: 1 / -1;">
    <div class="section-title">
      <h2>📅 Agendamentos</h2>
    </div>
    <div style="overflow-x: auto;">
      <table class="tabela-historico">
        <thead>
          <tr>
            <th>ID</th>
            <th>Usuário</th>
            <th>Unidade</th>
            <th>Data/Hora</th>
            <th>Status</th>
            <th>Criado em</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($todos_agendamentos as $ag): ?>
            <tr>
              <td><?php echo $ag['id']; ?></td>
              <td><strong><?php echo htmlspecialchars($ag['usuario']); ?></strong></td>
              <td><?php echo htmlspecialchars($ag['unidade']); ?></td>
              <td><?php echo date('d/m/Y', strtotime($ag['data'])) . ' às ' . $ag['hora']; ?></td>
              <td>
                <span class="badge badge-<?php echo strtolower($ag['status']); ?>">
                  <?php echo ucfirst($ag['status']); ?>
                </span>
              </td>
              <td><?php echo date('d/m/Y H:i', strtotime($ag['criado_em'])); ?></td>
              <td class="acoes">
                <?php if ($ag['status'] === 'PENDENTE'): ?>
                  <button class="btn btn-sucesso" onclick="confirmarAgendamento(<?php echo $ag['id']; ?>)">Confirmar</button>
                <?php endif; ?>
                <?php if ($ag['status'] !== 'CANCELADO' && $ag['status'] !== 'REALIZADO'): ?>
                  <button class="btn btn-erro" onclick="cancelarAgendamento(<?php echo $ag['id']; ?>)">Cancelar</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function distribuir(){
      window.location.href = 'distribuir.php';
    }
    
    function editarUsuario(id) {
      if (confirm('Editar usuário #' + id + '?')) {
        window.location.href = 'editar_usuario.php?id=' + id;
      }
    }

    function excluirUsuario(id) {
      if (confirm('⚠️ EXCLUIR usuário #' + id + '? Esta ação não pode ser desfeita!')) {
        window.location.href = 'excluir_usuario.php?id=' + id;
      }
    }

    function editarDoacao(id) {
      if (confirm('Editar doação #' + id + '?')) {
        window.location.href = 'editar_doacao.php?id=' + id;
      }
    }

    function excluirDoacao(id) {
      if (confirm('⚠️ EXCLUIR doação #' + id + '?')) {
        window.location.href = 'excluir_doacao.php?id=' + id;
      }
    }

    function confirmarAgendamento(id) {
      if (confirm('Confirmar agendamento #' + id + '?')) {
        window.location.href = 'confirmar_agendamento.php?id=' + id;
      }
    }

    function cancelarAgendamento(id) {
      if (confirm('Cancelar agendamento #' + id + '?')) {
        window.location.href = 'cancelar_agendamento.php?id=' + id;
      }
    }
  </script>
</body>
</html>