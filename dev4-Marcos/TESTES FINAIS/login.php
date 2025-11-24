<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – Gota Vermelha</title>
  <style>
    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      display: <?php echo isset($_SESSION['erro_login']) ? 'block' : 'none'; ?>;
    }
  </style>
</head>
<body>
  <main class="login-card">
    <h1>Login</h1>
    <p class="subtitle">Acesse sua conta no Gota Vermelha</p>
    
    <?php if (isset($_SESSION['erro_login'])): ?>
      <div class="alert alert-error"><?php echo $_SESSION['erro_login']; ?></div>
      <?php unset($_SESSION['erro_login']); ?>
    <?php endif; ?>

    <form action="processalogin.php" method="POST">
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
      <a href="cadastro.php">Criar nova conta</a>
      <a href="index.html" style="border: 2px solid var(--cor-primaria); color: var(--cor-primaria); font-weight: 600;">
        ← Voltar para a página inicial
      </a>
      <a href="loginADM.php" style="border: 2px solid var(--cor-primaria); color: var(--cor-primaria); font-weight: 600;">
        → Ir para a página de Login Adminstrativo
      </a>
    </div>
  </main>
</body>
</html>