<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;

class BadgeService extends Fpdi
{
    private int $availableWidth;

    private int $baseFontSize;

    public function __construct()
    {
        parent::__construct();
    }

    public function SetA6PortraitSize(): void
    {
        $this->AddPage('P', [100, 148]);
        $this->SetAutoPageBreak(false);
    }

    public function AddTextRows(string $row1, string $row2, string $row3, $color): void
    {
        $settings = PrintingSettings::where('name', 'Default');

        $this->setInitialValues($settings);

        [$row1, $row2, $row3] = $this->replaceNonRegularCharacters([$row1, $row2, $row3]);

        $this->availableWidth = $this->GetPageWidth() - $this->GetPageWidth() * 0.1 * 2; // Updated to 90% of the page width

        [$row1Dimensions, $row2Dimensions, $row3Dimensions] = $this->getRowDimensions([$row1, $row2, $row3]);

        $y_offset = $settings->y_offset;
        // smaller number means HIGHER on paper
        $y = $this->GetY() + $y_offset; // WAS 35, 40

        $this->displayText($row1, $y, $row1Dimensions, $settings);

        // Row 2
        $this->SetFont($settings->font_family, $settings->font_weight, $row2Dimensions['fontSize']);
        $this->SetY($this->GetY() + $row1Dimensions['rowHeight'] - 2);
        $this->Cell($this->availableWidth, $row2Dimensions['rowHeight'], $row2, 0, 1, 'C');

        // row3
        $this->SetFont('Times', 'B', $row3Dimensions['fontSize']);
        $this->SetY($this->GetY() + $row2Dimensions['rowHeight'] + 8);

        $maxLineLength = 15;
        $textToDisplay = $row3;

        if (strlen($textToDisplay) > $maxLineLength * 2) {
            $middlePoint = strlen($textToDisplay) / 2;

            // Find the nearest space to the middle point horizontally
            $spacePosition = strpos($textToDisplay, ' ', $middlePoint);

            if ($spacePosition !== false) {
                $middlePoint = $spacePosition;
            }

            $firstPart = trim(substr($textToDisplay, 0, $middlePoint));
            $secondPart = trim(substr($textToDisplay, $middlePoint));

            // Set font size for irst part
            $this->SetFont('Times', 'B', 18); // WAS 18

            // Output first part
            $this->SetX($this->GetPageWidth() * 0.1);
            $this->Cell($this->availableWidth, $row3Dimensions['rowHeight'], $firstPart, 0, 1, 'C');

            $this->SetY($this->GetY() + $row3Dimensions['rowHeight'] - 2);

            // Set font size for second part
            $this->SetFont('Times', 'B', 18);

            // Output second part
            $this->SetX($this->GetPageWidth() * 0.1);
            $this->Cell($this->availableWidth, $row3Dimensions['rowHeight'], $secondPart, 0, 1, 'C');
        } else {
            $this->SetFont('Times', 'B', $row3Dimensions['fontSize']);
            $this->SetX($this->GetPageWidth() * 0.1);
            $this->SetY(95); // Set Y position to the initial value WAS 101
            $this->Cell($this->availableWidth, $row3Dimensions['rowHeight'], $row3, 0, 1, 'C');
        }

        // $this->SetFillColor($color['red'], $color['green'], $color['blue']);
        // $this->Rect(0, $this->GetPageHeight() - 35.55, $this->GetPageWidth(), 11, 'F');
    }

    private function displayText(string $text, int $y, array $dimensions, PrintingSettings $settings): void
    {
        $fontSize = $dimensions['fontSize'];
        $rowHeight = $dimensions['rowHeight'];

        $this->SetX($this->GetPageWidth() * 0.1);

        $this->SetY($y);

        $this->SetFont($settings->font_family, $settings->font_weight, $fontSize);

        $this->Cell($this->availableWidth, $rowHeight, $text, 0, 1, 'C');
    }

    private function calculateRowDimensions($text): array
    {
        $maxWidth = $this->GetStringWidth($text);
        $fontSize = ($maxWidth > $this->availableWidth) ? ($this->availableWidth / $maxWidth) * $this->baseFontSize : $this->baseFontSize;
        $rowHeight = $fontSize * 0.2;

        return [
            'fontSize' => $fontSize,
            'rowHeight' => $rowHeight,
        ];
    }

    private function replaceNonRegularCharacters(array $text): array
    {
        return $text;
    }

    private function getRowDimensions(array $text): array
    {
        $row1 = $text[0];
        $row2 = $text[1];
        $row3 = $text[2];

        $row1Dimensions = $this->calculateRowDimensions($row1);
        $row2Dimensions = $this->calculateRowDimensions($row2);
        $row3Dimensions = $this->calculateRowDimensions($row3);

        return [
            $row1Dimensions,
            $row2Dimensions,
            $row3Dimensions,
        ];
    }

    private function setInitialValues(PrintingSettings $settings): void
    {
        $this->SetTextColor(0, 0, 0);

        $this->baseFontSize = $settings->base_font_size;

        $this->SetFont('Times', '', $this->baseFontSize);
    }
}
