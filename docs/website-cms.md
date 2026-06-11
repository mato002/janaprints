# Website Content CMS — Operations Guide

## Overview

Administration → **Website Content** manages the public Jana Prints storefront without code changes:

- **Gallery** — portfolio projects (`website_gallery_items`)
- **Media Library** — fixed image slots (`website_media_items`)
- **Footer & Contact Settings** — text, contact, map, WhatsApp, social (`website_settings`)
- **SEO / Global Settings** — site name, default meta (`website_settings`)

Config files (`config/site.php`, `config/conversion.php`, `config/public-images.php`) remain as permanent fallbacks.

## Storage

| Asset type | Disk | Folder | Notes |
|------------|------|--------|-------|
| Media slot uploads | `public` | `storage/app/public/website-media/` | Served via `/storage/website-media/...` |
| Gallery uploads | `public` | `storage/app/public/website-gallery/` | Served via `/storage/website-gallery/...` |

### Deployment requirement

```bash
php artisan storage:link
```

Run after deploy if the `public/storage` symlink is missing.

### Accepted file types

- JPEG / JPG
- PNG
- WebP
- Max size: **5 MB** per image

## Fallback behaviour

### Media slots

1. Active uploaded `image_path`
2. DB `fallback_path` (seeded from config/static paths)
3. `config/public-images.php` / `config/website_content.php`
4. `public-images.default`

Inactive slots skip uploads and use fallbacks. Removing an upload reverts to config fallback without deleting static files in `public/images/`.

### Settings

1. Active DB `value`
2. DB `fallback_value` (seeded from config)
3. `config/site.php` or `config/conversion.php`

Use **Reset to fallback** in admin to clear a customized value.

## Cache

- Media paths/alts: cached 1 hour; cleared on media update, toggle, or image reset
- Settings: cached 1 hour; cleared on save or per-setting reset
- Gallery: not cached (changes are immediate)

## Baseline seeding

Populate registry rows without overwriting custom values:

```bash
php artisan website:content-baseline
```

Idempotent. Safe to re-run after deploy.

## Permissions

| Permission | Purpose |
|------------|---------|
| `website.gallery.view` | View gallery admin |
| `website.gallery.create` | Add gallery items |
| `website.gallery.edit` | Edit gallery items / featured flag |
| `website.gallery.delete` | Delete gallery items |
| `website.gallery.publish` | Publish / unpublish gallery items |
| `website.media.view` | View media library |
| `website.media.edit` | Upload, edit alt text, toggle slots |
| `website.settings.view` | View footer/SEO settings |
| `website.settings.edit` | Save settings |

Users without permission do not see workspace cards and receive **403** on direct URL access.

## Branding

Logo and favicon remain under **Branding** settings — not duplicated in Website Content.
