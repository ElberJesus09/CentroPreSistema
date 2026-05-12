@extends('layouts.portal')

@section('title', 'Inscripcion | '.config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Inscripcion en linea</h1>
            <p class="mt-1 text-sm text-neutral-600">Complete cada paso. Puede retroceder sin perder lo ya ingresado.</p>
        </div>
        @if ($step > 1)
            <a
                href="{{ route('registration.step.show', ['step' => $step - 1]) }}"
                class="shrink-0 text-sm font-medium text-brand hover:underline"
            >
                &larr; Paso anterior
            </a>
        @endif
    </div>

    <x-registration.stepper :step="$step" />

    <div class="rounded-2xl border border-neutral-200/90 bg-white p-6 shadow-sm transition-all duration-200 sm:p-8">
        @if ($step === 1)
            <form method="post" action="{{ route('registration.step1.store') }}" class="relative space-y-6">
                @csrf
                <x-registration.honeypot />
                <h2 class="text-sm font-semibold uppercase tracking-wide text-neutral-500">Datos personales</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="Nombres" name="student[first_name]" :value="old('student.first_name', data_get($draft, 'student.first_name'))" />
                    <x-input label="Apellido paterno" name="student[last_name]" :value="old('student.last_name', data_get($draft, 'student.last_name'))" />
                    <x-input label="Apellido materno" name="student[mother_last_name]" :value="old('student.mother_last_name', data_get($draft, 'student.mother_last_name'))" />
                    <x-input label="DNI (8 digitos)" name="student[dni]" :value="old('student.dni', data_get($draft, 'student.dni'))" />
                    <x-input label="Fecha de nacimiento" name="student[birth_date]" type="date" :value="old('student.birth_date', data_get($draft, 'student.birth_date'))" />
                    <div class="space-y-1">
                        <label for="student_gender" class="block text-sm font-medium text-neutral-800">Sexo</label>
                        <select
                            id="student_gender"
                            name="student[gender]"
                            class="block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                        >
                            @php $g = old('student.gender', data_get($draft, 'student.gender')); @endphp
                            <option value="male" @selected($g === 'male')>Masculino</option>
                            <option value="female" @selected($g === 'female')>Femenino</option>
                        </select>
                        @error('student.gender')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-input label="Celular (9 digitos)" name="student[phone]" :value="old('student.phone', data_get($draft, 'student.phone'))" />
                    <x-input label="Correo" name="student[email]" type="email" :value="old('student.email', data_get($draft, 'student.email'))" />
                    <div class="space-y-1 sm:col-span-2">
                        <x-textarea
                            label="Direccion"
                            name="student[address]"
                            error-key="student.address"
                            rows="2"
                            :value="old('student.address', data_get($draft, 'student.address'))"
                        />
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-neutral-100 pt-6">
                    <x-button type="submit" variant="primary">Continuar</x-button>
                </div>
            </form>
        @elseif ($step === 2)
            <form method="post" action="{{ route('registration.step2.store') }}" class="relative space-y-6">
                @csrf
                <x-registration.honeypot />
                <h2 class="text-sm font-semibold uppercase tracking-wide text-neutral-500">Datos del apoderado</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="Nombres" name="guardian[first_name]" :value="old('guardian.first_name', data_get($draft, 'guardian.first_name'))" />
                    <x-input label="Apellido paterno" name="guardian[last_name]" :value="old('guardian.last_name', data_get($draft, 'guardian.last_name'))" />
                    <x-input label="Apellido materno" name="guardian[mother_last_name]" :value="old('guardian.mother_last_name', data_get($draft, 'guardian.mother_last_name'))" />
                    <x-input label="DNI (8 digitos)" name="guardian[dni]" :value="old('guardian.dni', data_get($draft, 'guardian.dni'))" />
                    <x-input label="Celular (9 digitos)" name="guardian[phone]" :value="old('guardian.phone', data_get($draft, 'guardian.phone'))" />
                    <div class="space-y-1">
                        <label for="guardian_relationship" class="block text-sm font-medium text-neutral-800">Parentesco</label>
                        <select
                            id="guardian_relationship"
                            name="guardian[relationship]"
                            class="block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                        >
                            @php $rel = old('guardian.relationship', data_get($draft, 'guardian.relationship', 'father')); @endphp
                            @foreach (['father' => 'Padre', 'mother' => 'Madre', 'uncle' => 'Tio', 'aunt' => 'Tia', 'guardian' => 'Apoderado'] as $value => $label)
                                <option value="{{ $value }}" @selected($rel === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('guardian.relationship')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-neutral-100 pt-6">
                    <x-button type="submit" variant="primary">Continuar</x-button>
                </div>
            </form>
        @elseif ($step === 3)
            <form method="post" action="{{ route('registration.step3.store') }}" class="relative space-y-6">
                @csrf
                <x-registration.honeypot />
                <h2 class="text-sm font-semibold uppercase tracking-wide text-neutral-500">Colegio de procedencia</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="Nombre del colegio" name="school[name]" :value="old('school.name', data_get($draft, 'school.name'))" />
                    <x-input label="Departamento" name="school[department]" :value="old('school.department', data_get($draft, 'school.department'))" />
                    <x-input label="Provincia" name="school[province]" :value="old('school.province', data_get($draft, 'school.province'))" />
                    <x-input label="Distrito" name="school[district]" :value="old('school.district', data_get($draft, 'school.district'))" />
                    <x-input label="Año de egreso" name="school[graduation_year]" type="number" :value="old('school.graduation_year', data_get($draft, 'school.graduation_year'))" />
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-neutral-100 pt-6">
                    <x-button type="submit" variant="primary">Continuar</x-button>
                </div>
            </form>
        @elseif ($step === 4)
            @if ($schedules->isEmpty())
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    En este momento no hay turnos con vacantes. Intente mas tarde o contacte a la institucion.
                </div>
            @else
                <form method="post" action="{{ route('registration.step4.store') }}" class="relative space-y-6">
                    @csrf
                    <x-registration.honeypot />
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-neutral-500">Informacion academica</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1 sm:col-span-2">
                            <label for="career_id" class="block text-sm font-medium text-neutral-800">Carrera postulante</label>
                            <select
                                id="career_id"
                                name="career_id"
                                class="block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                            >
                                @foreach ($careers as $career)
                                    <option value="{{ $career->id }}" @selected((string) old('career_id', data_get($draft, 'career_id')) === (string) $career->id)>
                                        {{ $career->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('career_id')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1 sm:col-span-2">
                            <label for="academic_cycle_shift_id" class="block text-sm font-medium text-neutral-800">Ciclo, sede y turno</label>
                            <select
                                id="academic_cycle_shift_id"
                                name="academic_cycle_shift_id"
                                class="block w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                            >
                                @foreach ($schedules as $row)
                                    @php
                                        $left = max(0, $row->capacity - $row->enrolled);
                                        $label = ($row->academicCycle?->name ?? '—').' — '.($row->campus?->name ?? '—').' — '.($row->shift?->name ?? '—')." ({$left} cupos)";
                                    @endphp
                                    <option value="{{ $row->id }}" @selected((string) old('academic_cycle_shift_id', data_get($draft, 'academic_cycle_shift_id')) === (string) $row->id)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_cycle_shift_id')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-3 border-t border-neutral-100 pt-6">
                        <x-button type="submit" variant="primary">Continuar</x-button>
                    </div>
                </form>
            @endif
        @elseif ($step === 5)
            <div class="space-y-8">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-neutral-500">Confirmacion</h2>
                <dl class="grid gap-6 text-sm sm:grid-cols-2">
                    <div class="rounded-lg border border-neutral-100 bg-neutral-50/50 p-4">
                        <dt class="text-xs font-semibold uppercase text-neutral-500">Postulante</dt>
                        <dd class="mt-2 text-neutral-900">
                            {{ data_get($draft, 'student.first_name') }} {{ data_get($draft, 'student.last_name') }}
                            {{ data_get($draft, 'student.mother_last_name') }}
                            <br />
                            <span class="text-neutral-600">DNI {{ data_get($draft, 'student.dni') }}</span>
                        </dd>
                    </div>
                    <div class="rounded-lg border border-neutral-100 bg-neutral-50/50 p-4">
                        <dt class="text-xs font-semibold uppercase text-neutral-500">Apoderado</dt>
                        <dd class="mt-2 text-neutral-900">
                            {{ data_get($draft, 'guardian.first_name') }} {{ data_get($draft, 'guardian.last_name') }}
                            <br />
                            <span class="text-neutral-600">{{ data_get($draft, 'guardian.phone') }}</span>
                        </dd>
                    </div>
                    <div class="rounded-lg border border-neutral-100 bg-neutral-50/50 p-4 sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase text-neutral-500">Colegio</dt>
                        <dd class="mt-2 text-neutral-900">{{ data_get($draft, 'school.name') }}</dd>
                    </div>
                    <div class="rounded-lg border border-neutral-100 bg-neutral-50/50 p-4 sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase text-neutral-500">Carrera y turno</dt>
                        <dd class="mt-2 text-neutral-900">
                            {{ $previewCareer?->name ?? '—' }}
                            <br />
                            <span class="text-neutral-600">
                                {{ $previewSchedule?->academicCycle?->name }} — {{ $previewSchedule?->campus?->name }} —
                                {{ $previewSchedule?->shift?->name }}
                            </span>
                        </dd>
                    </div>
                </dl>
                <form method="post" action="{{ route('registration.finish') }}" class="relative flex flex-wrap items-center justify-between gap-4 border-t border-neutral-100 pt-6">
                    @csrf
                    <x-registration.honeypot />
                    <a href="{{ route('registration.step.show', ['step' => 1, 'reset' => 1]) }}" class="text-sm text-neutral-600 hover:text-neutral-900 hover:underline">
                        Reiniciar formulario
                    </a>
                    <x-button type="submit" variant="primary">Finalizar registro</x-button>
                </form>
            </div>
        @endif
    </div>
@endsection
