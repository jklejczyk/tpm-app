<?php

namespace App\Support;

use App\Models\User;

final class UserDirectory
{
    /** @var array<string, string|null> */
    private array $names = [];

    /**
     * @param  list<string>  $ids
     */
    public function preload(array $ids): void
    {
        $missing = array_values(array_diff(array_unique($ids), array_keys($this->names)));

        if ($missing === []) {
            return;
        }

        $numericIds = array_values(array_filter($missing, 'ctype_digit'));

        $found = $numericIds === []
            ? collect()
            : User::query()
                ->whereIn('id', $numericIds)
                ->get()
                ->mapWithKeys(fn (User $user): array => [(string) $user->id => $user->name]);

        foreach ($missing as $id) {
            $this->names[$id] = $found->get($id);
        }
    }

    public function name(string $id): ?string
    {
        if (! array_key_exists($id, $this->names)) {
            $this->preload([$id]);
        }

        return $this->names[$id];
    }
}
