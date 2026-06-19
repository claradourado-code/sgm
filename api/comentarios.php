<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Sessão expirada."]);
    exit;
}

// Garante que a tabela de comentários existe (Auto-healing / Self-healing)
$table_check = "CREATE TABLE IF NOT EXISTS chamados_comentarios (
    id_comentario INT NOT NULL AUTO_INCREMENT,
    texto TEXT NOT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_chamado INT NOT NULL,
    id_usuario INT NOT NULL,
    PRIMARY KEY (id_comentario),
    FOREIGN KEY (id_chamado) REFERENCES chamados (id_chamado) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$conn->query($table_check);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = (int)($_GET['id_chamado'] ?? 0);
    $sql = "SELECT c.texto, c.data_envio, u.nome, u.perfil 
            FROM chamados_comentarios c 
            JOIN usuarios u ON c.id_usuario = u.id_usuario 
            WHERE c.id_chamado = $id 
            ORDER BY c.data_envio ASC";
    $result = $conn->query($sql);
    if ($result) {
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    } else {
        echo json_encode([]);
    }
} else {
    $data = json_decode(file_get_contents("php://input"));
    if (!$data || !isset($data->id_chamado) || empty($data->comentario)) {
        echo json_encode(["success" => false, "message" => "Dados inválidos."]);
        exit;
    }
    
    $texto = $conn->real_escape_string($data->comentario);
    $id_c = (int)$data->id_chamado;
    $id_u = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO chamados_comentarios (id_chamado, id_usuario, texto) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $id_c, $id_u, $texto);
    $success = $stmt->execute();
    
    echo json_encode(["success" => $success]);
}
?>