<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone_no')
                    ->maxLength(255)->hidden(),
                Forms\Components\TextInput::make('website')
                    ->maxLength(255)->hidden(),
                Forms\Components\TextInput::make('founded')
                    ->numeric()->hidden(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)->hidden(),
                Forms\Components\TextInput::make('logo_name')
                    ->maxLength(255)->hidden(),
                Forms\Components\TextInput::make('type')
                    ->maxLength(255)->hidden(),
                Forms\Components\TextInput::make('ticker')
                    ->maxLength(255)->hidden(),
                Forms\Components\Textarea::make('profile')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('short_profile')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('financial_summary')
                    ->columnSpanFull()->hidden(),
                Forms\Components\Textarea::make('address')
                    ->columnSpanFull()->hidden(),
                Forms\Components\Textarea::make('sector')
                    ->columnSpanFull()->hidden(),
                Forms\Components\TextInput::make('airtableId')
                    ->maxLength(64),
                Forms\Components\TextInput::make('city')
                    ->maxLength(255)->hidden(),
                Forms\Components\TextInput::make('country')
                    ->maxLength(255)->hidden(),
                Forms\Components\TextInput::make('zip')
                    ->maxLength(255)->hidden(),
                Forms\Components\TextInput::make('street')
                    ->maxLength(255)->hidden(),
                Forms\Components\TextInput::make('state')
                    ->maxLength(255)->hidden(),
                Forms\Components\Textarea::make('airtableLogo')
                    ->columnSpanFull()->hidden(),
                Forms\Components\TextInput::make('nif_airtable_id')
                    ->maxLength(255)->hidden(),
                Forms\Components\Textarea::make('short_v2')
                    ->columnSpanFull()->hidden(),
                Forms\Components\Toggle::make('needs_checking'),
                FileUpload::make('cloudinary_url')
                    ->label('Company Logo')
                    ->disk('r2')
                    ->image()
                    ->directory('company-logos')
                    ->visibility('public'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('needs_checking')
                    ->boolean(),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
