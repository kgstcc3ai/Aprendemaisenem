
<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once "conexão.php";

try {
    if (empty($_SESSION["usuario_email"])) {
        throw new Exception("Faça login para iniciar o simulado.");
    }

    $idioma = $_GET["idioma"] ?? "";

    if (!in_array($idioma, ["ingles", "espanhol"], true)) {
        throw new Exception("Selecione Inglês ou Espanhol.");
    }

    // Busca 5 questões do idioma escolhido
    $sqlIdioma = "
        SELECT id, ano, numero_questao, idioma, imagem, resposta_correta
        FROM questoes_enem_imagens
        WHERE ativa = 1
        AND ano IN (2023, 2024, 2025)
        AND idioma = :idioma
        ORDER BY RAND()
        LIMIT 5
    ";

    $consulta = $conexao->prepare($sqlIdioma);
    $consulta->execute([":idioma" => $idioma]);
    $questoesIdioma = $consulta->fetchAll();

    // Busca 15 questões gerais
    $sqlGeral = "
        SELECT id, ano, numero_questao, idioma, imagem, resposta_correta
        FROM questoes_enem_imagens
        WHERE ativa = 1
        AND ano IN (2023, 2024, 2025)
        AND idioma = 'geral'
        ORDER BY RAND()
        LIMIT 15
    ";

    $questoesGerais = $conexao->query($sqlGeral)->fetchAll();

    if (count($questoesIdioma) !== 5 ||
        count($questoesGerais) !== 15) {
        throw new Exception("Não existem questões suficientes.");
    }

    $questoes = array_merge($questoesIdioma, $questoesGerais);
    shuffle($questoes);

    // Guarda as respostas corretas exclusivamente na sessão PHP
    $gabarito = [];

    foreach ($questoes as $questao) {
        $gabarito[$questao["id"]] = strtoupper(
            $questao["resposta_correta"]
        );
    }

    $token = bin2hex(random_bytes(24));

    $_SESSION["simulado_enem"] = [
        "token" => $token,
        "usuario" => $_SESSION["usuario_email"],
        "gabarito" => $gabarito,
        "inicio" => time(),
        "finalizado" => false
    ];

    // Prepara as questões que serão enviadas ao navegador
    $questoesTela = [];

    foreach ($questoes as $questao) {
        $questoesTela[] = [
            "id" => (int)$questao["id"],
            "ano" => (int)$questao["ano"],
            "numero_questao" => (int)$questao["numero_questao"],
            "idioma" => $questao["idioma"],

            // Caminho correto das imagens no servidor
            "imagem" => "/" . ltrim($questao["imagem"], "/")
        ];
    }

    echo json_encode([
        "sucesso" => true,
        "token" => $token,
        "total" => 20,
        "tempo" => 2400,
        "questoes" => $questoesTela
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $erro) {
    http_response_code(400);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => $erro->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
