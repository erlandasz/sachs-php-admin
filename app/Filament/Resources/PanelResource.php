<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PanelResource\Pages;
use App\Models\Panel;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PanelResource extends Resource
{
    protected static ?string $model = Panel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('room')
                    ->maxLength(255),
                Select::make('type')
                    ->required()
                    ->options([
                        'general' => 'General',
                        'panel' => 'Panel',
                        'keynote' => 'Keynote',
                        'interview' => 'Interview',
                        'presentation' => 'Presentation',
                        'discussion' => 'Discussion',
                        'panel_introduction' => 'Panel Introduction',
                        'session' => 'Session',
                        'fireside-chat' => 'Fireside Chat',
                    ])->live(),
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'presentation'),
                Forms\Components\TimePicker::make('starts_at')
                    ->required(),
                Forms\Components\TimePicker::make('ends_at')
                    ->required(),
                Select::make('event_id')
                    ->relationship('event', 'name')
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $event = \App\Models\Event::find($state);
                        if ($event) {
                            $set('day', null); // Reset day if event changes
                        }
                    }),
                DatePicker::make('day')
                    ->required()
                    ->disabled(fn (Forms\Get $get) => ! $get('event_id'))
                    ->minDate(function (Forms\Get $get) {
                        $event = \App\Models\Event::find($get('event_id'));

                        return $event?->starts_at;
                    })
                    ->maxDate(function (Forms\Get $get) {
                        $event = \App\Models\Event::find($get('event_id'));

                        return $event?->ends_at;
                    }),
                Forms\Components\TextInput::make('recording')
                    ->maxLength(255),
                Forms\Components\Select::make('track')
                    ->options([
                        'track_a' => 'Track A',
                        'track_b' => 'Track B',
                        'track_c' => 'Track C',
                        'track_d' => 'Track D',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('room')
                    ->searchable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->time(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->time(),
                Tables\Columns\TextColumn::make('company.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('day')
                    ->date(),
                Tables\Columns\TextColumn::make('track'),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'general' => 'General',
                        'panel' => 'Panel',
                        'keynote' => 'Keynote',
                        'interview' => 'Interview',
                        'presentation' => 'Presentation',
                        'discussion' => 'Discussion',
                        'panel_introduction' => 'Panel Introduction',
                        'session' => 'Session',
                        'fireside-chat' => 'Fireside Chat',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPanels::route('/'),
            'create' => Pages\CreatePanel::route('/create'),
            'edit' => Pages\EditPanel::route('/{record}/edit'),
        ];
    }
}
