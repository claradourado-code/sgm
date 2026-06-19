<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'tecnico') {
    header("Location: login.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGM - Minha Agenda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background: linear-gradient(135deg, #2b2d42, #8d99ae) !important;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-tools me-2"></i> SGM TÉCNICO</a>
            <div class="navbar-nav ms-auto align-items-center">
                <a class="nav-link text-white small me-3 active" href="tecnico_minhas_tarefas.php"><i class="bi bi-calendar-check me-1"></i> Minha Agenda</a>
                <a class="nav-link text-white small me-3" href="tecnico_historico.php"><i class="bi bi-archive me-1"></i> Meu Histórico</a>
                <span class="nav-link text-white small me-3"><?= $_SESSION['user_nome'] ?></span>
                <a class="btn btn-sm btn-outline-light border-0" href="api/logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
            </div>
        </div>
    </nav>

    <div class="container animate-fade-in pb-5">
        <div class="row mb-4">
            <div class="col">
                <h4 class="fw-bold mb-0">Minha Fila de Trabalho</h4>
                <p class="text-muted small">Ordene por prioridade para melhor eficiência.</p>
            </div>
        </div>
        
        <div id="listaTarefas" class="row g-4">
            <!-- Carregado via AJAX -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const coresPrioridade = { 'urgente': 'danger', 'alta': 'warning', 'media': 'primary', 'baixa': 'secondary' };

        async function carregar() {
            try {
                const res = await fetch('api/tecnico_tarefas.php');
                const tarefas = await res.json();
                const container = document.getElementById('listaTarefas');

                if (tarefas.length === 0) {
                    container.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <div class="glass-card p-5">
                                <i class="bi bi-check-circle text-success display-1"></i>
                                <h5 class="mt-3">Excelente! Nenhuma tarefa pendente.</h5>
                            </div>
                        </div>`;
                    return;
                }

                container.innerHTML = tarefas.map(t => `
                    <div class="col-md-6 col-lg-4">
                        <div class="glass-card p-0 overflow-hidden h-100 shadow-sm">
                            <div class="p-3 bg-${coresPrioridade[t.prioridade]} bg-opacity-10 d-flex justify-content-between align-items-center">
                                <span class="badge bg-${coresPrioridade[t.prioridade]} text-uppercase">${t.prioridade}</span>
                                <small class="fw-bold">#${t.id_chamado}</small>
                            </div>
                            <div class="p-4">
                                <div class="mb-3">
                                    <div class="small text-muted mb-1">${t.bloco_nome}</div>
                                    <h5 class="fw-bold mb-0">${t.ambiente_nome}</h5>
                                </div>
                                <p class="text-muted small mb-4" style="min-height: 40px;">${t.descricao_problema}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="small fw-bold ${t.data_previsao_conclusao ? 'text-danger' : 'text-muted'}">
                                        <i class="bi bi-calendar-event me-1"></i> 
                                        ${t.data_previsao_conclusao ? new Date(t.data_previsao_conclusao).toLocaleDateString() : 'Sem prazo'}
                                    </div>
                                    <a href="tecnico_atendimento.php?id=${t.id_chamado}" class="btn btn-primary px-4 rounded-pill">
                                        Atender
                                    </a>
                                </div>
                            </div>
                            <div class="px-4 py-2 bg-light small border-top text-center">
                                Status: <span class="fw-bold">${t.status.replace('_', ' ').toUpperCase()}</span>
                            </div>
                        </div>
                    </div>
                `).join('');
            } catch(e) { console.error("Erro ao carregar tarefas:", e); }
        }

        carregar();
    </script>
</body>
</html>