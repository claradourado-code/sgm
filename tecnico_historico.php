<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'tecnico') {
    header("Location: login.php"); exit;
}
require_once 'config/database.php';

$id_tecnico = $_SESSION['user_id'];

// Seleciona chamados finalizados pelo técnico logado
$sql = "SELECT c.*, a.nome as ambiente_nome, b.nome as bloco_nome, ts.nome as tipo_nome
        FROM chamados c
        JOIN ambientes a ON c.id_ambiente = a.id_ambiente
        JOIN blocos b ON a.id_bloco = b.id_bloco
        JOIN tipos_servico ts ON c.id_tipo_servico = ts.id_tipo
        WHERE c.id_tecnico = $id_tecnico AND c.status IN ('concluido', 'fechado')
        ORDER BY c.id_chamado DESC";

$result = $conn->query($sql);
$chamados = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGM - Meu Histórico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background: linear-gradient(135deg, #2b2d42, #8d99ae) !important;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="tecnico_minhas_tarefas.php"><i class="bi bi-tools me-2"></i> SGM TÉCNICO</a>
            <div class="navbar-nav ms-auto align-items-center">
                <a class="nav-link text-white small me-3" href="tecnico_minhas_tarefas.php"><i class="bi bi-calendar-check me-1"></i> Minha Agenda</a>
                <a class="nav-link text-white small me-3 active" href="tecnico_historico.php"><i class="bi bi-archive me-1"></i> Meu Histórico</a>
                <span class="nav-link text-white small me-3"><?= $_SESSION['user_nome'] ?></span>
                <a class="btn btn-sm btn-outline-light border-0" href="api/logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
            </div>
        </div>
    </nav>

    <div class="container animate-fade-in pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Histórico de Atendimentos</h4>
                <p class="text-muted small mb-0">Aqui estão todos os chamados que você já concluiu.</p>
            </div>
            <a href="tecnico_minhas_tarefas.php" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Fila Ativa
            </a>
        </div>

        <?php if (count($chamados) === 0): ?>
            <div class="row">
                <div class="col-12 text-center py-5">
                    <div class="glass-card p-5">
                        <i class="bi bi-archive text-muted display-2"></i>
                        <h5 class="mt-3 text-muted">Nenhum chamado concluído por você até o momento.</h5>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($chamados as $c): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="glass-card p-0 overflow-hidden h-100 shadow-sm border-0 d-flex flex-column justify-content-between">
                            <div>
                                <div class="p-3 bg-success bg-opacity-10 d-flex justify-content-between align-items-center border-bottom border-white border-opacity-25">
                                    <span class="badge bg-success text-uppercase"><?= $c['status'] ?></span>
                                    <small class="fw-bold text-muted">#<?= $c['id_chamado'] ?></small>
                                </div>
                                <div class="p-4">
                                    <div class="mb-3">
                                        <div class="small text-muted mb-1"><?= htmlspecialchars($c['bloco_nome']) ?></div>
                                        <h5 class="fw-bold mb-0"><?= htmlspecialchars($c['ambiente_nome']) ?></h5>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block small">Problema relatado:</small>
                                        <p class="text-muted small mb-0 text-truncate"><?= htmlspecialchars($c['descricao_problema']) ?></p>
                                    </div>
                                    <div class="mb-3 bg-white bg-opacity-50 p-2 rounded border border-white border-opacity-50">
                                        <small class="fw-bold text-success d-block small mb-1"><i class="bi bi-check-circle-fill me-1"></i>Solução Aplicada:</small>
                                        <p class="text-dark small mb-0 text-truncate" title="<?= htmlspecialchars($c['solucao_tecnica']) ?>"><?= htmlspecialchars($c['solucao_tecnica']) ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 py-3 bg-light border-top d-flex justify-content-between align-items-center">
                                <span class="small text-muted"><i class="bi bi-clock me-1"></i> Gasto: <strong><?= $c['tempo_gasto_minutos'] ?? 0 ?> min</strong></span>
                                <span class="badge bg-secondary bg-opacity-10 text-dark border"><?= htmlspecialchars($c['tipo_nome']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
