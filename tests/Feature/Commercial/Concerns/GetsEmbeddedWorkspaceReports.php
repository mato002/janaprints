<?php

namespace Tests\Feature\Commercial\Concerns;

use App\Models\User;
use Illuminate\Testing\TestResponse;

trait GetsEmbeddedWorkspaceReports
{
    /**
     * @param  array<string, mixed>  $query
     */
    protected function getEmbeddedReport(User $user, string $routeName, array $query = []): TestResponse
    {
        return $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route($routeName, array_merge(['embedded' => '1'], $query)));
    }
}
