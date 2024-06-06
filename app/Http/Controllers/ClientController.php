<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Enums\EmployeeType;
use Illuminate\Support\Facades\Validator;


class ClientController extends Controller
{


    const MIN_LEN = 1;
    const MAX_LEN = 7;


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

            
            return response()->json($clients->isEmpty() ? [] : $clients, Response::HTTP_OK);

            
        } catch (\Exception $e) {
            
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


        /**
     * @OA\Get(
     *      path="/api/clients/search",
     *      tags={"Clinet"},
     *      summary="Search for clients by personal number",
     *      description="Searches for clients in the system based on the provided personal number.",
     *      @OA\Parameter(
     *          name="search_string",
     *          in="path",
     *          required=true,
     *          description="Search string (personal number)",
     *          @OA\Schema(type="string")
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
     *                  @OA\Property(property="personal_number", type="string", example="s5671482"),
     *                  @OA\Property(property="email", type="string", example="s5671482@army.idf.il"),
     *                  @OA\Property(property="phone", type="string", example="0556926412"),
     *                  @OA\Property(property="emp_type_id", type="integer", example=2),
     *                  @OA\Property(
     *                      property="employee_type",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="name", type="string", example="miluim")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="נתונים שנשנלחו אינם בפורמט תקין")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="BadRequest",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="חובה לשלוח מספר אישי לחיפוש")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="התרחשה בעיה בשרת. אנא נסה שוב מאוחר יותר.")
     *          )
     *      ),
     * )
     */

     //? search clients records based on pn or by name.

    public function searchClients(Request $request)
    {
        try {


            // set custom error messages in Hebrew
            $customMessages = [
                'query.required' => 'יש לשלוח שדה לחיפוש',
                'query.string' => 'ערך השדה שנשלח אינו תקין.',
            ];
            //set the rules

            $rules = [
                'query' => 'required|string',
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {

                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            $searchQuery = $request->input('query');

            $searchQuery = str_replace(' ', '', $request->input('query'));

            if ((ctype_digit($searchQuery) == true) && (strlen($searchQuery) < self::MIN_LEN || strlen($searchQuery) > self::MAX_LEN)) {
                return response()->json(['message' => 'נתונים שנשנלחו אינם בפורמט תקין'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ((ctype_digit($searchQuery) == true)) {

                //? search user by personal_number
                $clientsRecords = Client::with(['employeeType'])
                    ->where('personal_number', 'like', '%' . $searchQuery . '%')
                    ->where('is_deleted', false)
                    ->orderBy('id', 'asc')
                    ->get();

                $clientsRecords->each(function ($client) {

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

                    return response()->json($clientsRecords,Response::HTTP_OK);
            }


            // Search users by name (ignoring spaces)
            $clientsRecords = Client::with(['employeeType'])
            ->whereRaw("REPLACE(name, ' ', '') LIKE ?", ['%' . $searchQuery . '%'])
            ->where('is_deleted', false)
                ->orderBy('id', 'asc')
                ->get();



            $clientsRecords->each(function ($client) {

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

                

            return response()->json($clientsRecords->isEmpty() ? []  : $clientsRecords, Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


}