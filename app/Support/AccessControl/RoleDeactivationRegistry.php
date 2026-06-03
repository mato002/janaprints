<?php

namespace App\Support\AccessControl;

class RoleDeactivationRegistry
{
    protected function path(): string
    {
        return storage_path('app/governance/deactivated_roles.json');
    }

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        if (! is_file($this->path())) {
            return [];
        }

        $data = json_decode((string) file_get_contents($this->path()), true);

        if (! is_array($data)) {
            return [];
        }

        return array_values(array_map('intval', $data));
    }

    public function isDeactivated(int $roleId): bool
    {
        return in_array($roleId, $this->ids(), true);
    }

    public function deactivate(int $roleId): void
    {
        $ids = $this->ids();

        if (! in_array($roleId, $ids, true)) {
            $ids[] = $roleId;
        }

        $this->write($ids);
    }

    public function reactivate(int $roleId): void
    {
        $this->write(array_values(array_filter(
            $this->ids(),
            fn (int $id) => $id !== $roleId,
        )));
    }

    /**
     * @param  list<int>  $ids
     */
    protected function write(array $ids): void
    {
        $directory = dirname($this->path());

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($this->path(), json_encode(array_values(array_unique($ids))));
    }
}
