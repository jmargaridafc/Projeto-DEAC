document.addEventListener('DOMContentLoaded', function () {

    const form      = document.getElementById('reserveForm');
    const fName     = document.getElementById('f_name');
    const fSurname  = document.getElementById('f_surname');
    const fEmail    = document.getElementById('f_email');
    const fPhone    = document.getElementById('f_phone');
    const fPayName  = document.getElementById('f_pay_name');
    const fPaySur   = document.getElementById('f_pay_surname');
    const fCard     = document.getElementById('f_card');
    const fExpire   = document.getElementById('f_expire');
    const fCvv      = document.getElementById('f_cvv');
    const fCheckout = document.getElementById('checkoutInput');

    if (!form) return;

    fCard.addEventListener('input', function () {
        let d = this.value.replace(/\D/g, '').slice(0, 16);
        this.value = d.replace(/(.{4})/g, '$1 ').trim();
    });

    fExpire.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 4);
        if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
        this.value = v;
    });

    function showError(input, msg) {
        input.classList.add('input-error');
        let span = input.parentNode.querySelector('.error-msg');
        if (!span) {
            span = document.createElement('span');
            span.className = 'error-msg';
            input.parentNode.appendChild(span);
        }
        span.textContent = msg;
    }

    function clearError(input) {
        input.classList.remove('input-error');
        const span = input.parentNode.querySelector('.error-msg');
        if (span) span.textContent = '';
    }

    [fName, fSurname, fEmail, fPhone, fPayName, fPaySur, fCard, fExpire, fCvv]
        .forEach(function(i) { i.addEventListener('input', function() { clearError(this); }); });

    form.addEventListener('submit', function (e) {
        [fName, fSurname, fEmail, fPhone, fPayName, fPaySur, fCard, fExpire, fCvv]
            .forEach(clearError);
        let valid = true;

        if (!fName.value.trim())    { showError(fName, 'Name is required.');    valid = false; }
        if (!fSurname.value.trim()) { showError(fSurname, 'Surname is required.'); valid = false; }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(fEmail.value.trim())) {
            showError(fEmail, 'Please enter a valid email address.');
            valid = false;
        }

        const phoneDigits = fPhone.value.replace(/\D/g, '');
        if (!/^(351)?(2|3|9)\d{8}$/.test(phoneDigits)) {
            showError(fPhone, 'Invalid number. Ex: 912 345 678');
            valid = false;
        }

        if (!fPayName.value.trim()) { showError(fPayName, 'Cardholder name is required.');    valid = false; }
        if (!fPaySur.value.trim())  { showError(fPaySur, 'Cardholder surname is required.'); valid = false; }

        if (!/^\d{16}$/.test(fCard.value.replace(/\s/g, ''))) {
            showError(fCard, 'Card number must have 16 digits.');
            valid = false;
        }

        const expireVal = fExpire.value.trim();
        if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expireVal)) {
            showError(fExpire, 'Invalid format. Use MM/YY (e.g. 08/26).');
            valid = false;
        } else {
            const [mm, yy]  = expireVal.split('/').map(Number);
            const expireDate = new Date(2000 + yy, mm, 0);
            const today      = new Date(); today.setHours(0,0,0,0);

            if (expireDate < today) {
                showError(fExpire, 'This card has already expired.');
                valid = false;
            } else if (fCheckout.value) {
                const checkout = new Date(fCheckout.value); checkout.setHours(0,0,0,0);
                if (expireDate < checkout) {
                    showError(fExpire, 'The card expires before checkout (' + fCheckout.value + ').');
                    valid = false;
                }
            }
        }

        if (!/^\d{3,4}$/.test(fCvv.value.trim())) {
            showError(fCvv, 'Invalid CVV (3 or 4 digits).');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
            const first = form.querySelector('.input-error');
            if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
