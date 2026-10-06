<?php

it('redirects the root to Arabic for Arabic browsers', function () {
    $this->get('/', ['Accept-Language' => 'ar-EG,ar;q=0.9,en;q=0.5'])
        ->assertRedirect('/ar')
        ->assertHeader('Vary', 'Accept-Language');
});

it('redirects the root to English for unsupported or missing languages', function (array $headers) {
    $this->get('/', $headers)->assertRedirect('/en');
})->with([
    'french' => [['Accept-Language' => 'fr-FR,fr;q=0.9']],
    'missing' => [[]],
    'english' => [['Accept-Language' => 'en-GB']],
]);

it('serves each locale with the right lang and dir attributes', function (string $locale, string $dir) {
    $this->get("/{$locale}")
        ->assertOk()
        ->assertSee("<html lang=\"{$locale}\" dir=\"{$dir}\">", false);
})->with([['en', 'ltr'], ['ar', 'rtl']]);

it('returns 404 for unsupported locale prefixes', function () {
    $this->get('/fr')->assertNotFound();
    $this->get('/de/about')->assertNotFound();
});

it('links the language switcher to the same page in the other locale', function () {
    $this->get('/en')->assertSee('href="'.url('/ar').'"', false);
    $this->get('/ar')->assertSee('href="'.url('/en').'"', false);
});

it('sets no cookies on public pages', function () {
    $response = $this->get('/en');

    expect($response->headers->getCookies())->toBeEmpty();
});

it('emits hreflang alternates and a self canonical', function () {
    $this->get('/ar')
        ->assertSee('<link rel="canonical" href="'.url('/ar').'">', false)
        ->assertSee('hreflang="en" href="'.url('/en').'"', false)
        ->assertSee('hreflang="ar" href="'.url('/ar').'"', false)
        ->assertSee('hreflang="x-default" href="'.url('/en').'"', false);
});
