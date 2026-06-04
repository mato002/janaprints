<?php

$map = [
    'photo-1626785774573-4b799315345d' => 'artwork',
    'photo-1586281380117-5a9146de8a2a' => 'prepress',
    'photo-1618005182384-a83a8bd57fbe' => 'print_press',
    'photo-1586281380349-632531db7ed4' => 'finishing',
    'photo-1454165804606-c3d57bc86b40' => 'quality',
    'photo-1607083206968-13611e3d76db' => 'packaging',
    'photo-1566576912321-d58ddd7a6088' => 'delivery',
    'photo-1561214115-f2f148c1e85d' => 'banner',
    'photo-1600880292203-757bb62b4baf' => 'team',
    'photo-1560250097-0b93528c311a' => 'portrait',
    'photo-1573496359142-b8d87734a5a2' => 'portrait',
    'photo-1556761175-b413da4baf72' => 'office',
    'photo-1544716278-ca5e3f4abd8c' => 'brochure',
    'photo-1449824913935-59a10b8d2000' => 'vehicle',
    'photo-1580489944761-15a19d654956' => 'portrait',
    'photo-1523050854058-8df90110c9f1' => 'school',
    'photo-1556745757-8d76bdb6834' => 'corporate',
    'photo-1586953208448-b95a79798f07' => 'stationery',
    'photo-1558618666-fcd25c85cd64' => 'merchandise',
];

$files = [
    __DIR__ . '/../config/facility.php',
    __DIR__ . '/../config/conversion.php',
    __DIR__ . '/../config/testimonials.php',
];

foreach ($files as $file) {
    if (! file_exists($file)) {
        continue;
    }

    $content = file_get_contents($file);

    if (! str_contains($content, '$i = require')) {
        $content = preg_replace(
            '/^<\?php\s*/',
            "<?php\n\n\$i = require __DIR__ . '/public-images.php';\n\n",
            $content,
            1,
        );
    }

    foreach ($map as $id => $key) {
        $content = preg_replace(
            '#https://images\.unsplash\.com/' . preg_quote($id, '#') . '[^\'"]+#',
            "\$i['{$key}']",
            $content,
        );
    }

    file_put_contents($file, $content);
    echo "Updated: {$file}\n";
}
