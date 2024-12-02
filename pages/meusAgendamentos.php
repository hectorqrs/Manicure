<?php
session_start();

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Configurações de conexão com o banco de dados
$host = 'localhost';
$usuario_db = 'root';
$senha_db = '';
$nome_db = 'manicure_db';

// Função para conectar ao banco de dados
function conectarBancoDados() {
    global $host, $usuario_db, $senha_db, $nome_db;
    
    try {
        $conexao = new PDO("mysql:host=$host;dbname=$nome_db;charset=utf8", $usuario_db, $senha_db);
        $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexao;
    } catch(PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
}

// Buscar agendamentos do usuário
function buscarAgendamentos($usuario_id) {
    $conexao = conectarBancoDados();
    
    $stmt = $conexao->prepare("SELECT a.id, s.nome as servico, s.preco, a.data_agendamento, a.status 
                                FROM agendamentos a
                                JOIN servicos s ON a.servico_id = s.id
                                WHERE a.usuario_id = :usuario_id
                                ORDER BY a.data_agendamento DESC");
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Cancelar agendamento
function cancelarAgendamento($agendamento_id, $usuario_id) {
    $conexao = conectarBancoDados();
    
    $stmt = $conexao->prepare("UPDATE agendamentos 
                                SET status = 'cancelado' 
                                WHERE id = :agendamento_id AND usuario_id = :usuario_id");
    $stmt->bindParam(':agendamento_id', $agendamento_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    
    try {
        $stmt->execute();
        return ['sucesso' => 'Agendamento cancelado com sucesso'];
    } catch(PDOException $e) {
        return ['erro' => 'Erro ao cancelar agendamento: ' . $e->getMessage()];
    }
}

// Processar ação de cancelamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cancelar_agendamento') {
    $resultado = cancelarAgendamento($_POST['agendamento_id'], $_SESSION['usuario_id']);
    echo json_encode($resultado);
    exit();
}

// Buscar agendamentos do usuário atual
$agendamentos = buscarAgendamentos($_SESSION['usuario_id']);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

    <head>
        <meta charset="UTF-8"> <!-- Usado para carregar caracteres especiais corretamente -->
        <meta name="viewport" content="width=device-width>, initial-scale=1.0"> <!-- Utilizado para -->
        <link rel="stylesheet" href="../css/meusAgendamentos.css"> <!-- CSS da Página -->
        <link rel="icon" href="../assets/favicon.png" type="image/png"> <!-- Ícone da Página -->
        <title>Meus Agendamentos</title> <!-- Título da Página -->

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
        <h1>Meus Agendamentos</h1>

        <?php if (empty($agendamentos)): ?>
            <p>Você não possui agendamentos.</p>
        <?php else: ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>Serviço</th>
                        <th>Preço</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agendamentos as $agendamento): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($agendamento['servico']); ?></td>
                            <td>R$ <?php echo number_format($agendamento['preco'], 2, ',', '.'); ?></td>
                            <td>
                                <?php 
                                $data = new DateTime($agendamento['data_agendamento']);
                                echo $data->format('d/m/Y H:i');
                                ?>
                            </td>
                            <td class="status-<?php echo strtolower($agendamento['status']); ?>">
                                <?php echo $agendamento['status']; ?>
                            </td>
                            <td>
                                <?php if ($agendamento['status'] === 'agendado'): ?>
                                    <button 
                                        class="botao-cancelar" 
                                        onclick="cancelarAgendamento(<?php echo $agendamento['id']; ?>)"
                                    >
                                        Cancelar
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <script>
            function cancelarAgendamento(agendamentoId) {
                if (!confirm('Tem certeza que deseja cancelar este agendamento?')) {
                    return;
                }

                const formData = new FormData();
                formData.append('acao', 'cancelar_agendamento');
                formData.append('agendamento_id', agendamentoId);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(resultado => {
                    if (resultado.sucesso) {
                        alert(resultado.sucesso);
                        window.location.reload();
                    } else {
                        alert(resultado.erro);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao processar cancelamento');
                });
            }
        </script>

    </body>
</html>