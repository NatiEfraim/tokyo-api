<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    //
    public function index()
    {
        try {

            $departments = Department::where('is_deleted', 0)->get();


            return response()->json($departments, Response::HTTP_OK);
        } catch (\Exception $e) {
            log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
