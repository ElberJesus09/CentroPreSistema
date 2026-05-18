@extends('layouts.app')

@section('title', 'Mensaje de correo | '.config('app.name'))

@section('content')
    <div class="mb-6">
        <h1 class="font-display text-xl font-bold text-primary md:text-2xl">Mensaje de correo</h1>
        <p class="text-sm text-on-surface-variant">Configure el mensaje del correo y active o desactive el envio automatico al terminar la inscripcion.</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,36rem)_minmax(0,1fr)]">
        <div class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-6 shadow-sm md:p-8">
            <form method="post" action="{{ route('exam-settings.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="rounded-lg border border-outline-variant bg-white p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-on-surface">Envio automatico de correo</p>
                            <p class="mt-1 text-xs leading-relaxed text-on-surface-variant">
                                Si esta desactivado, el postulante vera botones para descargar la ficha y el reglamento al finalizar.
                            </p>
                        </div>
                        <label for="registration_mail_enabled" class="inline-flex cursor-pointer items-center gap-3">
                            <input
                                type="checkbox"
                                name="registration_mail_enabled"
                                id="registration_mail_enabled"
                                value="1"
                                @checked(old('registration_mail_enabled', $examSetting->registration_mail_enabled))
                                class="h-5 w-5 rounded border-outline-variant text-primary focus:ring-primary"
                            />
                            <span class="text-sm font-semibold text-on-surface">Activado</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="exam_date" class="block text-sm font-semibold text-on-surface-variant">Fecha del examen</label>
                    <input
                        type="date"
                        name="exam_date"
                        id="exam_date"
                        value="{{ old('exam_date', optional($examSetting->exam_date)->format('Y-m-d')) }}"
                        class="mt-1 block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    />
                </div>

                <div>
                    <label for="exam_time" class="block text-sm font-semibold text-on-surface-variant">Hora del examen</label>
                    <input
                        type="text"
                        name="exam_time"
                        id="exam_time"
                        value="{{ old('exam_time', $examSetting->exam_time) }}"
                        placeholder="Ej. 09:00"
                        class="mt-1 block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    />
                </div>

                <div>
                    <label for="exam_location" class="block text-sm font-semibold text-on-surface-variant">Lugar del examen</label>
                    <input
                        type="text"
                        name="exam_location"
                        id="exam_location"
                        value="{{ old('exam_location', $examSetting->exam_location) }}"
                        class="mt-1 block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    />
                </div>

                <div>
                    <label for="institutional_message" class="block text-sm font-semibold text-on-surface-variant">Mensaje institucional / indicaciones</label>
                    <textarea
                        name="institutional_message"
                        id="institutional_message"
                        rows="5"
                        class="mt-1 block w-full rounded-lg border border-outline-variant bg-white px-3 py-2.5 text-sm text-on-surface shadow-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >{{ old('institutional_message', $examSetting->institutional_message) }}</textarea>
                </div>

                <div class="flex justify-end border-t border-outline-variant/50 pt-5">
                    <x-button type="submit">Guardar cambios</x-button>
                </div>
            </form>
        </div>

        <section class="rounded-xl border border-outline-variant/50 bg-surface-container-lowest p-6 shadow-sm md:p-8">
            <div class="mb-5">
                <h2 class="font-display text-lg font-semibold text-primary">Vista previa del correo</h2>
                <p class="text-sm text-on-surface-variant">Asi se mostrara la informacion principal dentro del correo de confirmacion.</p>
            </div>

            <div @class([
                'mb-5 rounded-lg border px-4 py-3 text-sm',
                'border-emerald-200 bg-emerald-50 text-emerald-950' => $examSetting->registration_mail_enabled,
                'border-secondary-container/50 bg-secondary-container/20 text-on-secondary-container' => ! $examSetting->registration_mail_enabled,
            ])>
                {{ $examSetting->registration_mail_enabled ? 'El correo automatico esta activado.' : 'El correo automatico esta desactivado; el registro mostrara descargas directas.' }}
            </div>

            <div class="overflow-hidden rounded-lg border border-outline-variant bg-white">
                <table class="w-full text-left text-sm">
                    <tbody class="divide-y divide-outline-variant/70">
                        <tr>
                            <th scope="row" class="w-32 bg-surface-container px-4 py-3 font-semibold text-on-surface-variant">Fecha</th>
                            <td class="px-4 py-3 text-on-surface">{{ $examSetting->exam_date ? $examSetting->exam_date->format('d/m/Y') : 'Por confirmar' }}</td>
                        </tr>
                        <tr>
                            <th scope="row" class="bg-surface-container px-4 py-3 font-semibold text-on-surface-variant">Hora</th>
                            <td class="px-4 py-3 text-on-surface">{{ $examSetting->exam_time ?: 'Por confirmar' }}</td>
                        </tr>
                        <tr>
                            <th scope="row" class="bg-surface-container px-4 py-3 font-semibold text-on-surface-variant">Lugar</th>
                            <td class="px-4 py-3 text-on-surface">{{ $examSetting->exam_location ?: 'Por confirmar' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-5 rounded-lg border border-outline-variant bg-white p-4">
                <p class="text-sm font-semibold text-on-surface">Indicaciones</p>
                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-on-surface-variant">{{ $examSetting->institutional_message ?: 'Sin indicaciones registradas.' }}</p>
            </div>
        </section>
    </div>
@endsection
