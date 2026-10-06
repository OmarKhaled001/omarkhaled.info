<?php

namespace App\View\Composers;

use Illuminate\View\View;

class FooterComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'footerServices' => [],
            'socialLinks' => [],
        ]);
    }
}
