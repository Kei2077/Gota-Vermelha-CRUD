<?php
include 'conexao.php';

$sql = "SELECT COUNT(*) as total FROM doadores";
$result = $conn->query($sql);

$totalDoadores = 0;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalDoadores = $row['total'];
}

// Exemplo simples: 0.5 litros por doador
$quantidadeSangue = $totalDoadores * 0.5;

echo json_encode([
    'totalDoadores' => $totalDoadores,
    'quantidadeSangue' => $quantidadeSangue
]);

$conn->close();
?>
