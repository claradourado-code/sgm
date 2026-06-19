<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'gestor') {
    header("Location: login.php"); exit;
}
require_once 'config/database.php';

// Filtro de data opcional
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');

// Sanitização simples das datas
$db_inicio = $conn->real_escape_string($data_inicio) . " 00:00:00";
$db_fim = $conn->real_escape_string($data_fim) . " 23:59:59";

$where_date = "WHERE c.data_abertura BETWEEN '$db_inicio' AND '$db_fim'";

// 1. KPI: Total de chamados no período
$total_chamados = $conn->query("SELECT COUNT(*) FROM chamados c $where_date")->fetch_row()[0];

// 2. KPI: Concluídos/Fechados no período
$total_concluidos = $conn->query("SELECT COUNT(*) FROM chamados c $where_date AND c.status IN ('concluido', 'fechado')")->fetch_row()[0];

// 3. KPI: Tempo Médio de Resolução (MTTR) em minutos
$mttr = $conn->query("SELECT AVG(tempo_gasto_minutos) FROM chamados c $where_date AND c.status IN ('concluido', 'fechado') AND tempo_gasto_minutos IS NOT NULL")->fetch_row()[0];
$mttr = $mttr ? round($mttr, 1) : 0;

// 4. Ambientes com mais chamados (Top 5)
$sql_ambientes = "SELECT a.nome as ambiente_nome, b.nome as bloco_nome, COUNT(c.id_chamado) as total
                  FROM chamados c
                  JOIN ambientes a ON c.id_ambiente = a.id_ambiente
                  JOIN blocos b ON a.id_bloco = b.id_bloco
                  $where_date
                  GROUP BY c.id_ambiente
                  ORDER BY total DESC LIMIT 5";
$top_ambientes = $conn->query($sql_ambientes)->fetch_all(MYSQLI_ASSOC);

// 5. Chamados por Categoria de Serviço
$sql_servicos = "SELECT ts.nome as servico_nome, COUNT(c.id_chamado) as total
                 FROM chamados c
                 JOIN tipos_servico ts ON c.id_tipo_servico = ts.id_tipo
                 $where_date
                 GROUP BY c.id_tipo_servico
                 ORDER BY total DESC";
$dist_servicos = $conn->query($sql_servicos)->fetch_all(MYSQLI_ASSOC);

// 6. Lista Geral de Chamados do Período
$sql_geral = "SELECT c.id_chamado, c.status, c.prioridade, c.data_abertura, 
                     a.nome as ambiente_nome, b.nome as bloco_nome, 
                     ts.nome as servico_nome, u.nome as solicitante_nome, t.nome as tecnico_nome
              FROM chamados c
              JOIN ambientes a ON c.id_ambiente = a.id_ambiente
              JOIN blocos b ON a.id_bloco = b.id_bloco
              JOIN tipos_servico ts ON c.id_tipo_servico = ts.id_tipo
              JOIN usuarios u ON c.id_solicitante = u.id_usuario
              LEFT JOIN usuarios t ON c.id_tecnico = t.id_usuario
              $where_date
              ORDER BY c.id_chamado DESC";
$lista_chamados = $conn->query($sql_geral)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGM - Relatórios Operacionais</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="gestor_dashboard.php">
                <i class="bi bi-tools me-2"></i> SGM ADMIN
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="gestor_dashboard.php">Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestor_chamados.php">Chamados</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestor_locais.php">Locais</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestor_servicos.php">Serviços</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestor_usuario.php">Usuários</a></li>
                    <li class="nav-item"><a class="nav-link active" href="gestor_relatorios.php">Relatórios</a></li>
                    <li class="nav-item ms-lg-3">
                        <div class="d-flex align-items-center bg-dark p-1 px-3 rounded-pill text-white">
                            <i class="bi bi-person-circle me-2"></i>
                            <span class="small me-3"><?= $_SESSION['user_nome'] ?></span>
                            <a href="api/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i></a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container animate-fade-in pb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Relatórios Operacionais</h2>
                <p class="text-muted">Análise de performance, recorrência e produtividade da equipe.</p>
            </div>
            <!-- Filtro de Datas -->
            <form method="GET" class="glass-card p-3 d-flex flex-wrap gap-2 align-items-end border-0 shadow-sm">
                <div>
                    <label class="form-label small fw-bold text-muted mb-1">Início</label>
                    <input type="date" name="data_inicio" class="form-control form-control-sm border-0 bg-light shadow-sm" value="<?= $data_inicio ?>">
                </div>
                <div>
                    <label class="form-label small fw-bold text-muted mb-1">Fim</label>
                    <input type="date" name="data_fim" class="form-control form-control-sm border-0 bg-light shadow-sm" value="<?= $data_fim ?>">
                </div>
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"><i class="bi bi-filter"></i> Filtrar</button>
            </form>
        </div>

        <!-- KPIs Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="glass-card p-4 h-100 border-0 shadow-sm" style="border-left: 5px solid var(--primary) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-500">Solicitações no Período</p>
                            <h2 class="mb-0 fw-bold"><?= $total_chamados ?></h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                            <i class="bi bi-clipboard-data fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 h-100 border-0 shadow-sm" style="border-left: 5px solid var(--success) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-500">Manutenções Concluídas</p>
                            <h2 class="mb-0 fw-bold"><?= $total_concluidos ?></h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                            <i class="bi bi-check-all fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 h-100 border-0 shadow-sm" style="border-left: 5px solid var(--warning) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-500">Tempo Médio de Reparo (MTTR)</p>
                            <h2 class="mb-0 fw-bold"><?= $mttr ?> <span class="fs-6 fw-normal text-muted">min</span></h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                            <i class="bi bi-clock-history fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <!-- Ambientes Críticos (Top Recorrência) -->
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt text-danger me-2"></i>Ambientes Críticos (Mais Ocorrências)</h5>
                    <?php if(count($top_ambientes) === 0): ?>
                        <p class="text-muted small py-4 text-center">Nenhum chamado no período selecionado.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Localização</th>
                                        <th class="text-center">Quantidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($top_ambientes as $amb): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($amb['ambiente_nome']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($amb['bloco_nome']) ?></small>
                                            </td>
                                            <td class="text-center fw-bold text-danger"><?= $amb['total'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Distribuição por Categorias de Serviço -->
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart text-primary me-2"></i>Chamados por Tipo de Serviço</h5>
                    <?php if(count($dist_servicos) === 0): ?>
                        <p class="text-muted small py-4 text-center">Nenhuma categoria registrada.</p>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3 mt-3">
                            <?php 
                            $max_total = max(array_column($dist_servicos, 'total')); 
                            foreach($dist_servicos as $serv): 
                                $percent = $total_chamados > 0 ? ($serv['total'] / $total_chamados) * 100 : 0;
                            ?>
                                <div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small fw-bold text-dark"><?= htmlspecialchars($serv['servico_nome']) ?></span>
                                        <span class="small text-muted fw-bold"><?= $serv['total'] ?> chamado(s) (<?= round($percent, 1) ?>%)</span>
                                    </div>
                                    <div class="progress rounded-pill" style="height: 10px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percent ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tabela Geral Detalhada -->
        <div class="row">
            <div class="col-12">
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-list-check me-2 text-dark"></i>Dados Analíticos do Período</h5>
                        <button onclick="window.print()" class="btn btn-sm btn-outline-dark rounded-pill px-3"><i class="bi bi-printer me-1"></i> Imprimir Relatório</button>
                    </div>
                    <?php if(count($lista_chamados) === 0): ?>
                        <p class="text-muted text-center py-5">Nenhuma ocorrência encontrada para os filtros aplicados.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th class="ps-3">ID</th>
                                        <th>Solicitante</th>
                                        <th>Localização</th>
                                        <th>Categoria</th>
                                        <th>Prioridade</th>
                                        <th>Responsável</th>
                                        <th>Status</th>
                                        <th class="pe-3">Data Abertura</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($lista_chamados as $ch): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold">#<?= $ch['id_chamado'] ?></td>
                                            <td><?= htmlspecialchars($ch['solicitante_nome']) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($ch['ambiente_nome']) ?></strong>
                                                <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($ch['bloco_nome']) ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($ch['servico_nome']) ?></td>
                                            <td class="text-uppercase fw-bold"><?= htmlspecialchars($ch['prioridade']) ?></td>
                                            <td><?= htmlspecialchars($ch['tecnico_nome'] ?? 'Pendente') ?></td>
                                            <td><span class="badge <?= obterBadgeClass($ch['status']) ?>"><?= htmlspecialchars(str_replace('_', ' ', $ch['status'])) ?></span></td>
                                            <td class="pe-3"><?= date('d/m/Y H:i', strtotime($ch['data_abertura'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
function obterBadgeClass($s) {
    $map = [ 'aberto' => 'bg-secondary', 'agendado' => 'bg-info text-white', 'em_execucao' => 'bg-warning text-dark', 'concluido' => 'bg-success', 'fechado' => 'bg-dark' ];
    return $map[$s] ?? 'bg-secondary';
}
?>
