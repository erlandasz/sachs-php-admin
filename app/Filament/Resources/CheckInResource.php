<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckInResource\Pages;
use App\Models\CheckIn;
use App\Services\PrintingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CheckInResource extends Resource
{
    protected static ?string $model = CheckIn::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'On-Site Tools';

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
                Forms\Components\TextInput::make('company_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('checking_in_user_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('checked_in')
                    ->required()
                    ->numeric()->hidden(),
                Forms\Components\DatePicker::make('checked_in_at'),
                Forms\Components\TextInput::make('events_attended')
                    ->required()
                    ->default('1')
                    ->maxLength(255)->helperText('E.g. 1,2,3 if attending 3 days of event, leave 1 if not relevant'),
                Forms\Components\TextInput::make('checkin_comment')
                    ->maxLength(255)
                    ->default(null),
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
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable(),
                IconColumn::make('checked_in')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('checkin_comment')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('checked_in')
                    ->label('Check-in Status')
                    ->options([
                        '1' => 'Checked In',
                        '0' => 'Not Checked In',
                    ]),
                Filter::make('has_comment')
                    ->label('Has Comment')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('comment')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('logCheckInAndPrinter')
                    ->label('Print')
                    ->icon('heroicon-o-bell')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->checkin_comment ? 'Comment exists' : ($record->checked_in ? 'Already checked in' : 'Confirm Action'))
                    ->modalDescription(fn ($record) => $record->checkin_comment ? 'This check-in has a comment:'.' '.$record->checkin_comment : ($record->checked_in ? 'This check-in is already checked in.' : 'Do you want to proceed?'))
                    ->action(function (array $arguments, $record, $livewire) {
                        $checkInId = $record->id;
                        $printerId = $livewire->selectedPrinterId ?? 'No printer selected';

                        if ($printerId === 'No printer selected') {
                            Notification::make()
                                ->title('No printer selected')
                                ->danger()
                                ->body('Please select a printer before printing.')
                                ->send();

                            return;
                        }

                        $printingService = app(PrintingService::class);
                        $printingService->checkIn($checkInId, $printerId);
                    }),
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
            'index' => Pages\ListCheckIns::route('/'),
            'create' => Pages\CreateCheckIn::route('/create'),
            'edit' => Pages\EditCheckIn::route('/{record}/edit'),
        ];
    }
}
