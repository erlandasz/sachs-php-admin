<?php

namespace App\Filament\Resources\PanelResource\RelationManagers;

use App\Models\Person;
use App\SpeakerType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PersonRelationManager extends RelationManager
{
    protected static string $relationship = 'person';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('pivot.role')
                    ->label('Speaker Type')
                    ->options(SpeakerType::class)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                Tables\Columns\TextColumn::make('full_name'),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn ($state): ?string => SpeakerType::tryFrom($state)?->getLabel())
                    ->color(fn ($state): ?string => SpeakerType::tryFrom($state)?->getColor()),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label('Person')
                            ->searchable()
                            ->required()
                            ->options(function () {
                                return Person::query()->pluck('full_name', 'id');
                            }),
                        Forms\Components\Select::make('role')
                            ->label('Speaker Type')
                            ->options(SpeakerType::class)
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
