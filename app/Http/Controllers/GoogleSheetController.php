<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoogleSheetUrlRequest;
use App\Contracts\SheetServiceInterface;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleSheetController extends Controller
{
    public function __construct(
        private SheetServiceInterface $googleSheetsService
    ) {}

    public function setUrl(GoogleSheetUrlRequest $request): JsonResponse
    {
        try {
            $spreadsheetId = $this->googleSheetsService->extractSpreadsheetId($request->url);

            if (!$spreadsheetId) {
                return response()->json(['error' => 'Invalid Google Sheets URL'], 400);
            }

            Cache::forever('google_sheet_url', $request->url);
            Cache::forever('google_sheet_id', $spreadsheetId);

            return response()->json([
                'message' => 'Google Sheet URL set successfully',
                'url' => $request->url,
                'spreadsheetId' => $spreadsheetId
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to set Google Sheet URL: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to set URL'], 500);
        }
    }

    public function syncNow(): JsonResponse
    {
        try {
            $spreadsheetId = Cache::get('google_sheet_id');

            if (empty($spreadsheetId)) {
                return response()->json(['error' => 'Google Sheet URL not set'], 400);
            }

            $documents = Document::allowed()->get();

            if ($documents->isEmpty()) {
                return response()->json(['warning' => 'No documents to sync'], 200);
            }

            $this->googleSheetsService->syncToSheet($spreadsheetId, $documents->toArray());

            return response()->json([
                'message' => 'Sync completed successfully',
                'count' => $documents->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Google Sheets sync failed: ' . $e->getMessage());
            return response()->json(['error' => 'Sync failed: ' . $e->getMessage()], 500);
        }
    }

}
