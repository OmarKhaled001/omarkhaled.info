<?php

namespace App\Filament\Resources\ContactSubmissions\Schemas;

use App\Enums\SubmissionStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactSubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make()->schema([
                Grid::make(['default' => 1, 'md' => 3])->schema([
                    TextEntry::make('name'),
                    TextEntry::make('email')->copyable(),
                    TextEntry::make('company')->placeholder('—'),
                    TextEntry::make('project_type')->formatStateUsing(fn (string $state) => __("contact.project_types.{$state}")),
                    TextEntry::make('budget_range')->formatStateUsing(fn (string $state) => __("contact.budgets.{$state}")),
                    TextEntry::make('status')->badge()
                        ->formatStateUsing(fn (SubmissionStatus $state) => $state->label())
                        ->color(fn (SubmissionStatus $state) => $state->color()),
                    TextEntry::make('locale')->badge(),
                    TextEntry::make('created_at')->dateTime(),
                    TextEntry::make('spam_reason')->placeholder('—'),
                ]),
            ]),
            Section::make('Message')->schema([
                TextEntry::make('message')->hiddenLabel()->prose()->extraAttributes(['style' => 'white-space: pre-wrap']),
            ]),
        ]);
    }
}
