<?php

namespace App\Filament\Resources\CheckInResource\Pages;

use App\Filament\Imports\CheckInImporter;
use App\Filament\Resources\CheckInResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Rawilk\Printing\Facades\Printing as PPPP;

class ListCheckIns extends ListRecords
{
    protected static string $resource = CheckInResource::class;

    public ?int $selectedPrinterId = null;

    public ?string $selectedPrinterName = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ImportAction::make()
                ->importer(CheckInImporter::class),
            Action::make('deleteCheckinsAbove520')
                ->label('Delete CheckIns')
                ->action(function () {
                    \App\Models\CheckIn::where('id', '>', 520)->delete();
                })
                ->requiresConfirmation()
                ->color('danger')
                ->icon('heroicon-o-trash'),
            Action::make('selectPrinter')
                ->label(function () {
                    return $this->selectedPrinterName ?? 'Select Printer';
                })
                ->modal()
                ->form([
                    Select::make('printer_id')
                        ->label('Printer')
                        ->options(
                            fn (): Collection => collect($this->getPrinters())
                                ->mapWithKeys(fn ($printer) => [$printer['id'] => $printer['name']])
                        ),
                ])
                ->action(function (array $data, $livewire) { // <-- Add $livewire as parameter
                    $selectedPrinter = collect($this->getPrinters())
                        ->firstWhere('id', $data['printer_id']);

                    // Update the properties using $livewire
                    $livewire->selectedPrinterId = $data['printer_id'];
                    $livewire->selectedPrinterName = $selectedPrinter['name'] ?? 'Unknown';
                }),
        ];
    }

    /**
     * Get the list of printers.
     *
     * @return array<int, object{
     *     id: int,
     *     name: string,
     *     description: string,
     *     online: bool,
     *     status: string
     * }>
     */
    public function getPrinters(): array
    {
        $printers = PPPP::printers();

        return $printers->map(function ($printer) {
            return [
                'id' => $printer->id(),
                'name' => $printer->name(),
            ];
        });
    }
}
