<?php
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

// Processar cadastro
function processarCadastro($nome, $email, $telefone, $senha) {
    $conexao = conectarBancoDados();

    // Verificar se o e-mail já está cadastrado
    $stmt = $conexao->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->fetchColumn() > 0) {
        return ['erro' => 'E-mail já cadastrado'];
    }

    // Hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Inserir novo usuário
    $stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, telefone, senha) 
                                VALUES (:nome, :email, :telefone, :senha)");
    
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':senha', $senha_hash);
    
    try {
        $stmt->execute();
        return ['sucesso' => 'Usuário cadastrado com sucesso, redirecionando para a página de login.'];
    } catch(PDOException $e) {
        return ['erro' => 'Erro ao cadastrar usuário: ' . $e->getMessage()];
    }
}

// Processar requisição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Validações básicas
    if (empty($nome) || empty($email) || empty($senha)) {
        echo json_encode(['erro' => 'Preencha todos os campos obrigatórios']);
        exit;
    }

    // Verificar formato de e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['erro' => 'E-mail inválido']);
        exit;
    }

    // Processar cadastro
    $resultado = processarCadastro($nome, $email, $telefone, $senha);
    echo json_encode($resultado);
}
?>