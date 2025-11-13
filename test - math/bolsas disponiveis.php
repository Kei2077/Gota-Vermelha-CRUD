<?php
include 'conexao.php';

// Busca todos os tipos sanguíneos
$result = $conn->query("SELECT 
    SUM(quantidade) as total,
    SUM(CASE WHEN tipo_sangue='A+' THEN quantidade ELSE 0 END) as a_pos,
    SUM(CASE WHEN tipo_sangue='A-' THEN quantidade ELSE 0 END) as a_neg,
    SUM(CASE WHEN tipo_sangue='B+' THEN quantidade ELSE 0 END) as b_pos,
    SUM(CASE WHEN tipo_sangue='B-' THEN quantidade ELSE 0 END) as b_neg,
    SUM(CASE WHEN tipo_sangue='AB+' THEN quantidade ELSE 0 END) as ab_pos,
    SUM(CASE WHEN tipo_sangue='AB-' THEN quantidade ELSE 0 END) as ab_neg,
    SUM(CASE WHEN tipo_sangue='O+' THEN quantidade ELSE 0 END) as o_pos,
    SUM(CASE WHEN tipo_sangue='O-' THEN quantidade ELSE 0 END) as o_neg
    FROM bolsas
    WHERE quantidade>0 AND data_validade>CURDATE()");

$row = $result ? $result->fetch_assoc() : array_fill_keys(['total','a_pos','a_neg','b_pos','b_neg','ab_pos','ab_neg','o_pos','o_neg'], 0);
$conn->close();
?>

<html>
<head>
    <meta charset="UTF-8">
    <title>Estoque</title>
    <style>
        body{font-family:Arial;padding:20px;line-height:1.6}
        table{border-collapse:collapse;margin-top:15px;font-size:12px}
        td,th{border:1px solid #ccc;padding:8px}
    </style>
</head>
<body>
    <h1>🩸 Estoque de Bolsas</h1>
    <p><strong>Atualizado:</strong> <?php echo date('H:i:s'); ?></p>
    
    <table>
        <tr>
            <th>Total</th>
            <th>A+</th><th>A-</th>
            <th>B+</th><th>B-</th>
            <th>AB+</th><th>AB-</th>
            <th>O+</th><th>O-</th>
        </tr>
        <tr>
            <td><?php echo $row['total']; ?></td>
            <td><?php echo $row['a_pos']; ?></td>
            <td><?php echo $row['a_neg']; ?></td>
            <td><?php echo $row['b_pos']; ?></td>
            <td><?php echo $row['b_pos']; ?></td>
            <td><?php echo $row['ab_pos']; ?></td>
            <td><?php echo $row['ab_neg']; ?></td>
            <td><?php echo $row['o_pos']; ?></td>
            <td><?php echo $row['o_neg']; ?></td>
        </tr>
    </table>
    
    <p><button onclick="location.reload()">Atualizar</button></p>
</body>
</html>

