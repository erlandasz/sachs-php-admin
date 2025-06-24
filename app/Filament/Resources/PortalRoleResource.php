<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortalRoleResource\Pages;
use App\Models\PortalRole;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PortalRoleResource extends Resource
{
    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->hasRole('super_admin');
    }

    protected static ?string $model = PortalRole::class;

    protected static ?string $navigationGroup = 'Utils';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
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
            'index' => Pages\ListPortalRoles::route('/'),
            'create' => Pages\CreatePortalRole::route('/create'),
            'edit' => Pages\EditPortalRole::route('/{record}/edit'),
        ];
    }
}
