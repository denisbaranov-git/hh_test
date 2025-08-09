<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class FetchController extends Controller
{
    protected $sheetsService;

    public function __construct(GoogleSheetsService $sheetsService)
    {
        $this->sheetsService = $sheetsService;
    }

    public function fetch($count = null): string
    {
        try {
            $spreadsheetId = Cache::get('google_sheet_id');
            $data = $this->sheetsService->getSheetData($count, $spreadsheetId);

            $output = "<div style='font-family: monospace;'>";
            $output .= "Fetched " . count($data) . " rows:\n\n";

            $total = count($data);
            $output .= "<div style='width: 50%; background: #f0f0f0; margin: 10px 0;'>";
            $percent = 0;
            $k = 0;
            foreach ($data as $i => $item) {
                $percent = ($i + 1) / $total * 100;
                if(isset($item[0]))
                $output .= "ID:{$item[0]}, Comment: {$item[count($item)-1]}<br>";
                $k++;
            }
            $output .= "<div style='width: {$percent}%; height: 20px; background: #4CAF50;'>($total/$k) - 100% </div>";
            $output .= "</div>";
            $output .= "Done!</div>";

            return $output;
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
