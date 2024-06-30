<?php

namespace App\Http\Controllers;

use App\Services\Client\ClientService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Enums\Status;
use Illuminate\Support\Facades\Validator;



class ClientController extends Controller
{


    const MIN_LEN = 1;
    const MAX_LEN = 7;

protected $_clientService;

    public function __construct()
    {
        $this->_clientService = new ClientService();
    }

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

            $result = $this->_clientService->fetchCleintsRecords();

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json(['data' => $result['data']], Response::HTTP_OK),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

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


            $result = $this->_clientService->searchClients($request);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json(['data' => $result['data']], Response::HTTP_OK),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


}