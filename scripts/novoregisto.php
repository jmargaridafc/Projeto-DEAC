<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registo - Projeto DEAC</title>
    <link rel="stylesheet" href="../styles/style_login.css">
</head>
<body>

    <header class="main-header">
        <div class="header-container">
            <div class="logo">LOGO</div>
        </div>
    </header>

    <main class="login-main">
        <div class="login-card">
            <h2>Criar Conta</h2>
            <p class="subtitle">Registe uma nova conta para gerir a sua atividade</p>

            <?php if (isset($_SESSION['erro'])): ?>
                <div style="color: #991b1b; background: #fee2e2; padding: 10px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; text-align: left;">
                    <?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?>
                </div>
            <?php endif; ?>

            <form id="registoForm" action="processar_registo.php" method="POST" class="login-form">
                
                <input type="hidden" name="perfil" value="cliente">

                <div class="input-group">
                    <label for="username">Nome de Utilizador</label>
                    <input type="text" id="username" name="username" placeholder="Escolha o seu username" required value="<?php echo isset($_SESSION['old_nome_utilizador']) ? htmlspecialchars($_SESSION['old_nome_utilizador']) : ''; unset($_SESSION['old_nome_utilizador']); ?>">
                </div>

                <div class="input-group">
                    <label for="password">Palavra-passe</label>
                    <input type="password" id="password" name="password" placeholder="Defina a sua password (mín. 6 car.)" required>
                </div>

                <div class="input-group">
                    <label for="password_conf">Confirmar Palavra-passe</label>
                    <input type="password" id="confirm_password" name="password_conf" placeholder="Repita a sua password" required>
                </div>

                <button type="submit" class="btn-login" style="margin-top: 15px;">Criar Conta</button>
            </form>

            <div class="login-footer">
                <p>Já tem uma conta? <a href="../login.php">Inicie sessão aqui</a></p>
            </div>
        </div>
    </main>

    <script src="validacao.js"></script>
</body>
</html>