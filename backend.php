<?php

// Configurações de conexão para ambiente Vercel
$host = getenv('DB_HOST');
$usuario_db = getenv('DB_USER');
$senha_db = getenv('DB_PASSWORD');
$nome_db = getenv('DB_NAME');

// Configurações de conexão com o banco de dados
// $host = 'localhost';
// $usuario_db = 'root';
// $senha_db = '';
// $nome_db = 'manicure_db';

// Função para conectar ao banco de dados
function conectarBancoDados() {
    global $host, $usuario_db, $senha_db, $nome_db;

    try {
        $conexao = new PDO("mysql:host=$host;dbname=$nome_db;charset=utf8", $usuario_db, $senha_db);
        $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexao;
    } catch (PDOException $e) {
        die("Erro de conexão: ". $e->getMessage());
    }
}

// Listar serviços disponíveis
function listarServicos() {
    $conexao = conectarBancoDados();
    $stmt = $conexao->prepare("SELECT * FROM servicos WHERE ativo = 1");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Criar um novo agendamento
function criarAgendamento($usuario_id, $servico_id, $data_agendamento){
    $conexao = conectarBancoDados();

     // Converter string para DateTime
    $data = new DateTime($data_agendamento);
    $diaSemana = $data->format('N'); // 1 (segunda) a 7 (domingo)
    $hora = $data->format('H:i');

    // Validações de horário
    if ($diaSemana == 7) { // Domingo
        return ['erro' => 'Não funcionamos aos domingos'.$hora.' Dia Enviado: '.$diaSemana];
    }

    // Horários de sábado
    if ($diaSemana == 6 && ($hora < '10:00' || $hora > '14:30')) {
        return ['erro' => 'Aos sábados, funcionamos das 10:00 às 14:30. Horário Enviado: '.$hora.' Dia Enviado: '.$diaSemana];
    }

    // Horários de semana
    if ($diaSemana < 6 && ($hora < '08:00' || $hora > '17:30')) {
        return ['erro' => 'Nos dias de semana, funcionamos das 08:00 às 17:30. Horário Enviado: '.$hora.' Dia Enviado: '.$diaSemana];
    }

    // Verificar disponibilidade do horário
    $stmt = $conexao->prepare("SELECT COUNT(*) FROM agendamentos
                                WHERE data_agendamento = :data
                                AND status NOT IN ('cancelado', 'concluido')");

    $stmt->bindParam(':data', $data_agendamento);
    $stmt->execute();
    $conflitos = $stmt->fetchColumn();

    if ($conflitos > 0) {
        return ['erro' => 'Horário já está ocupado'];
    }


    // Inserir novo agendamento
    $stmt = $conexao->prepare("INSERT INTO agendamentos
                                (usuario_id, servico_id, data_agendamento)
                                VALUES (:usuario_id, :servico_id, :data)");

    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':servico_id', $servico_id);
    $stmt->bindParam(':data', $data_agendamento);

    try {
        $stmt->execute();
        return ['sucesso' => 'Agendamento criado com sucesso'];
    } catch (PDOException $e) {
        return ['erro' => 'Erro ao criar agendamento: '. $e->getMessage().'usuario_id: '.$usuario_id.' servico_id: '.$servico_id.' data_agendamento: '.$data_agendamento];
    }
}

// Processar requisições
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET["acao"]) && $_GET["acao"] == "listar_servicos"){
        echo json_encode(listarServicos());
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if(isset($_POST["acao"]) && $_POST["acao"] === "criar_agendamento") {
        $usuario_id =  $_POST["usuario_id"];
        $servico_id =  $_POST["servico_id"];
        $data_agendamento =  $_POST["data_agendamento"];

        echo json_encode(criarAgendamento($usuario_id, $servico_id, $data_agendamento));
    }
}
?>