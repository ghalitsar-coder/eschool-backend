<?php

use Illuminate\Support\Facades\Route;
use App\Models\School;
use App\Models\Eschool;

// Temporary route for debugging
Route::get('/debug/eschools', function () {
    $schools = School::with('eschools')->get();
    
    $data = [];
    foreach ($schools as $school) {
        $eschoolData = [];
        foreach ($school->eschools as $eschool) {
            $eschoolData[] = [
                'id' => $eschool->id,
                'name' => $eschool->name,
                'school_id' => $eschool->school_id,
            ];
        }
        
        $data[] = [
            'school_id' => $school->id,
            'school_name' => $school->name,
            'eschools' => $eschoolData,
        ];
    }
    
    return response()->json($data);
});