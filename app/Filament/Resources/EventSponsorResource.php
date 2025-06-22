<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventSponsorResource\Pages;
use App\Models\EventSponsor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventSponsorResource extends Resource
{
    protected static ?string $model = EventSponsor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Event';

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
                Forms\Components\Select::make('sponsor_type_id')
                    ->relationship('sponsorType', 'name')
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
                Tables\Columns\TextColumn::make('sponsorType.name')
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
                SelectFilter::make('sponsorType')
                    ->relationship('sponsorType', 'name')
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
            'index' => Pages\ListEventSponsors::route('/'),
            'create' => Pages\CreateEventSponsor::route('/create'),
            'edit' => Pages\EditEventSponsor::route('/{record}/edit'),
        ];
    }
}
