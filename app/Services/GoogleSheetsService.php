<?php
namespace App\Services;

use Revolution\Google\Sheets\Facades\Sheets;
use Illuminate\Support\Facades\Cache;

class GoogleSheetsService
{
    public function syncToSheet(string $spreadsheetId, $documents): void
    {
//       $currentData = Sheets::spreadsheet($spreadsheetId)->sheet('Documents')->get()->toArray();
//
//        $headers = array_shift($currentData);
//
//        $comments = [];
//        foreach ($currentData as $row) {
//            if (count($row) > count($headers)) {
//                $comments[$row[0]] = $row[count($headers)]; // ID => comments
//            }
//        }
//
//        $newData = [array_keys($items[0] ?? [])];
//        foreach ($documents as $item) {
//            $row = array_values($item);
//            if (isset($comments[$item['id']])) {
//                $row[] = $comments[$item['id']];
//            }
//            $newData[] = $row;
//        }
//
//        Sheets::spreadsheet($spreadsheetId)
//            ->sheet('Documents')
//            ->clear();
//
//        Sheets::spreadsheet($spreadsheetId)
//            ->sheet('Documents')
//            ->append($newData);
//
//

        $sheets = Sheets::spreadsheet($spreadsheetId)->sheetList();

        $currentData = Sheets::spreadsheet($spreadsheetId)
            ->sheet($sheets[0])
            ->get()
            ->toArray();

        $headers = array_shift($currentData);
        $commentColumnIndex = count($headers);

        $comments = [];
        foreach ($currentData as $row) {
            if (isset($row[0]) && count($row) > $commentColumnIndex) {
                $comments[$row[0]] = $row[$commentColumnIndex];
            }
        }
        $documents[0]['comment'] = '';

        $newData = [array_keys($documents[0] ?? [])];
        //dd($documents);
        foreach ($documents as $document) {
            $row = array_values($document);
            //$row[]= 'Комментарий';
            if (isset($document['id']) && array_key_exists($document['id'], $comments)) {
                $row[] = $comments[$document['id']];
            } else {
                $row[] = '';
            }

            $newData[] = $row;
        }

        $sheets = Sheets::spreadsheet($spreadsheetId)->sheetList();

        Sheets::spreadsheet($spreadsheetId)
            ->sheet($sheets[0])
            ->update($newData);
    }



    public function getSheetData(int $count, $spreadsheetId): array
    {
        if (empty($spreadsheetId)) {
            return [];
        }

        try {
            $sheets = Sheets::spreadsheet($spreadsheetId)->sheetList();
            $rows = Sheets::spreadsheet($spreadsheetId)
                ->sheet($sheets[0])
                ->get()->toArray();

            $count = $count ?? count($rows);
            $rows = array_slice($rows, 0, $count);

        } catch (\Exception $e) {
            return [];
        }
        return $rows;
    }


    public function extractSpreadsheetId($url)
    {
        preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
        return $matches[1] ?? null;
    }

}
