<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\View;
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
                    ->maxLength(255),
                Forms\Components\TextInput::make('website')
                    ->maxLength(255),
                Forms\Components\TextInput::make('founded')
                    ->numeric(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                FileUpload::make('logo_name')
                    ->label('Company Logo')
                    ->disk('local')
                    ->directory('company-logos')
                    ->visibility('private')
                    ->image()
                    ->preserveFilenames()
                    ->storeFileNamesUsing(fn ($file) => 'logo_'.time().'.'.$file->getClientOriginalExtension())
                    ->imagePreview(function ($record) {
                        return $record->cloudinary_url;
                    }),
                Forms\Components\TextInput::make('type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('ticker')
                    ->maxLength(255),
                Forms\Components\Textarea::make('profile')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('short_profile')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('financial_summary')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('address')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('sector')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('airtableId')
                    ->maxLength(64),
                Forms\Components\TextInput::make('city')
                    ->maxLength(255),
                Forms\Components\TextInput::make('country')
                    ->maxLength(255),
                Forms\Components\TextInput::make('zip')
                    ->maxLength(255),
                Forms\Components\TextInput::make('street')
                    ->maxLength(255),
                Forms\Components\TextInput::make('state')
                    ->maxLength(255),
                Forms\Components\Textarea::make('airtableLogo')
                    ->columnSpanFull()->hidden(),
                Forms\Components\TextInput::make('nif_airtable_id')
                    ->maxLength(255)->hidden(),
                Forms\Components\Textarea::make('short_v2')
                    ->columnSpanFull()->hidden(),
                Forms\Components\Toggle::make('needs_checking'),
                View::make('preview-image')
                    ->label('Current Logo')
                    ->visible(fn ($get) => filled($get('cloudinary_url'))),
                // FileUpload::make('cloudinary_url')
                //     ->label('Company Logo')
                //     ->disk('r2')
                //     ->image()
                //     ->directory('company-logos')
                //     ->visibility('public'),
                Checkbox::make('remove_logo')
                    ->label('Remove current logo')
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('cloudinary_url', null);
                        }
                    }),
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
