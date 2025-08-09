<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Revolution\Google\Sheets\Facades\Sheets;

class FetchGoogleSheetData extends Command
{
    protected $signature = 'fetch:sheet-data {--count= : Limit the number of rows to display}';
    protected $description = 'Fetch data from Google Sheet and display ID and comments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //$spreadsheetId = Config::get('google.sheets.spreadsheet_id');
        $spreadsheetId = Cache::get('google_sheet_id');

        if (empty($spreadsheetId)) {
            $this->error('Google Spreadsheet ID is not configured');
            return 1;
        }

        $this->info('Fetching data from Google Sheet...');

        try {
            $sheets = Sheets::spreadsheet($spreadsheetId)->sheetList();
            $rows = Sheets::spreadsheet($spreadsheetId)
                ->sheet($sheets[0])
                ->get()->toArray();

            $headers = array_shift($rows);

//            $idIndex = array_search('id', $headers);
            $commentIndex = count($headers);

//            if ($idIndex === false) {
//                $this->error('ID column not found in the sheet');
//                return 1;
//            }

            $count = $this->option('count') ? (int)$this->option('count') : count($rows);
            $rows = array_slice($rows, 0, $count);

            $this->info("Displaying {$count} rows:");

            $bar = $this->output->createProgressBar(count($rows));
            $bar->start();

            foreach ($rows as $row) {
                //$id = $row[$idIndex] ?? 'N/A';
                $id = $row[0] ?? 'N/A';
                $comment = $row[$commentIndex] ?? 'No comment';

                $this->newLine();
                $this->line("ID: {$id}, Comment: {$comment}");

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->info('Done!');

            return 0;
        } catch (\Exception $e) {
            $this->error('Error fetching data: ' . $e->getMessage());
            return 1;
        }
    }
}
