@props(['current' => ''])

<aside
    class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-black/10 bg-brand text-white lg:flex"
    aria-label="Navegacion principal"
>
    <div class="border-b border-white/10 px-5 py-4">
        <p class="text-sm font-semibold tracking-wide">{{ config('app.name') }}</p>
        <p class="mt-1 text-xs text-white/70">Panel administrativo</p>
    </div>
    <nav class="flex flex-1 flex-col gap-1 p-3 text-sm">
        <a
            href="{{ route('dashboard') }}"
            @class([
                'rounded-md px-3 py-2 font-medium transition-colors',
                'bg-white/15 text-white' => $current === 'dashboard',
                'text-white/85 hover:bg-white/10 hover:text-white' => $current !== 'dashboard',
            ])
        >
            Dashboard
        </a>
        @can('viewAny', \App\Models\Staff::class)
            <a
                href="{{ route('staff.index') }}"
                @class([
                    'rounded-md px-3 py-2 font-medium transition-colors',
                    'bg-white/15 text-white' => str_starts_with((string) $current, 'staff.'),
                    'text-white/85 hover:bg-white/10 hover:text-white' => ! str_starts_with((string) $current, 'staff.'),
                ])
            >
                Staff
            </a>
        @endcan
        @can('viewAny', \App\Models\AcademicCycleShift::class)
            <a
                href="{{ route('academic-cycles.index') }}"
                @class([
                    'rounded-md px-3 py-2 font-medium transition-colors',
                    'bg-white/15 text-white' => str_starts_with((string) $current, 'academic-cycles.'),
                    'text-white/85 hover:bg-white/10 hover:text-white' => ! str_starts_with((string) $current, 'academic-cycles.'),
                ])
            >
                Academic Cycles
            </a>
        @endcan
        @can('viewAny', \App\Models\Student::class)
            <a
                href="{{ route('students.index') }}"
                @class([
                    'rounded-md px-3 py-2 font-medium transition-colors',
                    'bg-white/15 text-white' => str_starts_with((string) $current, 'students.'),
                    'text-white/85 hover:bg-white/10 hover:text-white' => ! str_starts_with((string) $current, 'students.'),
                ])
            >
                Students
            </a>
        @endcan
    </nav>
</aside>
