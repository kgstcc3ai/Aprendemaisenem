
<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

try {
    if (empty($_SESSION["usuario_email"])) {
        throw new Exception("Sua sessão expirou. Faça login novamente.");
    }

    $dados = json_decode(file_get_contents("php://input"), true);

    if (!is_array($dados)) {
        throw new Exception("Dados inválidos.");
    }

    $simulado = $_SESSION["simulado_enem"] ?? null;

    if (!$simulado ||
        $simulado["usuario"] !== $_SESSION["usuario_email"] ||
        $simulado["token"] !== ($dados["token"] ?? "") ||
        $simulado["finalizado"]) {
        throw new Exception("Simulado inválido ou já finalizado.");
    }

    if (time() - $simulado["inicio"] > 2400) {
        throw new Exception("O tempo do simulado terminou.");
    }

    $respostas = $dados["respostas"] ?? [];

    if (!is_array($respostas)) {
        throw new Exception("Respostas inválidas.");
    }

    $gabarito = $simulado["gabarito"];

    $acertos = 0;
    $total = count($gabarito);

    foreach ($gabarito as $id => $correta) {
        $marcada = strtoupper($respostas[$id] ?? "");

        if ($marcada === $correta) {
            $acertos++;
        }
    }

    $_SESSION["simulado_enem"]["finalizado"] = true;

    unset($_SESSION["simulado_enem"]);

    echo json_encode([
        "sucesso" => true,
        "acertos" => $acertos,
        "total" => $total,
        "erros" => $total - $acertos
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $erro) {
    http_response_code(400);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => $erro->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
