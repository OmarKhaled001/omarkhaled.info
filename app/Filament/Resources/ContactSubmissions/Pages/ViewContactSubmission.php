<?php

namespace App\Filament\Resources\ContactSubmissions\Pages;

use App\Enums\SubmissionStatus;
use App\Filament\Resources\ContactSubmissions\ContactSubmissionResource;
use App\Models\ContactSubmission;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContactSubmission extends ViewRecord
{
    protected static string $resource = ContactSubmissionResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var ContactSubmission $submission */
        $submission = $this->getRecord();

        if ($submission->status === SubmissionStatus::New) {
            $submission->update(['status' => SubmissionStatus::Read]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')->icon('heroicon-o-envelope')->color('primary')
                ->url(fn (ContactSubmission $record) => 'mailto:'.rawurlencode($record->email).'?subject='.rawurlencode('Re: your project inquiry')),
            Action::make('spam')->label('Mark as spam')->color('danger')
                ->action(fn (ContactSubmission $record) => $record->update(['status' => SubmissionStatus::Spam])),
            DeleteAction::make(),
        ];
    }
}
