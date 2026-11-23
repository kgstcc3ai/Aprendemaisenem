

<?php
session_start();



/* ==========================================
   1. CONEXÃO COM O BANCO DE DADOS
   Aproveita a variável $conexao do seu conexão.php.
========================================== */

try {
    require_once __DIR__ . "/conexão.php";

    if (!isset($conexao) || !($conexao instanceof PDO)) {
        throw new RuntimeException("A variável de conexão não contém um objeto PDO.");
    }

    $pdo = $conexao;
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (Throwable $erro) {
    error_log("Erro em linguagem.php: " . $erro->getMessage());
    http_response_code(500);
    exit("Não foi possível conectar ao banco de dados. Confira o arquivo conexão.php e a conexão com o servidor.");
}

/* ==========================================
   2. CRIAR TABELA DE QUESTÕES
========================================== */


$pdo->exec("
    CREATE TABLE IF NOT EXISTS questoes_linguagens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        enunciado TEXT NOT NULL,
        alternativa_a TEXT NOT NULL,
        alternativa_b TEXT NOT NULL,
        alternativa_c TEXT NOT NULL,
        alternativa_d TEXT NOT NULL,
        alternativa_e TEXT NOT NULL,
        resposta_correta CHAR(1) NOT NULL,
        explicacao TEXT,
        criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");


/* ==========================================
   3. QUESTÕES INICIAIS
   Serão inseridas somente se a tabela estiver vazia.
========================================== */


$questoesIniciais = [


[
"Em três enunciados aparecem as conjunções 'e', 'portanto' e 'porém'. Considerando o sentido que cada uma estabelece entre as ideias, elas expressam, respectivamente:",
"A) Adição - explicação - conclusão.",
"B) Adição - conclusão - oposição.",
"C) Separação - explicação - oposição.",
"D) Adição - exclusão - justificação.",
"E) Explicação - conclusão - oposição.",
"B"
],


[
"Um narrador conta que, durante muito tempo, acreditou viver feliz porque não conhecia determinados acontecimentos. Depois de adquirir novos conhecimentos, percebe que sua antiga felicidade era apenas resultado da falta de informação. Ao afirmar que antes não era feliz, 'mas' somente ignorante, a conjunção destacada:",
"A) Estabelece oposição entre a felicidade imaginada e a condição de ignorância.",
"B) Opõe o tempo presente ao tempo passado.",
"C) Estabelece uma relação de explicação.",
"D) Acrescenta uma informação sem oposição.",
"E) Indica uma relação de consequência.",
"A"
],


[
"A conjunção 'mas' é utilizada para estabelecer oposição entre ideias. Assinale a alternativa em que essa conjunção não seria adequada para preencher o espaço entre as duas orações:",
"A) Queria sair, ___ começou a chover.",
"B) O dia amanheceu ___ os pássaros começaram a cantar.",
"C) Estava cansado, ___ continuou trabalhando.",
"D) Estudou bastante, ___ não conseguiu a nota esperada.",
"E) Queria descansar, ___ precisou terminar a atividade.",
"B"
],


[
"Em um poema de temática romântica, o eu lírico demonstra profunda tristeza diante da perda de uma pessoa amada, expressando sofrimento e sentimento de impotência. Essa manifestação sentimental representa:",
"A) A angústia diante da certeza da morte.",
"B) A melancolia que impede uma reação diante da perda.",
"C) A valorização exclusiva da razão.",
"D) A exaltação das conquistas sociais.",