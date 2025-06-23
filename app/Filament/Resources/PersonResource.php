<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonResource\Pages;
use App\Models\Person;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('photo')
                            ->image()
                            ->previewable(true)
                            ->disk('local') // Store files locally
                            ->directory('temp/speakers') // Use a temp directory
                            ->default('noPic.png'),
                        View::make('preview-image')
                            ->view('filament.forms.components.preview-image'),
                        Forms\Components\TextInput::make('photo_small')->maxLength(255)->hidden(),
                        Forms\Components\TextInput::make('photo_v2')->maxLength(255)->hidden(),
                        Forms\Components\Textarea::make('bio')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Social Media')
                    ->schema([
                        Forms\Components\TextInput::make('linkedin')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('twitter')
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Professional Information')
                    ->schema([
                        Forms\Components\TextInput::make('job_title')
                            ->required()
                            ->maxLength(255),

                    ])->columns(2),

                Forms\Components\Section::make('System Information')
                    ->schema([
                        Forms\Components\TextInput::make('airtableId')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('needs_checking'),
                        Forms\Components\Toggle::make('show_title'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_small')
                    ->label('Photo')
                    ->getStateUsing(fn ($record) => $record->photo_small ?: ($record->photo ?: 'noPic.png'))
                    ->default('noPic.png'),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('companyName'),
                Tables\Columns\TextColumn::make('job_title')
                    ->searchable(),
                Tables\Columns\IconColumn::make('needs_checking')
                    ->boolean(),
                Tables\Columns\IconColumn::make('show_title')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([

                Tables\Filters\TernaryFilter::make('needs_checking'),
                Tables\Filters\TernaryFilter::make('show_title'),
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
            \App\Filament\Resources\PersonResource\RelationManagers\PanelsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeople::route('/'),
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
