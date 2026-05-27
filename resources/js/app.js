const studentAutofillFields = {
    student: [
        'first_name',
        'last_name',
        'mother_last_name',
        'birth_date',
        'gender',
        'phone',
        'address',
        'email',
    ],
    guardian: [
        'first_name',
        'last_name',
        'mother_last_name',
        'dni',
        'phone',
        'relationship',
    ],
    school: [
        'name',
        'department',
        'province',
        'district',
        'graduation_year',
    ],
};

function debounce(callback, delay = 350) {
    let timer = null;

    return (...args) => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => callback(...args), delay);
    };
}

function fieldFor(section, key) {
    return document.querySelector(`[name="${section}[${key}]"]`);
}

function setFieldValue(section, key, value) {
    if (value === null || value === undefined || value === '') {
        return;
    }

    const field = fieldFor(section, key);
    if (!field || (field.value && field.value !== value)) {
        return;
    }

    field.value = value;
    field.dispatchEvent(new Event('change', { bubbles: true }));
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

async function lookupStudentProfile(input) {
    const dni = input.value.replace(/\D/g, '');
    if (input.value !== dni) {
        input.value = dni;
    }

    if (dni.length !== 8) {
        setAutofillMessage(input, 'Ingrese 8 dígitos para buscar registros previos.');
        return;
    }

    setAutofillMessage(input, 'Buscando registros previos...');

    try {
        const response = await fetch(`/registration/dni-lookup?dni=${encodeURIComponent(dni)}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            setAutofillMessage(input, 'No se pudo consultar el DNI en este momento.', 'warning');
            return;
        }

        const data = await response.json();
        if (!data.found || !data.profile) {
            setAutofillMessage(input, 'No hay datos previos para este DNI.');
            return;
        }

        Object.entries(studentAutofillFields).forEach(([section, fields]) => {
            fields.forEach((key) => setFieldValue(section, key, data.profile[section]?.[key]));
        });

        setAutofillMessage(input, 'Datos previos encontrados. Se completaron los campos disponibles.', 'success');
    } catch {
        setAutofillMessage(input, 'No se pudo consultar el DNI en este momento.', 'warning');
    }
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
    dniInput.addEventListener('input', debounce(() => lookupStudentProfile(dniInput)));
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
        title.textContent = submitter?.dataset.confirmTitle || form.dataset.confirmTitle || 'Confirmar acción';
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
