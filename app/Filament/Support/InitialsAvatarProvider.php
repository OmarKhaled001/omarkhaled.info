<?php

namespace App\Filament\Support;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

/** Local SVG initials avatar: Filament's default calls ui-avatars.com, leaking the admin's name to a third party. */
class InitialsAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        $name = Filament::getNameForDefaultAvatar($record);
        $initials = collect(preg_split('/\s+/u', trim($name)) ?: [])->take(2)->map(fn (string $p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" fill="#E8542A"/>'
            .'<text x="32" y="41" font-family="system-ui,sans-serif" font-size="26" font-weight="600" text-anchor="middle" fill="#131316">'
            .e($initials).'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
