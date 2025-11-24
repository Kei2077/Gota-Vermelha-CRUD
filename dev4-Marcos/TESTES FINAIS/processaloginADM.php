<?php
session_start();
include 'conexao.php';


function limparDados($dados, $conn) {
    return $conn->real_escape_string(trim($dados));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limparDados($_POST['email'], $conn);
    $senha = $_POST['senha'];

    
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuariosADM WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        
        
        if (password_verify($senha, $usuario['senha'])) {
           
            $_SESSION['usuarioADM_id'] = $usuario['id'];
            $_SESSION['usuarioADM_nome'] = $usuario['nome'];
            $_SESSION['logado'] = true;
            
            
            header("Location: dashboardADM.php");
            exit();
        } else {
            
            $_SESSION['erro_login'] = "E-mail ou senha incorretos.";
            header("Location: loginADM.php");
            exit();
        }
    } else {
        
        $_SESSION['erro_login'] = "E-mail ou senha incorretos.";
        header("Location: loginADM.php");
        exit();
    }
} else {
    header("Location: loginADM.php");
    exit();
}