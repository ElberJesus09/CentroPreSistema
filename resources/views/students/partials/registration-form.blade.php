{{--
    Formulario compartido (publico y panel).
    Variables: $action, $method, $student (nullable), $schedules, $careers, $showStatusField
--}}
@php
    $method = strtolower($method ?? 'post');
    $locations = config('peru_locations', []);
    $selectedSchoolDepartment = old('school.department', $student?->school?->department ?? '');
    $selectedSchoolProvince = old('school.province', $student?->school?->province ?? '');
    $selectedSchoolDistrict = old('school.district', $student?->school?->district ?? '');
@endphp
<form method="post" action="{{ $action }}" class="space-y-10 rounded-xl border border-outline-variant/40 bg-surface-container-lowest p-6 shadow-sm lg:p-8">
    @csrf
    @if ($method === 'put')
        @method('PUT')
    @endif

    <section>
        <h2 class="mb-4 border-b border-outline-variant/50 pb-2 text-sm font-bold uppercase tracking-wide text-on-surface-variant">
            Datos personales
        </h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-input label="Nombres" name="student[first_name]" :value="old('student.first_name', $student?->first_name ?? '')" />
            <x-input label="Apellido paterno" name="student[last_name]" :value="old('student.last_name', $student?->last_name ?? '')" />
            <x-input label="Apellido materno" name="student[mother_last_name]" :value="old('student.mother_last_name', $student?->mother_last_name ?? '')" />
            <x-input label="DNI (8 dígitos)" name="student[dni]" :value="old('student.dni', $student?->dni ?? '')" />
            <x-input label="Fecha de nacimiento" name="student[birth_date]" type="date" :value="old('student.birth_date', $student?->birth_date?->format('Y-m-d') ?? '')" />
            <div class="space-y-1">
                <label for="student_gender" class="block text-sm font-semibold text-on-surface-variant">Género</label>
                <select
                    id="student_gender"
                    name="student[gender]"
                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                    <option value="male" @selected(old('student.gender', $student?->gender ?? '') === 'male')>Masculino</option>
                    <option value="female" @selected(old('student.gender', $student?->gender ?? '') === 'female')>Femenino</option>
                </select>
                @error('student.gender')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <x-input label="Celular (9 dígitos)" name="student[phone]" :value="old('student.phone', $student?->phone ?? '')" />
            <x-input label="Correo" name="student[email]" type="email" :value="old('student.email', $student?->email ?? '')" />
            <div class="space-y-1 sm:col-span-2">
                <x-textarea
                    label="Dirección"
                    name="student[address]"
                    error-key="student.address"
                    rows="2"
                    :value="old('student.address', $student?->address ?? '')"
                />
            </div>
        </div>
    </section>

    <section>
        <h2 class="mb-4 border-b border-outline-variant/50 pb-2 text-sm font-bold uppercase tracking-wide text-on-surface-variant">
            Apoderado
        </h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-input label="Nombres" name="guardian[first_name]" :value="old('guardian.first_name', $student?->guardian?->first_name ?? '')" />
            <x-input label="Apellido paterno" name="guardian[last_name]" :value="old('guardian.last_name', $student?->guardian?->last_name ?? '')" />
            <x-input label="Apellido materno" name="guardian[mother_last_name]" :value="old('guardian.mother_last_name', $student?->guardian?->mother_last_name ?? '')" />
            <x-input label="DNI (8 dígitos)" name="guardian[dni]" :value="old('guardian.dni', $student?->guardian?->dni ?? '')" />
            <x-input label="Teléfono (9 dígitos)" name="guardian[phone]" :value="old('guardian.phone', $student?->guardian?->phone ?? '')" />
            <div class="space-y-1">
                <label for="guardian_relationship" class="block text-sm font-semibold text-on-surface-variant">Parentesco</label>
                <select
                    id="guardian_relationship"
                    name="guardian[relationship]"
                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                    @foreach (['father' => 'Padre', 'mother' => 'Madre', 'uncle' => 'Tío', 'aunt' => 'Tía', 'guardian' => 'Apoderado'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('guardian.relationship', $student?->guardian?->relationship ?? '') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('guardian.relationship')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    <section>
        <h2 class="mb-4 border-b border-outline-variant/50 pb-2 text-sm font-bold uppercase tracking-wide text-on-surface-variant">
            Colegio de procedencia
        </h2>
        <div
            class="grid gap-4 sm:grid-cols-2"
            data-peru-address
            data-locations='@json($locations)'
        >
            <x-input label="Nombre del colegio" name="school[name]" :value="old('school.name', $student?->school?->name ?? '')" />
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
                    <p class="text-sm text-red-600">{{ $message }}</p>
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
                    <p class="text-sm text-red-600">{{ $message }}</p>
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
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <x-input label="Año de egreso" name="school[graduation_year]" type="number" :value="old('school.graduation_year', $student?->school?->graduation_year ?? '')" />
        </div>
    </section>

    <section>
        <h2 class="mb-4 border-b border-outline-variant/50 pb-2 text-sm font-bold uppercase tracking-wide text-on-surface-variant">
            Datos del pago
        </h2>
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(16rem,24rem)] lg:items-start">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-input
                    label="Número de voucher"
                    name="student[payment_voucher_number]"
                    :value="$student?->payment_voucher_number ?? ''"
                    inputmode="numeric"
                    placeholder="1742..."
                />
                <x-input
                    label="Número de agencia"
                    name="student[payment_agency_number]"
                    :value="$student?->payment_agency_number ?? ''"
                    inputmode="numeric"
                    maxlength="4"
                    placeholder="0230"
                />
                <x-input
                    label="Fecha del pago"
                    name="student[payment_date]"
                    type="date"
                    :value="$student?->payment_date?->format('Y-m-d') ?? ''"
                />
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
    </section>

    <section>
        <h2 class="mb-4 border-b border-outline-variant/50 pb-2 text-sm font-bold uppercase tracking-wide text-on-surface-variant">
            Datos académicos
        </h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1 sm:col-span-2">
                <label for="career_id" class="block text-sm font-semibold text-on-surface-variant">Carrera postulante</label>
                <select
                    id="career_id"
                    name="career_id"
                    class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                    @foreach ($careers as $career)
                        <option value="{{ $career->id }}" @selected((string) old('career_id', $student?->career_id ?? '') === (string) $career->id)>
                            {{ $career->name }}{{ $career->status ? '' : ' (inactiva)' }}
                        </option>
                    @endforeach
                </select>
                @error('career_id')
                    <p class="text-sm text-red-600">{{ $message }}</p>
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
                        <option value="{{ $row->id }}" @selected((string) old('academic_cycle_shift_id', $student?->academic_cycle_shift_id ?? '') === (string) $row->id)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('academic_cycle_shift_id')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @if ($showStatusField)
                <div class="space-y-1 sm:col-span-2">
                    <label for="status" class="block text-sm font-semibold text-on-surface-variant">Estado del expediente</label>
                    <select
                        id="status"
                        name="status"
                        class="block w-full max-w-md rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="{{ \App\Models\Student::STATUS_PENDING }}" @selected(old('status', $student?->status ?? \App\Models\Student::STATUS_PENDING) === \App\Models\Student::STATUS_PENDING)>
                            Pendiente
                        </option>
                        <option value="{{ \App\Models\Student::STATUS_ACTIVE }}" @selected(old('status', $student?->status ?? '') === \App\Models\Student::STATUS_ACTIVE)>
                            Activo
                        </option>
                        <option value="{{ \App\Models\Student::STATUS_REJECTED }}" @selected(old('status', $student?->status ?? '') === \App\Models\Student::STATUS_REJECTED)>
                            Rechazado
                        </option>
                    </select>
                    @error('status')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </div>
    </section>

    <div class="flex flex-wrap gap-3 border-t border-outline-variant/50 pt-6">
        <x-button type="submit" variant="primary">{{ $submitLabel ?? 'Enviar postulación' }}</x-button>
        @isset($cancelUrl)
            <a
                href="{{ $cancelUrl }}"
                class="inline-flex items-center justify-center rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface hover:bg-surface-container-high"
            >
                Cancelar
            </a>
        @endisset
    </div>
</form>
