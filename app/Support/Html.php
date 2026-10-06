<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/** Allowlist sanitizer for admin-authored rich text rendered on public pages. */
final class Html
{
    private static ?HtmlSanitizer $sanitizer = null;

    public static function clean(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        self::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowElement('p')
                ->allowElement('br')
                ->allowElement('strong')
                ->allowElement('b')
                ->allowElement('em')
                ->allowElement('i')
                ->allowElement('code')
                ->allowElement('blockquote')
                ->allowElement('ul')
                ->allowElement('ol')
                ->allowElement('li')
                ->allowElement('h2')
                ->allowElement('h3')
                ->allowElement('a', ['href', 'title'])
                ->allowLinkSchemes(['https', 'http', 'mailto'])
                ->forceAttribute('a', 'rel', 'noopener')
                ->withMaxInputLength(100_000)
        );

        return trim(self::$sanitizer->sanitize($html));
    }

    /** Plain text for meta tags, llms.txt and JSON-LD. */
    public static function text(?string $html): string
    {
        $text = html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '</li>'], ["\n\n", "\n", "\n"], (string) $html)), ENT_QUOTES | ENT_HTML5);

        return trim(preg_replace("/\n{3,}/", "\n\n", preg_replace('/[ \t]+/', ' ', $text)) ?? '');
    }
}
