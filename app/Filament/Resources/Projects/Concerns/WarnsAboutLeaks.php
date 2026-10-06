<?php

namespace App\Filament\Resources\Projects\Concerns;

use App\Models\Project;
use App\Support\Anonymity\AnonymityGuard;
use Filament\Notifications\Notification;

/** After saving, warn if a client identifier would be public while the project is anonymized. */
trait WarnsAboutLeaks
{
    protected function afterSave(): void
    {
        $this->warnAboutLeaks();
    }

    protected function afterCreate(): void
    {
        $this->warnAboutLeaks();
    }

    private function warnAboutLeaks(): void
    {
        /** @var Project $project */
        $project = $this->getRecord()->refresh();
        $leaks = app(AnonymityGuard::class)->leaksInFields($project);

        if ($leaks === []) {
            return;
        }

        Notification::make()
            ->title('Possible client identity leak')
            ->body('These terms appear in public fields while the project is anonymized: '.implode(', ', $leaks).'. Reword them, or reveal the client if they approved it.')
            ->warning()
            ->persistent()
            ->send();
    }
}
