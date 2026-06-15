<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Projeto DEAC</title>
    <link rel="stylesheet" href="styles/style_login.css">
</head>
<body>

    <header class="main-header">
        <div class="header-container">
            <div class="logo">LOGO</div>
            <div class="header-actions">
                <button class="icon-btn" title="Idioma">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12.87 15.07l-2.54-2.51.03-.03c1.74-1.94 2.98-4.17 3.71-6.53H17V4h-7V2H8v2H1v2h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-1.04zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12zm-2.62 7l1.62-4.33L19.12 17h-3.24z"/></svg>
                </button>
                <button class="icon-btn" title="Ajuda">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 16h-2v-2h2v2zm1.07-7.75l-.9.92C12.45 11.9 12 12.5 12 14h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.25z"/></svg>
                </button>
                <button class="icon-btn" title="Utilizador">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                </button>
            </div>
        </div>
    </header>

    <main class="login-main">
        <div class="login-card">
            <h2>Iniciar Sessão</h2>
            <p class="subtitle">Aceda à sua conta para gerir a sua atividade</p>

            <?php if (isset($_SESSION['sucesso'])): ?>
                <div style="color: #166534; background: #dcfce7; padding: 10px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; text-align: left;">
                    <?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['erro'])): ?>
                <div style="color: #991b1b; background: #fee2e2; padding: 10px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; text-align: left;">
                    <?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" action="scripts/processar_login.php" method="POST" class="login-form">
                
                <input type="hidden" name="redirect_to" value="<?php echo isset($_GET['next']) ? htmlspecialchars($_GET['next']) : 'index.php'; ?>">

                <div class="input-group">
                    <label for="username">Nome de Utilizador</label>
                    <input type="text" id="username" name="username" placeholder="Digite o seu username" required>
                </div>

                <div class="input-group">
                    <label for="password">Palavra-passe</label>
                    <input type="password" id="password" name="password" placeholder="Digite a sua password" required>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember"> Lembrar-me
                    </label>
                    <a href="#" class="forgot-pass">Esqueceu-se da senha?</a>
                </div>

                <button type="submit" class="btn-login">Entrar</button>
            </form>

            <div class="login-footer">
                <p>Não tem uma conta? <a href="scripts/novoregisto.php">Registe-se aqui</a></p>
            </div>
        </div>
    </main>

    <script src="scripts/validacao_login