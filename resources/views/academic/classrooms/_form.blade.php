@csrf
<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-semibold text-on-surface-variant">Ciclo académico</label>
        <select name="academic_cycle_id" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm">
            @foreach ($cycles as $cycle)
                <option value="{{ $cycle->id }}" @selected((int) old('academic_cycle_id', $classroom->academic_cycle_id ?? 0) === (int) $cycle->id)>{{ $cycle->name }}</option>
            @endforeach
        </select>
        @error('academic_cycle_id')<p class="mt-1 text-sm text-error">{{ $message }}</p>@enderror
    </div>
    <x-input label="Nombre" name="name" :value="$classroom->name ?? null" />
    <x-input label="Código" name="code" :value="$classroom->code ?? null" />
    <x-input label="Piso" name="floor" type="number" :value="$classroom->floor ?? 1" min="1" />
    <x-input label="Capacidad máxima" name="capacity" type="number" :value="$classroom->capacity ?? 40" min="1" />
    <x-input label="Prioridad académica" name="academic_priority" type="number" :value="$classroom->academic_priority ?? 1" min="1" />
    <div>
        <label class="mb-1 block text-sm font-semibold text-on-surface-variant">Estado</label>
        <select name="status" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm">
            <option value="1" @selected((int) old('status', $classroom->status ?? 1) === 1)>Activo</option>
            <option value="0" @selected((int) old('status', $classroom->status ?? 1) === 0)>Inactivo</option>
        </select>
        @error('status')<p class="mt-1 text-sm text-error">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-semibold text-on-surface-variant">Descripción</label>
        <textarea name="description" rows="3" class="block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm">{{ old('description', $classroom->description ?? '') }}</textarea>
        @error('description')<p class="mt-1 text-sm text-error">{{ $message }}</p>@enderror
    </div>
</div>
<div class="mt-6 flex justify-end gap-2">
    <a href="{{ route('academic.classrooms.index') }}" class="rounded-lg border border-outline-variant px-4 py-2.5 text-sm font-semibold text-primary">Cancelar</a>
    <x-button type="submit">Guardar aula</x-button>
</div>
