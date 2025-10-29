<?php

// namespace App\Http\Controllers;

// use App\Models\Connector;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Http;

// class ConnectorController extends Controller
// {
    

//     public function index()
// {
//     try {
//         $response = Http::timeout(5)->get('http://127.0.0.1:8001/poll');

//         if (!$response->successful()) {
//             return response()->json([
//                 'error' => 'CDC service returned an error',
//                 'details' => $response->body()
//             ], 500);
//         }

//         $payload = $response->json();

//         // ✅ Normalize Python output to frontend format
//         $records = [];
//         if (!empty($payload['new_records'])) {
//             foreach ($payload['new_records'] as $record) {
//                 $records[] = [
//                     'table' => 'users', // fixed table name (you can make dynamic later)
//                     'op' => 'insert',
//                     'data' => $record
//                 ];
//             }
//         }

//         return response()->json($records, 200);

//     } catch (\Exception $e) {
//         return response()->json([
//             'error' => 'Could not connect to Python CDC service',
//             'message' => $e->getMessage()
//         ], 500);
//     }
// }

//     public function store(Request $request)
//     {
//         $validated = $request->validate([
//             'name' => 'required|string|max:255',
//             'source_db' => 'required|string',
//             'target_system' => 'nullable|string',
//         ]);

//         $connector = Connector::create($validated);

//         try {
//             Http::post('http://127.0.0.1:8001/start-cdc', [
//                 'connector_id' => $connector->id,
//                 'config' => $connector
//             ]);
//         } catch (\Exception $e) {
//             \Log::error('CDC Python service unavailable: ' . $e->getMessage());
//         }

//         return response()->json([
//             'message' => 'Connector added successfully',
//             'connector' => $connector
//         ], 201);
//     }

//     public function view()
//     {
//         return view('connectors');
//     }
// }



namespace App\Http\Controllers;

use App\Models\Connector;
use App\Models\CDCLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ConnectorController extends Controller
{
    public function index()
{
    try {
        $response = Http::timeout(5)->get('http://127.0.0.1:8001/poll');

        if (!$response->successful()) {
            return response()->json([
                'error' => 'CDC service returned an error',
                'details' => $response->body()
            ], 500);
        }

        $payload = $response->json();

        // ✅ Normalize Python output
        $records = [];
        if (!empty($payload['new_records'])) {
            foreach ($payload['new_records'] as $record) {
                // Save each record into cdc_logs table
                \App\Models\CDCLog::create([
                    'table_name' => 'users',
                    'operation' => 'insert',
                    'data' => $record
                ]);

                $records[] = [
                    'table_name' => 'users',
                    'operation' => 'insert',
                    'data' => $record
                ];
            }
        }

        // ✅ Get both live + stored history (last 50)
        $history = \App\Models\CDCLog::latest()->take(50)->get();

        // Merge live and history (avoid duplicates)
        $merged = collect($records)->merge($history)->unique('id')->values();

        return response()->json($merged, 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Could not connect to Python CDC service',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'source_db' => 'required|string',
            'target_system' => 'nullable|string',
        ]);

        $connector = Connector::create($validated);

        try {
            Http::post('http://127.0.0.1:8001/start-cdc', [
                'connector_id' => $connector->id,
                'config' => $connector
            ]);
        } catch (\Exception $e) {
            \Log::error('CDC Python service unavailable: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Connector added successfully',
            'connector' => $connector
        ], 201);
    }

    public function view()
    {
        return view('connectors');
    }
}

