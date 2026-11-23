
<?php
session_start();

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
    require_once "conexão.php";

    $dados = json_decode(file_get_contents("php://input"), true);

    if (!is_array($dados)) {
        http_response_code(400);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Dados enviados inválidos."
        ]);
        exit;
    }

    $email = trim($dados["email"] ?? "");
    $senha = $dados["senha"] ?? "";

    if ($email === "" || $senha === "") {
        http_response_code(400);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Preencha o e-mail e a senha."
        ]);
        exit;
    }

    $sql = "SELECT email, senha
            FROM `Usuários`
            WHERE email = :email
            LIMIT 1";

    $consulta = $conexao->prepare($sql);
    $consulta->execute([":email" => $email]);

    $usuario = $consulta->fetch();

    if ($usuario && password_verify($senha, $usuario["senha"])) {
        session_regenerate_id(true);

        $_SESSION["usuario_email"] = $usuario["email"];

        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Login realizado com sucesso!",
            "redirecionar" => "paginicial.html"
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "E-mail ou senha incorretos."
        ]);
    }

} catch (Throwable $erro) {
    error_log("Erro durante o login: " . $erro->getMessage());

    http_response_code(500);
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Não foi possível realizar o login. Verifique a configuração do sistema."
    ]);
}
?>
