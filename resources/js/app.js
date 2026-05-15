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

document.addEventListener('DOMContentLoaded', () => {
    const dniInput = document.querySelector('[name="student[dni]"]');
    if (!dniInput) {
        return;
    }

    dniInput.setAttribute('inputmode', 'numeric');
    dniInput.setAttribute('maxlength', '8');
    dniInput.addEventListener('input', debounce(() => lookupStudentProfile(dniInput)));
});
