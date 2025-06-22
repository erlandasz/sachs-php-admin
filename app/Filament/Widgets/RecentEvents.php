<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentEvents extends TableWidget
{
    protected int|string|array $columnSpan = 'full'; // Optional: set width

    protected static ?int $sort = 2; // Optional: set order

    protected function getTableQuery(): Builder
    {
        return Event::query()->orderBy('created_at', 'desc')->limit(5)->withCount(['panels as panels_count'])
            ->with(['panels' => function ($query) {
                $query->withCount('person');
            }]);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Event Title'),
            TextColumn::make('starts_at')
                ->label('Start Date')
                ->date(),
            TextColumn::make('ends_at')
                ->label('End Date')
                ->date(),
            TextColumn::make('presenters_count')
                ->label('Total Presenters')
                ->counts('presenters'),
            TextColumn::make('sponsors_count')
                ->label('Total Sponsors')
                ->counts('sponsors'),
            TextColumn::make('total_speakers')
                ->label('Total Speakers')
                ->getStateUsing(function (Event $record): int {
                    return $record->panels->sum('person_count');
                }),
        ];
    }
}
