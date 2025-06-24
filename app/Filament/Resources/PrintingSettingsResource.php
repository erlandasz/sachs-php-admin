<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrintingSettingsResource\Pages;
use App\Models\PrintingSettings;
use App\Orientation;
use App\PageSize;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
                Forms\Components\Toggle::make('is_default')
                    ->label('Is default'),
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
                Forms\Components\Toggle::make('has_colors')->live(),
                Fieldset::make('Color Settings')
                    ->visible(fn (Get $get): bool => $get('has_colors'))
                    ->schema([
                        ColorPicker::make('all_days_color')
                            ->label('All Days Color')
                            ->rgb()
                            ->default(function (?PrintingSettings $record) {
                                $value = $record ? sprintf('rgb(%d, %d, %d)', $record->all_days_r, $record->all_days_g, $record->all_days_b) : null;
                                \Log::info('all_days_color default value:', ['value' => $value, 'record_id' => $record?->id]);

                                return $value;
                            }),
                        ColorPicker::make('days_1_2_color')
                            ->label('Days 1 and 2 Color')
                            ->rgb()
                            ->default(fn (?PrintingSettings $record) => $record ? sprintf('rgb(%d, %d, %d)', $record->days_1_and_2_r, $record->days_1_and_2_g, $record->days_1_and_2_b) : null),
                        ColorPicker::make('days_2_3_color')
                            ->label('Days 2 and 3 Color')
                            ->rgb()
                            ->default(fn (?PrintingSettings $record) => $record ? sprintf('rgb(%d, %d, %d)', $record->days_2_and_3_r, $record->days_2_and_3_g, $record->days_2_and_3_b) : null),
                        ColorPicker::make('day_1_color')
                            ->label('Day 1 Color')
                            ->rgb()
                            ->default(fn (?PrintingSettings $record) => $record ? sprintf('rgb(%d, %d, %d)', $record->day_1_r, $record->day_1_g, $record->day_1_b) : null),
                        ColorPicker::make('day_2_color')
                            ->label('Day 2 Color')
                            ->rgb()
                            ->default(fn (?PrintingSettings $record) => $record ? sprintf('rgb(%d, %d, %d)', $record->day_2_r, $record->day_2_g, $record->day_2_b) : null),
                        ColorPicker::make('day_3_color')
                            ->label('Day 3 Color')
                            ->rgb()
                            ->default(fn (?PrintingSettings $record) => $record ? sprintf('rgb(%d, %d, %d)', $record->day_3_r, $record->day_3_g, $record->day_3_b) : null),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_default')->boolean(),
                Tables\Columns\TextColumn::make('page_size')
                    ->searchable(),
                Tables\Columns\TextColumn::make('orientation')
                    ->searchable(),
                Tables\Columns\IconColumn::make('has_colors')->boolean(),
                Tables\Columns\TextColumn::make('y_offset')
                    ->numeric()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
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

    protected function parseRgbFields(array $data): array
    {
        $rgbFields = [
            'all_days_color' => ['all_days_r', 'all_days_g', 'all_days_b'],
            'days_1_2_color' => ['days_1_and_2_r', 'days_1_and_2_g', 'days_1_and_2_b'],
            'days_2_3_color' => ['days_2_and_3_r', 'days_2_and_3_g', 'days_2_and_3_b'],
            'day_1_color' => ['day_1_r', 'day_1_g', 'day_1_b'],
            'day_2_color' => ['day_2_r', 'day_2_g', 'day_2_b'],
            'day_3_color' => ['day_3_r', 'day_3_g', 'day_3_b'],
        ];

        foreach ($rgbFields as $colorField => $rgbFields) {
            if (isset($data[$colorField])) {
                preg_match('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $data[$colorField], $matches);
                if (count($matches) === 4) {
                    $data[$rgbFields[0]] = $matches[1];
                    $data[$rgbFields[1]] = $matches[2];
                    $data[$rgbFields[2]] = $matches[3];
                }
                unset($data[$colorField]);
            }
        }

        return $data;
    }
}
