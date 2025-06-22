<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Models\Company;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SponsorsRelationManager extends RelationManager
{
    protected static string $relationship = 'sponsors';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Company'),
                Tables\Columns\TextColumn::make('sponsor_type_name')
                    ->label('Sponsor Type')
                    ->getStateUsing(function (Company $record) {
                        $presenterTypeId = $record->pivot->sponsor_type_id;
                        $presenterType = \App\Models\PresenterType::find($presenterTypeId);

                        return $presenterType ? $presenterType->name : 'N/A';
                    }),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
