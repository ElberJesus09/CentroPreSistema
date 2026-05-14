<header class="sticky top-0 z-20 border-b border-outline-variant bg-surface-container-lowest/95 backdrop-blur">
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 lg:px-8">
        <div class="flex flex-1 items-center gap-4 lg:hidden">
            <details class="group relative">
                <summary
                    class="cursor-pointer list-none rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm font-semibold text-on-surface hover:bg-surface-container-high [&::-webkit-details-marker]:hidden"
                >
                    Menú
                </summary>
                <div
                    class="absolute left-0 z-40 mt-1 min-w-[12rem] rounded-xl border border-outline-variant bg-surface-container-lowest py-1 shadow-lg"
                >
                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-high">
                        Panel
                    </a>
                    @can('viewAny', \App\Models\Staff::class)
                        <a href="{{ route('staff.index') }}" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-high">
                            Personal
                        </a>
                    @endcan
                    @can('viewAny', \App\Models\AcademicCycleShift::class)
                        <a href="{{ route('academic-cycles.index') }}" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-high">
                            Ciclos académicos
                        </a>
                    @endcan
                    @can('viewAny', \App\Models\Student::class)
                        <a href="{{ route('students.index') }}" class="block px-4 py-2 text-sm text-on-surface hover:bg-surface-container-high">
                            Alumnos
                        </a>
                    @endcan
                </div>
            </details>
        </div>
        <div class="ml-auto flex items-center gap-4 text-sm text-on-surface-variant">
            <span class="hidden sm:inline">{{ auth()->user()->username }}</span>
            <span class="hidden rounded-lg bg-surface-container-high px-2 py-0.5 text-xs text-on-surface md:inline">
                {{ auth()->user()->role?->name ?? 'Sin rol' }}
            </span>
            <form method="post" action="{{ route('logout') }}" class="inline">
                @csrf
                <x-button type="submit" variant="secondary" class="text-xs">
                    Salir
                </x-button>
            </form>
        </div>
    </div>
</header>
