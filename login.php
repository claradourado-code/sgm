<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGM - Acesso ao Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('assets/img/login_bg.png') no-repeat center center fixed;
            background-size: cover;
        }
        body::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 40px;
        }
        .logo-container {
            width: 80px;
            height: 80px;
            background: var(--primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }
        .logo-container i {
            font-size: 40px;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-card glass-card animate-fade-in">
        <div class="logo-container">
            <i class="bi bi-tools"></i>
        </div>
        <h3 class="text-center mb-2 fw-bold text-dark">SGM</h3>
        <p class="text-center text-muted mb-4">Gestão de Manutenção Inteligente</p>
        
        <form id="formLogin">
            <div class="mb-3">
                <label class="form-label small fw-600">E-mail Corporativo</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" id="email" class="form-control border-start-0" placeholder="nome@empresa.com" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-600">Senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" id="senha" class="form-control border-start-0" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">
                Entrar no Sistema <i class="bi bi-arrow-right ms-2"></i>
            </button>
            <div id="mensagem" class="text-center text-danger small"></div>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">Esqueceu a senha? <a href="#" class="text-primary text-decoration-none">Clique aqui</a></small>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>