<?php

require_once 'conexao.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de agendamento inválido!");
}

$agendamento_id = (int)$_GET['id'];


$sql = "UPDATE agendamentos SET status = 'CONFIRMADO' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $agendamento_id);

if ($stmt->execute()) {
    header("Location: dashboardADM.php?msg=agendamento_confirmado");
} else {
    header("Location: dashboardADM.php?msg=agendamento_erro");
}

$stmt->close();
$conn->close();
exit();
?>