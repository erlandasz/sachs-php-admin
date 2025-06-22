<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortalUserResource\Pages;
use App\Models\PortalUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PortalUserResource extends Resource
{
    protected static ?string $model = PortalUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Portal Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->options([
                        1 => 'User',
                        2 => 'Admin',
                    ])->hidden()
                    ->required(),
                Forms\Components\DateTimePicker::make('last_login')->disabled(),
                Forms\Components\DateTimePicker::make('last_activity')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'User',
                        2 => 'Admin',
                        default => 'Unknown',
                    })->hidden(),
                Tables\Columns\TextColumn::make('last_login')
                    ->dateTime()->disabled(),
                Tables\Columns\TextColumn::make('last_activity')
                    ->dateTime()->disabled(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        1 => 'Administrator',
                        2 => 'Editor',
                        3 => 'User',
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
            \App\Filament\Resources\PortalUserResource\RelationManagers\RolesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortalUsers::route('/'),
            'create' => Pages\CreatePortalUser::route('/create'),
            'edit' => Pages\EditPortalUser::route('/{record}/edit'),
        ];
    }
}
