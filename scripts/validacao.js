document.addEventListener('DOMContentLoaded', function() {
    // Procura o formulário pelo ID que definimos
    const form = document.getElementById('registoForm');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            // 1. Captura os elementos e os seus valores
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_password');

            const username = usernameInput.value.trim();
            const password = passwordInput.value;
            const confirmPassword = confirmInput.value;

            // 2. Validação do Nome de Utilizador
            if (username === "") {
                alert("Por favor, preencha o campo Nome de Utilizador.");
                usernameInput.focus();
                event.preventDefault(); // Trava o envio do formulário
                return;
            }

            // 3. Validação da Palavra-passe (Mínimo 6 caracteres)
            if (password.length < 6) {
                alert("A palavra-passe deve ter pelo menos 6 caracteres.");
                passwordInput.focus();
                event.preventDefault(); // Trava o envio do formulário
                return;
            }

            // 4. Validação da Confirmação (Verifica se são iguais)
            if (password !== confirmPassword) {
                alert("As palavras-passe introduzidas não coincidem!");
                confirmInput.focus();
                event.preventDefault(); // Trava o envio do formulário
                return;
            }
            
            // Se passar por todas as regras, o formulário é enviado com sucesso!
        });
    }
});