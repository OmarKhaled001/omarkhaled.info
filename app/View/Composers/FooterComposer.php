<?php

namespace App\View\Composers;

use App\Support\Profile;
use Illuminate\View\View;

class FooterComposer
{
    public function __construct(private readonly Profile $profile) {}

    public function compose(View $view): void
    {
        $view->with([
            'footerServices' => [],
            'socialLinks' => $this->profile->socialLinks(),
        ]);
    }
}
