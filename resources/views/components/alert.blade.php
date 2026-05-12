@if (session('success'))
    <div
        class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900"
        role="status"
    >
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
        <p class="font-medium">Revise los datos del formulario.</p>
        <ul class="mt-2 list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
