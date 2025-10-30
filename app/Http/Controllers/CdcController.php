<?php 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\CdcConnection;

class CdcController extends Controller
{
    public function saveConnection(Request $request)
    {
        $validated = $request->validate([
            'db_type' => 'required|string',
            'host' => 'required|string',
            'port' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'database_name' => 'required|string',
            'table_name' => 'required|string',
        ]);

        // Save connection info
        $connection = CdcConnection::create($validated);

        // Notify Python CDC service
        try {
            $response = Http::post('http://127.0.0.1:8001/set-connection', $validated);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to connect to Python CDC service', 'details' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Connection established successfully', 'connection' => $connection]);
    }
}
