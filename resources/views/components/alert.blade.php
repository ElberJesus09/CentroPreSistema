@if (session('success'))
    <div
        class="mb-6 rounded-lg border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-950"
        role="status"
    >
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-6 rounded-lg border border-error/30 bg-error-container px-4 py-3 text-sm text-error" role="status">
        {{ session('error') }}
    </div>
@endif

@if (session('warning'))
    <div class="mb-6 rounded-lg border border-secondary-container/50 bg-secondary-container/20 px-4 py-3 text-sm text-on-secondary-container" role="status">
        {{ session('warning') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-error/30 bg-error-container px-4 py-3 text-sm text-error" role="alert">
        <p class="font-semibold">Revise los datos del formulario.</p>
        <ul class="mt-2 list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
