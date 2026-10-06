<?php

namespace App\Support\Anonymity;

use App\Models\Project;
use App\Presenters\PublicProject;

/**
 * Detects client identifiers in public output for projects that are not (fully) revealed.
 * Used by feature tests on rendered pages and by the admin on save.
 */
final class AnonymityGuard
{
    /**
     * Strings that must not appear publicly for this project, given its toggles.
     *
     * @return list<string>
     */
    public function forbiddenTerms(Project $project): array
    {
        $terms = [];

        if (! $project->show_client_name) {
            foreach (['en', 'ar'] as $locale) {
                $terms[] = (string) $project->getTranslation('client_name', $locale, false);
            }

            $terms = array_merge($terms, $project->client_aliases ?? []);
        }

        if (! $project->show_live_link && $project->live_url) {
            $host = (string) parse_url($project->live_url, PHP_URL_HOST);
            $terms[] = preg_replace('/^www\./', '', $host) ?? $host;
        }

        if (! $project->show_repo_link && $project->repo_url) {
            $terms[] = trim((string) parse_url($project->repo_url, PHP_URL_PATH), '/');
        }

        return array_values(array_unique(array_filter(
            array_map(fn (string $t) => trim($t), $terms),
            fn (string $t) => mb_strlen($t) >= 3,
        )));
    }

    /**
     * Forbidden terms found in the given content.
     *
     * @return list<string>
     */
    public function leaks(Project $project, string $content): array
    {
        $haystack = self::normalize($content);

        return array_values(array_filter(
            $this->forbiddenTerms($project),
            fn (string $term) => str_contains($haystack, self::normalize($term)),
        ));
    }

    /**
     * Leaks in the project's own public text fields (title, sections, features, facts, meta).
     *
     * @return list<string>
     */
    public function leaksInFields(Project $project): array
    {
        return $this->leaks($project, implode("\n", (new PublicProject($project))->publicText()));
    }

    /** Case-insensitive, Arabic letter-variant-insensitive comparison form. */
    public static function normalize(string $text): string
    {
        $text = mb_strtolower(html_entity_decode($text, ENT_QUOTES | ENT_HTML5));
        // Strip Arabic diacritics and tatweel, unify alef / yaa / taa-marbuta variants.
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $text) ?? $text;

        return strtr($text, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ى' => 'ي', 'ة' => 'ه']);
    }
}
