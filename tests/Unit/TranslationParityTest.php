<?php

use Illuminate\Support\Arr;

it('has the same translation keys in English and Arabic', function () {
    $base = dirname(__DIR__, 2).'/lang';

    foreach (glob("{$base}/en/*.php") as $file) {
        $name = basename($file);
        $ar = "{$base}/ar/{$name}";
        expect(file_exists($ar))->toBeTrue("lang/ar/{$name} is missing");

        $enKeys = array_keys(Arr::dot(require $file));
        $arKeys = array_keys(Arr::dot(require $ar));

        expect(array_values(array_diff($enKeys, $arKeys)))->toBe([], "keys missing in ar/{$name}")
            ->and(array_values(array_diff($arKeys, $enKeys)))->toBe([], "extra keys in ar/{$name}");
    }
});
