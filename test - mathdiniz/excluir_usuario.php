<?php


require_once 'conexao.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de usuário inválido!");
}

$usuario_id = (int)$_GET['id'];


$sql = "DELETE FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);

if ($stmt->execute()) {
    header("Location: dashboardADM.php?msg=usuario_excluido_sucesso");
} else {
    header("Location: dashboardADM.php?msg=usuario_excluido_erro");
}

$stmt->close();
$conn->close();
exit();
?>