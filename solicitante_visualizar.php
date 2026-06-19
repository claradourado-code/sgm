<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'solicitante') {
    header("Location: login.php"); exit;
}
$id = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGM - Detalhes da Solicitação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .thumb-img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid white;
        }
        .thumb-img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .chat-box {
            height: 300px;
            overflow-y: auto;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 16px;
        }
        .chat-msg {
            max-width: 75%;
            margin-bottom: 12px;
            padding: 10px 14px;
            border-radius: 16px;
            font-size: 0.9rem;
        }
        .chat-msg-sent {
            background: var(--primary);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }
        .chat-msg-received {
            background: rgba(255, 255, 255, 0.85);
            color: var(--dark);
            margin-right: auto;
            border-bottom-left-radius: 4px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        .timeline-step {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            position: relative;
        }
        .timeline-step:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 17px;
            top: 34px;
            width: 2px;
            height: 20px;
            background: rgba(0, 0, 0, 0.1);
        }
        .timeline-step.active:not(:last-child)::after {
            background: var(--success);
        }
        .timeline-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            margin-right: 15px;
            z-index: 2;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="solicitante_dashboard.php"><i class="bi bi-tools me-2"></i> SGM</a>
            <div class="navbar-nav ms-auto align-items-center">
                <a href="solicitante_dashboard.php" class="btn btn-sm btn-outline-light border-0"><i class="bi bi-chevron-left me-1"></i> Voltar</a>
            </div>
        </div>
    </nav>

    <div class="container animate-fade-in pb-5">
        <div class="row g-4">
            <!-- Coluna Detalhes -->
            <div class="col-lg-8">
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h3 class="fw-bold mb-1">Chamado #<?= $id ?></h3>
                            <p id="localChamado" class="text-muted mb-0">Carregando localização...</p>
                        </div>
                        <span id="badgeStatus" class="badge px-3 py-2 fs-6">...</span>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <label class="form-label info-label fw-bold text-muted small">Descrição do Problema</label>
                        <div id="descricaoProblema" class="bg-white p-3 rounded-3 border fs-5 shadow-sm">...</div>
                    </div>
                    <div id="fotosAbertura" class="row mt-3"></div>
                </div>

                <!-- Painel de Comunicação (Chat) -->
                <div class="glass-card p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-chat-dots-fill text-primary me-2"></i>Histórico e Chat de Mensagens</h5>
                    <div id="chatBox" class="chat-box mb-3 d-flex flex-column">
                        <!-- Mensagens carregadas por AJAX -->
                        <div class="text-center my-auto text-muted small"><i class="bi bi-chat me-1"></i>Nenhuma mensagem registrada.</div>
                    </div>
                    <form id="formChat" class="d-flex gap-2">
                        <input type="text" id="inputChat" class="form-control border-0 shadow-sm" placeholder="Escreva uma mensagem ou tire uma dúvida..." required autocomplete="off">
                        <button type="submit" class="btn btn-primary px-4 rounded-pill"><i class="bi bi-send"></i></button>
                    </form>
                </div>
            </div>

            <!-- Coluna Workflow / Informações Adicionais -->
            <div class="col-lg-4">
                <!-- Timeline / Status -->
                <div class="glass-card p-4 mb-4">
                    <h5 class="fw-bold mb-4">Status da Resolução</h5>
                    <div id="timelineContainer">
                        <div class="timeline-step" id="step_aberto">
                            <div class="timeline-icon bg-secondary text-white" id="icon_aberto"><i class="bi bi-check"></i></div>
                            <div>
                                <div class="fw-bold small">Chamado Aberto</div>
                                <small class="text-muted">Aguardando triagem do Gestor</small>
                            </div>
                        </div>
                        <div class="timeline-step" id="step_agendado">
                            <div class="timeline-icon bg-secondary text-white" id="icon_agendado"><i class="bi bi-person"></i></div>
                            <div>
                                <div class="fw-bold small">Técnico Atribuído</div>
                                <small class="text-muted" id="nomeTecnico">Aguardando agendamento</small>
                            </div>
                        </div>
                        <div class="timeline-step" id="step_em_execucao">
                            <div class="timeline-icon bg-secondary text-white" id="icon_em_execucao"><i class="bi bi-play"></i></div>
                            <div>
                                <div class="fw-bold small">Em Atendimento</div>
                                <small class="text-muted">Técnico no local</small>
                            </div>
                        </div>
                        <div class="timeline-step" id="step_concluido">
                            <div class="timeline-icon bg-secondary text-white" id="icon_concluido"><i class="bi bi-flag"></i></div>
                            <div>
                                <div class="fw-bold small">Serviço Concluído</div>
                                <small class="text-muted" id="timelineConcluido">Aguardando encerramento</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações Operacionais -->
                <div class="glass-card p-4">
                    <h5 class="fw-bold mb-3">Informações de Apoio</h5>
                    <div class="mb-3">
                        <small class="text-muted d-block">Tipo de Serviço</small>
                        <span id="tipoServico" class="fw-bold text-dark">...</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Prioridade Definida</small>
                        <span id="prioridadeChamado" class="fw-bold text-dark">...</span>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Prazo Previsto de Conclusão</small>
                        <span id="prazoConclusao" class="fw-bold text-dark">...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Foto -->
    <div class="modal fade" id="modalFoto" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0 text-center">
                    <img src="" id="imgModal" class="img-fluid rounded-4 shadow-lg">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const chatBox = document.getElementById('chatBox');

        async function carregarDados() {
            try {
                // 1. Detalhes do Chamado
                const res = await fetch(`api/chamados.php?id=<?= $id ?>`);
                const c = await res.json();

                if (!c || c.success === false) {
                    alert("Chamado não encontrado!");
                    location.href = 'solicitante_dashboard.php';
                    return;
                }

                document.getElementById('localChamado').innerText = `${c.bloco_nome} — ${c.ambiente_nome}`;
                document.getElementById('descricaoProblema').innerText = c.descricao_problema;
                document.getElementById('tipoServico').innerText = c.tipo_nome || 'Não definido';
                
                // Prioridade
                const prioridade = c.prioridade ? c.prioridade.toUpperCase() : 'AGUARDANDO';
                document.getElementById('prioridadeChamado').innerText = prioridade;
                document.getElementById('prioridadeChamado').className = `fw-bold ${obterCorPrioridade(c.prioridade)}`;
                
                // Prazo
                document.getElementById('prazoConclusao').innerText = c.data_previsao_conclusao ? new Date(c.data_previsao_conclusao).toLocaleDateString() : 'A definir pelo gestor';

                // Status
                const badge = document.getElementById('badgeStatus');
                badge.innerText = c.status.replace('_', ' ').toUpperCase();
                badge.className = `badge px-3 py-2 ${obterBadgeClass(c.status)}`;

                // Atualizar Timeline
                atualizarTimeline(c.status, c.tecnico_nome);

                // 2. Fotos/Evidências
                const resAnexos = await fetch(`api/anexos.php?id_chamado=<?= $id ?>`);
                const anexos = await resAnexos.json();
                const fotosDiv = document.getElementById('fotosAbertura');
                fotosDiv.innerHTML = '';
                if(anexos.length > 0) {
                    fotosDiv.innerHTML = '<div class="col-12"><hr><h6 class="fw-bold mb-3 text-muted">Evidências Fotográficas</h6></div>';
                    anexos.forEach(a => {
                        fotosDiv.innerHTML += `
                            <div class="col-4 col-sm-3 mb-3">
                                <img src="${a.caminho_arquivo}" class="thumb-img" onclick="verFoto('${a.caminho_arquivo}')">
                                <div class="text-center small mt-1 text-muted" style="font-size:0.75rem;">${a.tipo_anexo === 'abertura' ? 'Abertura' : 'Conclusão'}</div>
                            </div>`;
                    });
                }
            } catch (e) {
                console.error(e);
            }
        }

        function atualizarTimeline(status, tecnico) {
            const steps = ['aberto', 'agendado', 'em_execucao', 'concluido'];
            let activeIndex = steps.indexOf(status);
            if(status === 'fechado') activeIndex = 3; // se estiver fechado, marca tudo como concluído

            steps.forEach((step, index) => {
                const el = document.getElementById(`step_${step}`);
                const icon = document.getElementById(`icon_${step}`);
                
                if (index <= activeIndex) {
                    el.classList.add('active');
                    icon.className = "timeline-icon bg-success text-white";
                } else {
                    el.classList.remove('active');
                    icon.className = "timeline-icon bg-secondary text-white";
                }
            });

            if (tecnico) {
                document.getElementById('nomeTecnico').innerText = `Técnico responsável: ${tecnico}`;
            } else {
                document.getElementById('nomeTecnico').innerText = 'Aguardando agendamento';
            }
        }

        async function carregarMensagens() {
            try {
                const res = await fetch(`api/comentarios.php?id_chamado=<?= $id ?>`);
                const msgs = await res.json();
                
                if(msgs.length === 0) {
                    chatBox.innerHTML = '<div class="text-center my-auto text-muted small"><i class="bi bi-chat me-1"></i>Nenhuma mensagem registrada. Envie um comentário abaixo!</div>';
                    return;
                }

                const currentName = "<?= $_SESSION['user_nome'] ?>";
                chatBox.innerHTML = msgs.map(m => {
                    const isSelf = m.nome === currentName;
                    const msgClass = isSelf ? 'chat-msg-sent' : 'chat-msg-received';
                    const time = new Date(m.data_envio).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    
                    return `
                        <div class="chat-msg ${msgClass}">
                            <div class="fw-bold mb-1" style="font-size: 0.75rem;">${isSelf ? 'Você' : m.nome + ' (' + m.perfil.toUpperCase() + ')'}</div>
                            <div>${m.texto}</div>
                            <div class="text-end mt-1 opacity-75" style="font-size: 0.65rem;">${time}</div>
                        </div>
                    `;
                }).join('');
                
                chatBox.scrollTop = chatBox.scrollHeight;
            } catch(e) { console.error("Erro ao carregar chat:", e); }
        }

        document.getElementById('formChat').onsubmit = async (e) => {
            e.preventDefault();
            const input = document.getElementById('inputChat');
            const texto = input.value.trim();
            if(!texto) return;

            const res = await fetch('api/comentarios.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id_chamado: <?= $id ?>, comentario: texto })
            });

            if((await res.json()).success) {
                input.value = '';
                carregarMensagens();
            }
        };

        function obterBadgeClass(s) {
            const map = { 'aberto': 'bg-secondary', 'agendado': 'bg-info text-white', 'em_execucao': 'bg-warning text-dark', 'concluido': 'bg-success', 'fechado': 'bg-dark' };
            return map[s] || 'bg-secondary';
        }

        function obterCorPrioridade(p) {
            const map = { 'urgente': 'text-danger', 'alta': 'text-warning', 'media': 'text-primary', 'baixa': 'text-secondary' };
            return map[p] || 'text-muted';
        }

        function verFoto(url) {
            document.getElementById('imgModal').src = url;
            new bootstrap.Modal(document.getElementById('modalFoto')).show();
        }

        carregarDados();
        carregarMensagens();
        
        // Auto-refresh do chat a cada 5 segundos
        setInterval(carregarMensagens, 5000);
    </script>
</body>
</html>
