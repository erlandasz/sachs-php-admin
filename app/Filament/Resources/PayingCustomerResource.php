<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayingCustomerResource\Pages;
use App\Models\PayingCustomer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

// <-- Add this import

class PayingCustomerResource extends Resource
{
    protected static ?string $model = PayingCustomer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'On-Site Tools';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('role_id')
                    ->label('Role')
                    ->searchable()
                    ->relationship('role', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_email')
                    ->searchable(),
                TextColumn::make('role.name')
                    ->label('Role'),
            ])
            ->filters([
            ])
            ->defaultSort('id', 'desc')
            ->actions([
            ])
            ->bulkActions([
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
            'index' => Pages\ListPayingCustomers::route('/'),
            'create' => Pages\CreatePayingCustomer::route('/create'),
            'edit' => Pages\EditPayingCustomer::route('/{record}/edit'),
        ];
    }
}
