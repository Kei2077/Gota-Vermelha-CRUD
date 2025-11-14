<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Faça login na plataforma Gota Vermelha">
  <title>Login – Gota Vermelha</title>
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
      --sombra: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --sombra-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      --raio-borda: 12px;
      --transicao: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
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
      transition: var(--transicao);
    }

    .login-card:hover {
      box-shadow: var(--sombra-hover);
    }

    .login-card h1 {
      color: var(--cor-primaria);
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 10px;
      text-align: center;
    }

    .login-card .subtitle {
      color: var(--cor-texto-secundario);
      font-size: 15px;
      text-align: center;
      margin-bottom: 32px;
    }

    .form-group {
      margin-bottom: 24px;
    }

    .login-card label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: var(--cor-texto);
      margin-bottom: 8px;
    }

    .login-card input[type="email"],
    .login-card input[type="password"] {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid var(--cor-borda);
      border-radius: 8px;
      font-size: 16px;
      transition: var(--transicao);
      background-color: #F9FAFB;
    }

    .login-card input::placeholder {
      color: #9CA3AF;
    }

    .login-card input:focus {
      outline: none;
      border-color: var(--cor-primaria);
      background-color: var(--cor-card);
      box-shadow: 0 0 0 3px rgba(226, 35, 10, 0.1);
    }

    .login-card button {
      width: 100%;
      padding: 14px;
      background: linear-gradient(to bottom, var(--cor-primaria), var(--cor-primaria-hover));
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transicao);
      margin-top: 8px;
      position: relative;
      overflow: hidden;
    }

    .login-card button:hover {
      background: linear-gradient(to bottom, var(--cor-primaria-hover), var(--cor-secundaria));
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(226, 35, 10, 0.25);
    }

    .login-card button:active {
      transform: translateY(0);
    }

    .login-card button:focus-visible {
      outline: 3px solid var(--cor-primaria);
      outline-offset: 2px;
    }

    .login-card .links {
      margin-top: 32px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .login-card a {
      color: var(--cor-primaria);
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      text-align: center;
      padding: 10px;
      border-radius: 6px;
      transition: var(--transicao);
    }

    .login-card a:hover {
      text-decoration: none;
      background-color: rgba(226, 35, 10, 0.05);
    }

    .login-card a:focus-visible {
      outline: 2px solid var(--cor-primaria);
      outline-offset: 2px;
    }

    .login-card .btn-outline {
      margin-top: 8px;
      border: 2px solid var(--cor-primaria);
      color: var(--cor-primaria);
      font-weight: 600;
    }

    .login-card .btn-outline:hover {
      background-color: var(--cor-primaria);
      color: #fff;
    }

    .login-card .btn-outline::before {
      content: "← ";
      margin-right: 4px;
    }

    @media (max-width: 480px) {
      .login-card {
        padding: 32px 24px;
      }
    }
  </style>
</head>

<body>
  <main class="login-card">
    <h1>Login</h1>
    <p class="subtitle">Acesse sua conta no Gota Vermelha</p>
    
    <form action="dashboard.html" method="post">
      <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required autocomplete="email" placeholder="seu@email.com">
      </div>

      <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required autocomplete="current-password" placeholder="••••••••">
      </div>

      <button type="submit">Entrar</button>
    </form>

    <div class="links">
      <a href="cadastro.php">Criar nova conta</a>
      <a href="index.html" class="btn-outline">Voltar para a página inicial</a>
    </div>
  </main>
</body>
</html>