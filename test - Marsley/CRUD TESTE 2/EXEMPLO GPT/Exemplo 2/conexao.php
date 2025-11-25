<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Projeto Doação de Sangue</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8f9fa;
        }
        header {
            text-align: center;
            margin-bottom: 30px;
        }
        section {
            background-color: #ffffff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 0 8px #ccc;
        }
        h2 {
            color: #c62828;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input, select, button {
            padding: 8px;
            margin-top: 5px;
            width: 100%;
            max-width: 300px;
            box-sizing: border-box;
        }
        button {
            background-color: #c62828;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background-color: #b71c1c;
        }
        .info {
            margin-top: 10px;
            font-weight: bold;
        }
        ul {
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Projeto Doação de Sangue</h1>
        <p>Cadastre-se para ser um doador e ajude a salvar vidas!</p>
    </header>

    <section id="cadastro">
        <h2>Cadastro de Doador</h2>
        <form id="formDoador" onsubmit="adicionarDoador(event)">
            <label for="nome">Nome Completo:</label>
            <input type="text" id="nome" name="nome" required />

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required />

            <label for="telefone">Telefone:</label>
            <input type="tel" id="telefone" name="telefone" required />

            <label for="tipoSanguineo">Tipo Sanguíneo:</label>
            <select id="tipoSanguineo" name="tipoSanguineo" required>
                <option value="">Selecione</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>

            <button type="submit">Cadastrar</button>
        </form>
        <p class="info" id="statusCadastro"></p>
    </section>

    <section id="estatisticas">
        <h2>Estatísticas</h2>
        <p>Total de doadores cadastrados: <span id="totalDoadores">0</span></p>
        <p>Quantidade de sangue armazenada: <span id="quantidadeSangue">0</span> litros</p>
    </section>

    <section id="bancosDeSangue">
        <h2>Locais de Bancos de Sangue</h2>
        <ul id="listaBancos">
            <li>Hospital Central - Rua das Flores, 123</li>
            <li>Hemocentro Regional - Av. Brasil, 456</li>
            <li>Clínica Vida - Praça da Saúde, 789</li>
        </ul>
    </section>

    <script>
        const doadores = [];
        let estoqueSangue = 0;

        function adicionarDoador(event) {
            event.preventDefault();
            const nome = document.getElementById('nome').value.trim();
            const email = document.getElementById('email').value.trim();
            const telefone = document.getElementById('telefone').value.trim();
            const tipoSanguineo = document.getElementById('tipoSanguineo').value;

            // Simples validação adicional
            if (!nome || !email || !telefone || !tipoSanguineo) {
                alert('Por favor, preencha todos os campos.');
                return;
            }

            // Adiciona doador à lista
            doadores.push({ nome, email, telefone, tipoSanguineo });

            // Exemplo: adiciona 0.5 litro por doador ao estoque
            estoqueSangue += 0.5;

            // Atualiza estatísticas visuais
            document.getElementById('totalDoadores').textContent = doadores.length;
            document.getElementById('quantidadeSangue').textContent = estoqueSangue.toFixed(1);

            document.getElementById('statusCadastro').textContent = `Obrigado por se cadastrar, ${nome}!`;

            // Limpa formulário
            event.target.reset();
        }
    </script>
</body>
</html>