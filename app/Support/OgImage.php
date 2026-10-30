<?php

namespace App\Support;

use GdImage;
use Illuminate\Support\Facades\File;

/**
 * Share images (1200×630) in the house style, drawn with GD and Inter.
 *
 * An image is only drawn while rendering a page, for that page's own title,
 * and stored under storage/app/og/{hash}.png; /og/{hash}.png only serves
 * files that exist. Without GD (or when drawing fails) every page uses the
 * fixed image public/images/og-default.png.
 */
class OgImage
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    /** Bump when the design changes, so every image is drawn again. */
    private const DESIGN = 1;

    public static function available(): bool
    {
        return config('serverscope.og_images', true) && function_exists('imagettftext') && function_exists('imagepng');
    }

    public static function directory(): string
    {
        return storage_path('app/og');
    }

    public static function url(string $title, ?string $eyebrow, string $locale): string
    {
        $fallback = url('/images/og-default.png');

        if (! self::available()) {
            return $fallback;
        }

        $hash = substr(hash('sha256', implode('|', [self::DESIGN, $locale, $eyebrow, $title, parse_url(url('/'), PHP_URL_HOST)])), 0, 32);
        $path = self::directory()."/{$hash}.png";

        if (! is_file($path)) {
            $written = rescue(function () use ($title, $eyebrow, $path) {
                File::ensureDirectoryExists(self::directory());

                return file_put_contents($path, self::draw($title, $eyebrow), LOCK_EX) !== false;
            }, false, report: false);

            if (! $written) {
                return $fallback;
            }
        }

        return url("/og/{$hash}.png");
    }

    /**
     * @return string PNG data
     */
    public static function draw(string $title, ?string $eyebrow = null, ?string $host = null): string
    {
        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        $color = fn (string $hex, int $alpha = 0) => imagecolorallocatealpha($image, hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2)), $alpha);
        $bold = resource_path('fonts/og/Inter-SemiBold.ttf');
        $regular = resource_path('fonts/og/Inter-Regular.ttf');

        imagefill($image, 0, 0, $color('#0a0a0b'));

        // The same faint grid as on the site.
        $grid = $color('#ffffff', 122);
        for ($x = 0; $x <= self::WIDTH; $x += 48) {
            imageline($image, $x, 0, $x, self::HEIGHT, $grid);
        }
        for ($y = 0; $y <= self::HEIGHT; $y += 48) {
            imageline($image, 0, $y, self::WIDTH, $y, $grid);
        }

        imagefilledrectangle($image, 0, 0, self::WIDTH, 6, $color('#7aa2f7'));

        self::mark($image, 80, 78, 44, $color('#7aa2f7'));
        imagettftext($image, 30, 0, 140, 113, $color('#fafafa'), $bold, 'ServerScope');

        $y = 250;
        if (filled($eyebrow)) {
            imagettftext($image, 24, 0, 80, $y, $color('#7aa2f7'), $bold, mb_strtoupper($eyebrow));
            $y += 30;
        }

        // Largest size at which the title fits in three lines.
        foreach ([64, 56, 48, 42] as $size) {
            $lines = self::wrap($title, $bold, $size, self::WIDTH - 160);
            if (count($lines) <= 3) {
                break;
            }
        }
        $lines = array_slice($lines, 0, 3);

        foreach ($lines as $line) {
            $y += (int) round($size * 1.3);
            imagettftext($image, $size, 0, 78, $y, $color('#fafafa'), $bold, $line);
        }

        imagettftext($image, 24, 0, 80, self::HEIGHT - 64, $color('#a1a1aa'), $regular, $host ?? parse_url(url('/'), PHP_URL_HOST));

        ob_start();
        imagepng($image, null, 9);

        return (string) ob_get_clean();
    }

    /**
     * Square logo: the pulse mark on the accent colour.
     *
     * @return string PNG data
     */
    public static function logo(int $size = 512): string
    {
        // Drawn four times larger and scaled down, for smooth lines.
        $big = $size * 4;
        $image = imagecreatetruecolor($big, $big);
        imagealphablending($image, false);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagealphablending($image, true);
        self::roundedRectangle($image, 0, 0, $big - 1, $big - 1, (int) ($big * 0.22), imagecolorallocate($image, 0x2F, 0x5F, 0xD0));
        self::mark($image, (int) ($big * 0.16), (int) ($big * 0.16), (int) ($big * 0.68), imagecolorallocate($image, 255, 255, 255));

        $out = imagecreatetruecolor($size, $size);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagecopyresampled($out, $image, 0, 0, 0, 0, $size, $size, $big, $big);

        ob_start();
        imagepng($out, null, 9);

        return (string) ob_get_clean();
    }

    /**
     * The pulse icon of the site (M3 12h4l3-8 4 16 3-8h4 in a 24 box).
     */
    private static function mark(GdImage $image, int $x, int $y, int $size, int $color): void
    {
        $points = [[3, 12], [7, 12], [10, 4], [14, 20], [17, 12], [21, 12]];
        $scale = $size / 24;
        $thickness = max(2, (int) round(2 * $scale));

        for ($i = 0; $i < count($points) - 1; $i++) {
            [$x1, $y1] = [$x + $points[$i][0] * $scale, $y + $points[$i][1] * $scale];
            [$x2, $y2] = [$x + $points[$i + 1][0] * $scale, $y + $points[$i + 1][1] * $scale];
            self::thickLine($image, $x1, $y1, $x2, $y2, $thickness, $color);
        }
    }

    private static function thickLine(GdImage $image, float $x1, float $y1, float $x2, float $y2, int $thickness, int $color): void
    {
        $angle = atan2($y2 - $y1, $x2 - $x1);
        $dx = sin($angle) * $thickness / 2;
        $dy = cos($angle) * $thickness / 2;

        imagefilledpolygon($image, [
            (int) round($x1 - $dx), (int) round($y1 + $dy), (int) round($x2 - $dx), (int) round($y2 + $dy),
            (int) round($x2 + $dx), (int) round($y2 - $dy), (int) round($x1 + $dx), (int) round($y1 - $dy),
        ], $color);
        // Round joins and caps.
        imagefilledellipse($image, (int) round($x1), (int) round($y1), $thickness, $thickness, $color);
        imagefilledellipse($image, (int) round($x2), (int) round($y2), $thickness, $thickness, $color);
    }

    private static function roundedRectangle(GdImage $image, int $x1, int $y1, int $x2, int $y2, int $r, int $color): void
    {
        imagefilledrectangle($image, $x1 + $r, $y1, $x2 - $r, $y2, $color);
        imagefilledrectangle($image, $x1, $y1 + $r, $x2, $y2 - $r, $color);
        foreach ([[$x1 + $r, $y1 + $r], [$x2 - $r, $y1 + $r], [$x1 + $r, $y2 - $r], [$x2 - $r, $y2 - $r]] as [$cx, $cy]) {
            imagefilledellipse($image, $cx, $cy, $r * 2, $r * 2, $color);
        }
    }

    /**
     * @return list<string>
     */
    private static function wrap(string $text, string $font, int $size, int $width): array
    {
        $lines = [];
        $line = '';

        foreach (preg_split('/\s+/u', trim($text)) as $word) {
            $try = $line === '' ? $word : "{$line} {$word}";
            $box = imagettfbbox($size, 0, $font, $try);

            if ($line !== '' && ($box[2] - $box[0]) > $width) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $try;
            }
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }
}
