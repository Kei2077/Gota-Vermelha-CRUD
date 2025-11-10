<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "gotavermelha"; #nome do banco de dados

    $conn = new msqli($servername, $username, $password, $dbname);

    if ($conn->connect_error){
        die("Falhou");
    }
?>