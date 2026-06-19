<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'gestor') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGM - Painel Administrativo</title>
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
                    <li class="nav-item"><a class="nav-link active" href="gestor_dashboard.php">Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestor_chamados.php">Chamados</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestor_locais.php">Locais</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestor_servicos.php">Serviços</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestor_usuario.php">Usuários</a></li>
                    <li class="nav-item"><a class="nav-link" href="gestor_relatorios.php">Relatórios</a></li>
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

    <div class="container">
        <div class="row mb-4 animate-fade-in">
            <div class="col-12">
                <h4 class="fw-bold mb-1">Bem-vindo, <?= $_SESSION['user_nome'] ?></h4>
                <p class="text-muted">Aqui está o resumo operacional de hoje.</p>
            </div>
        </div>

        <div class="row g-4 mb-5 animate-fade-in" style="animation-delay: 0.1s">
            <div class="col-md-4">
                <div class="card-stat bg-white p-4 shadow-sm h-100 border-0" style="border-left: 5px solid var(--primary) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-500">Novas Solicitações</p>
                            <h2 class="mb-0 fw-bold" id="qtdAbertos">0</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                            <i class="bi bi-envelope-plus fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-stat bg-white p-4 shadow-sm h-100 border-0" style="border-left: 5px solid var(--warning) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-500">Em Atendimento</p>
                            <h2 class="mb-0 fw-bold" id="qtdExecucao">0</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                            <i class="bi bi-gear-wide-connected fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-stat bg-white p-4 shadow-sm h-100 border-0" style="border-left: 5px solid var(--danger) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-500">Críticos / Urgentes</p>
                            <h2 class="mb-0 fw-bold" id="qtdUrgentes">0</h2>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
                            <i class="bi bi-exclamation-triangle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 animate-fade-in" style="animation-delay: 0.2s">
            <div class="col-md-8">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Ações Rápidas</h5>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <a href="gestor_chamados.php" class="btn btn-light w-100 text-start p-3 border shadow-sm">
                                <i class="bi bi-list-check text-primary me-2 fs-5"></i>
                                <div>
                                    <div class="fw-bold">Visualizar Chamados</div>
                                    <small class="text-muted">Gerencie a fila de trabalho</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="gestor_locais.php" class="btn btn-light w-100 text-start p-3 border shadow-sm">
                                <i class="bi bi-geo-alt text-success me-2 fs-5"></i>
                                <div>
                                    <div class="fw-bold">Gestão de Locais</div>
                                    <small class="text-muted">Blocos e Ambientes</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="gestor_servicos.php" class="btn btn-light w-100 text-start p-3 border shadow-sm">
                                <i class="bi bi-ui-checks-grid text-info me-2 fs-5"></i>
                                <div>
                                    <div class="fw-bold">Configurar Serviços</div>
                                    <small class="text-muted">Tipos de Manutenção</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="gestor_usuario.php" class="btn btn-light w-100 text-start p-3 border shadow-sm">
                                <i class="bi bi-people text-warning me-2 fs-5"></i>
                                <div>
                                    <div class="fw-bold">Equipe Técnica</div>
                                    <small class="text-muted">Gerenciar Usuários</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-12">
                            <a href="gestor_relatorios.php" class="btn btn-light w-100 text-start p-3 border shadow-sm" style="border-left: 5px solid var(--danger) !important;">
                                <i class="bi bi-bar-chart-line text-danger me-2 fs-5"></i>
                                <div>
                                    <div class="fw-bold">Relatórios Operacionais</div>
                                    <small class="text-muted">Indicadores de performance, MTTR, volumetria e dados analíticos</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold mb-3">Status do Sistema</h5>
                    <div class="d-flex align-items-center mb-3">
                        <div class="spinner-grow spinner-grow-sm text-success me-2"></div>
                        <span class="small">Sincronizado com o Banco de Dados</span>
                    </div>
                    <hr>
                    <div class="text-center py-3">
                        <i class="bi bi-shield-check text-success display-4"></i>
                        <p class="mt-3 text-muted small">Proteção de dados ativa</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function atualizarResumo() {
            try {
                const res = await fetch('api/dashboard_gestor.php');
                const dados = await res.json();
                
                document.getElementById('qtdAbertos').innerText = dados.abertos || 0;
                document.getElementById('qtdExecucao').innerText = dados.em_execucao || 0;
                document.getElementById('qtdUrgentes').innerText = dados.urgentes || 0;
            } catch(e) { console.error("Erro ao carregar dashboard:", e); }
        }

        atualizarResumo();
        setInterval(atualizarResumo, 15000);
    </script>
</body>
</html>