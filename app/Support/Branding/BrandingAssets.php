<?php

namespace App\Support\Branding;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandingAssets
{
    public const DISK = 'public';

    /**
     * @param  list<string>  $allowedMimes
     */
    public function storeCompanyLogo(Company $company, UploadedFile $file, array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif']): string
    {
        $this->delete($company->logo);

        return $this->store($file, "branding/companies/{$company->id}/logo", $allowedMimes);
    }

    /**
     * @param  list<string>  $allowedMimes
     */
    public function storeCompanyFavicon(Company $company, UploadedFile $file, array $allowedMimes = ['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml']): string
    {
        $this->delete($company->favicon_path);

        return $this->store($file, "branding/companies/{$company->id}/favicon", $allowedMimes);
    }

    /**
     * @param  list<string>  $allowedMimes
     */
    public function storeUserAvatar(User $user, UploadedFile $file, array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp']): string
    {
        $this->delete($user->avatar_path);

        return $this->store($file, "branding/users/{$user->id}/avatar", $allowedMimes);
    }

    public function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        // Always serve through Laravel so branding works even when public/storage
        // is not reachable from the web server document root (common on cPanel).
        return url('/branding/'.ltrim($path, '/'));
    }

    /**
     * @return array{brandingLogoUrl: string, brandingSidebarLogoUrl: string, brandingFaviconUrl: string}
     */
    public function presentation(): array
    {
        if ($this->presentationCache !== null) {
            return $this->presentationCache;
        }

        $company = $this->resolveCompany();
        $siteLogoUrl = url(config('site.local.logo'));
        $siteSidebarLogoUrl = url(config('site.local.sidebar_logo', config('site.local.logo')));
        $siteFaviconUrl = url(config('site.local.favicon', config('site.local.logo')));

        return $this->presentationCache = [
            'brandingLogoUrl' => $this->url($company?->logo) ?? $siteLogoUrl,
            'brandingSidebarLogoUrl' => $this->url($company?->logo) ?? $siteSidebarLogoUrl,
            'brandingFaviconUrl' => $this->url($company?->favicon_path) ?? $siteFaviconUrl,
        ];
    }

    public function logoUrl(): string
    {
        return $this->presentation()['brandingLogoUrl'];
    }

    public function faviconUrl(): string
    {
        return $this->presentation()['brandingFaviconUrl'];
    }

    public function resolveCompany(): ?Company
    {
        if ($this->resolvedCompany !== null) {
            return $this->resolvedCompany;
        }

        if (app()->bound(\App\Support\TenantContext::class)) {
            $company = tenant()->company;
            if ($company) {
                return $this->resolvedCompany = $company;
            }
        }

        $user = auth()->user();
        if ($user?->company) {
            return $this->resolvedCompany = $user->company;
        }

        return $this->resolvedCompany = $this->defaultCompany();
    }

    protected function defaultCompany(): ?Company
    {
        static $company = null;
        static $loaded = false;

        if ($loaded) {
            return $company;
        }

        $loaded = true;
        $code = config('site.branding_company_code', 'JANA');

        $company = Company::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first()
            ?? Company::query()->where('is_active', true)->orderBy('name')->first();

        return $company;
    }

    protected ?Company $resolvedCompany = null;

    /** @var array{brandingLogoUrl: string, brandingSidebarLogoUrl: string, brandingFaviconUrl: string}|null */
    protected ?array $presentationCache = null;

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /**
     * @param  list<string>  $allowedMimes
     */
    protected function store(UploadedFile $file, string $directory, array $allowedMimes): string
    {
        if (! in_array($file->getMimeType(), $allowedMimes, true)) {
            throw new \InvalidArgumentException(__('Unsupported file type.'));
        }

        $extension = $file->guessExtension() ?: $file->extension() ?: 'bin';
        $filename = Str::uuid().'.'.strtolower($extension);

        return $file->storeAs($directory, $filename, self::DISK);
    }
}
