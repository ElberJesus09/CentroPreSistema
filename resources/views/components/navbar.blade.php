<header class="sticky top-0 z-20 border-b border-neutral-200 bg-white">
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 lg:px-8">
        <div class="flex flex-1 items-center gap-4 lg:hidden">
            <details class="relative group">
                <summary
                    class="cursor-pointer list-none rounded-md border border-neutral-200 px-3 py-2 text-sm font-medium text-neutral-800 hover:bg-neutral-50 [&::-webkit-details-marker]:hidden"
                >
                    Menu
                </summary>
                <div
                    class="absolute left-0 z-40 mt-1 min-w-[12rem] rounded-md border border-neutral-200 bg-white py-1 shadow-md"
                >
                    <a
                        href="{{ route('dashboard') }}"
                        class="block px-4 py-2 text-sm text-neutral-800 hover:bg-neutral-50"
                    >
                        Dashboard
                    </a>
                    @can('viewAny', \App\Models\Staff::class)
                        <a
                            href="{{ route('staff.index') }}"
                            class="block px-4 py-2 text-sm text-neutral-800 hover:bg-neutral-50"
                        >
                            Staff
                        </a>
                    @endcan
                    @can('viewAny', \App\Models\AcademicCycleShift::class)
                        <a
                            href="{{ route('academic-cycles.index') }}"
                            class="block px-4 py-2 text-sm text-neutral-800 hover:bg-neutral-50"
                        >
                            Academic Cycles
                        </a>
                    @endcan
                    @can('viewAny', \App\Models\Student::class)
                        <a
                            href="{{ route('students.index') }}"
                            class="block px-4 py-2 text-sm text-neutral-800 hover:bg-neutral-50"
                        >
                            Students
                        </a>
                    @endcan
                </div>
            </details>
        </div>
        <div class="ml-auto flex items-center gap-4 text-sm text-neutral-700">
            <span class="hidden sm:inline">{{ auth()->user()->username }}</span>
            <span class="hidden rounded bg-neutral-100 px-2 py-0.5 text-xs text-neutral-600 md:inline">
                {{ auth()->user()->role?->name ?? 'sin rol' }}
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
