<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'solicitante') {
    header("Location: login.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGM - Minhas Solicitações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="solicitante_dashboard.php"><i class="bi bi-tools me-2"></i> SGM</a>
            <div class="navbar-nav ms-auto align-items-center">
                <span class="nav-link text-white small me-3">Olá, <?= $_SESSION['user_nome'] ?></span>
                <a class="btn btn-sm btn-outline-danger" href="api/logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
            </div>
        </div>
    </nav>

    <div class="container animate-fade-in">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h3 class="fw-bold mb-0">Minhas Solicitações</h3>
            </div>
            <div class="col-auto">
                <a href="solicitante_abrir_chamado.php" class="btn btn-primary shadow">
                    <i class="bi bi-plus-lg me-1"></i> Nova Solicitação
                </a>
            </div>
        </div>

        <div id="listaChamados" class="row g-4">
            <!-- Carregado via AJAX -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function carregar() {
            const res = await fetch('api/chamados.php?meus_pedidos=true');
            const chamados = await res.json();
            const container = document.getElementById('listaChamados');

            if(chamados.length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <div class="glass-card p-5">
                            <i class="bi bi-emoji-smile text-muted display-1"></i>
                            <h5 class="mt-3 text-muted">Você não possui chamados abertos.</h5>
                            <a href="solicitante_abrir_chamado.php" class="btn btn-link">Abrir meu primeiro chamado</a>
                        </div>
                    </div>`;
                return;
            }

            container.innerHTML = chamados.map(c => `
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card p-4 h-100 border-top border-4 border-${obterCorStatus(c.status)} d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="badge ${obterBadgeClass(c.status)}">${c.status.replace('_', ' ').toUpperCase()}</span>
                                <small class="text-muted">#${c.id_chamado}</small>
                            </div>
                            <h6 class="fw-bold mb-1">${c.ambiente_nome}</h6>
                            <p class="text-muted small mb-3">${c.bloco_nome}</p>
                            <p class="text-truncate small mb-3">${c.descricao_problema}</p>
                        </div>
                        <div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted"><i class="bi bi-calendar-check me-1"></i> ${new Date(c.data_abertura).toLocaleDateString()}</small>
                                <span class="small fw-bold ${obterCorPrioridade(c.prioridade)} text-uppercase">${c.prioridade}</span>
                            </div>
                            <a href="solicitante_visualizar.php?id=${c.id_chamado}" class="btn btn-sm btn-primary w-100 rounded-pill py-2">
                                <i class="bi bi-eye me-1"></i> Acompanhar Chamado
                            </a>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function obterCorStatus(s) {
            const map = { 'aberto': 'secondary', 'em_execucao': 'warning', 'concluido': 'success', 'fechado': 'dark' };
            return map[s] || 'secondary';
        }

        function obterBadgeClass(s) {
            const map = { 'aberto': 'bg-secondary', 'em_execucao': 'bg-warning text-dark', 'concluido': 'bg-success', 'fechado': 'bg-dark' };
            return map[s] || 'bg-secondary';
        }

        function obterCorPrioridade(p) {
            const map = { 'urgente': 'text-danger', 'alta': 'text-warning', 'media': 'text-primary', 'baixa': 'text-secondary' };
            return map[p] || 'text-muted';
        }

        carregar();
    </script>
</body>
</html>