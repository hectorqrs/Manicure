<?php

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

// Buscar informações do usuário
function buscarUsuário($id) {
    $conexao = conectarBancoDados();
    $stmt = $conexao->prepare("SELECT * from usuarios WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Processar ação de atualização de status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'])) {
    $resultado = buscarUsuário($_POST['usuario_id']);
    echo json_encode($resultado[0]);
    exit();
}
?>