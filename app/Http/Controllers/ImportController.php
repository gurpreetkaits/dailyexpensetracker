<?php

namespace App\Http\Controllers;

use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TransactionsImport;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    protected $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $file = $request->file('file');
            $headings = $this->importService->getFileHeadings($file);

            return response()->json([
                'success' => true,
                'headings' => $headings,
                'file_path' => $file->store('imports')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to read file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveMappings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mappings' => 'required|array',
            'mappings.*.field' => 'required|string',
            'mappings.*.column' => 'nullable|string',
            'mapping_name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $mapping = $this->importService->saveMapping(
                Auth::id(),
                $request->mapping_name,
                $request->mappings
            );

            return response()->json([
                'success' => true,
                'mapping' => $mapping
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getMappings()
    {
        $mappings = $this->importService->getUserMappings(Auth::id());
        
        return response()->json([
            'success' => true,
            'mappings' => $mappings
        ]);
    }

    public function importTransactions(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file_path' => 'required|string',
            'mappings' => 'required|array',
            'mappings.*.field' => 'required|string',
            'mappings.*.column' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Custom validation for required fields
        $mappings = $request->mappings;
        $errors = [];

        $hasDate = false;
        $hasAmount = false;
        $hasValidType = false;

        foreach ($mappings as $mapping) {
            $field = $mapping['field'];
            $column = $mapping['column'] ?? '';

            if ($field === 'transaction_date' && !empty($column)) {
                $hasDate = true;
            } elseif ($field === 'amount' && !empty($column)) {
                $hasAmount = true;
            } elseif ($field === 'type') {
                // Type is valid if it has useMapping=true with column, or useMapping=false with defaultType
                if (isset($mapping['useMapping']) && $mapping['useMapping'] && !empty($column)) {
                    $hasValidType = true;
                } elseif (!isset($mapping['useMapping']) || !$mapping['useMapping']) {
                    if (isset($mapping['defaultType']) && !empty($mapping['defaultType'])) {
                        $hasValidType = true;
                    }
                }
            }
        }

        if (!$hasDate) {
            $errors[] = 'Transaction date field is required';
        }
        if (!$hasAmount) {
            $errors[] = 'Amount field is required';
        }
        if (!$hasValidType) {
            $errors[] = 'Transaction type field is required';
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => implode(', ', $errors)
            ], 422);
        }

        try {
            $result = $this->importService->importTransactions(
                Auth::id(),
                $request->file_path,
                $request->mappings
            );

            return response()->json([
                'success' => true,
                'imported_count' => $result['imported_count'],
                'failed_count' => $result['failed_count'],
                'errors' => $result['errors']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteMapping($id)
    {
        try {
            $this->importService->deleteMapping(Auth::id(), $id);

            return response()->json([
                'success' => true,
                'message' => 'Mapping deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
