<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SynchronizeToGoogleSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:google-sheets';
    protected $description = 'Synchronize allowed items to Google Sheets';

    /**
     * Execute the console command.
     */
    public function handle(GoogleSheetsService $sheetsService)
    {
        //$spreadsheetId = config('google.sheets.spreadsheet_id');
        $spreadsheetId = Cache::get('google_sheet_id');
        if (empty($spreadsheetId)) {
            $this->error('Google Spreadsheet ID not configured');
            return;
        }

        $sheetsService->syncToSheet($spreadsheetId);
        $this->info('Documents synchronized successfully');
    }
}
