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

        return Storage::disk(self::DISK)->url($path);
    }

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
