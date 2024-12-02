<?php
session_start();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_id']) || !$_SESSION['usuario_eh_admin']) {
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

    <head>
        <meta charset="UTF-8"> <!-- Usado para carregar caracteres especiais corretamente -->
        <meta name="viewport" content="width=device-width>, initial-scale=1.0"> <!-- Utilizado para -->
        <link rel="stylesheet" href="../css/usuario.css"> <!-- CSS da Página -->
        <link rel="icon" href="../assets/favicon.png" type="image/png"> <!-- Ícone da Página -->
        <title>Página de Usuário</title> <!-- Título da Página -->

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

        <div class="usuario-card" id="usuarioDetalhes">
            <h1>Detalhes do Usuário</h1>
            <!-- Conteúdo será preenchido dinamicamente com JavaScript -->
            <div id="carregando" class="carregando">Carregando...</div>
        </div>

        <script>
            // Função para formatar data
            function formatarData(dataString) {
                const data = new Date(dataString);
                return data.toLocaleString('pt-BR', {
                    day: '2-digit', 
                    month: '2-digit', 
                    year: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit'
                });
            }

            // Função para renderizar detalhes do usuário
            function renderizarUsuario(usuario) {
                const container = document.getElementById('usuarioDetalhes');
                const carregando = document.getElementById('carregando');
                
                // Esconder mensagem de carregando
                carregando.style.display = 'none';

                // Adicionar badge de admin se necessário
                const adminBadge = usuario.eh_admin 
                    ? '<a href="adminDashboard.php" class="admin-badge">Voltar ao Dashboard</a>' 
                    : '';

                container.innerHTML = `
                    ${adminBadge}
                    <div class="usuario-info">
                        <p><strong>Nome:</strong> ${usuario.nome}</p>
                        <p><strong>E-mail:</strong> ${usuario.email}</p>
                        <p><strong>Telefone:</strong> ${usuario.telefone || 'Não informado'}</p>
                        <p><strong>Data de Criação da Conta:</strong> ${formatarData(usuario.data_criacao)}</p>
                    </div>
                `;
            }

            // Função para mostrar erro
            function mostrarErro(mensagem) {
                const container = document.getElementById('usuarioDetalhes');
                const carregando = document.getElementById('carregando');
                
                // Esconder mensagem de carregando
                carregando.style.display = 'none';

                container.innerHTML = `
                    <div class="erro">
                        <h2>Erro ao carregar usuário</h2>
                        <p>${mensagem}</p>
                    </div>
                `;
            }

            function carregarUsuario(usuarioId) {
                const formData = new FormData();
                formData.append('usuario_id', usuarioId);

                fetch("../processar_usuario.php", {
                    method: 'POST',
                    body: formData
                })
                .then(resposta => {
                    if (!resposta.ok) {
                        throw new Error('Erro na resposta do servidor');
                    }
                    return resposta.json();
                })
                .then(renderizarUsuario)
                .catch(erro => {
                    console.error('Erro:', erro);
                    mostrarErro('Não foi possível carregar os detalhes do usuário. Tente novamente mais tarde.');
                });
            }

            // Carregar usuário quando a página carregar
            document.addEventListener('DOMContentLoaded', () => {
                const usuarioId = <?php echo $_GET['usuario_id'] ?>; // Exemplo, substitua pelo ID real
                carregarUsuario(usuarioId);
            });
        </script>

    </body>
</html>