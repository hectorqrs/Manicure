<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

    <head>
        <meta charset="UTF-8"> <!-- Usado para carregar caracteres especiais corretamente -->
        <meta name="viewport" content="width=device-width>, initial-scale=1.0"> <!-- Utilizado para -->
        <link rel="stylesheet" href="../css/login.css"> <!-- CSS da Página -->
        <link rel="icon" href="../assets/favicon.png" type="image/png"> <!-- Ícone da Página -->
        <title>Login</title> <!-- Título da Página -->

        <!-- Importando fontes -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=Whisper&display=swap"
            rel="stylesheet">
    </head>

    <body>
        <header>
            <img class="logo" src="../assets/logo.png" width="135px" height="85px">
            <a href="../index.php">Inicio</a>
            <a href="servicos.php">Serviços</a>
            <a href="faleconosco.php">Fale conosco</a>
            <a href="sobrenos.php">Sobre Nós</a>

            <!-- Mostrar páginas diferentes dependendo se você estiver logado ou não -->
            <?php if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_eh_admin'])) { ?>
                <a href="adminDashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php } elseif (isset($_SESSION['usuario_id'])) { ?>
                <a href="agendamento.php">Agendar</a>
                <a href="meusAgendamentos.php">Meus Agendamentos</a>
                <a href="logout.php">Logout</a>
            <?php } else { ?>
                <a href="cadastro.php">Cadastro</a>
                <a href="login.php">Login</a>
            <?php } ?>
        </header>
        <h1>Login</h1>

        <fieldset>
            <form id="formulario-login">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>

                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>

                <button type="submit">Entrar</button>
                <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
            </form>

            <div id="mensagem-erro" class="erro"></div>
        </fieldset>

        <script>
            document.getElementById('formulario-login').addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                const mensagemErro = document.getElementById('mensagem-erro');

                fetch('../processar_login.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(resultado => {
                        if (resultado.sucesso) {
                            window.location.href = '../index.php'; // Redirecionar para página inicial
                        } else {
                            mensagemErro.textContent = resultado.erro;
                            mensagemErro.style.display = "block";
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        mensagemErro.textContent = 'Erro ao processar o login';
                        mensagemErro.style.display = "block";
                    });
            });
        </script>

    </body>
</html>