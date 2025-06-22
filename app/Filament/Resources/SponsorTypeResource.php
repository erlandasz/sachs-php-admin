<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SponsorTypeResource\Pages;
use App\Models\SponsorType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SponsorTypeResource extends Resource
{
    protected static ?string $model = SponsorType::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Utils';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('header_singular')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('header_plural')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('display_order')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('header_singular')
                    ->searchable(),
                Tables\Columns\TextColumn::make('header_plural')
                    ->searchable(),
                Tables\Columns\TextColumn::make('display_order')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListSponsorTypes::route('/'),
            'create' => Pages\CreateSponsorType::route('/create'),
            'edit' => Pages\EditSponsorType::route('/{record}/edit'),
        ];
    }
}
