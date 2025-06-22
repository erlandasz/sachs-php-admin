<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventPresenterResource\Pages;
use App\Models\EventPresenter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventPresenterResource extends Resource
{
    protected static ?string $model = EventPresenter::class;

    protected static ?string $navigationGroup = 'Event';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required()->searchable(),
                Forms\Components\Select::make('presenter_type_id')
                    ->relationship('presenterType', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.short_name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('presenterType.name')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->relationship('event', 'short_name')
                    ->searchable(),
                SelectFilter::make('company')
                    ->relationship('company', 'name')
                    ->searchable(),
                SelectFilter::make('presenterType')
                    ->relationship('presenterType', 'name')
                    ->searchable(),
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
            'index' => Pages\ListEventPresenters::route('/'),
            'create' => Pages\CreateEventPresenter::route('/create'),
            'edit' => Pages\EditEventPresenter::route('/{record}/edit'),
        ];
    }
}
