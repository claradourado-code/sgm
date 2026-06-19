<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'gestor') {
    header("Location: login.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGM - Gestão de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .card-service {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-service:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.12);
        }
        .icon-box {
            width: 48px;
            height: 48px;
            background: rgba(67, 97, 238, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 24px;
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
                    <li class="nav-item"><a class="nav-link" href="gestor_locais.php">Locais</a></li>
                    <li class="nav-item"><a class="nav-link active" href="gestor_servicos.php">Serviços</a></li>
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
                <h2 class="fw-bold mb-1">Tipos de Serviço</h2>
                <p class="text-muted">Gerencie as categorias de manutenção oferecidas aos solicitantes.</p>
            </div>
            <button class="btn btn-primary d-flex align-items-center shadow" onclick="abrirModal()">
                <i class="bi bi-plus-circle-fill me-2 fs-5"></i> Novo Serviço
            </button>
        </div>

        <div class="row g-4" id="listaServicos">
            <!-- Serviços via AJAX -->
            <div class="col-12 text-center py-5 text-muted">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalServico" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formServico" class="modal-content glass-card border-0 overflow-hidden shadow-lg">
                <div class="modal-header border-0 bg-dark text-white p-4">
                    <h5 class="modal-title fw-bold" id="modalTitle"><i class="bi bi-gear-fill me-2"></i> Novo Serviço</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <input type="hidden" id="id_tipo">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nome do Serviço</label>
                        <input type="text" id="nome" class="form-control border-0 shadow-sm bg-white" required placeholder="Ex: Hidráulica, Elétrica, Pintura">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Descrição</label>
                        <textarea id="descricao" class="form-control border-0 shadow-sm bg-white" rows="4" placeholder="Descreva brevemente o escopo deste tipo de serviço..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 pb-4">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Salvar Serviço</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modal = new bootstrap.Modal(document.getElementById('modalServico'));

        // Mapeamento simples de ícones baseados no nome do serviço
        function obterIcone(nome) {
            const n = nome.toLowerCase();
            if (n.includes('elétr') || n.includes('energia')) return 'bi-lightning-charge-fill';
            if (n.includes('hidráu') || n.includes('água') || n.includes('vazamento')) return 'bi-droplet-fill';
            if (n.includes('ar') || n.includes('clima') || n.includes('refrig')) return 'bi-wind';
            if (n.includes('pint') || n.includes('civil') || n.includes('predial')) return 'bi-paint-bucket';
            if (n.includes('limp') || n.includes('higien')) return 'bi-trash3-fill';
            if (n.includes('ti') || n.includes('rede') || n.includes('comput')) return 'bi-pc-display';
            return 'bi-tools';
        }

        async function carregarServicos() {
            const container = document.getElementById('listaServicos');
            try {
                const res = await fetch('api/tipos_servico.php?acao=listar');
                const dados = await res.json();
                
                if (dados.length === 0) {
                    container.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <div class="glass-card p-5">
                                <i class="bi bi-patch-exclamation-fill text-muted display-4"></i>
                                <h5 class="mt-3 text-muted">Nenhum serviço cadastrado ainda.</h5>
                            </div>
                        </div>`;
                    return;
                }

                container.innerHTML = dados.map(s => `
                    <div class="col-md-6 col-lg-4">
                        <div class="card-service glass-card h-100 border-0 p-4 shadow-sm d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="icon-box">
                                        <i class="bi ${obterIcone(s.nome)}"></i>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary border-0 rounded-circle" onclick='editar(${JSON.stringify(s)})' title="Editar"><i class="bi bi-pencil-fill"></i></button>
                                        <button class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="excluir(${s.id_tipo})" title="Excluir"><i class="bi bi-trash-fill"></i></button>
                                    </div>
                                </div>
                                <h5 class="fw-bold text-dark mb-2">${s.nome}</h5>
                                <p class="text-muted small mb-0">${s.descricao || '<span class="italic text-black-50">Sem descrição detalhada.</span>'}</p>
                            </div>
                        </div>
                    </div>
                `).join('');
            } catch(e) {
                console.error(e);
                container.innerHTML = `<div class="col-12 text-center py-5 text-danger"><i class="bi bi-exclamation-triangle-fill display-4"></i><h5 class="mt-2">Erro ao conectar com o servidor.</h5></div>`;
            }
        }

        function abrirModal() {
            document.getElementById('formServico').reset();
            document.getElementById('id_tipo').value = '';
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-gear-fill me-2"></i> Novo Serviço';
            modal.show();
        }

        function editar(s) {
            document.getElementById('id_tipo').value = s.id_tipo;
            document.getElementById('nome').value = s.nome;
            document.getElementById('descricao').value = s.descricao;
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i> Editar Serviço';
            modal.show();
        }

        document.getElementById('formServico').onsubmit = async (e) => {
            e.preventDefault();
            const payload = {
                id: document.getElementById('id_tipo').value,
                nome: document.getElementById('nome').value,
                descricao: document.getElementById('descricao').value
            };
            const res = await fetch('api/tipos_servico.php?acao=salvar', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            if((await res.json()).success) {
                modal.hide();
                carregarServicos();
            } else {
                alert("Erro ao salvar serviço.");
            }
        };

        async function excluir(id) {
            if(!confirm("Tem certeza que deseja remover esta categoria de serviço? Ordens de serviço vinculadas a ela podem ficar sem classificação.")) return;
            const res = await fetch(`api/tipos_servico.php?acao=excluir&id=${id}`, { method: 'DELETE' });
            if((await res.json()).success) carregarServicos();
        }

        carregarServicos();
    </script>
</body>
</html>