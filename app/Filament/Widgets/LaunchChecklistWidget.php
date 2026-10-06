<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Settings\ManageContact;
use App\Filament\Pages\Settings\ManageIdentity;
use App\Support\Launch\LaunchChecklist;
use Filament\Widgets\Widget;

class LaunchChecklistWidget extends Widget
{
    protected string $view = 'filament.widgets.launch-checklist';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -10;

    // Rendered with the page so the checklist is visible immediately (and testable).
    protected static bool $isLazy = false;

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $checklist = app(LaunchChecklist::class);

        return [
            'placeholders' => collect($checklist->placeholders())->map(fn (array $item) => $item + [
                'url' => $item['page'] === 'contact' ? ManageContact::getUrl() : ManageIdentity::getUrl(),
            ])->all(),
            'advisories' => $checklist->advisories(),
        ];
    }
}
