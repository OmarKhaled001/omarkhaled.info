<?php

use App\Support\PlaceholderDetector;

it('flags seed placeholders and empty values', function (mixed $value) {
    expect(PlaceholderDetector::isPlaceholder($value))->toBeTrue();
})->with([
    'example email' => 'contact@example.com',
    'upwork placeholder' => 'https://www.upwork.com/freelancers/placeholder',
    'fake whatsapp' => '+10000000000',
    'marker' => 'Lorem [placeholder]',
    'empty' => '',
    'null' => null,
    'translatable with one placeholder locale' => [['en' => 'Real', 'ar' => '[placeholder]']],
]);

it('accepts real values', function (mixed $value) {
    expect(PlaceholderDetector::isPlaceholder($value))->toBeFalse();
})->with([
    'email' => 'omar@omarkhaled.info',
    'linkedin' => 'https://www.linkedin.com/in/omar-khaled-890b14396',
    'translatable' => [['en' => 'Egypt', 'ar' => 'مصر']],
    'number' => 24,
]);
