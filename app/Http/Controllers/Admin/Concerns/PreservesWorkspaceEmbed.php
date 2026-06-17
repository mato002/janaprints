<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\Navigation\WorkspaceEmbed;
use Illuminate\Http\Request;

trait PreservesWorkspaceEmbed
{
    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function workspaceEmbedParams(array $params = [], ?Request $request = null): array
    {
        return WorkspaceEmbed::queryParams($params, $request);
    }
}
