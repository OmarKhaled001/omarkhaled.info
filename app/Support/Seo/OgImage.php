<?php

namespace App\Support\Seo;

use App\Support\Design\AccentPalette;
use App\Support\Design\Theme;
use ArPHP\I18N\Arabic;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * 1200×630 Open Graph cards rendered with GD (no headless browser, works on any host).
 * Arabic is shaped with ar-php and laid out in visual order here (see visualRtl). Files are content-addressed: the name is a hash of everything
 * drawn, so cards are generated once, cached forever, and change whenever the text changes.
 */
final class OgImage
{
    private const int W = 1200;

    private const int H = 630;

    private const int VERSION = 4;

    private ?Arabic $arabic = null;

    public function url(string $title, string $eyebrow, string $locale): ?string
    {
        $footer = __('site.name', [], $locale).' · '.__('site.role', [], $locale);
        $accent = Theme::accentHex();
        $path = 'og/'.substr(hash('sha256', implode('|', [self::VERSION, $title, $eyebrow, $footer, $locale, $accent])), 0, 32).'.png';
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            try {
                $disk->put($path, $this->render($title, $eyebrow, $footer, $locale, $accent));
            } catch (Throwable $e) {
                report($e);

                return null;
            }
        }

        return $disk->url($path);
    }

    public function render(string $title, string $eyebrow, string $footer, string $locale, string $accentHex): string
    {
        $rtl = $locale === 'ar';
        $im = imagecreatetruecolor(self::W, self::H);
        imageantialias($im, true);

        $bg = $this->color($im, '#0A0A0B');
        $grid = $this->color($im, '#18181B');
        $mark = $this->color($im, '#3A3A41');
        $text = $this->color($im, '#EDEDEF');
        $muted = $this->color($im, '#A1A1AA');
        $accent = $this->color($im, AccentPalette::from($accentHex)->darkText);

        imagefilledrectangle($im, 0, 0, self::W, self::H, $bg);
        for ($x = 40; $x < self::W; $x += 40) {
            imageline($im, $x, 0, $x, self::H, $grid);
        }
        for ($y = 30; $y < self::H; $y += 40) {
            imageline($im, 0, $y, self::W, $y, $grid);
        }

        // Crop marks in the corners: the print-shop signature of the brand.
        imagesetthickness($im, 2);
        foreach ([[48, 48, 1, 1], [self::W - 48, 48, -1, 1], [48, self::H - 48, 1, -1], [self::W - 48, self::H - 48, -1, -1]] as [$cx, $cy, $dx, $dy]) {
            imageline($im, $cx, $cy, $cx + 30 * $dx, $cy, $mark);
            imageline($im, $cx, $cy, $cx, $cy + 30 * $dy, $mark);
        }
        // Registration mark.
        $rx = $rtl ? 110 : self::W - 110;
        imageellipse($im, $rx, self::H - 110, 34, 34, $accent);
        imageline($im, $rx, self::H - 134, $rx, self::H - 86, $accent);
        imageline($im, $rx - 24, self::H - 110, $rx + 24, self::H - 110, $accent);

        $sans = resource_path($rtl ? 'fonts/og/Cairo-SemiBold.ttf' : 'fonts/og/Geist-SemiBold.ttf');
        $mono = resource_path('fonts/og/GeistMono-Regular.ttf');
        $pad = 96;

        // Eyebrow (mono, accent).
        $this->line($im, $rtl ? $sans : $mono, 22, $accent, $rtl ? $this->visualRtl($this->words($eyebrow)) : mb_strtoupper($eyebrow), $pad, 150, $rtl);
        imagefilledrectangle($im, $rtl ? self::W - $pad - 64 : $pad, 176, $rtl ? self::W - $pad : $pad + 64, 179, $accent);

        // Title: wrap to at most 4 lines, shrinking for long titles.
        $size = mb_strlen($title) > 70 ? 46 : (mb_strlen($title) > 45 ? 54 : 62);
        $lines = $rtl ? $this->rtlLines($title, $sans, $size, self::W - 2 * $pad) : $this->wrap($title, $sans, $size, self::W - 2 * $pad);
        $y = 250 + $size;
        foreach (array_slice($lines, 0, 4) as $line) {
            $this->line($im, $sans, $size, $text, $line, $pad, $y, $rtl);
            $y += (int) round($size * ($rtl ? 1.45 : 1.18));
        }

        // Footer.
        $footerFont = $rtl ? $sans : $mono;
        $this->line($im, $footerFont, 22, $muted, $rtl ? $this->visualRtl($this->words($footer)) : $footer, $pad, self::H - 100, $rtl);

        ob_start();
        imagepng($im, null, 7);
        $png = (string) ob_get_clean();
        imagedestroy($im);

        return $png;
    }

    private function line(\GdImage $im, string $font, int $size, int $color, string $text, int $pad, int $y, bool $rtl): void
    {
        $x = $pad;
        if ($rtl) {
            $box = imagettfbbox($size, 0, $font, $text) ?: [0, 0, 0, 0, 0, 0, 0, 0];
            $x = self::W - $pad - abs($box[2] - $box[0]);
        }
        imagettftext($im, $size, 0, (int) $x, $y, $color, $font, $text);
    }

    /** @return list<string> */
    private function wrap(string $text, string $font, int $size, int $max): array
    {
        $lines = [];
        $current = '';
        foreach (preg_split('/\s+/u', trim($text)) ?: [] as $word) {
            $try = $current === '' ? $word : "{$current} {$word}";
            $box = imagettfbbox($size, 0, $font, $try) ?: [0, 0, 0, 0, 0, 0, 0, 0];
            if (abs($box[2] - $box[0]) > $max && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $try;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    /** @return list<string> */
    private function words(string $text): array
    {
        return array_values(array_filter(preg_split('/\s+/u', trim($text)) ?: [], fn (string $w) => $w !== ''));
    }

    /**
     * Wraps a right-to-left text by measured width, returning each line in visual order.
     *
     * @return list<string>
     */
    private function rtlLines(string $text, string $font, int $size, int $max): array
    {
        $lines = [];
        $current = [];

        foreach ($this->words($text) as $word) {
            $try = [...$current, $word];
            $box = imagettfbbox($size, 0, $font, $this->visualRtl($try)) ?: [0, 0, 0, 0, 0, 0, 0, 0];

            if ($current !== [] && abs($box[2] - $box[0]) > $max) {
                $lines[] = $this->visualRtl($current);
                $current = [$word];
            } else {
                $current = $try;
            }
        }

        if ($current !== []) {
            $lines[] = $this->visualRtl($current);
        }

        return $lines;
    }

    /**
     * GD draws left to right, so a right-to-left line is assembled in visual order by hand:
     * consecutive Latin words (Laravel Filament, 24) stay one left-to-right run, Arabic words are
     * shaped by ar-php, and the runs are laid out from the end of the line. Doing this per word
     * keeps the spaces where they belong — ar-php's own bidi moves them around Latin runs.
     *
     * @param  list<string>  $words  logical order
     */
    private function visualRtl(array $words): string
    {
        $runs = [];
        foreach ($words as $word) {
            $ltr = preg_match('/[A-Za-z0-9]/', $word) === 1 && preg_match('/\p{Arabic}/u', $word) === 0;
            $last = array_key_last($runs);

            if ($ltr && $last !== null && $runs[$last]['ltr']) {
                $runs[$last]['words'][] = $word;
            } else {
                $runs[] = ['ltr' => $ltr, 'words' => [$word]];
            }
        }

        $parts = [];
        foreach (array_reverse($runs) as $run) {
            $parts[] = $run['ltr'] ? implode(' ', $run['words']) : $this->visualWord($run['words'][0]);
        }

        return implode(' ', $parts);
    }

    /** One right-to-left word: its script segments reversed (e.g. "وFilament"), Arabic ones shaped. */
    private function visualWord(string $word): string
    {
        preg_match_all('/\p{Arabic}+|[^\p{Arabic}]+/u', $word, $segments);

        return implode('', array_map(
            fn (string $segment): string => preg_match('/\p{Arabic}/u', $segment) === 1
                ? trim($this->arabic()->utf8Glyphs($segment, 1000, false))
                : $segment,
            array_reverse($segments[0]),
        ));
    }

    private function arabic(): Arabic
    {
        return $this->arabic ??= new Arabic;
    }

    private function color(\GdImage $im, string $hex): int
    {
        [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%02x%02x%02x') ?: [0, 0, 0];

        return (int) imagecolorallocate($im, (int) $r, (int) $g, (int) $b);
    }
}
