<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Enums\EmployeeType;


class ClientController extends Controller
{
    //
    /**
     * @OA\Get(
     *      path="/api/clients",
     *      tags={"Clinet"},
     *      summary="Get all clinets records name and id",
     *      description="Retrieves all clinets from the system only name and id.",
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Page number for pagination",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default=1
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Percival Schulist"),
     *                  @OA\Property(property="population", type="string", example="c9810738"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="משתמש אינו מחובר")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="התרחש בעיית שרתת יש לנסות שוב מאוחר יותר.")
     *          )
     *      )
     * )
     */
    public function index()
    {

        try {

            // Fetch users with their employeeType and roles
            $clients = Client::select('id','name', 'emp_type_id', 'personal_number')
            ->where('is_deleted',false)
            ->get();

            $clients->each(function ($client) {
                
                    if ($client->emp_type_id) {
                    //? set and format poplution for each client records
                    //set the first letter for the persnal_number
                    $client->population = match ($client->emp_type_id) {
                        EmployeeType::KEVA->value, EmployeeType::SADIR->value => 's' . $client->personal_number,
                        EmployeeType::MILUIM->value => 'm' . $client->personal_number,
                        EmployeeType::OVED_TZAHAL->value => 'c' . $client->personal_number,
                        default => throw new \InvalidArgumentException('סוג עובד לא תקין.')
                    };
                    
                    }
                $client->makeHidden(['personal_number', 'emp_type_id']);
                
                return $client;
            });

            
            return response()->json($clients, Response::HTTP_OK);

            
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

}