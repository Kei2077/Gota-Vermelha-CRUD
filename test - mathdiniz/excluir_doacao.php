<?php

require_once 'conexao.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido!");
}

$doacao_id = (int)$_GET['id'];

$sql = "DELETE FROM doacoes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doacao_id);

if ($stmt->execute()) {
    header("Location: dashboardADM.php?msg=excluido_sucesso");
} else {
    header("Location: dashboardADM.php?msg=excluido_erro");
}

$stmt->close();
$conn->close();
exit();
?>