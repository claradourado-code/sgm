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
    <title>SGM - Gestão de Locais</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .list-group-item {
            background: transparent;
            border-color: rgba(255, 255, 255, 0.2);
            transition: all 0.2s ease;
        }
        .list-group-item:hover {
            background-color: rgba(255, 255, 255, 0.15) !important;
            transform: translateX(4px);
        }
        .card-bloco {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-bloco:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.15);
        }
    </style>
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
                    <li class="nav-item"><a class="nav-link active" href="gestor_locais.php">Locais</a></li>
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
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gestão de Locais</h2>
                <p class="text-muted">Cadastre e gerencie a estrutura física de blocos e ambientes.</p>
            </div>
            <button class="btn btn-primary d-flex align-items-center shadow" data-bs-toggle="modal" data-bs-target="#modalBloco">
                <i class="bi bi-plus-circle-fill me-2 fs-5"></i> Novo Bloco
            </button>
        </div>

        <div class="row g-4" id="containerBlocos">
            <!-- Blocos serão carregados via AJAX -->
            <div class="col-12 text-center py-5 text-muted">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bloco -->
    <div class="modal fade" id="modalBloco" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formBloco" class="modal-content glass-card border-0 overflow-hidden shadow-lg">
                <div class="modal-header border-0 bg-dark text-white p-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-building me-2"></i> Novo Bloco</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <input type="hidden" id="blocoId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nome do Bloco</label>
                        <input type="text" id="blocoNome" class="form-control border-0 shadow-sm bg-white" required placeholder="Ex: Bloco Administrativo">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 pb-4">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Salvar Bloco</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ambiente -->
    <div class="modal fade" id="modalAmbiente" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formAmbiente" class="modal-content glass-card border-0 overflow-hidden shadow-lg">
                <div class="modal-header border-0 bg-dark text-white p-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-door-open me-2"></i> Novo Ambiente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <input type="hidden" id="ambienteId">
                    <input type="hidden" id="ambienteBlocoId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nome do Ambiente</label>
                        <input type="text" id="ambienteNome" class="form-control border-0 shadow-sm bg-white" required placeholder="Ex: Recepção, Sala 102">
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 pb-4">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Salvar Ambiente</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function carregarTudo() {
            const container = document.getElementById('containerBlocos');
            try {
                const res = await fetch('api/localizacoes.php?acao=listar_blocos');
                const blocos = await res.json();
                
                if (blocos.length === 0) {
                    container.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <div class="glass-card p-5">
                                <i class="bi bi-geo-fill text-muted display-4"></i>
                                <h5 class="mt-3 text-muted">Nenhum bloco cadastrado ainda.</h5>
                            </div>
                        </div>`;
                    return;
                }

                container.innerHTML = '';

                for (const bloco of blocos) {
                    const resAmb = await fetch(`api/localizacoes.php?acao=listar_ambientes&id_bloco=${bloco.id_bloco}`);
                    const ambientes = await resAmb.json();

                    const card = `
                        <div class="col-md-6 col-lg-4">
                            <div class="card-bloco glass-card h-100 overflow-hidden d-flex flex-column border-0 shadow-sm">
                                <div class="p-3 bg-dark bg-opacity-10 d-flex justify-content-between align-items-center border-bottom border-white border-opacity-25">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-building text-primary me-2 fs-5"></i>
                                        <strong class="text-dark fs-6">${bloco.nome}</strong>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary border-0 rounded-circle" onclick="novoAmbiente(${bloco.id_bloco})" title="Adicionar Ambiente"><i class="bi bi-plus-lg"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="excluirBloco(${bloco.id_bloco})" title="Excluir Bloco"><i class="bi bi-trash-fill"></i></button>
                                    </div>
                                </div>
                                <div class="card-body p-3 flex-grow-1">
                                    <ul class="list-group list-group-flush">
                                        ${ambientes.map(a => `
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-1 rounded">
                                                <span class="small text-dark fw-500"><i class="bi bi-door-open text-muted me-2"></i>${a.nome}</span>
                                                <button class="btn btn-sm text-danger border-0 hover-scale p-1" onclick="excluirAmbiente(${a.id_ambiente})" title="Remover Ambiente"><i class="bi bi-x-circle-fill fs-6"></i></button>
                                            </li>
                                        `).join('') || '<li class="list-group-item text-muted small py-3"><i class="bi bi-info-circle me-1"></i>Nenhum ambiente neste bloco</li>'}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    `;
                    container.innerHTML += card;
                }
            } catch (e) {
                console.error(e);
                container.innerHTML = `<div class="col-12 text-center py-5 text-danger"><i class="bi bi-exclamation-triangle-fill display-4"></i><h5 class="mt-2">Erro ao conectar com o banco de dados.</h5></div>`;
            }
        }

        function novoAmbiente(id_bloco) {
            document.getElementById('ambienteBlocoId').value = id_bloco;
            document.getElementById('ambienteNome').value = '';
            new bootstrap.Modal(document.getElementById('modalAmbiente')).show();
        }

        document.getElementById('formBloco').onsubmit = async (e) => {
            e.preventDefault();
            const nome = document.getElementById('blocoNome').value;
            const res = await fetch('api/localizacoes.php?acao=salvar_bloco', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ nome: nome })
            });
            const result = await res.json();
            if(result.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalBloco')).hide();
                carregarTudo();
            }
        };

        document.getElementById('formAmbiente').onsubmit = async (e) => {
            e.preventDefault();
            const nome = document.getElementById('ambienteNome').value;
            const id_bloco = document.getElementById('ambienteBlocoId').value;
            const res = await fetch('api/localizacoes.php?acao=salvar_ambiente', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ nome: nome, id_bloco: id_bloco })
            });
            const result = await res.json();
            if(result.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalAmbiente')).hide();
                carregarTudo();
            }
        };

        async function excluirBloco(id) {
            if(!confirm("Tem certeza que deseja excluir este bloco e TODOS os ambientes vinculados? Esta ação não pode ser desfeita.")) return;
            const res = await fetch(`api/localizacoes.php?acao=excluir_bloco&id=${id}`, { method: 'DELETE' });
            if((await res.json()).success) carregarTudo();
        }

        async function excluirAmbiente(id) {
            if(!confirm("Tem certeza que deseja remover este ambiente?")) return;
            const res = await fetch(`api/localizacoes.php?acao=excluir_ambiente&id=${id}`, { method: 'DELETE' });
            if((await res.json()).success) carregarTudo();
        }

        carregarTudo();
    </script>
</body>
</html>