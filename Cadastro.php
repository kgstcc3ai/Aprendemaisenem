
<?php

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ]);

    exit;
}

try {

    // Conexão com o banco
    require_once "conexão.php";

    // Receber os dados enviados pelo HTML
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!is_array($dados)) {
        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Dados inválidos."
        ]);

        exit;
    }

    // Receber e-mail e senha
    $email = trim($dados["email"] ?? "");
    $senha = $dados["senha"] ?? "";

    // Validar e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Informe um e-mail válido."
        ]);

        exit;
    }

    // Validar senha
    if (!is_string($senha) || strlen($senha) < 8) {
        http_response_code(400);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "A senha deve ter pelo menos 8 caracteres."
        ]);

        exit;
    }

    // Verificar se o e-mail já existe
    $verificar = $conexao->prepare(
        "SELECT email FROM `Usuários` WHERE email = :email LIMIT 1"
    );

    $verificar->execute([
        ":email" => $email
    ]);

    if ($verificar->fetch()) {
        http_response_code(409);

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Este e-mail já está cadastrado."
        ]);

        exit;
    }

    // Criptografar senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Inserir usuário
    $sql = "INSERT INTO `Usuários` (email, senha)
            VALUES (:email, :senha)";

    $inserir = $conexao->prepare($sql);

    $inserir->execute([
        ":email" => $email,
        ":senha" => $senhaHash
    ]);

    http_response_code(201);

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Cadastro realizado com sucesso!"
    ]);

} catch (PDOException $erro) {

    error_log("Erro no cadastro: " . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao cadastrar usuário no banco de dados."
    ]);

} catch (Throwable $erro) {

    error_log("Erro interno: " . $erro->getMessage());

    http_response_code(500);

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro interno no servidor."
    ]);
}

?>
