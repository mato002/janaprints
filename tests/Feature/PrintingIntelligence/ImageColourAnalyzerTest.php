<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Services\PrintingIntelligence\ImageColourAnalyzer;
use Tests\TestCase;

class ImageColourAnalyzerTest extends TestCase
{
    protected function createPng(int $r, int $g, int $b, int $width = 100, int $height = 100): string
    {
        $image = imagecreatetruecolor($width, $height);
        $colour = imagecolorallocate($image, $r, $g, $b);
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $colour);
        $path = tempnam(sys_get_temp_dir(), 'pi2img');
        imagepng($image, $path);
        imagedestroy($image);

        return $path;
    }

    protected function createTransparentPng(): string
    {
        $image = imagecreatetruecolor(50, 50);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);
        $path = tempnam(sys_get_temp_dir(), 'pi2alpha');
        imagepng($image, $path);
        imagedestroy($image);

        return $path;
    }

    public function test_detects_mostly_white_image_as_low_coverage(): void
    {
        $path = $this->createPng(255, 255, 255);
        $result = app(ImageColourAnalyzer::class)->analyze($path, 300);

        $this->assertContains($result['status'], ['completed', 'manual_review']);
        $this->assertLessThan(5, $result['aggregate']['rgb_coverage_percent']);
        $this->assertSame('low', $result['aggregate']['coverage_class']);
        unlink($path);
    }

    public function test_detects_dark_full_image_as_high_coverage(): void
    {
        $path = $this->createPng(0, 0, 0);
        $result = app(ImageColourAnalyzer::class)->analyze($path, 300);

        $this->assertGreaterThan(90, $result['aggregate']['rgb_coverage_percent']);
        $this->assertContains($result['aggregate']['coverage_class'], ['high', 'full']);
        unlink($path);
    }

    public function test_detects_transparent_png_area(): void
    {
        $path = $this->createTransparentPng();
        $result = app(ImageColourAnalyzer::class)->analyze($path, 300);

        $this->assertGreaterThan(0, $result['aggregate']['transparent_area_percent']);
        unlink($path);
    }

    public function test_returns_dominant_colours(): void
    {
        $path = $this->createPng(200, 0, 0);
        $result = app(ImageColourAnalyzer::class)->analyze($path, 300);

        $this->assertNotEmpty($result['aggregate']['dominant_colours']);
        unlink($path);
    }

    public function test_produces_cmyk_approximation(): void
    {
        $analyzer = app(ImageColourAnalyzer::class);
        $cmyk = $analyzer->rgbToCmyk(255, 0, 0);

        $this->assertGreaterThan(0, $cmyk['m']);
        $this->assertGreaterThan(0, $cmyk['y']);
    }

    public function test_channel_area_composition_totals_one_hundred(): void
    {
        $path = $this->createPng(200, 0, 0);
        $result = app(ImageColourAnalyzer::class)->analyze($path, 300);

        $composition = $result['aggregate']['channel_area_composition'];

        $this->assertSame(100.0, $composition['total']);
        $this->assertEqualsWithDelta(
            100,
            $composition['cyan']
            + $composition['magenta']
            + $composition['yellow']
            + $composition['black']
            + $composition['white']
            + $composition['transparent'],
            0.05,
        );

        unlink($path);
    }

    public function test_detects_all_significant_colours_in_multi_colour_image(): void
    {
        $image = imagecreatetruecolor(120, 40);
        $red = imagecolorallocate($image, 220, 0, 0);
        $green = imagecolorallocate($image, 0, 180, 0);
        $blue = imagecolorallocate($image, 0, 0, 220);
        imagefilledrectangle($image, 0, 0, 39, 39, $red);
        imagefilledrectangle($image, 40, 0, 79, 39, $green);
        imagefilledrectangle($image, 80, 0, 119, 39, $blue);
        $path = tempnam(sys_get_temp_dir(), 'pi2multi');
        imagepng($image, $path);
        imagedestroy($image);

        $result = app(ImageColourAnalyzer::class)->analyze($path, 300);
        $colours = $result['aggregate']['dominant_colours'];

        $this->assertGreaterThanOrEqual(3, count($colours));
        $this->assertEqualsWithDelta(100, collect($colours)->sum('percent'), 1.0);

        unlink($path);
    }
}
