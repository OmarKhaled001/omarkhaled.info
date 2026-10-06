<?php

it('renders a branded 404 in the locale of the URL prefix', function (string $path, string $locale, string $title) {
    $this->get($path)
        ->assertNotFound()
        ->assertSee("<html lang=\"{$locale}\"", false)
        ->assertSee($title)
        ->assertSee('noindex', false);
})->with([
    ['/en/does-not-exist', 'en', 'This page didn’t make it to print.'],
    ['/ar/does-not-exist', 'ar', 'هذه الصفحة لم تصل إلى المطبعة.'],
]);

it('infers the 404 locale from Accept-Language when the path has no prefix', function () {
    $this->get('/totally/unknown', ['Accept-Language' => 'ar'])
        ->assertNotFound()
        ->assertSee('<html lang="ar"', false);
});
