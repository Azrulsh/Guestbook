(() => {
    'use strict';

    const form = document.getElementById('entry-form');
    const nameField = document.getElementById('nama');
    const emailField = document.getElementById('email');
    const commentField = document.getElementById('komen');
    const submitButton = document.getElementById('submit-entry');
    const resetButton = document.getElementById('reset-entry');
    const submitLabel = document.getElementById('submit-label');
    const counter = document.getElementById('comment-count');

    if (!form || !nameField || !emailField || !commentField || !submitButton || !submitLabel || !counter) {
        return;
    }

    const fields = [nameField, emailField, commentField];
    const errors = new Map(fields.map(field => [field, document.getElementById(`${field.id}-error`)]));

    if ([...errors.values()].some(element => !element)) {
        return;
    }

    const touchedFields = new Set();
    let isSubmitting = false;
    const maxCommentBytes = Number.parseInt(commentField.dataset.maxBytes, 10);

    if (!Number.isInteger(maxCommentBytes) || maxCommentBytes <= 0 || typeof Blob === 'undefined') {
        return;
    }

    const errorMessage = field => {
        const value = field.value.trim();

        if (value === '') {
            return { nama: 'Masukkan nama anda.', email: 'Masukkan email anda.', komen: 'Tulis sedikit catatan sebelum menyimpan.' }[field.id];
        }

        const controlPattern = field === commentField
            ? /[\u0000-\u0008\u000b\u000c\u000e-\u001f\u007f]/
            : /\p{Cc}/u;

        if (controlPattern.test(value)) {
            return 'Buang aksara kawalan yang tidak dibenarkan daripada teks ini.';
        }

        if (field !== commentField && Array.from(value).length > field.maxLength) {
            return `Maksimum ${field.maxLength} aksara dibenarkan.`;
        }

        if (field === emailField && field.validity.typeMismatch) {
            return 'Masukkan email yang sah, seperti nama@contoh.com.';
        }

        if (field === commentField && new Blob([value]).size > maxCommentBytes) {
            return 'Catatan terlalu panjang. Pendekkan sedikit dan cuba lagi.';
        }

        return '';
    };

    const showFieldError = field => {
        const message = errorMessage(field);
        const error = errors.get(field);
        error.textContent = message;
        error.hidden = message === '';

        if (message) {
            field.setAttribute('aria-invalid', 'true');
        } else {
            field.removeAttribute('aria-invalid');
        }

        return message === '';
    };

    const updateCounter = () => {
        counter.textContent = `${Array.from(commentField.value).length.toLocaleString('ms-MY')} aksara`;
    };

    fields.forEach(field => {
        field.addEventListener('blur', () => {
            touchedFields.add(field);
            showFieldError(field);
        });

        field.addEventListener('input', () => {
            if (touchedFields.has(field)) {
                showFieldError(field);
            }
        });
    });

    commentField.addEventListener('input', updateCounter);

    form.addEventListener('submit', event => {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        let firstInvalidField = null;

        fields.forEach(field => {
            touchedFields.add(field);
            if (!showFieldError(field) && !firstInvalidField) {
                firstInvalidField = field;
            }
        });

        if (firstInvalidField) {
            event.preventDefault();
            firstInvalidField.focus();
            return;
        }

        // Use the normal POST so server validation and CSRF remain authoritative.
        isSubmitting = true;
        submitButton.disabled = true;
        if (resetButton) {
            resetButton.disabled = true;
        }
        submitLabel.textContent = 'Menyimpan…';
        form.setAttribute('aria-busy', 'true');
    });

    form.addEventListener('reset', event => {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        // The native reset restores field values after the reset event finishes.
        window.setTimeout(() => {
            touchedFields.clear();
            fields.forEach(field => {
                field.removeAttribute('aria-invalid');
                const error = errors.get(field);
                error.textContent = '';
                error.hidden = true;
            });
            updateCounter();
        }, 0);
    });

    // Re-enable the form when returning with the browser Back button.
    window.addEventListener('pageshow', () => {
        isSubmitting = false;
        submitButton.disabled = false;
        if (resetButton) {
            resetButton.disabled = false;
        }
        submitLabel.textContent = 'Simpan catatan';
        form.removeAttribute('aria-busy');
        updateCounter();
    });

    counter.hidden = false;
    updateCounter();
    // Native browser validation is the fallback when this enhancement is unavailable.
    form.noValidate = true;
})();
