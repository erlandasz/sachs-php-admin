<?php

namespace App\Services;

use App\Models\CheckIn;
use App\Models\PrintingSettings;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Rawilk\Printing\Facades\Printing as PPPP;

class PrintingService
{
    public function checkIn($id, $printerId, ?bool $is_test = false)
    {
        $attendee = CheckIn::findOrFail($id);
        $eventsAttended = $attendee->events_attended;

        $has_day_one = strpos($eventsAttended, '1') !== false;
        $has_day_two = strpos($eventsAttended, '2') !== false;
        $has_day_three = strpos($eventsAttended, '3') !== false;

        $roleColors = $this->pickColor($has_day_one, $has_day_two, $has_day_three);

        $file_path = $this->print($attendee->first_name, $attendee->last_name, $attendee->company_name, $attendee->id, $roleColors, $printerId, $is_test);
        $this->markAsCheckedIn($id);

        if (file_exists($file_path) && app()->environment('production') && ! $is_test) {
            unlink($file_path);
        }

        logger($file_path);

        return $file_path;
    }

    private function markAsCheckedIn($checkInId): void
    {
        $checkIn = CheckIn::findOrFail($checkInId);

        $checkIn->checked_in = true;
        $checkIn->checked_in_at = now();

        $checkIn->save();
    }

    private function print(string $firstName, string $lastName, string $companyName, int|string $id, $color, $printerId, ?bool $is_test = false)
    {
        $outputFile = $this->fillPDFFile($firstName, $lastName, $companyName, $color);

        if (app()->environment('production') && ! $is_test) {
            PPPP::newPrintTask()
                ->printer($printerId)
                ->file($outputFile)
                ->send();
        }

        return $outputFile;
    }

    private function fillPDFFile(string $firstName, string $lastName, string $companyName, $color): string
    {
        $templatePath = Storage::path('pdf/template.pdf');
        $uuid = Str::uuid()->toString();
        $pdf = new BadgeService;
        $pdf->SetA6PortraitSize();
        $pdf->setSourceFile($templatePath);
        $page = $pdf->importPage(1);

        if ($page === false) {
            return 'Failed to import the page!';
        }

        $pdf->useTemplate($page);

        $pdf->AddTextRows($firstName, $lastName, $companyName, $color);

        $outputFilePath = Storage::path("pdf/badges/{$uuid}.pdf");

        $pdf->Output($outputFilePath, 'F');

        return $outputFilePath;
    }

    private function pickColor($has_day_one, $has_day_two, $has_day_three)
    {
        $settings = PrintingSettings::where('is_default', true)->first();

        if ($has_day_one && $has_day_two && $has_day_three) {
            return $settings->getAllDaysColor();
        }

        if ($has_day_one && $has_day_two) {
            return $settings->getDays1And2Color();
        }

        if ($has_day_two && $has_day_three) {
            return $settings->getDays2And3Color();
        }

        if ($has_day_one && $has_day_three) {
            abort(400);
        }

        if ($has_day_one) {
            return $settings->getDay1Color();
        }

        if ($has_day_two) {
            return $settings->getDay2Color();
        }

        if ($has_day_three) {
            return $settings->getDay3Color();
        }

        return [
            'red' => 255,
            'green' => 255,
            'blue' => 255,
        ];
    }
}
