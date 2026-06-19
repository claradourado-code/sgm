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
    <title>SGM - Gestão de Usuários</title>
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
                    <li class="nav-item"><a class="nav-link active" href="gestor_usuario.php">Usuários</a></li>
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
            <h2 class="fw-bold mb-0">Usuários do Sistema</h2>
            <button class="btn btn-primary" onclick="abrirModal()">
                <i class="bi bi-person-plus-fill me-1"></i> Novo Usuário
            </button>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-4">Nome</th>
                            <th>Email</th>
                            <th>Perfil</th>
                            <th>Status</th>
                            <th class="text-center pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaUsuarios">
                        <!-- Conteúdo AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalUsuario" tabindex="-1">
        <div class="modal-dialog">
            <form id="formUsuario" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id_usuario">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nome Completo</label>
                        <input type="text" id="nome" class="form-control border-0 shadow-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">E-mail</label>
                        <input type="email" id="email" class="form-control border-0 shadow-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Perfil de Acesso</label>
                        <select id="perfil" class="form-select border-0 shadow-sm" required>
                            <option value="solicitante">Solicitante</option>
                            <option value="tecnico">Técnico</option>
                            <option value="gestor">Gestor</option>
                        </select>
                    </div>
                    <div id="areaSenha" class="mb-3">
                        <label class="form-label fw-bold small">Senha Inicial</label>
                        <input type="password" id="senha" class="form-control border-0 shadow-sm" placeholder="Mínimo 6 caracteres">
                        <small class="text-muted">Deixe em branco para manter a senha atual ao editar.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100 py-2">Salvar Usuário</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));

        async function carregar() {
            const res = await fetch('api/usuarios_gestao.php?acao=listar');
            const usuarios = await res.json();
            const body = document.getElementById('tabelaUsuarios');

            body.innerHTML = usuarios.map(u => `
                <tr>
                    <td class="ps-4 fw-bold">${u.nome}</td>
                    <td>${u.email}</td>
                    <td><span class="badge bg-opacity-10 text-dark border" style="background-color: #e9ecef">${u.perfil.toUpperCase()}</span></td>
                    <td><span class="badge ${u.ativo == 1 ? 'bg-success' : 'bg-danger'}">${u.ativo == 1 ? 'Ativo' : 'Inativo'}</span></td>
                    <td class="text-center pe-4">
                        <button class="btn btn-sm btn-outline-primary" onclick='editar(${JSON.stringify(u)})'><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="excluir(${u.id_usuario})"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        }

        function abrirModal() {
            document.getElementById('formUsuario').reset();
            document.getElementById('id_usuario').value = '';
            document.getElementById('modalTitle').innerText = 'Novo Usuário';
            document.getElementById('senha').required = true;
            modal.show();
        }

        function editar(u) {
            document.getElementById('id_usuario').value = u.id_usuario;
            document.getElementById('nome').value = u.nome;
            document.getElementById('email').value = u.email;
            document.getElementById('perfil').value = u.perfil;
            document.getElementById('modalTitle').innerText = 'Editar Usuário';
            document.getElementById('senha').required = false;
            modal.show();
        }

        document.getElementById('formUsuario').onsubmit = async (e) => {
            e.preventDefault();
            const payload = {
                id: document.getElementById('id_usuario').value,
                nome: document.getElementById('nome').value,
                email: document.getElementById('email').value,
                perfil: document.getElementById('perfil').value,
                senha: document.getElementById('senha').value
            };
            
            const res = await fetch('api/usuarios_gestao.php?acao=salvar', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            
            if((await res.json()).success) {
                modal.hide();
                carregar();
            } else {
                alert("Erro ao salvar usuário.");
            }
        };

        async function excluir(id) {
            if(!confirm("Deseja realmente inativar este usuário?")) return;
            const res = await fetch(`api/usuarios_gestao.php?acao=excluir&id=${id}`, { method: 'DELETE' });
            if((await res.json()).success) carregar();
        }

        carregar();
    </script>
</body>
</html>