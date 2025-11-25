<?php
session_start();
include 'conexao.php';// <-- SUA CONEXÃO AQUI


function limparDados($dados, $conn) {
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($dados))));
}

// Valida CPF
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) return false;
    if (preg_match('/(\d)\1{10}/', $cpf)) return false;
    
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }
    return true;
}

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Valida campos obrigatórios
        $campos = ['nome', 'cpf', 'data_nascimento', 'email', 'senha', 'confirma_senha', 'tipo_sanguineo', 'fator_rh', 'telefone', 'endereco'];
        foreach ($campos as $campo) {
            if (empty($_POST[$campo])) {
                throw new Exception("O campo " . ucfirst(str_replace('_', ' ', $campo)) . " é obrigatório.");
            }
        }

   
        $nome = limparDados($_POST['nome'], $conn);
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $data_nascimento = $_POST['data_nascimento'];
        $email = filter_var(limparDados($_POST['email'], $conn), FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'];
        $confirma_senha = $_POST['confirma_senha'];
        $tipo_sanguineo = limparDados($_POST['tipo_sanguineo'], $conn);
        $fator_rh = limparDados($_POST['fator_rh'], $conn);
        $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);
        $endereco = limparDados($_POST['endereco'], $conn);

        // Validações
        if (!validarCPF($cpf)) throw new Exception("CPF inválido.");
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("E-mail inválido.");
        if (strlen($senha) < 8) throw new Exception("Senha deve ter no mínimo 8 caracteres.");
        if ($senha !== $confirma_senha) throw new Exception("As senhas não coincidem.");

        // Verifica duplicidade
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE cpf = ? OR email = ?");
        $stmt->bind_param("ss", $cpf, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("CPF ou E-mail já cadastrado.");
        }

        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        
        $stmt = $conn->prepare("INSERT INTO usuarios 
            (nome, cpf, data_nascimento, email, senha, tipo_sanguineo, fator_rh, telefone, endereco, criado_em) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->bind_param("sssssssss", 
            $nome, $cpf, $data_nascimento, $email, $senha_hash, 
            $tipo_sanguineo, $fator_rh, $telefone, $endereco
        );

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Cadastro realizado com sucesso! Faça login.";
            header("Location: login.php");
            exit();
        } else {
            throw new Exception("Erro ao cadastrar: " . $conn->error);
        }

    } catch (Exception $e) {
        $_SESSION['erro'] = $e->getMessage();
        header("Location: cadastro.php");
        exit();
    }
} else {
    header("Location: cadastro.php");
    exit();
}