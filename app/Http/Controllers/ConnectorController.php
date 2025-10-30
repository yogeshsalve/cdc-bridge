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

//         // ✅ Normalize Python output
//         $records = [];
//         if (!empty($payload['new_records'])) {
//             foreach ($payload['new_records'] as $record) {
//                 // Save each record into cdc_logs table
//                 \App\Models\CDCLog::create([
//                     'table_name' => 'users',
//                     'operation' => 'insert',
//                     'data' => $record
//                 ]);

//                 $records[] = [
//                     'table_name' => 'users',
//                     'operation' => 'insert',
//                     'data' => $record
//                 ];
//             }
//         }

//         // ✅ Get both live + stored history (last 50)
//         $history = \App\Models\CDCLog::latest()->take(50)->get();

//         // Merge live and history (avoid duplicates)
//         $merged = collect($records)->merge($history)->unique('id')->values();

//         return response()->json($merged, 200);

//     } catch (\Exception $e) {
//         return response()->json([
//             'error' => 'Could not connect to Python CDC service',
//             'message' => $e->getMessage()
//         ], 500);
//     }
// }

// public function index()
// {
//     try {
//         // Poll the Python CDC service
//         $response = Http::timeout(5)->get('http://127.0.0.1:8001/poll');

//         if (!$response->successful()) {
//             return response()->json([
//                 'error' => 'CDC service returned an error',
//                 'details' => $response->body()
//             ], 500);
//         }

//         $payload = $response->json();
//         $liveRecord = null;

//         // ✅ If Python CDC returns new record(s)
//         if (!empty($payload['new_records'])) {
//             foreach ($payload['new_records'] as $record) {
//                 // Ensure data is stored as JSON string
//                 \App\Models\CDCLog::create([
//                     'table_name' => $record['table'] ?? 'users',
//                     'operation'  => $record['action'] ?? 'insert',
//                     'data'       => is_array($record) ? json_encode($record) : $record,
//                 ]);
//             }

//             // Take the last record as "live"
//             $liveRecord = end($payload['new_records']);
//         } else {
//             // ✅ If Python has no new data, get latest from DB
//             $lastEntry = \App\Models\CDCLog::latest()->first();
//             if ($lastEntry) {
//                 $data = $lastEntry->data;
//                 $liveRecord = is_string($data) ? json_decode($data, true) : $data;
//             }
//         }

//         // ✅ Fetch last 50 history entries
//         $history = \App\Models\CDCLog::latest()->take(50)->get(['id', 'table_name', 'operation', 'data', 'created_at']);

//         // Safely decode JSON strings
//         $history = $history->map(function ($item) {
//             $decoded = $item->data;
//             if (is_string($decoded)) {
//                 $decoded = json_decode($decoded, true);
//             }

//             return [
//                 'id'         => $item->id,
//                 'table_name' => $item->table_name,
//                 'operation'  => $item->operation,
//                 'data'       => $decoded,
//                 'created_at' => $item->created_at->toDateTimeString(),
//             ];
//         });

//         // ✅ Final JSON response
//         return response()->json([
//             'live'    => $liveRecord,
//             'history' => $history
//         ], 200);

//     } catch (\Exception $e) {
//         return response()->json([
//             'error'   => 'Could not connect to Python CDC service',
//             'message' => $e->getMessage()
//         ], 500);
//     }
// }

// public function index()
// {
//     try {
//         // Poll the Python CDC service
//         $response = Http::timeout(5)->get('http://127.0.0.1:8001/poll');

//         if (!$response->successful()) {
//             return response()->json([
//                 'error' => 'CDC service returned an error',
//                 'details' => $response->body()
//             ], 500);
//         }

//         $payload = $response->json();
//         $liveRecord = null;

//         // ✅ If Python CDC returns new record(s)
//         if (!empty($payload['new_records'])) {
//             foreach ($payload['new_records'] as $record) {
//                 $table = $record['table'] ?? 'users';
//                 $operation = $record['action'] ?? 'insert';

//                 // Save each record in CDCLog
//                 \App\Models\CDCLog::create([
//                     'table_name' => $table,
//                     'operation'  => $operation,
//                     'data'       => is_array($record) ? json_encode($record) : $record,
//                 ]);
//             }

//             // Take the last record as "live" and make its structure match history
//             $lastRecord = end($payload['new_records']);
//             $liveRecord = [
//                 'table_name' => $lastRecord['table'] ?? 'users',
//                 'operation'  => $lastRecord['action'] ?? 'insert',
//                 'data'       => $lastRecord,
//             ];
//         } else {
//             // ✅ If no new data, get latest from DB
//             $lastEntry = \App\Models\CDCLog::latest()->first();
//             if ($lastEntry) {
//                 $decodedData = is_string($lastEntry->data)
//                     ? json_decode($lastEntry->data, true)
//                     : $lastEntry->data;

//                 $liveRecord = [
//                     'table_name' => $lastEntry->table_name,
//                     'operation'  => $lastEntry->operation,
//                     'data'       => $decodedData,
//                 ];
//             }
//         }

//         // ✅ Fetch last 50 history entries
//         $history = \App\Models\CDCLog::latest()->take(50)->get(['id', 'table_name', 'operation', 'data', 'created_at']);

//         $history = $history->map(function ($item) {
//             $decoded = is_string($item->data) ? json_decode($item->data, true) : $item->data;
//             return [
//                 'id'         => $item->id,
//                 'table_name' => $item->table_name,
//                 'operation'  => $item->operation,
//                 'data'       => $decoded,
//                 'created_at' => $item->created_at->toDateTimeString(),
//             ];
//         });

//         // ✅ Final JSON response (uniform structure)
//         return response()->json([
//             'live'    => $liveRecord,
//             'history' => $history
//         ], 200);

//     } catch (\Exception $e) {
//         return response()->json([
//             'error'   => 'Could not connect to Python CDC service',
//             'message' => $e->getMessage()
//         ], 500);
//     }
// }


public function index()
{
    try {
        // Poll the Python CDC service
        $response = Http::timeout(5)->get('http://127.0.0.1:8001/poll');

        if (!$response->successful()) {
            return response()->json([
                'error' => 'CDC service returned an error',
                'details' => $response->body()
            ], 500);
        }

        $payload = $response->json();
        $liveRecord = null;

        // ✅ If Python CDC returns new record(s)
        if (!empty($payload['new_records'])) {
            foreach ($payload['new_records'] as $record) {
                $table = $record['table'] ?? 'users';
                $operation = $record['action'] ?? 'insert';
                $dataJson = is_array($record) ? json_encode($record) : $record;

                // ✅ Check if the same record already exists
                $exists = \App\Models\CDCLog::where('table_name', $table)
                    ->where('operation', $operation)
                    ->where('data', $dataJson)
                    ->exists();

                if (!$exists) {
                    \App\Models\CDCLog::create([
                        'table_name' => $table,
                        'operation'  => $operation,
                        'data'       => $dataJson,
                    ]);
                }
            }

            // Take the last record as "live"
            $liveRecord = end($payload['new_records']);
        } else {
            // ✅ If Python has no new data, get latest from DB
            $lastEntry = \App\Models\CDCLog::latest()->first();
            if ($lastEntry) {
                $data = $lastEntry->data;
                $liveRecord = is_string($data) ? json_decode($data, true) : $data;
            }
        }

        // ✅ Fetch last 50 history entries
        $history = \App\Models\CDCLog::latest()->take(50)->get(['id', 'table_name', 'operation', 'data', 'created_at']);

        // Decode JSON safely
        $history = $history->map(function ($item) {
            $decoded = $item->data;
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            return [
                'id'         => $item->id,
                'table_name' => $item->table_name,
                'operation'  => $item->operation,
                'data'       => $decoded,
                'created_at' => $item->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'live'    => ['data' => $liveRecord],
            'history' => $history
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error'   => 'Could not connect to Python CDC service',
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

