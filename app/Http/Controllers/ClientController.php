<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;


class ClientController extends Controller
{
    //

    public function index()
    {

        try {

            // Fetch users with their employeeType and roles
            $clients = Client::select('id','name')
            ->where('is_deleted',false)
            ->get();

            return response()->json($clients, Response::HTTP_OK);

            
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

}