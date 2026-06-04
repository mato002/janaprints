<?php

$files = [
    __DIR__ . '/../config/facility.php',
    __DIR__ . '/../config/conversion.php',
    __DIR__ . '/../config/testimonials.php',
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $content = preg_replace("/'\\\$i\\['([^']+)'\\]'/", "\$i['\$1']", $content);
    file_put_contents($file, $content);
    echo "Fixed: {$file}\n";
}
