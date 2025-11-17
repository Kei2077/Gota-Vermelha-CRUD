<?php
session_start();
if (!isset($_SESSION['logado'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Gota Vermelha</title>
</head>
<body style="font-family: Arial; padding: 40px; background: #f9f9f9;">
  <div style="max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px;">
    <h1 style="color: #E2230A;">Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h1>
    <p>Você está logado. ID: <?php echo $_SESSION['usuario_id']; ?></p>
    <button onclick="window.location.href='logout.php'" style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 6px; margin-top: 20px;">
      Sair
    </button>
  </div>
</body>
</html>