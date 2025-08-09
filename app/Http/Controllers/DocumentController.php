<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\GoogleSheet;
use Faker\Factory;
use Illuminate\Http\Request;
use App\Services\GoogleSheetsService;
use Illuminate\Support\Facades\Cache;

class DocumentController extends Controller
{
    protected $googleSheetsService;
    public function __construct(GoogleSheetsService  $googleSheetsService){
        $this->googleSheetsService = $googleSheetsService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('index');
        //return response()->json(Document::all());
    }

    public function getDocuments()
    {
        //return response()->json(Document::all());

        return response()->json([
            'googleSheetUrlVal' => Cache::get('google_sheet_url'),
            'documents' => Document::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:Allowed,Prohibited',
            'description' => 'nullable|string',
        ]);

        $item = Document::create($validated);
        return response()->json($item, 201);
    }

    public function show(Document $document)
    {
        return response()->json($document);
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:Allowed,Prohibited',
            'description' => 'nullable|string',
        ]);

        $document->update($validated);
        return response()->json($document);
    }

    public function destroy(Document $document)
    {
        $document->delete();
        return response()->json(null, 204);
    }

    public function generate()
    {
        Document::truncate();

        $items = [];
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 1000; $i++) {
            $status = $i % 2 === 0 ? 'Allowed' : 'Prohibited';
            $items[] = [
                'name' => 'Document ' . ($i + 1),
                'status' => $status,
                'description' => $faker->text(100),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Document::insert($items);

        return response()->json(['message' => '1000 items generated successfully']);
    }

    public function clear(): \Illuminate\Http\JsonResponse
    {
        Document::truncate();

        return response()->json(['message' => 'All Documents deleted successfully']);
    }
    public function setGoogleSheetUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $spreadsheetId = $this->googleSheetsService->extractSpreadsheetId($request->url);

        if (!$spreadsheetId) {
            return response()->json(['error' => 'Invalid Google Sheets URL'], 400);
        }

        $url = $request->input('url');
        $spreadsheetId = $this->googleSheetsService->extractSpreadsheetId($url);

        Cache::forever('google_sheet_url', $url);
        Cache::forever('google_sheet_id', $spreadsheetId);

        return response()->json(['message' => 'Google Sheet URL set successfully','url' => $url, 'spreadsheetId' => $spreadsheetId]);
    }

    public function syncNow(): \Illuminate\Http\JsonResponse
    {
        $documents = Document::allowed()->get()->toArray();

        $spreadsheetId = Cache::get('google_sheet_id');

        if (empty($spreadsheetId)) {
            return response()->json(['error' => 'Google Sheet URL not set'], 400);
        }

        $this->googleSheetsService->syncToSheet($spreadsheetId, $documents);

        return response()->json(['message' => 'Sync completed successfully']);
    }

}
