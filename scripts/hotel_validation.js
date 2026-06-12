document.addEventListener('DOMContentLoaded', function() {
    
    const formulario = document.getElementById('formTriagem');
    const campoQuarto = document.getElementById('quartoSelect');
    const campoCheckIn = document.getElementById('checkInInput');
    const campoCheckOut = document.getElementById('checkOutInput');

    formulario.addEventListener('submit', function(evento) {
        
        let submissaoValida = true;

        if (campoQuarto.value === "") {
            campoQuarto.style.borderColor = 'red';
            submissaoValida = false;
        } else {
            campoQuarto.style.borderColor = '';
        }

        if (campoCheckIn.value === "") {
            campoCheckIn.style.borderColor = 'red';
            submissaoValida = false;
        } else {
            campoCheckIn.style.borderColor = '';
        }

        if (campoCheckOut.value === "") {
            campoCheckOut.style.borderColor = 'red';
            submissaoValida = false;
        } else {
            campoCheckOut.style.borderColor = '';
        }

        if (submissaoValida === false) {
            evento.preventDefault();
        }
    });
});