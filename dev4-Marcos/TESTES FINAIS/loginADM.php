<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login (ADMIN) – Gota Vermelha</title>
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
      --cor-erro: #EF4444;
      --cor-sucesso: #10B981;
      --sombra: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      --sombra-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      --raio-borda: 12px;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, var(--cor-fundo) 0%, #F3F4F6 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 16px;
    }
    .login-card {
      background: var(--cor-card);
      border-radius: var(--raio-borda);
      box-shadow: var(--sombra);
      width: 100%;
      max-width: 420px;
      padding: 48px 32px;
    }
    h1 { color: var(--cor-primaria); font-size: 28px; font-weight: 700; margin-bottom: 10px; text-align: center; }
    .subtitle { color: var(--cor-texto-secundario); font-size: 15px; text-align: center; margin-bottom: 32px; }
    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      display: <?php echo isset($_SESSION['erro_login']) ? 'block' : 'none'; ?>;
    }
    .alert-error { background-color: #FEE2E2; color: #991B1B; border: 1px solid var(--cor-erro); }
    .form-group { margin-bottom: 24px; }
    label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; }
    input { width: 100%; padding: 12px 16px; border: 2px solid var(--cor-borda); border-radius: 8px; font-size: 16px; transition: all 0.2s; }
    input:focus { outline: none; border-color: var(--cor-primaria); box-shadow: 0 0 0 3px rgba(226, 35, 10, 0.1); }
    button {
      width: 100%;
      padding: 14px;
      background: linear-gradient(to bottom, var(--cor-primaria), var(--cor-primaria-hover));
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      margin-top: 8px;
    }
    button:hover {
      background: linear-gradient(to bottom, var(--cor-primaria-hover), var(--cor-secundaria));
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(226, 35, 10, 0.25);
    }
    .links { margin-top: 32px; display: flex; flex-direction: column; gap: 12px; }
    a { color: var(--cor-primaria); text-decoration: none; font-size: 14px; font-weight: 500; text-align: center; padding: 10px; border-radius: 6px; }
    a:hover { background-color: rgba(226, 35, 10, 0.05); }
    @media (max-width: 480px) { .login-card { padding: 32px 24px; } }
  </style>
</head>
<body>
  <main class="login-card">
    <h1>Login Adminstrativo</h1>
    <p class="subtitle">Acesse sua conta adminstrativa no Gota Vermelha</p>
    
    <?php if (isset($_SESSION['erro_login'])): ?>
      <div class="alert alert-error"><?php echo $_SESSION['erro_login']; ?></div>
      <?php unset($_SESSION['erro_login']); ?>
    <?php endif; ?>

    <form action="processaloginADM.php" method="POST">
      <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required placeholder="seu@email.com">
      </div>
      <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required placeholder="••••••••">
      </div>
      <button type="submit">Entrar</button>
    </form>

    <div class="links">
      <a href="login.php" style="border: 2px solid var(--cor-primaria); color: var(--cor-primaria); font-weight: 600;">
        ← Voltar para a página de Login comum
      <a href="index.html" style="border: 2px solid var(--cor-primaria); color: var(--cor-primaria); font-weight: 600;">
        <← Voltar para a página inicial
      </a>
    </div>
  </main>
</body>
</html>