<?php
session_start();

// Configurações de conexão para ambiente Vercel
// $host = getenv('DB_HOST');
// $usuario_db = getenv('DB_USER');
// $senha_db = getenv('DB_PASSWORD');
// $nome_db = getenv('DB_NAME');

// Configurações de conexão com o banco de dados
// $host = 'localhost';
// $usuario_db = 'root';
// $senha_db = '';
// $nome_db = 'manicure_db';

// Função para conectar ao banco de dados
function conectarBancoDados() {
    global $host, $usuario_db, $senha_db, $nome_db;
    
    try {
        $conexao = new PDO('sqlite:' . __DIR__ . '/pages/manicure_db.sqlite');
        $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexao;
    } catch(PDOException $e) {
        die(json_encode(['erro' => "Erro de conexão: " . $e->getMessage()]));
    }
}

// Processar login
function processarLogin($email, $senha) {
    $conexao = conectarBancoDados();

    // Buscar usuário pelo e-mail
    $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        return ['erro' => 'Usuário ou Senha incorretos']; // Usuário não encontrado
    }

    // Verificar senha
    if (!password_verify($senha, $usuario['senha'])) {
        return ['erro' => 'Usuário ou Senha incorretos']; // Senha incorreta
    }

    // Iniciar sessão
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    if ($usuario['eh_admin']) {
        $_SESSION['usuario_eh_admin'] = $usuario['eh_admin'];
    }

    return ['sucesso' => 'Login realizado com sucesso'];
}

// Processar requisição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Validações básicas
    if (empty($email) || empty($senha)) {
        echo json_encode(['erro' => 'Preencha todos os campos']);
        exit;
    }

    // Processar login
    $resultado = processarLogin($email, $senha);
    echo json_encode($resultado);
}
?>