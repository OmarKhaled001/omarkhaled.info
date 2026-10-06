<?php

it('uses only logical (RTL-safe) spacing and alignment utilities in views', function () {
    $physical = '/(?<![\w-])-?(ml|mr|pl|pr|left|right|rounded-l|rounded-r|border-l|border-r|text-left|text-right|float-left|float-right)-[\w\[\]\/.]+|(?<![\w-])(text-left|text-right)(?![\w-])/';
    $offenders = [];

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__, 2).'/resources/views'));
    foreach ($files as $file) {
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }
        preg_match_all('/class="([^"]*)"/', file_get_contents($file->getPathname()), $classes);
        foreach ($classes[1] as $classList) {
            if (preg_match($physical, $classList, $m)) {
                $offenders[] = str_replace(dirname(__DIR__, 2).'/', '', $file->getPathname()).": {$m[0]}";
            }
        }
    }

    expect($offenders)->toBe([]);
});
