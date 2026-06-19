<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

// Proteção: Apenas Técnicos logados
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'tecnico') {
    echo json_encode(["success" => false, "message" => "Acesso negado."]);
    exit;
}

// 1. Tenta obter ação do GET
$acao = $_GET['acao'] ?? '';
$id_chamado = 0;

// 2. Se for requisição POST via JSON, decodifica
$inputJSON = json_decode(file_get_contents("php://input"), true);
if (is_array($inputJSON)) {
    if (isset($inputJSON['acao'])) {
        $acao = $inputJSON['acao'];
    }
    if (isset($inputJSON['id_chamado'])) {
        $id_chamado = (int)$inputJSON['id_chamado'];
    }
}

// 3. Se for enviado via FormData tradicional, pega do $_POST
if (empty($acao) && isset($_POST['acao'])) {
    $acao = $_POST['acao'];
}

if ($acao === 'iniciar') {
    $id = ($id_chamado > 0) ? $id_chamado : (int)($_POST['id_chamado'] ?? 0);
    if ($id <= 0) {
        echo json_encode(["success" => false, "message" => "ID do chamado inválido."]);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE chamados SET status = 'em_execucao' WHERE id_chamado = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    
    echo json_encode(["success" => $success]);
} 
elseif ($acao === 'finalizar' || $acao === 'concluir') {
    $id = ($id_chamado > 0) ? $id_chamado : (int)($_POST['id_chamado'] ?? 0);
    if ($id <= 0) {
        echo json_encode(["success" => false, "message" => "ID do chamado inválido."]);
        exit;
    }

    $solucao = isset($_POST['solucao']) ? $conn->real_escape_string($_POST['solucao']) : '';
    
    // Suporta 'tempo' em minutos ou 'tempo_dias' antigo
    $minutos = 0;
    if (isset($_POST['tempo'])) {
        $minutos = (int)$_POST['tempo'];
    } elseif (isset($_POST['tempo_dias'])) {
        $minutos = (int)$_POST['tempo_dias'] * 1440;
    }

    // Atualiza o chamado
    $sql = "UPDATE chamados SET 
            status = 'concluido', 
            solucao_tecnica = '$solucao', 
            tempo_gasto_minutos = $minutos 
            WHERE id_chamado = $id";
    
    if ($conn->query($sql)) {
        // Tenta atualizar data_fechamento se a coluna existir
        $checkCol = $conn->query("SHOW COLUMNS FROM chamados LIKE 'data_fechamento'");
        if ($checkCol && $checkCol->num_rows > 0) {
            $conn->query("UPDATE chamados SET data_fechamento = NOW() WHERE id_chamado = $id");
        }

        // Processamento de Fotos de Fechamento
        if (isset($_FILES['fotos']) || isset($_FILES['foto'])) {
            $diretorio = "../assets/uploads/";
            if (!is_dir($diretorio)) {
                mkdir($diretorio, 0777, true);
            }

            $file_array = isset($_FILES['fotos']) ? $_FILES['fotos'] : $_FILES['foto'];
            
            $names = (array)$file_array['name'];
            $tmp_names = (array)$file_array['tmp_name'];

            foreach ($tmp_names as $key => $tmp_name) {
                if (!empty($tmp_name)) {
                    $extensao = pathinfo($names[$key], PATHINFO_EXTENSION);
                    $nome_arq = "conclusao_" . uniqid() . "." . $extensao;
                    if (move_uploaded_file($tmp_name, $diretorio . $nome_arq)) {
                        $caminho_db = "assets/uploads/" . $nome_arq;
                        $conn->query("INSERT INTO chamados_anexos (id_chamado, caminho_arquivo, tipo_anexo) 
                                     VALUES ($id, '$caminho_db', 'conclusao')");
                    }
                }
            }
        }
        echo json_encode(["success" => true, "message" => "Serviço concluído com sucesso!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Erro ao salvar conclusão: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Ação não encontrada: " . $acao]);
}
?>