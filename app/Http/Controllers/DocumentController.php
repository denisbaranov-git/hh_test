<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentStoreRequest;
use App\Http\Requests\DocumentUpdateRequest;
use App\Http\Resources\DocumentCollection;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentGeneratorService $generatorService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('index');
    }

    public function getDocuments(): JsonResponse
    {
        return response()->json([
            'googleSheetUrlVal' => Cache::get('google_sheet_url'),
            'documents' => new DocumentCollection(Document::all()),
        ]);
    }

    public function store(DocumentStoreRequest $request): JsonResponse
    {
        $document = Document::create($request->validated());

        return (new DocumentResource($document))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Document $document): JsonResponse
    {
        return response()->json(new DocumentResource($document));
    }

    public function update(DocumentUpdateRequest $request, Document $document): JsonResponse
    {
        $document->update($request->validated());

        return response()->json(new DocumentResource($document));
    }

    public function destroy(Document $document): JsonResponse
    {
        $document->delete();

        return response()->json(null, 204);
    }

    public function generate(): JsonResponse
    {
        try {
            $this->generatorService->generate();

            return response()->json([
                'message' => '1000 items generated successfully',
                'count' => Document::count()
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Generation failed'], 500);
        }
    }

    public function clear(): JsonResponse
    {
        Document::truncate();

        return response()->json([
            'message' => 'All Documents deleted successfully'
        ]);
    }
}
