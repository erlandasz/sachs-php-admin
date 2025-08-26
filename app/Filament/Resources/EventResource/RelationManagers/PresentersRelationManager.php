<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Models\Company;
use App\Models\PresenterType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PresentersRelationManager extends RelationManager
{
    protected static string $relationship = 'presenters';

    public function form(Form $form): Form
    {
        // This is not used for attach in default BelongsToMany relation manager
        return $form
            ->schema([
                // Not used for attach in this context
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Company'),
                Tables\Columns\TextColumn::make('presenter_type_name')
                    ->label('Presenter Type')
                    ->getStateUsing(function (Company $record) {
                        $presenterTypeId = $record->pivot->presenter_type_id;
                        $presenterType = \App\Models\PresenterType::find($presenterTypeId);

                        return $presenterType ? $presenterType->name : 'N/A';
                    }),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label('Company')
                            ->searchable()
                            ->required()
                            ->options(function () {
                                return Company::query()->pluck('name', 'id');
                            }),
                        Forms\Components\Select::make('role')
                            ->label('Presenter Type')
                            ->options(PresenterType::query()->pluck('name', 'id'))
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
