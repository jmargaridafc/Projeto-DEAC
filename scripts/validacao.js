document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registoForm');
    
    if (form) {
        // Captura os elementos do formulário
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');

        // Cria o elemento de erro para as passwords dinamicamente
        const errorSpan = document.createElement('span');
        errorSpan.style.color = '#db4455';
        errorSpan.style.fontSize = '12px';
        errorSpan.style.marginTop = '5px';
        errorSpan.style.display = 'none'; // Fica escondido ao início
        errorSpan.id = 'password-error-msg';
        
        // Insere o span de erro logo a seguir ao input de confirmar password
        confirmInput.parentNode.appendChild(errorSpan);

        // --- VALIDAÇÃO EM TEMPO REAL (Enquanto o utilizador digita) ---
        function verificarPasswords() {
            if (passwordInput.value !== confirmInput.value && confirmInput.value !== "") {
                errorSpan.textContent = "As palavras-passe introduzidas não coincidem!";
                errorSpan.style.display = 'block';
                confirmInput.style.borderColor = '#db4455'; // Fica com borda vermelha
            } else {
                errorSpan.style.display = 'none';
                confirmInput.style.borderColor = ''; // Volta à cor normal
            }
        }

        // Corre a função sempre que o utilizador digita em qualquer um dos campos de pass
        passwordInput.addEventListener('input', verificarPasswords);
        confirmInput.addEventListener('input', verificarPasswords);


        // --- VALIDAÇÃO AO SUBMETER O FORMULÁRIO ---
        form.addEventListener('submit', function(event) {
            const username = usernameInput.value.trim();
            const password = passwordInput.value;
            const confirmPassword = confirmInput.value;

            // 1. Validação do Nome de Utilizador
            if (username === "") {
                alert("Por favor, preencha o campo Nome de Utilizador.");
                usernameInput.focus();
                event.preventDefault();
                return;
            }

            // 2. Validação do Tamanho da Palavra-passe
            if (password.length < 6) {
                alert("A palavra-passe deve ter pelo menos 6 caracteres.");
                passwordInput.focus();
                event.preventDefault();
                return;
            }

            // 3. Validação de Correspondência (Última barreira ao clicar no botão)
            if (password !== confirmPassword) {
                errorSpan.textContent = "As palavras-passe introduzidas não coincidem!";
                errorSpan.style.display = 'block';
                confirmInput.style.borderColor = '#db4455';
                confirmInput.focus();
                event.preventDefault(); // Impede o envio do formulário
                return;
            }
        });
    }
});