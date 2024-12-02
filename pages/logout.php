<?php
session_start();
session_destroy(); // Encerra todas as sessões
header('Location: login.php'); // Redireciona para página de login
exit();
?>