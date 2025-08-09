<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class FetchCommandController extends Controller
{
    public function fetch($count = null)
    {
        try {
            $params = [];
            if ($count) {
                $params['--count'] = (int)$count;
            }

            $exitCode = Artisan::call('fetch:sheet-data', $params);

            if ($exitCode !== 0) {
                throw new \Exception("Command failed with code: $exitCode");
            }

            $output = Artisan::output();

            return nl2br($output);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Command execution failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
