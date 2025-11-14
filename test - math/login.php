<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – Gota Vermelha</title>
  <style>
    :root {
      --cor-primaria: #e2230a;
      --cor-secundaria: #880202;
      --cor-texto: #333;
      --cor-fundo: #f9f9f9;
      --sombra: 0 2px 10px rgba(0,0,0,0.1);
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: var(--cor-fundo);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .login-card {
      background: #fff;
      padding: 40px 30px;
      border-radius: 8px;
      box-shadow: var(--sombra);
      width: 100%;
      max-width: 400px;
    }

    .login-card h1 {
      margin: 0 0 25px;
      text-align: center;
      color: var(--cor-primaria);
      font-size: 26px;
    }

    .login-card label {
      display: block;
      margin-bottom: 6px;
      font-weight: 600;
      color: var(--cor-texto);
    }

    .login-card input[type="email"],
    .login-card input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
    }

    .login-card input:focus {
      outline: 2px solid var(--cor-primaria);
      outline-offset: 2px;
    }

    .login-card button {
      width: 100%;
      padding: 12px;
      background-color: var(--cor-primaria);
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 18px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .login-card button:hover,
    .login-card button:focus {
      background-color: var(--cor-secundaria);
    }

    .login-card a {
      display: block;
      text-align: center;
      margin-top: 15px;
      color: var(--cor-primaria);
      text-decoration: none;
      font-size: 14px;
    }

    .login-card a:hover {
      text-decoration: underline;
    }

    @media (max-width: 480px) {
      .login-card {
        margin: 20px;
      }
    }
  </style>
</head>

<body>
  <main class="login-card">
    <h1>Login - Entrar</h1>
    <form action="dashboard.html" method="post">
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" required autocomplete="email">

      <label for="senha">Senha</label>
      <input type="password" id="senha" name="senha" required autocomplete="current-password">

      <button type="submit">Acessar</button>
    </form>

    <a href="cadastro.php">Criar nova conta</a>
    <a href="index.html" style="
        display: inline-block;
        width: 100%;
        margin-top: 20px;
        padding: 10px;
        background-color: transparent;
        color: var(--cor-primaria);
        border: 2px solid var(--cor-primaria);
        border-radius: 6px;
        text-align: center;
        text-decoration: none;
        font-weight: bold;
        font-size: 16px;
        transition: all 0.3s ease;
        " onmouseover="this.style.backgroundColor='var(--cor-primaria)'; this.style.color='#fff';"
        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--cor-primaria)';">
        ← Voltar para a página inicial
    </a>
  </main>
</body>
</html>