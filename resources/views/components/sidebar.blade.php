@props(['current' => ''])

<aside
    class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-on-primary-fixed-variant/20 bg-primary text-on-primary lg:flex"
    aria-label="Navegación principal"
>
    <div class="border-b border-on-primary-fixed-variant/20 px-5 py-4">
        <p class="font-display text-sm font-bold tracking-wide">{{ config('app.name') }}</p>
        <p class="mt-1 text-xs text-primary-fixed/80">Panel administrativo</p>
    </div>
    <nav class="flex flex-1 flex-col gap-1 p-3 text-sm">
        <a
            href="{{ route('dashboard') }}"
            @class([
                'rounded-lg px-3 py-2 font-semibold transition-colors',
                'bg-white/15 text-white' => $current === 'dashboard',
                'text-primary-fixed/90 hover:bg-white/10 hover:text-white' => $current !== 'dashboard',
            ])
        >
            Panel
        </a>
        @can('viewAny', \App\Models\Staff::class)
            <a
                href="{{ route('staff.index') }}"
                @class([
                    'rounded-lg px-3 py-2 font-semibold transition-colors',
                    'bg-white/15 text-white' => str_starts_with((string) $current, 'staff.'),
                    'text-primary-fixed/90 hover:bg-white/10 hover:text-white' => ! str_starts_with((string) $current, 'staff.'),
                ])
            >
                Personal
            </a>
        @endcan
        @can('viewAny', \App\Models\AcademicCycleShift::class)
            <a
                href="{{ route('academic-cycles.index') }}"
                @class([
                    'rounded-lg px-3 py-2 font-semibold transition-colors',
                    'bg-white/15 text-white' => str_starts_with((string) $current, 'academic-cycles.'),
                    'text-primary-fixed/90 hover:bg-white/10 hover:text-white' => ! str_starts_with((string) $current, 'academic-cycles.'),
                ])
            >
                Ciclos académicos
            </a>
        @endcan
        @can('viewAny', \App\Models\ExamSetting::class)
            <a
                href="{{ route('exam-settings.edit') }}"
                @class([
                    'rounded-lg px-3 py-2 font-semibold transition-colors',
                    'bg-white/15 text-white' => str_starts_with((string) $current, 'exam-settings.'),
                    'text-primary-fixed/90 hover:bg-white/10 hover:text-white' => ! str_starts_with((string) $current, 'exam-settings.'),
                ])
            >
                Mensaje de correo
            </a>
        @endcan
        @can('viewAny', \App\Models\Student::class)
            <a
                href="{{ route('students.index') }}"
                @class([
                    'rounded-lg px-3 py-2 font-semibold transition-colors',
                    'bg-white/15 text-white' => str_starts_with((string) $current, 'students.'),
                    'text-primary-fixed/90 hover:bg-white/10 hover:text-white' => ! str_starts_with((string) $current, 'students.'),
                ])
            >
                Alumnos
            </a>
        @endcan
        @if (auth()->user()?->canAccessReportsModule())
            <a
                href="{{ route('reports.index') }}"
                @class([
                    'rounded-lg px-3 py-2 font-semibold transition-colors',
                    'bg-white/15 text-white' => str_starts_with((string) $current, 'reports.'),
                    'text-primary-fixed/90 hover:bg-white/10 hover:text-white' => ! str_starts_with((string) $current, 'reports.'),
                ])
            >
                Reportes
            </a>
        @endif
    </nav>
</aside>
