<?php

namespace App\Support\Assets;

use App\Models\Assets\FixedAsset;
use App\Services\Assets\Asset360Service;

class Asset360Presenter
{
    public function __construct(
        protected Asset360Service $workspace,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(FixedAsset $asset, ?string $tab = null): array
    {
        $data = $this->workspace->build($asset, $tab);

        return [
            'asset' => $data['asset'],
            'header' => $data['header'],
            'health' => $data['health'],
            'active_tab' => $data['active_tab'],
            'tabs' => $data['tabs'],
            'tab_data' => $data['tab_data'],
            'show_url' => route('admin.assets.360.show', ['asset' => $asset, 'tab' => $data['active_tab']]),
        ];
    }
}
