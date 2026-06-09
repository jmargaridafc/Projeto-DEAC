document.addEventListener('DOMContentLoaded', function() {
        
        const form = document.querySelector('.booking-form');
        const quartoInput = document.querySelector('select[name="quarto"]');
        const checkInInput = document.querySelector('input[name="check_in"]');
        const checkOutInput = document.querySelector('input[name="check_out"]');
        
        const caixaErros = document.createElement('div');
        caixaErros.style.color = '#D8000C'; 
        caixaErros.style.backgroundColor = '#FFBABA'; 
        caixaErros.style.padding = '10px';
        caixaErros.style.marginBottom = '15px';
        caixaErros.style.borderRadius = '4px';
        caixaErros.style.display = 'none'; 
        form.insertBefore(caixaErros, form.firstChild);

        form.addEventListener('submit', function(evento) {
            
            let detetouErro = false;
            caixaErros.innerHTML = ''; 
            caixaErros.style.display = 'none';
            
            quartoInput.style.borderColor = '#ddd';
            checkInInput.style.borderColor = '#ddd';
            checkOutInput.style.borderColor = '#ddd';

            if (quartoInput.value === "") {
                quartoInput.style.borderColor = 'red';
                detetouErro = true;
            }
            if (checkInInput.value === "") {
                checkInInput.style.borderColor = 'red';
                detetouErro = true;
            }
            if (checkOutInput.value === "") {
                checkOutInput.style.borderColor = 'red';
                detetouErro = true;
            }

            if (detetouErro) {
                evento.preventDefault(); 
                caixaErros.innerHTML = '<strong>Atenção:</strong> Por favor, preencha todos os campos obrigatórios (marcados a vermelho).<br>';
                caixaErros.style.display = 'block';
                return; 
            }

            const dataCheckIn = new Date(checkInInput.value);
            const dataCheckOut = new Date(checkOutInput.value);
            
            const dataHoje = new Date();
            dataHoje.setHours(0, 0, 0, 0); 

            if (dataCheckIn < dataHoje) {
                checkInInput.style.borderColor = 'red';
                caixaErros.innerHTML += '<strong>Erro de Data:</strong> A data de Check-in não pode ser no passado.<br>';
                detetouErro = true;
            }

            if (dataCheckOut <= dataCheckIn) {
                checkOutInput.style.borderColor = 'red';
                caixaErros.innerHTML += '<strong>Erro de Lógica:</strong> A data de Check-out tem obrigatoriamente de ser posterior ao Check-in.<br>';
                detetouErro = true;
            }

            if (detetouErro) {
                evento.preventDefault();
                caixaErros.style.display = 'block';
            }
        });
    });