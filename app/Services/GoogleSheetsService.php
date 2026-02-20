<?php

namespace App\Services;

use App\Contracts\SheetServiceInterface;
use Revolution\Google\Sheets\Facades\Sheets;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleSheetsService implements SheetServiceInterface
{
    private const COMMENT_COLUMN_OFFSET = 1;

    public function syncToSheet(string $spreadsheetId, array $documents): void
    {
        try {
            $sheet = $this->getFirstSheet($spreadsheetId);
            $existingData = $sheet->get()->toArray();

            $comments = $this->extractComments($existingData);
            $newData = $this->prepareSheetData($documents, $comments);

            $sheet->update($newData);

        } catch (Exception $e) {
            Log::error('Failed to sync to Google Sheet: ' . $e->getMessage());
            throw new Exception('Sync failed: ' . $e->getMessage());
        }
    }

    public function getSheetData(int $count, string $spreadsheetId): array
    {
        if (empty($spreadsheetId)) {
            return [];
        }

        try {
            $sheet = $this->getFirstSheet($spreadsheetId);
            $rows = $sheet->get()->toArray();

            return array_slice($rows, 0, $count ?: count($rows));

        } catch (Exception $e) {
            Log::error('Failed to get sheet data: ' . $e->getMessage());
            return [];
        }
    }

    public function extractSpreadsheetId(string $url): ?string
    {
        preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
        return $matches[1] ?? null;
    }

    private function getFirstSheet(string $spreadsheetId)
    {
        $sheets = Sheets::spreadsheet($spreadsheetId)->sheetList();
        return Sheets::spreadsheet($spreadsheetId)->sheet($sheets[0]);
    }

    private function extractComments(array $existingData): array
    {
        if (empty($existingData)) {
            return [];
        }

        $headers = array_shift($existingData);
        $commentColumnIndex = count($headers);
        $comments = [];

        foreach ($existingData as $row) {
            if (isset($row[0]) && isset($row[$commentColumnIndex])) {
                $comments[$row[0]] = $row[$commentColumnIndex];
            }
        }

        return $comments;
    }

    private function prepareSheetData(array $documents, array $comments): array
    {
        if (empty($documents)) {
            return [[]];
        }

        $newData = [array_keys($documents[0])];

        foreach ($documents as $document) {
            $row = array_values($document);
            $row[] = $comments[$document['id']] ?? '';
            $newData[] = $row;
        }

        return $newData;
    }
}
