@extends('layouts.portal')

@section('title', 'Inscripción | '.config('app.name'))

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-8 rounded-xl bg-primary p-6 text-on-primary shadow-xl shadow-primary/10 md:p-8">
            <header class="flex flex-col justify-between gap-6 sm:flex-row sm:items-start">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.14em] text-secondary-fixed">
                        <span class="material-symbols-outlined text-base">edit_document</span>
                        Paso {{ $step }} de 5
                    </span>
                    <h1 class="mt-4 font-display text-3xl font-bold leading-tight text-white md:text-4xl">Inscripción en línea</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-primary-fixed md:text-base">
                    Complete cada paso. Puede retroceder sin perder lo ya ingresado.
                    </p>
                </div>
            </header>
            @if ($step > 1)
                <a
                    href="{{ route('registration.step.show', ['step' => $step - 1]) }}"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-white/20 sm:text-right"
                >
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Paso anterior
                </a>
            @endif
        </div>

        <x-registration.stepper :step="$step" />

        <div
            class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-6 shadow-xl shadow-primary/5 sm:p-8 md:p-10"
        >
            @if ($step === 1)
                @php
                    $locations = config('peru_locations', []);
                    $addressDraft = data_get($draft, 'student_address', []);
                    $selectedDepartment = old('address_department', data_get($addressDraft, 'address_department', ''));
                    $selectedProvince = old('address_province', data_get($addressDraft, 'address_province', ''));
                    $selectedDistrict = old('address_district', data_get($addressDraft, 'address_district', ''));
                    $addressLine = old('address_line', data_get($addressDraft, 'address_line', ''));
                @endphp
                <form method="post" action="{{ route('registration.step1.store') }}" class="relative space-y-8">
                    @csrf
                    <x-registration.honeypot />
                    <div class="flex items-center gap-3 border-b border-outline-variant/50 pb-4">
                        <div class="rounded-lg bg-primary-fixed p-2">
                            <span class="material-symbols-outlined text-primary">person</span>
                        </div>
                        <h2 class="font-display text-xl font-semibold text-primary">Datos personales</h2>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-input label="Nombres" name="student[first_name]" :value="old('student.first_name', data_get($draft, 'student.first_name'))" />
                        <x-input label="Apellido paterno" name="student[last_name]" :value="old('student.last_name', data_get($draft, 'student.last_name'))" />
                        <x-input label="Apellido materno" name="student[mother_last_name]" :value="old('student.mother_last_name', data_get($draft, 'student.mother_last_name'))" />
                        <x-input label="DNI (8 dígitos)" name="student[dni]" :value="old('student.dni', data_get($draft, 'student.dni'))" />
                        <x-input label="Fecha de nacimiento" name="student[birth_date]" type="date" :value="old('student.birth_date', data_get($draft, 'student.birth_date'))" />
                        <div class="space-y-1">
                            <label for="student_gender" class="block text-sm font-semibold text-on-surface-variant">Género</label>
                            <select
                                id="student_gender"
                                name="student[gender]"
                                class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                @php $g = old('student.gender', data_get($draft, 'student.gender')); @endphp
                                <option value="male" @selected($g === 'male')>Masculino</option>
                                <option value="female" @selected($g === 'female')>Femenino</option>
                            </select>
                            @error('student.gender')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <x-input label="Celular (9 dígitos)" name="student[phone]" :value="old('student.phone', data_get($draft, 'student.phone'))" />
                        <x-input label="Correo electrónico" name="student[email]" type="email" :value="old('student.email', data_get($draft, 'student.email'))" />
                    </div>
                    <div
                        class="grid gap-5 border-t border-outline-variant/50 pt-6 sm:grid-cols-3"
                        data-peru-address
                        data-locations='@json($locations)'
                    >
                        <div class="space-y-1">
                            <label for="address_department" class="block text-sm font-semibold text-on-surface-variant">Departamento</label>
                            <select
                                id="address_department"
                                name="address_department"
                                data-address-department
                                data-selected="{{ $selectedDepartment }}"
                                class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                <option value="">Seleccione</option>
                                @foreach ($locations as $department => $provinces)
                                    <option value="{{ $department }}" @selected($selectedDepartment === $department)>{{ $department }}</option>
                                @endforeach
                            </select>
                            @error('address_department')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1">
                            <label for="address_province" class="block text-sm font-semibold text-on-surface-variant">Provincia</label>
                            <select
                                id="address_province"
                                name="address_province"
                                data-address-province
                                data-selected="{{ $selectedProvince }}"
                                class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                <option value="">Seleccione</option>
                            </select>
                            @error('address_province')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1">
                            <label for="address_district" class="block text-sm font-semibold text-on-surface-variant">Distrito</label>
                            <select
                                id="address_district"
                                name="address_district"
                                data-address-district
                                data-selected="{{ $selectedDistrict }}"
                                class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                <option value="">Seleccione</option>
                            </select>
                            @error('address_district')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1 sm:col-span-3">
                            <label for="address_line" class="block text-sm font-semibold text-on-surface-variant">Calle, jirón, avenida o referencia</label>
                            <input
                                id="address_line"
                                name="address_line"
                                type="text"
                                value="{{ $addressLine }}"
                                placeholder="Ej. Av. Principal 123"
                                class="block w-full rounded-xl border border-outline-variant bg-surface-container-lowest px-3.5 py-3 text-sm text-on-surface shadow-sm placeholder:text-outline/50 transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                            />
                            @error('address_line')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-3 border-t border-outline-variant/50 pt-6">
                        <x-button type="submit" variant="primary" class="gap-2 rounded-lg px-8 shadow-md">
                            Continuar
                            <span class="material-symbols-outlined text-lg">arrow_forward</span>
                        </x-button>
                    </div>
                </form>
            @elseif ($step === 2)
                <form method="post" action="{{ route('registration.step2.store') }}" class="relative space-y-8">
                    @csrf
                    <x-registration.honeypot />
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-outline-variant/50 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-primary-fixed p-2">
                                <span class="material-symbols-outlined text-primary">supervisor_account</span>
                            </div>
                            <h2 class="font-display text-xl font-semibold text-primary">Datos del apoderado</h2>
                        </div>
                        @unless ($guardianRequired)
                            <x-button type="submit" name="skip_guardian" value="1" variant="secondary" class="gap-2 rounded-lg px-5">
                                Saltar
                                <span class="material-symbols-outlined text-lg">skip_next</span>
                            </x-button>
                        @endunless
                    </div>
                    @unless ($guardianRequired)
                        <div class="rounded-lg border border-secondary-container/50 bg-secondary-container/20 px-4 py-3 text-sm text-on-secondary-container">
                            Es mayor de edad. Puede registrar apoderado si desea; si no, puede continuar.
                        </div>
                    @endunless
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-input label="Nombres" name="guardian[first_name]" :value="old('guardian.first_name', data_get($draft, 'guardian.first_name'))" />
                        <x-input label="Apellido paterno" name="guardian[last_name]" :value="old('guardian.last_name', data_get($draft, 'guardian.last_name'))" />
                        <x-input label="Apellido materno" name="guardian[mother_last_name]" :value="old('guardian.mother_last_name', data_get($draft, 'guardian.mother_last_name'))" />
                        <x-input label="DNI (8 dígitos)" name="guardian[dni]" :value="old('guardian.dni', data_get($draft, 'guardian.dni'))" />
                        <x-input label="Celular (9 dígitos)" name="guardian[phone]" :value="old('guardian.phone', data_get($draft, 'guardian.phone'))" />
                        <div class="space-y-1">
                            <label for="guardian_relationship" class="block text-sm font-semibold text-on-surface-variant">Parentesco</label>
                            <select
                                id="guardian_relationship"
                                name="guardian[relationship]"
                                class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                @php $rel = old('guardian.relationship', data_get($draft, 'guardian.relationship', 'father')); @endphp
                                @foreach (['father' => 'Padre', 'mother' => 'Madre', 'uncle' => 'Tío', 'aunt' => 'Tía', 'guardian' => 'Apoderado'] as $value => $label)
                                    <option value="{{ $value }}" @selected($rel === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('guardian.relationship')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-3 border-t border-outline-variant/50 pt-6">
                        <x-button type="submit" variant="primary" class="gap-2 rounded-lg px-8 shadow-md">
                            Continuar
                            <span class="material-symbols-outlined text-lg">arrow_forward</span>
                        </x-button>
                    </div>
                </form>
            @elseif ($step === 3)
                @php
                    $locations = config('peru_locations', []);
                    $selectedSchoolDepartment = old('school.department', data_get($draft, 'school.department', ''));
                    $selectedSchoolProvince = old('school.province', data_get($draft, 'school.province', ''));
                    $selectedSchoolDistrict = old('school.district', data_get($draft, 'school.district', ''));
                @endphp
                <form method="post" action="{{ route('registration.step3.store') }}" class="relative space-y-8">
                    @csrf
                    <x-registration.honeypot />
                    <div class="flex items-center gap-3 border-b border-outline-variant/50 pb-4">
                        <div class="rounded-lg bg-primary-fixed p-2">
                            <span class="material-symbols-outlined text-primary">apartment</span>
                        </div>
                        <h2 class="font-display text-xl font-semibold text-primary">Colegio de procedencia</h2>
                    </div>
                    <div
                        class="grid gap-5 sm:grid-cols-2"
                        data-peru-address
                        data-locations='@json($locations)'
                    >
                        <x-input label="Nombre del colegio" name="school[name]" :value="old('school.name', data_get($draft, 'school.name'))" />
                        <div class="space-y-1">
                            <label for="school_department" class="block text-sm font-semibold text-on-surface-variant">Departamento</label>
                            <select
                                id="school_department"
                                name="school[department]"
                                data-address-department
                                data-selected="{{ $selectedSchoolDepartment }}"
                                class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                <option value="">Seleccione</option>
                                @foreach ($locations as $department => $provinces)
                                    <option value="{{ $department }}" @selected($selectedSchoolDepartment === $department)>{{ $department }}</option>
                                @endforeach
                            </select>
                            @error('school.department')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1">
                            <label for="school_province" class="block text-sm font-semibold text-on-surface-variant">Provincia</label>
                            <select
                                id="school_province"
                                name="school[province]"
                                data-address-province
                                data-selected="{{ $selectedSchoolProvince }}"
                                class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                <option value="">Seleccione</option>
                            </select>
                            @error('school.province')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1">
                            <label for="school_district" class="block text-sm font-semibold text-on-surface-variant">Distrito</label>
                            <select
                                id="school_district"
                                name="school[district]"
                                data-address-district
                                data-selected="{{ $selectedSchoolDistrict }}"
                                class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                <option value="">Seleccione</option>
                            </select>
                            @error('school.district')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <x-input label="Año de egreso" name="school[graduation_year]" type="number" :value="old('school.graduation_year', data_get($draft, 'school.graduation_year'))" placeholder="Ej. 2019" />
                    </div>
                    <div class="flex flex-wrap justify-end gap-3 border-t border-outline-variant/50 pt-6">
                        <x-button type="submit" variant="primary" class="gap-2 rounded-lg px-8 shadow-md">
                            Continuar
                            <span class="material-symbols-outlined text-lg">arrow_forward</span>
                        </x-button>
                    </div>
                </form>
            @elseif ($step === 4)
                @if ($schedules->isEmpty())
                    <div class="rounded-lg border border-secondary-container/50 bg-secondary-container/20 px-4 py-3 text-sm text-on-secondary-container">
                        En este momento no hay turnos con vacantes. Intente más tarde o contacte a la institución.
                    </div>
                @else
                    <form method="post" action="{{ route('registration.step4.store') }}" class="relative space-y-8">
                        @csrf
                        <x-registration.honeypot />
                        <div class="flex items-center gap-3 border-b border-outline-variant/50 pb-4">
                            <div class="rounded-lg bg-primary-fixed p-2">
                                <span class="material-symbols-outlined text-primary">school</span>
                            </div>
                            <h2 class="font-display text-xl font-semibold text-primary">Información académica</h2>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="space-y-1 sm:col-span-2">
                                <label for="career_id" class="block text-sm font-semibold text-on-surface-variant">Carrera postulante</label>
                                <select
                                    id="career_id"
                                    name="career_id"
                                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                >
                                    @foreach ($careers as $career)
                                        <option value="{{ $career->id }}" @selected((string) old('career_id', data_get($draft, 'career_id')) === (string) $career->id)>
                                            {{ $career->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('career_id')
                                    <p class="text-sm text-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="space-y-1 sm:col-span-2">
                                <label for="academic_cycle_shift_id" class="block text-sm font-semibold text-on-surface-variant">Ciclo, sede y turno</label>
                                <select
                                    id="academic_cycle_shift_id"
                                    name="academic_cycle_shift_id"
                                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                >
                                    @foreach ($schedules as $row)
                                        @php
                                            $label = ($row->academicCycle?->name ?? '—').' — '.($row->campus?->name ?? '—').' — '.($row->shift?->name ?? '—');
                                        @endphp
                                        <option value="{{ $row->id }}" @selected((string) old('academic_cycle_shift_id', data_get($draft, 'academic_cycle_shift_id')) === (string) $row->id)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('academic_cycle_shift_id')
                                    <p class="text-sm text-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid gap-6 border-t border-outline-variant/50 pt-6 lg:grid-cols-[minmax(0,1fr)_minmax(16rem,24rem)] lg:items-start">
                            <div>
                                <div class="mb-4 flex items-center gap-3">
                                    <div class="rounded-lg bg-primary-fixed p-2">
                                        <span class="material-symbols-outlined text-primary">receipt_long</span>
                                    </div>
                                    <h3 class="font-display text-lg font-semibold text-primary">Datos del pago</h3>
                                </div>
                                <div class="grid gap-5 sm:grid-cols-2">
                                    <x-input
                                        label="Número de voucher"
                                        name="student[payment_voucher_number]"
                                        :value="data_get($draft, 'student.payment_voucher_number')"
                                        inputmode="numeric"
                                        placeholder="1742..."
                                    />
                                    <x-input
                                        label="Número de agencia"
                                        name="student[payment_agency_number]"
                                        :value="data_get($draft, 'student.payment_agency_number')"
                                        inputmode="numeric"
                                        maxlength="4"
                                        placeholder="0230"
                                    />
                                    <x-input
                                        label="Fecha del pago"
                                        name="student[payment_date]"
                                        type="date"
                                        :value="data_get($draft, 'student.payment_date')"
                                    />
                                </div>
                            </div>
                            <div class="overflow-hidden rounded-lg border border-outline-variant/50 bg-surface-container-low">
                                <img
                                    src="{{ asset('images/public/vaucher.png') }}"
                                    alt="Ejemplo de voucher con número de voucher, agencia y fecha de pago"
                                    class="h-auto w-full object-contain"
                                    loading="lazy"
                                >
                            </div>
                        </div>
                        <div class="flex flex-wrap justify-end gap-3 border-t border-outline-variant/50 pt-6">
                            <x-button type="submit" variant="primary" class="gap-2 rounded-lg px-8 shadow-md">
                                Continuar
                                <span class="material-symbols-outlined text-lg">arrow_forward</span>
                            </x-button>
                        </div>
                    </form>
                @endif
            @elseif ($step === 5)
                <div class="space-y-8">
                    <div class="flex items-center gap-3 border-b border-outline-variant/50 pb-4">
                        <div class="rounded-lg bg-primary-fixed p-2">
                            <span class="material-symbols-outlined text-primary">task_alt</span>
                        </div>
                        <h2 class="font-display text-xl font-semibold text-primary">Confirmación</h2>
                    </div>
                    <dl class="grid gap-5 text-sm sm:grid-cols-2">
                        <div class="rounded-lg border border-outline-variant/40 bg-surface-container-low p-4">
                            <dt class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Postulante</dt>
                            <dd class="mt-2 text-on-surface">
                                {{ data_get($draft, 'student.first_name') }} {{ data_get($draft, 'student.last_name') }}
                                {{ data_get($draft, 'student.mother_last_name') }}
                                <br />
                                <span class="text-on-surface-variant">DNI {{ data_get($draft, 'student.dni') }}</span>
                            </dd>
                        </div>
                        <div class="rounded-lg border border-outline-variant/40 bg-surface-container-low p-4">
                            <dt class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Apoderado</dt>
                            <dd class="mt-2 text-on-surface">
                                @if (data_get($draft, 'guardian.first_name'))
                                    {{ data_get($draft, 'guardian.first_name') }} {{ data_get($draft, 'guardian.last_name') }}
                                    <br />
                                    <span class="text-on-surface-variant">{{ data_get($draft, 'guardian.phone') }}</span>
                                @else
                                    <span class="text-on-surface-variant">No registrado por mayoria de edad</span>
                                @endif
                            </dd>
                        </div>
                        <div class="rounded-lg border border-outline-variant/40 bg-surface-container-low p-4 sm:col-span-2">
                            <dt class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Colegio</dt>
                            <dd class="mt-2 text-on-surface">{{ data_get($draft, 'school.name') }}</dd>
                        </div>
                        <div class="rounded-lg border border-outline-variant/40 bg-surface-container-low p-4 sm:col-span-2">
                            <dt class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Carrera y turno</dt>
                            <dd class="mt-2 text-on-surface">
                                {{ $previewCareer?->name ?? '—' }}
                                <br />
                                <span class="text-on-surface-variant">
                                    {{ $previewSchedule?->academicCycle?->name }} — {{ $previewSchedule?->campus?->name }} —
                                    {{ $previewSchedule?->shift?->name }}
                                </span>
                            </dd>
                        </div>
                        <div class="rounded-lg border border-outline-variant/40 bg-surface-container-low p-4 sm:col-span-2">
                            <dt class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Pago</dt>
                            <dd class="mt-2 text-on-surface">
                                Voucher {{ data_get($draft, 'student.payment_voucher_number') }}
                                <br />
                                <span class="text-on-surface-variant">
                                    Agencia {{ data_get($draft, 'student.payment_agency_number') }} — Fecha {{ data_get($draft, 'student.payment_date') }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                    <form
                        method="post"
                        action="{{ route('registration.finish') }}"
                        class="relative flex flex-wrap items-center justify-between gap-4 border-t border-outline-variant/50 pt-6"
                    >
                        @csrf
                        <x-registration.honeypot />
                        <a
                            href="{{ route('registration.step.show', ['step' => 1, 'reset' => 1]) }}"
                            class="text-sm text-on-surface-variant hover:text-primary hover:underline"
                        >
                            Reiniciar formulario
                        </a>
                        <x-button type="submit" variant="primary" class="rounded-lg px-8 shadow-md">Finalizar registro</x-button>
                    </form>
                </div>
            @endif
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            <div class="flex gap-3 rounded-xl border border-secondary-container/30 bg-secondary-container/15 p-4">
                <span class="material-symbols-outlined text-secondary">verified_user</span>
                <div>
                    <h3 class="text-sm font-bold text-on-secondary-container">Privacidad</h3>
                    <p class="mt-1 text-xs text-on-secondary-container/90">Sus datos se tratan conforme a la normativa peruana de protección de datos personales.</p>
                </div>
            </div>
            <div class="flex gap-3 rounded-xl border border-outline-variant/40 bg-surface-container-high/50 p-4">
                <span class="material-symbols-outlined text-primary">support_agent</span>
                <div>
                    <h3 class="text-sm font-bold text-on-surface">¿Necesita ayuda?</h3>
                    <p class="mt-1 text-xs text-on-surface-variant">Comuníquese con secretaría del centro preuniversitario.</p>
                </div>
            </div>
            <div class="flex gap-3 rounded-xl border border-outline-variant/40 bg-surface-container-high/50 p-4">
                <span class="material-symbols-outlined text-primary">schedule</span>
                <div>
                    <h3 class="text-sm font-bold text-on-surface">Horario</h3>
                    <p class="mt-1 text-xs text-on-surface-variant">Atención según calendario institucional publicado.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
