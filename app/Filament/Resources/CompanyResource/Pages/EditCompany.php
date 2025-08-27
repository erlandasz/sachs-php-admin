<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Services\AirtableService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Webbingbrasil\FilamentCopyActions\Pages\Actions\CopyAction;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
            // Action::make('copyWebsite')
            //     ->label('Copy Website')
            //     ->action(function () {
            //         $website = $this->prepareWebsiteForCopy($this->record->website);
            //         $this->copyToClipboard($website);
            //     }),
            // Action::make('copyProfileOrShortProfile')
            //     ->label('Copy Profile')
            //     ->action(function () {
            //         $copyText = $this->record->short_profile ?? $this->record->profile ?? '';
            //         $this->copyToClipboard($copyText);
            //     }),
            // Action::make('copyLogoUrl')
            //     ->label('Copy Logo URL')
            //     ->action(function () {
            //         $logoUrl = $this->getLogoUrlForCopy();
            //         $this->copyToClipboard($logoUrl);
            //     }),
            CopyAction::make()->label('website')->copyable(fn () => $this->record->website),
            CopyAction::make()->label('profile')->copyable(fn () => $this->record->short_profile ?? $this->record->profile ?? ''),
            CopyAction::make()->label('logo')->copyable(fn () => $this->getLogoUrlForCopy()),
        ];
    }

    protected function prepareWebsiteForCopy(string $website): string
    {
        if (! $website) {
            return '';
        }

        $website = rtrim($website, '/');
        $website = preg_replace('#^https?://#', '', $website);
        $website = preg_replace('#^www\.#', '', $website);
        $website = 'www.'.$website;

        return $website;
    }

    protected function getLogoUrlForCopy(): string
    {
        $cloudinaryUrl = $this->record->cloudinary_url ?? null;
        $logoName = $this->record->logo_name ?? null;

        if ($cloudinaryUrl) {
            return $cloudinaryUrl;
        }

        if ($logoName) {
            return "https://sachsevent.com/images/compLogos/{$logoName}";
        }

        return '';
    }

    protected function copyToClipboard(string $value): void
    {
        $this->dispatch('copyToClipboard', clipboardValue: $value);

        $this->dispatch(
            'notify',
            type: 'success',
            message: 'Copied to clipboard!',
        );
    }

    public function loadFromAirtable(Company $record): void
    {
        $airtableService = app()->make(AirtableService::class);
        $airtableService->loadCompany($record->id);

        Notification::make()
            ->title('Success')
            ->body('Data fetched from Airtable!')
            ->success()
            ->send();
    }
}
