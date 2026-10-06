<?php

namespace App\Filament\Resources\ContactSubmissions\Tables;

use App\Enums\SubmissionStatus;
use App\Models\ContactSubmission;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        $options = fn (string $key, string $group) => collect(config()->array("portfolio.contact.{$key}"))
            ->mapWithKeys(fn (string $value) => [$value => __("contact.{$group}.{$value}")])->all();

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium')
                    ->description(fn (ContactSubmission $record) => $record->company),
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('project_type')->label('Type')->badge()->color('gray')
                    ->formatStateUsing(fn (string $state) => __("contact.project_types.{$state}")),
                TextColumn::make('budget_range')->label('Budget')
                    ->formatStateUsing(fn (string $state) => __("contact.budgets.{$state}")),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (SubmissionStatus $state) => $state->label())
                    ->color(fn (SubmissionStatus $state) => $state->color()),
                TextColumn::make('locale')->badge()->color('gray'),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(collect(SubmissionStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])),
                SelectFilter::make('project_type')->options($options('project_types', 'project_types')),
                SelectFilter::make('budget_range')->options($options('budget_ranges', 'budgets')),
                Filter::make('created_at')->schema([
                    DatePicker::make('from'),
                    DatePicker::make('until'),
                ])->query(fn (Builder $query, array $data) => $query
                    ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                    ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    Action::make('reply')->icon('heroicon-o-envelope')
                        ->url(fn (ContactSubmission $record) => 'mailto:'.rawurlencode($record->email).'?subject='.rawurlencode('Re: your project inquiry'))
                        ->after(fn (ContactSubmission $record) => $record->update(['status' => SubmissionStatus::Replied])),
                    ...collect([SubmissionStatus::Read, SubmissionStatus::Replied, SubmissionStatus::Spam])->map(
                        fn (SubmissionStatus $status) => Action::make("mark_{$status->value}")
                            ->label("Mark as {$status->value}")
                            ->color($status->color())
                            ->hidden(fn (ContactSubmission $record) => $record->status === $status)
                            ->action(fn (ContactSubmission $record) => $record->update(['status' => $status]))
                    )->all(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('export_csv')->label('Export CSV')->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (Collection $records): StreamedResponse => self::csv($records)),
                    BulkAction::make('mark_spam')->label('Mark as spam')->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['status' => SubmissionStatus::Spam])),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** @param Collection<int, ContactSubmission> $records */
    private static function csv(Collection $records): StreamedResponse
    {
        return response()->streamDownload(function () use ($records): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Name', 'Email', 'Company', 'Type', 'Budget', 'Status', 'Locale', 'Message']);
            foreach ($records as $r) {
                // Prefix formula-like cells so spreadsheets don't execute them (CSV injection).
                $cells = [$r->created_at?->toDateTimeString(), $r->name, $r->email, $r->company, $r->project_type, $r->budget_range, $r->status->value, $r->locale, $r->message];
                fputcsv($out, array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'".$v : $v, $cells));
            }
            fclose($out);
        }, 'inquiries-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
