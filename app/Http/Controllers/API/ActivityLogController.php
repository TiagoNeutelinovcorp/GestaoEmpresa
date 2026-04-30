<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::query()->with('causer')->latest();

        if ($request->filled('menu')) {
            $query->where('properties->menu', $request->string('menu'));
        }

        return response()->json($query->paginate(20));
    }
}
