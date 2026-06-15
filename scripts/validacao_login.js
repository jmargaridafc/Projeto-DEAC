// validacao_login.js
document.addEventListener('DOMContentLoaded', function() {
    // Procura o formulário de login (ajusta o ID se o teu for diferente)
    const loginForm = document.getElementById('loginForm'); 
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');

            // 1. Valida se o username está vazio
            if (usernameInput.value.trim() === "") {
                alert("Por favor, introduza o seu Nome de Utilizador.");
                usernameInput.focus();
                event.preventDefault(); // Bloqueia o envio
                return;
            }

            // 2. Valida se a password está vazia
            if (passwordInput.value === "") {
                alert("Por favor, introduza a sua Palavra-passe.");
                passwordInput.focus();
                event.preventDefault(); // Bloqueia o envio
                return;
            }
        });
    }
});