<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'seo' => null,
    'title' => null,
    'metaDescription' => null,
    'canonical' => null,
    'robots' => null,
    'ogImage' => null,
    'ogType' => null,
    'structuredData' => [],
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'seo' => null,
    'title' => null,
    'metaDescription' => null,
    'canonical' => null,
    'robots' => null,
    'ogImage' => null,
    'ogType' => null,
    'structuredData' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $site = $websiteSite ?? config('site');
    $seoDefaults = $websiteSeo ?? ($site['seo'] ?? config('site.seo', []));

    if ($seo instanceof \App\Support\Storefront\SeoMeta) {
        $pageTitle = $seo->title;
        $pageDescription = $seo->description;
        $pageUrl = $seo->canonical;
        $pageRobots = $seo->robots;
        $resolvedOgImage = $seo->ogImageUrl();
        $resolvedOgType = $seo->ogType;
        $twitterTitle = $seo->twitterTitle();
        $twitterDescription = $seo->twitterDescription();
        $twitterImage = $seo->twitterImageUrl();
        $jsonLdBlocks = $seo->structuredData ?: $structuredData;
    } else {
        $pageTitle = $title ?? $seoDefaults['title'];
        $pageDescription = $metaDescription ?? $seoDefaults['description'];
        $pageUrl = $canonical ?? url()->current();
        $pageRobots = $robots ?? 'index, follow';
        $settingsOgPath = $seoDefaults['og_image'] ?? null;
        $defaultOgPath = $settingsOgPath
            ? (str_starts_with((string) $settingsOgPath, 'http') ? $settingsOgPath : url($settingsOgPath))
            : url(app(\App\Services\Website\WebsiteMediaResolver::class)->resolvePath('seo.og_image'));
        $resolvedOgImage = $ogImage
            ? (str_starts_with((string) $ogImage, 'http') ? $ogImage : url($ogImage))
            : $defaultOgPath;
        $resolvedOgType = $ogType ?? 'website';
        $twitterTitle = $pageTitle;
        $twitterDescription = $pageDescription;
        $twitterImage = $resolvedOgImage;
        $jsonLdBlocks = $structuredData;
    }

    $analytics = $site['analytics'];
?>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?php echo e($pageDescription); ?>">
<meta name="keywords" content="<?php echo e($seoDefaults['keywords']); ?>">
<meta name="author" content="<?php echo e($site['name']); ?>">
<meta name="robots" content="<?php echo e($pageRobots); ?>">
<link rel="canonical" href="<?php echo e($pageUrl); ?>">

<title><?php echo e($pageTitle); ?></title>

<?php if($analytics['google_search_console_verification']): ?>
    <meta name="google-site-verification" content="<?php echo e($analytics['google_search_console_verification']); ?>">
<?php endif; ?>

<meta property="og:type" content="<?php echo e($resolvedOgType); ?>">
<meta property="og:site_name" content="<?php echo e($site['name']); ?>">
<meta property="og:title" content="<?php echo e($pageTitle); ?>">
<meta property="og:description" content="<?php echo e($pageDescription); ?>">
<meta property="og:url" content="<?php echo e($pageUrl); ?>">
<meta property="og:image" content="<?php echo e($resolvedOgImage); ?>">
<meta property="og:locale" content="en_KE">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo e($twitterTitle); ?>">
<meta name="twitter:description" content="<?php echo e($twitterDescription); ?>">
<meta name="twitter:image" content="<?php echo e($twitterImage); ?>">
<?php if($seoDefaults['twitter_site']): ?>
    <meta name="twitter:site" content="<?php echo e($seoDefaults['twitter_site']); ?>">
<?php endif; ?>

<link rel="icon" href="<?php echo e($brandingFaviconUrl ?? url($site['local']['favicon'] ?? $site['local']['logo'])); ?>" type="image/png">
<link rel="apple-touch-icon" href="<?php echo e($brandingFaviconUrl ?? url($site['local']['favicon'] ?? $site['local']['logo'])); ?>">

<?php $__currentLoopData = $jsonLdBlocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schema): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <script type="application/ld+json"><?php echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if($analytics['google_analytics_id']): ?>
    
    <meta name="ga-measurement-id" content="<?php echo e($analytics['google_analytics_id']); ?>">
<?php endif; ?>

<?php if($analytics['facebook_pixel_id']): ?>
    <meta name="facebook-pixel-id" content="<?php echo e($analytics['facebook_pixel_id']); ?>">
<?php endif; ?>

<?php if($analytics['tiktok_pixel_id']): ?>
    <meta name="tiktok-pixel-id" content="<?php echo e($analytics['tiktok_pixel_id']); ?>">
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\seo-meta.blade.php ENDPATH**/ ?>