<?php
/**
 * db.php
 * Responsável por abrir a conexão com o banco de dados (via PDO)
 * e garantir que a tabela "tarefas" exista (criação automática).
 *
 * As credenciais de conexão vêm de variáveis de ambiente definidas
 * diretamente no docker-compose.yml (não usamos arquivo .env).
 */

// Lê as variáveis de ambiente configuradas no serviço "app" do docker-compose.yml
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

try {
    // Cria a conexão PDO com o MySQL, usando UTF-8 para acentuação correta
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    // Caso o banco ainda esteja subindo (container mais lento), tentamos algumas vezes
    $tentativas = 0;
    $conectou = false;
    while ($tentativas < 10 && !$conectou) {
        sleep(2);
        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $conectou = true;
        } catch (PDOException $e2) {
            $tentativas++;
        }
    }
    if (!$conectou) {
        die("Não foi possível conectar ao banco de dados: " . $e->getMessage());
    }
}

// Cria a tabela "tarefas" automaticamente caso ela ainda não exista.
// Isso evita a necessidade de rodar um script SQL manual.
$sql = "CREATE TABLE IF NOT EXISTS tarefas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT,
    data_cadastro DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$pdo->exec($sql);
