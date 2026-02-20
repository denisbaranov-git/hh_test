<?php

namespace App\Contracts;

interface SheetServiceInterface
{
    public function syncToSheet(string $spreadsheetId, array $data): void;
    public function extractSpreadsheetId(string $url): ?string;
    public function getSheetData(int $count, string $spreadsheetId): array;
}
