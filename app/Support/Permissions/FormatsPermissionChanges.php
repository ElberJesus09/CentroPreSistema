<?php

namespace App\Support\Permissions;

trait FormatsPermissionChanges
{
    /**
     * @param  list<string>  $before
     * @param  list<string>  $after
     * @return array<string, list<string>>
     */
    private function permissionDiff(array $before, array $after): array
    {
        return [
            'Agregados' => $this->permissionLabels(array_values(array_diff($after, $before))),
            'Quitados' => $this->permissionLabels(array_values(array_diff($before, $after))),
        ];
    }

    /**
     * @param  list<string>  $permissionNames
     * @return list<string>
     */
    private function permissionLabels(array $permissionNames): array
    {
        $labels = collect(PermissionCatalog::groups())
            ->flatMap(fn (array $group) => $group['permissions'])
            ->all();

        return collect($permissionNames)
            ->sort()
            ->map(fn (string $name) => $labels[$name] ?? $name)
            ->values()
            ->all();
    }
}
