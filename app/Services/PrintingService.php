<?php

namespace App\Services;

use App\Models\CheckIn;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Rawilk\Printing\Facades\Printing as PPPP;

class PrintingService
{
    public function checkIn($id, $printerId)
    {
        $attendee = CheckIn::findOrFail($id);
        $eventsAttended = $attendee->events_attended;

        $has_day_one = strpos($eventsAttended, '1') !== false;
        $has_day_two = strpos($eventsAttended, '2') !== false;
        $has_day_three = strpos($eventsAttended, '3') !== false;

        $roleColors = $this->pickColor($has_day_one, $has_day_two, $has_day_three);

        $file_path = $this->print($attendee->first_name, $attendee->last_name, $attendee->company_name, $attendee->id, $roleColors, $printerId);
        $this->markAsCheckedIn($id);

        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    private function markAsCheckedIn($checkInId)
    {
        $checkIn = CheckIn::findOrFail($checkInId);

        $checkIn->checked_in = true;
        $checkIn->checked_in_at = now();

        $checkIn->save();

        return $checkIn;
    }

    private function print($firstName, $lastName, $companyName, $id, $color, $printerId)
    {
        $outputFile = $this->fillPDFFile($firstName, $lastName, $companyName, $color);

        if (app()->environment('production')) {
            PPPP::newPrintTask()
                ->printer($printerId)
                ->file($outputFile)
                ->send();
        }

        return $outputFile;
    }

    private function fillPDFFile($firstName, $lastName, $companyName, $color): string
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
        $blue = [
            'red' => 29,
            'green' => 69,
            'blue' => 136,
        ];

        $lightBlue = [
            'red' => 173,
            'green' => 216,
            'blue' => 230,
        ];

        $red = [
            'red' => 220,
            'green' => 33,
            'blue' => 42,
        ];

        $purple = [
            'red' => 109,
            'green' => 97,
            'blue' => 150,
        ];

        $green = [
            'red' => 112,
            'green' => 173,
            'blue' => 71,
        ];

        $orange = [
            'red' => 255,
            'green' => 165,
            'blue' => 0,
        ];

        if ($has_day_one && $has_day_two && $has_day_three) {
            return $green;
        }

        if ($has_day_one && $has_day_two) {
            return $purple;
        }

        if ($has_day_two && $has_day_three) {
            return $red;
        }

        if ($has_day_one && $has_day_three) {
            abort(400);
        }

        if ($has_day_one) {
            return $blue;
        }

        if ($has_day_two) {
            return $orange;
        }

        if ($has_day_three) {
            return $purple;
        }

        return [
            'red' => 255,
            'green' => 255,
            'blue' => 255,
        ];
    }
}
