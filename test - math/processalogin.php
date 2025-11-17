<?php
session_start();
include 'conexão.php';


function limparDados($dados, $conn) {
    return $conn->real_escape_string(trim($dados));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limparDados($_POST['email'], $conn);
    $senha = $_POST['senha'];

    
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        
        
        if (password_verify($senha, $usuario['senha'])) {
           
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['logado'] = true;
            
            
            header("Location: dashboard.php");
            exit();
        } else {
            
            $_SESSION['erro_login'] = "E-mail ou senha incorretos.";
            header("Location: login.php");
            exit();
        }
    } else {
        
        $_SESSION['erro_login'] = "E-mail ou senha incorretos.";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}