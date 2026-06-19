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
    <title>SGM - Gestão de Chamados</title>
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
                    <li class="nav-item"><a class="nav-link active" href="gestor_chamados.php">Chamados</a></li>
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

    <div class="container animate-fade-in">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Fila de Chamados</h2>
            <div class="btn-group glass-card p-1">
                <button class="btn btn-sm btn-outline-primary active" onclick="filtrar(this, '')">Todos</button>
                <button class="btn btn-sm btn-outline-primary" onclick="filtrar(this, 'aberto')">Abertos</button>
                <button class="btn btn-sm btn-outline-primary" onclick="filtrar(this, 'em_execucao')">Execução</button>
                <button class="btn btn-sm btn-outline-primary" onclick="filtrar(this, 'concluido')">Concluídos</button>
            </div>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Solicitante</th>
                            <th>Localização</th>
                            <th>Prioridade</th>
                            <th>Técnico</th>
                            <th>Status</th>
                            <th class="text-center pe-4">Ação</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaGeral">
                        <!-- Conteúdo AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const coresPrioridade = { 
            'urgente': 'text-danger', 
            'alta': 'text-warning', 
            'media': 'text-primary', 
            'baixa': 'text-secondary' 
        };
        const badgesStatus = { 
            'aberto': 'bg-secondary', 
            'em_execucao': 'bg-warning text-dark', 
            'concluido': 'bg-success', 
            'fechado': 'bg-dark' 
        };

        async function carregar(status = '') {
            const res = await fetch(`api/gestor_chamados.php?status=${status}`);
            const chamados = await res.json();
            const body = document.getElementById('tabelaGeral');

            if(chamados.length === 0) {
                body.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">Nenhum chamado encontrado.</td></tr>`;
                return;
            }

            body.innerHTML = chamados.map(c => `
                <tr>
                    <td class="ps-4 fw-bold">#${c.id_chamado}</td>
                    <td>
                        <div class="fw-600">${c.solicitante_nome}</div>
                        <small class="text-muted">${new Date(c.data_abertura).toLocaleDateString()}</small>
                    </td>
                    <td>
                        <div class="small text-muted">${c.bloco_nome}</div>
                        <div class="fw-bold">${c.ambiente_nome}</div>
                    </td>
                    <td>
                        <i class="bi bi-lightning-fill ${coresPrioridade[c.prioridade]}"></i>
                        <span class="small fw-600 text-uppercase">${c.prioridade}</span>
                    </td>
                    <td>${c.tecnico_nome || '<span class="text-muted italic">Pendente</span>'}</td>
                    <td><span class="badge ${badgesStatus[c.status]}">${c.status.replace('_', ' ').toUpperCase()}</span></td>
                    <td class="text-center pe-4">
                        <a href="gestor_detalhes.php?id=${c.id_chamado}" class="btn btn-sm btn-primary rounded-pill px-3">
                            Gerenciar
                        </a>
                    </td>
                </tr>
            `).join('');
        }

        function filtrar(btn, status) {
            document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            carregar(status);
        }

        carregar();
    </script>
</body>
</html>