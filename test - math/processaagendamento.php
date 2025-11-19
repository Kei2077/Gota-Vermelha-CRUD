<?php
session_start();

// Verifica se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexao.php';


function limparDados($dados, $conn) {
    return $conn->real_escape_string(trim($dados));
}


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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
       
        $campos = ['nome', 'email', 'telefone', 'cpf', 'unidade', 'data', 'hora'];
        foreach ($campos as $campo) {
            if (empty($_POST[$campo])) {
                throw new Exception("O campo " . ucfirst(str_replace('_', ' ', $campo)) . " é obrigatório.");
            }
        }

     
        $usuario_id = (int)$_SESSION['usuario_id'];
        $nome = limparDados($_POST['nome'], $conn);
        $email = filter_var(limparDados($_POST['email'], $conn), FILTER_SANITIZE_EMAIL);
        $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $unidade = limparDados($_POST['unidade'], $conn);
        $data = $_POST['data'];
        $hora = $_POST['hora'];

      
        if (!validarCPF($cpf)) throw new Exception("CPF inválido.");
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception("E-mail inválido.");
        if (strlen($telefone) < 10) throw new Exception("Telefone inválido.");
        
        // Verifica se data é futura
        $hoje = date('Y-m-d');
        if ($data <= $hoje) throw new Exception("A data deve ser a partir de amanhã.");
        
        // Verifica se já existe agendamento para o mesmo dia/horário 
        $stmt = $conn->prepare("SELECT id FROM agendamentos WHERE usuario_id = ? AND data = ? LIMIT 1");
        $stmt->bind_param("is", $usuario_id, $data);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Você já tem um agendamento para esta data.");
        }

       
        $sql = "INSERT INTO agendamentos 
                (usuario_id, nome, email, telefone, cpf, unidade, data, hora, status, criado_em) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PENDENTE', NOW())";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro na preparação: " . $conn->error);
        }

        $stmt->bind_param("isssssss", 
            $usuario_id, $nome, $email, $telefone, $cpf, $unidade, $data, $hora
        );

        if ($stmt->execute()) {
            $_SESSION['agendamento_msg'] = "Agendamento realizado com sucesso! Você receberá um e-mail de confirmação.";
            $_SESSION['agendamento_tipo'] = "success";
            header("Location: dashboard.php");
            exit();
        } else {
            throw new Exception("Erro ao salvar agendamento: " . $conn->error);
        }

    } catch (Exception $e) {
        $_SESSION['agendamento_msg'] = $e->getMessage();
        $_SESSION['agendamento_tipo'] = "error";
        header("Location: agendamento.php");
        exit();
    }
} else {
    header("Location: agendamento.php");
    exit();
}