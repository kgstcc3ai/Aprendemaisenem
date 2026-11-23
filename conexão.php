<?php

$servidor = "10.0.11.231";
$banco = "aprendemais";
$usuario = "aluno";
$senha = "ceep@1234";

try {
    $conexao = new PDO(
        "mysql:host=$servidor;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );

    $conexao->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $conexao->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $erro) {
    error_log("Erro na conexão MySQL: " . $erro->getMessage());
    throw $erro;
}