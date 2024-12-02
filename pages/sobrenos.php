<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

    <head>
        <meta charset="UTF-8"> <!-- Usado para carregar caracteres especiais corretamente -->
        <meta name="viewport" content="width=device-width>, initial-scale=1.0"> <!-- Utilizado para -->
        <link rel="stylesheet" href="../css/sobrenos.css"> <!-- CSS da Página -->
        <link rel="icon" href="../assets/favicon.png" type="image/png"> <!-- Ícone da Página -->
        <title>Sobre Nós</title> <!-- Título da Página -->

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
        <h1>Sobre Nós</h1>
        <div class="unhas unha1"></div>
        <div class="unhas unha2"></div>
        <p>Nanicure a manicure que tira o peso das suas mãos.</p>
    </body>

</html>