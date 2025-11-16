<?php
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $conn->real_escape_string($_POST['nome']);
    $email = $conn->real_escape_string($_POST['email']);
    $telefone = $conn->real_escape_string($_POST['telefone']);
    $tipoSanguineo = $conn->real_escape_string($_POST['tipoSanguineo']);

    $sql = "INSERT INTO doadores (nome, email, telefone, tipo_sanguineo) 
            VALUES ('$nome', '$email', '$telefone', '$tipoSanguineo')";

    if ($conn->query($sql) === TRUE) {
        echo "Cadastro realizado com sucesso!";
    } else {
        echo "Erro: " . $conn->error;
    }
}
$conn->close();
?>
