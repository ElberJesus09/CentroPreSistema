function debounce(callback, delay = 350) {
    let timer = null;

    return (...args) => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => callback(...args), delay);
    };
}

function setAutofillMessage(input, message, tone = 'neutral') {
    let target = document.getElementById('student-dni-autofill-status');
    if (!target) {
        target = document.createElement('p');
        target.id = 'student-dni-autofill-status';
        target.className = 'text-xs';
        input.closest('.space-y-1')?.appendChild(target);
    }

    const toneClass = tone === 'success'
        ? 'text-emerald-700'
        : tone === 'warning'
            ? 'text-amber-700'
            : 'text-on-surface-variant';

    target.className = `text-xs ${toneClass}`;
    target.textContent = message;
}

function normalizeStudentDni(input) {
    const dni = input.value.replace(/\D/g, '');
    if (input.value !== dni) {
        input.value = dni;
    }

    if (dni.length !== 8) {
        setAutofillMessage(input, 'Ingrese 8 digitos.');
        return;
    }

    setAutofillMessage(input, 'DNI completo.', 'success');
}

function fillSelect(select, values, selectedValue) {
    select.innerHTML = '<option value="">Seleccione</option>';

    values.forEach((value) => {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = value;
        option.selected = value === selectedValue;
        select.appendChild(option);
    });
}

function initPeruAddressSelects() {
    document.querySelectorAll('[data-peru-address]').forEach((root) => {
        const department = root.querySelector('[data-address-department]');
        const province = root.querySelector('[data-address-province]');
        const district = root.querySelector('[data-address-district]');

        if (!department || !province || !district) {
            return;
        }

        let locations = {};
        try {
            locations = JSON.parse(root.dataset.locations || '{}');
        } catch {
            locations = {};
        }

        const populateDistricts = () => {
            const provinceMap = locations[department.value] || {};
            const districts = provinceMap[province.value] || [];
            fillSelect(district, districts, district.dataset.selected || '');
        };

        const populateProvinces = () => {
            const provinceMap = locations[department.value] || {};
            fillSelect(province, Object.keys(provinceMap), province.dataset.selected || '');
            populateDistricts();
        };

        department.addEventListener('change', () => {
            province.dataset.selected = '';
            district.dataset.selected = '';
            populateProvinces();
        });

        province.addEventListener('change', () => {
            district.dataset.selected = '';
            populateDistricts();
        });

        populateProvinces();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const dniInput = document.querySelector('[name="student[dni]"]');
    if (!dniInput) {
        return;
    }

    dniInput.setAttribute('inputmode', 'numeric');
    dniInput.setAttribute('maxlength', '8');
    dniInput.addEventListener('input', debounce(() => normalizeStudentDni(dniInput)));
});

document.addEventListener('DOMContentLoaded', initPeruAddressSelects);

document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('app-confirm-dialog');
    if (!dialog) {
        return;
    }

    const title = document.getElementById('app-confirm-dialog-title');
    const message = document.getElementById('app-confirm-dialog-message');
    const accept = dialog.querySelector('[data-confirm-accept]');
    const cancelButtons = dialog.querySelectorAll('[data-confirm-cancel]');
    let pendingForm = null;

    document.addEventListener('submit', (event) => {
        const form = event.target;
        const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
        const confirmMessage = form instanceof HTMLFormElement
            ? (submitter?.dataset.confirmMessage || form.dataset.confirmMessage)
            : null;

        if (!(form instanceof HTMLFormElement) || !confirmMessage || form.dataset.confirmed === 'true') {
            return;
        }

        event.preventDefault();
        pendingForm = form;
        title.textContent = submitter?.dataset.confirmTitle || form.dataset.confirmTitle || 'Confirmar accion';
        message.textContent = confirmMessage;
        accept.textContent = submitter?.dataset.confirmButton || form.dataset.confirmButton || 'Confirmar';
        dialog.showModal();
    });

    accept?.addEventListener('click', () => {
        if (!pendingForm) {
            dialog.close();
            return;
        }

        pendingForm.dataset.confirmed = 'true';
        dialog.close();
        pendingForm.requestSubmit();
        pendingForm = null;
    });

    cancelButtons.forEach((button) => {
        button.addEventListener('click', () => {
            pendingForm = null;
            dialog.close();
        });
    });
});
