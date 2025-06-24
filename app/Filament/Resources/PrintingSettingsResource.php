<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrintingSettingsResource\Pages;
use App\Models\PrintingSettings;
use App\Orientation;
use App\PageSize;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrintingSettingsResource extends Resource
{
    protected static ?string $model = PrintingSettings::class;

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Select::make('page_size')
                    ->options(PageSize::class)
                    ->required()->default('A6'),
                Forms\Components\Select::make('orientation')
                    ->options(Orientation::class)
                    ->required(),
                Forms\Components\Textarea::make('page_dimensions')
                    ->columnSpanFull()->hidden(),
                Forms\Components\TextInput::make('font_family')
                    ->required()->default('Times')->disabled(),
                Forms\Components\TextInput::make('font_weight')
                    ->required()->default('B')->disabled(),
                Forms\Components\TextInput::make('base_font_size')
                    ->required()
                    ->numeric()
                    ->default(32)->helperText('Be careful when changing'),
                Forms\Components\TextInput::make('available_width_multiplier')
                    ->required()
                    ->numeric()
                    ->default(0.8)->helperText('Be careful when changing'),
                Forms\Components\Textarea::make('default_colors')
                    ->columnSpanFull()->hidden(),
                Forms\Components\TextInput::make('row_padding')
                    ->required()
                    ->numeric()
                    ->default(8)->helperText('Be careful when changing'),
                Forms\Components\TextInput::make('y_offset')
                    ->required()
                    ->numeric()
                    ->default(45)->helperText('Smaller number = Higher on print, 45 was used in EBOIF, 35 & 40 were also used'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('page_size')
                    ->searchable(),
                Tables\Columns\TextColumn::make('orientation')
                    ->searchable(),
                Tables\Columns\TextColumn::make('font_family')
                    ->searchable(),
                Tables\Columns\TextColumn::make('font_weight')
                    ->searchable(),
                Tables\Columns\TextColumn::make('base_font_size')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('available_width_multiplier')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('row_padding')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('y_offset')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListPrintingSettings::route('/'),
            'create' => Pages\CreatePrintingSettings::route('/create'),
            'edit' => Pages\EditPrintingSettings::route('/{record}/edit'),
        ];
    }
}
