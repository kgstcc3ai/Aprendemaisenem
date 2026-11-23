
<?php
session_start();

if (empty($_SESSION["usuario_email"])) {
    header("Location: index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Simulado ENEM - Aprende+ ENEM</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6fb;
    color: #26364f;
}

header {
    background: linear-gradient(120deg, #163b7a, #315eb0);
    color: white;
    padding: 20px 6%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header h2 {
    margin: 0;
}

#cronometro {
    background: white;
    color: #163b7a;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 20px;
    font-weight: bold;
}

.container {
    max-width: 1100px;
    margin: 30px auto;
    padding: 20px;
}

.progresso {
    background: #ddd;
    height: 10px;
    border-radius: 10px;
    margin-bottom: 25px;
    overflow: hidden;
}

#barra {
    height: 100%;
    width: 0;
    background: #315eb0;
    transition: .3s;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 15px #00000010;
}

.info {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

#imagemQuestao {
    width: 100%;
    height: auto;
    display: block;
    margin: 15px auto;
    border-radius: 5px;
}

.alternativas {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
    margin: 25px 0;
}

.alternativa {
    width: 55px;
    height: 55px;
    border: 2px solid #315eb0;
    border-radius: 50%;
    background: white;
    color: #163b7a;
    font-size: 19px;
    font-weight: bold;
    cursor: pointer;
}

.alternativa.selecionada {
    background: #315eb0;
    color: white;
}

.navegacao {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: 25px;
}

button {
    cursor: pointer;
}

.btn {
    border: none;
    border-radius: 7px;
    padding: 13px 22px;
    font-weight: bold;
    font-size: 15px;
}

.btn-azul {
    background: #315eb0;
    color: white;
}

.btn-cinza {
    background: #e4e8f0;
    color: #26364f;
}

.btn-finalizar {
    background: #188653;
    color: white;
}

#carregando {
    text-align: center;
    padding: 60px 15px;
    font-size: 18px;
}

#resultado {
    display: none;
    background: white;
    text-align: center;
    padding: 40px 20px;
    border-radius: 12px;
    box-shadow: 0 3px 15px #00000010;
}

#resultado h2 {
    color: #163b7a;
}

#numeroAcertos {
    font-size: 42px;
    font-weight: bold;
    color: #188653;
}

#mensagem {
    color: #b42318;
    text-align: center;
    margin: 20px;
}

@media(max-width:600px) {
    header {
        padding: 15px;
    }

    header h2 {
        font-size: 17px;
    }

    #cronometro {
        font-size: 16px;
        padding: 10px;
    }

    .card {
        padding: 15px;
    }
}
</style>
</head>

<body>

<header>
    <h2>Aprende+ ENEM</h2>
    <div id="cronometro">40:00</div>
</header>

<div class="container">

    <div id="carregando">
        Carregando questões do ENEM...
    </div>

    <div id="areaSimulado" style="display:none">

        <div class="info">
            <strong id="numeroAtual">Questão 1 de 20</strong>
            <span id="respondidas">Respondidas: 0/20</span>
        </div>

        <div class="progresso">
            <div id="barra"></div>
        </div>

        <div class="card">

            <div class="info">
                <strong id="anoQuestao"></strong>
                <span id="tipoQuestao"></span>
            </div>

            <img id="imagemQuestao" alt="Imagem da questão do ENEM">

            <h3>Selecione uma alternativa:</h3>

            <div class="alternativas" id="alternativas"></div>

            <div class="navegacao">
                <button class="btn btn-cinza"
                        id="anterior"
                        onclick="mudarQuestao(-1)">
                    Anterior
                </button>

                <button class="btn btn-azul"
                        id="proxima"
                        onclick="mudarQuestao(1)">
                    Próxima
                </button>

                <button class="btn btn-finalizar"
                        onclick="confirmarFinalizacao()">
                    Finalizar
                </button>
            </div>

        </div>
    </div>

    <div id="mensagem"></div>

    <div id="resultado">
        <h2>Simulado finalizado!</h2>

        <p>Você acertou:</p>

        <div id="numeroAcertos">0/20</div>

        <p id="erros"></p>

        <button class="btn btn-azul"
                onclick="location.href='pagsimulados.html'">
            Voltar aos simulados
        </button>
    </div>

</div>

<script>
let questoes = [];
let respostas = {};
let indiceAtual = 0;
let token = "";
let tempoRestante = 2400;
let intervalo = null;
let finalizando = false;

const letras = ["A", "B", "C", "D", "E"];

// Inicia o simulado com o idioma escolhido
async function iniciarSimulado() {

    const parametros = new URLSearchParams(location.search);
    const idioma = parametros.get("idioma");

    if (!["ingles", "espanhol"].includes(idioma)) {
        mostrarErro("Idioma inválido. Volte e selecione Inglês ou Espanhol.");
        return;
    }

    try {
        const resposta = await fetch(
            "buscar_questoes.php?idioma=" + encodeURIComponent(idioma)
        );

        const dados = await resposta.json();

        if (!resposta.ok || !dados.sucesso) {
            throw new Error(dados.mensagem || "Erro ao carregar questões.");
        }

        questoes = dados.questoes;
        token = dados.token;
        tempoRestante = dados.tempo;

        document.getElementById("carregando").style.display = "none";
        document.getElementById("areaSimulado").style.display = "block";

        mostrarQuestao();
        iniciarCronometro();

    } catch (erro) {
        mostrarErro(erro.message);
    }
}

function mostrarQuestao() {

    const questao = questoes[indiceAtual];

    document.getElementById("numeroAtual").textContent =
        `Questão ${indiceAtual + 1} de ${questoes.length}`;

    document.getElementById("anoQuestao").textContent =
        "ENEM " + questao.ano;

    document.getElementById("tipoQuestao").textContent =
        questao.idioma === "geral" ? "Questão geral" :
        questao.idioma === "ingles" ? "Inglês" : "Espanhol";

    document.getElementById("imagemQuestao").src = questao.imagem;

    document.getElementById("imagemQuestao").onerror = function() {
        mostrarErro("Não foi possível carregar a imagem da questão. Verifique o caminho: " + questao.imagem);
    };

    const area = document.getElementById("alternativas");
    area.innerHTML = "";

    letras.forEach(letra => {

        const botao = document.createElement("button");

        botao.className = "alternativa";
        botao.textContent = letra;

        if (respostas[questao.id] === letra) {
            botao.classList.add("selecionada");
        }

        botao.onclick = () => selecionarResposta(letra);

        area.appendChild(botao);
    });

    document.getElementById("anterior").disabled = indiceAtual === 0;

    document.getElementById("proxima").disabled =
        indiceAtual === questoes.length - 1;

    atualizarProgresso();
}

function selecionarResposta(letra) {

    const id = questoes[indiceAtual].id;

    respostas[id] = letra;

    mostrarQuestao();
}

function mudarQuestao(direcao) {

    const novoIndice = indiceAtual + direcao;

    if (novoIndice >= 0 && novoIndice < questoes.length) {
        indiceAtual = novoIndice;
        mostrarQuestao();
    }
}

function atualizarProgresso() {

    const totalRespondidas = Object.keys(respostas).length;

    document.getElementById("respondidas").textContent =
        `Respondidas: ${totalRespondidas}/20`;

    document.getElementById("barra").style.width =
        (totalRespondidas / 20 * 100) + "%";
}

function iniciarCronometro() {

    atualizarCronometro();

    intervalo = setInterval(() => {

        tempoRestante--;

        atualizarCronometro();

        if (tempoRestante <= 0) {
            clearInterval(intervalo);
            finalizarSimulado();
        }

    }, 1000);
}

function atualizarCronometro() {

    const minutos = Math.floor(tempoRestante / 60);
    const segundos = tempoRestante % 60;

    document.getElementById("cronometro").textContent =
        String(minutos).padStart(2, "0") + ":" +
        String(segundos).padStart(2, "0");
}

function confirmarFinalizacao() {

    if (Object.keys(respostas).length < 20) {

        const faltam = 20 - Object.keys(respostas).length;

        if (!confirm(`Você ainda tem ${faltam} questões sem resposta. Deseja finalizar mesmo assim?`)) {
            return;
        }

    } else if (!confirm("Deseja realmente finalizar seu simulado?")) {
        return;
    }

    finalizarSimulado();
}

async function finalizarSimulado() {

    if (finalizando) return;

    finalizando = true;

    clearInterval(intervalo);

    document.getElementById("areaSimulado").style.display = "none";
    document.getElementById("carregando").style.display = "block";
    document.getElementById("carregando").textContent = "Corrigindo seu simulado...";

    try {

        const resposta = await fetch("finalizar_simulado.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                token: token,
                respostas: respostas
            })
        });

        const dados = await resposta.json();

        if (!resposta.ok || !dados.sucesso) {
            throw new Error(dados.mensagem || "Erro ao corrigir.");
        }

        document.getElementById("carregando").style.display = "none";

        document.getElementById("resultado").style.display = "block";

        document.getElementById("numeroAcertos").textContent =
            dados.acertos + "/" + dados.total;

        document.getElementById("erros").textContent =
            "Você errou " + dados.erros + " questões.";

    } catch (erro) {

        finalizando = false;

        document.getElementById("carregando").style.display = "none";
        mostrarErro(erro.message);
    }
}

function mostrarErro(texto) {
    document.getElementById("mensagem").textContent = texto;
}

iniciarSimulado();
</script>

</body>
</html>
