<?php

namespace App\Filament\Resources\PersonResource\RelationManagers;

use App\Models\Panel;
use App\SpeakerType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PanelsRelationManager extends RelationManager
{
    protected static string $relationship = 'panels';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required(),
            ]);
    }

    public static function recordTitle($record): string
    {
        return $record->name;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn ($state): ?string => SpeakerType::tryFrom($state)?->getLabel())
                    ->color(fn ($state): ?string => SpeakerType::tryFrom($state)?->getColor()),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label('Panel')
                            ->options(function (): array {
                                // Eager load the event relationship to avoid N+1 queries
                                $panels = Panel::with('event')
                                    // Optionally filter by event_id if needed
                                    // ->where('event_id', $someEventId)
                                    ->get();

                                // Build the options array
                                return $panels->mapWithKeys(function (Panel $panel) {
                                    $label = "({$panel->event?->slug}) {$panel->name}";

                                    return [$panel->id => $label];
                                })->toArray();
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('role')
                            ->label('Speaker Type')
                            ->options(SpeakerType::class)
                            ->required(),
                    ]),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
