<?php
session_start();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario_id']) || !$_SESSION['usuario_eh_admin']) {
    header("Location: login.php");
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
        die(json_encode(['erro' => "Erro de conexão: " . $e->getMessage()]));
    }
}

// Buscar todos os agendamentos
function buscarAgendamentos() {
    $conexao = conectarBancoDados();
    $stmt = $conexao->prepare("SELECT a.id, u.nome AS usuario, s.nome AS servico, 
                                    a.data_agendamento, a.status 
                                FROM agendamentos a
                                JOIN usuarios u ON a.usuario_id = u.id
                                JOIN servicos s ON a.servico_id = s.id
                                ORDER BY a.data_agendamento");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Atualizar Status
function atualizarStatus($agendamento_id, $novo_status) {
    $conexao = conectarBancoDados();
    $stmt = $conexao->prepare("UPDATE agendamentos SET status = :novo_status WHERE id = :agendamento_id");
    $stmt->bindParam(':novo_status', $novo_status);
    $stmt->bindParam(':agendamento_id', $agendamento_id);

    try {
        $stmt->execute();
        return ['sucesso' => 'Status de agendamento alterado com sucesso'];
    } catch (PDOException $e) {
        return ['erro' => 'Erro ao alterar status: '. $e->getMessage()];
    }
}

// Processar ação de atualização de status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar_status') {
    $resultado = atualizarStatus($_POST['agendamento_id'], $_POST['novo_status']);
    echo json_encode($resultado);
    exit();
}

// Buscar todos os agendamentos
$agendamentos = buscarAgendamentos();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

    <head>
        <meta charset="UTF-8"> <!-- Usado para carregar caracteres especiais corretamente -->
        <meta name="viewport" content="width=device-width>, initial-scale=1.0"> <!-- Utilizado para -->
        <link rel="stylesheet" href="../css/adminDashboard.css"> <!-- CSS da Página -->
        <link rel="icon" href="../assets/favicon.png" type="image/png"> <!-- Ícone da Página -->
        <title>Dashboard de Admin</title> <!-- Título da Página -->

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
        <h1>Dashboard</h1>

        <!-- Interface para o admin -->
        <div class="admin-dashboard">

            <?php if (empty($agendamentos)): ?>
                <p>Você não possui agendamentos.</p>
            <?php else: ?>
                <section class="agendamentos">
                    <h2>Todos os Agendamentos</h2>
                    <table border="1">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Serviço</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agendamentos as $agendamento): ?>
                            <tr>
                                <td><?= $agendamento['id'] ?></td>
                                <td><a href="usuario.php?usuario_id=<?php echo $agendamento['id'] ?>"><?= $agendamento['usuario'] ?></a></td>
                                <td><?= htmlspecialchars($agendamento['servico']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($agendamento['data_agendamento'])) ?></td>
                                <td><?= $agendamento['status'] ?></td>
                                <td>
                                    <select onchange="atualizarStatus(<?= $agendamento['id'] ?>, this.value)">
                                        <option>Alterar Status</option>
                                        <option value="agendado">Agendado</option>
                                        <option value="confirmado">Confirmado</option>
                                        <option value="cancelado">Cancelado</option>
                                        <option value="concluído">Concluído</option>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endif; ?>
        </div>

        <script>
            function atualizarStatus(agendamentoId, novoStatus) {
                const formData = new FormData();
                formData.append('acao', 'atualizar_status');
                formData.append('agendamento_id', agendamentoId);
                formData.append('novo_status', novoStatus);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(resultado => {
                    if (resultado.sucesso) {
                        alert('Status atualizado com sucesso');
                        location.reload();
                    } else {
                        alert(resultado.erro);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao atualizar status');
                });
            }
        </script>

    </body>
</html>
