<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseTestController extends Controller
{
    public function testConnection()
    {
        // Test a simple query
        $result = DB::select('SELECT * from users');
        
        return response()->json([
            'status' => 'success',
            'message' => 'Database connection successful!',
            'test_query' => $result
        ]);
    }
}
