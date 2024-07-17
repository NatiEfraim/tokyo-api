<?php

namespace App\Http\Controllers;


use App\Enums\DistributionStatus;
use App\Enums\Status;
use App\Http\Requests\AllocationDistributionRequest;
use App\Http\Requests\ChangeStatusDistributionRequest;
use App\Http\Requests\StoreDistributionRequest;
use App\Models\Distribution;
use App\Services\Distribution\DistributionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
// use App\Mail\DistributionFailure;
// use Illuminate\Support\Facades\Mail;




class DistributionController extends Controller
{
    //

    const MIN_LEN = 1;
    const MAX_LEN = 7;

    protected $_distributionService;

    public function __construct(){
        $this->_distributionService = new DistributionService();
    }


    /**
     * Retrieve all distributions.
     *
     * This endpoint retrieves all distribution records along with their associated inventory and department.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/distributions",
     *     summary="Retrieve all distributions",
     *     tags={"Distributions"},
     *      summary="Get all Distributions",
     *      description="Returns a list of all Distributions.",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="comment", type="string", example="Velit veritatis quia vel nemo qui. Eaque commodi expedita enim libero ut. Porro ducimus repellendus tenetur."),
     *              @OA\Property(property="status", type="integer", example=1),
     *              @OA\Property(property="quantity", type="integer", example=44),
     *              @OA\Property(property="inventory_id", type="integer", example=24),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(
     *                  property="inventory",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=24),
     *                  @OA\Property(property="quantity", type="integer", example=10),
     *                  @OA\Property(property="sku", type="string", example="1359395842801"),
     *                  @OA\Property(property="item_type", type="string", example="magni"),
     *                  @OA\Property(property="detailed_description", type="string", example="Velit ut ipsam neque tempora est dicta. Et distinctio eligendi expedita corporis assumenda aspernatur hic.")
     *              ),
     *              @OA\Property(
     *                  property="created_for_user",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Percival Schulist"),
     *                  @OA\Property(property="emp_type_id", type="integer", example=2),
     *                  @OA\Property(property="phone", type="string", example="0556926412"),
     *                  @OA\Property(
     *                      property="employee_type",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="name", type="string", example="miluim")
     *                  ),
     *                  @OA\Property(
     *                      property="department",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="name", type="string", example="ducimus")
     *                  ),
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *         )
     *     )
     * )
     */

    public function index()
    {
        try {



            $result = $this->_distributionService->index();
          

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

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
     *      path="/api/distributions/fetch-quartermaster/{id}",
     *      tags={"Distributions"},
     *      summary="fetch quartermaster by id records ",
     *      description="fetch quartermaster that sign on the records.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the distribution",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="quartermaster_name", type="string", example="Ramiro Adams"),
     *              @OA\Property(property="quartermaster_id", type="integer", example=1),
     *              @OA\Property(property="created_at_time", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(property="created_at_date", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="יש לשלוח מספר מזהה של שורה")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *          )
     *      )
     * )
     */

    //? fetch associated quartermaster
    public function fetchQuartermaster($id = null)
    {
        try {


            $result = $this->_distributionService->fetchQuartermaster($id);


            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };


        } catch (\Exception $e) {

            Log::error($e->getMessage());

        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    /**
     * Retrieve all distributions.
     *
     * This endpoint retrieves all distribution records along with their associated inventory and department.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/distributions/fetch-history",
     *     summary="Retrieve all distributions based on role of user",
     *     tags={"Distributions"},
     *      summary="Get all Distributions records based on user role",
     *      description="Returns a list of all history Distributions.",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="comment", type="string", example="Velit veritatis quia vel nemo qui. Eaque commodi expedita enim libero ut. Porro ducimus repellendus tenetur."),
     *              @OA\Property(property="status", type="integer", example=1),
     *              @OA\Property(property="quantity", type="integer", example=44),
     *              @OA\Property(property="inventory_id", type="integer", example=24),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(
     *                  property="inventory",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=24),
     *                  @OA\Property(property="quantity", type="integer", example=10),
     *                  @OA\Property(property="sku", type="string", example="1359395842801"),
     *                  @OA\Property(property="item_type", type="string", example="magni"),
     *                  @OA\Property(property="detailed_description", type="string", example="Velit ut ipsam neque tempora est dicta. Et distinctio eligendi expedita corporis assumenda aspernatur hic.")
     *              ),
     *              @OA\Property(
     *                  property="created_for_user",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Percival Schulist"),
     *                  @OA\Property(property="emp_type_id", type="integer", example=2),
     *                  @OA\Property(property="phone", type="string", example="0556926412"),
     *                  @OA\Property(
     *                      property="employee_type",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="name", type="string", example="miluim"),
     *                      @OA\Property(property="population", type="string", example="מילואים"),
     *                  ),
     *                  @OA\Property(
     *                      property="department",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="name", type="string", example="ducimus")
     *                  ),
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *         )
     *     )
     * )
     */

    // ? fetch all records - based on role of user
    public function fetchRecordsByType(Request $request)
    {
        try {


            // set custom error messages in Hebrew
            $customMessages = [
                'query.required' => 'יש לשלוח שדה לחיפוש',
                'query.string' => 'ערך השדה שנשלח אינו תקין.',
            ];
            //set the rules

            $rules = [
                'query' => 'nullable|string',
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            $result = $this->_distributionService->fetchRecordsByType($request);


            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };


        } catch (\Exception $e) {

            Log::error($e->getMessage());

        }

        return response()->json(['message' => 'התרחש בעיית שרת.נסה שוב מאוחר יותר'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Retrieve all approved distributions records.
     *
     * This endpoint retrieves all distribution records where status is approved by Liran .
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/distributions/fetch-approved",
     *     summary="Retrieve all distributions where status is 1",
     *     tags={"Distributions"},
     *      summary="Get all Distributions records approved",
     *      description="Returns a list of all Distributions records that has been approved.",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="comment", type="string", example="Velit veritatis quia vel nemo qui. Eaque commodi expedita enim libero ut. Porro ducimus repellendus tenetur."),
     *              @OA\Property(property="status", type="integer", example=1),
     *              @OA\Property(property="quantity", type="integer", example=44),
     *              @OA\Property(property="inventory_id", type="integer", example=24),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(
     *                  property="inventory",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=24),
     *                  @OA\Property(property="quantity", type="integer", example=10),
     *                  @OA\Property(property="sku", type="string", example="1359395842801"),
     *                  @OA\Property(property="item_type", type="string", example="magni"),
     *                  @OA\Property(property="detailed_description", type="string", example="Velit ut ipsam neque tempora est dicta. Et distinctio eligendi expedita corporis assumenda aspernatur hic.")
     *              ),
     *              @OA\Property(
     *                  property="created_for_user",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Percival Schulist"),
     *                  @OA\Property(property="emp_type_id", type="integer", example=2),
     *                  @OA\Property(property="phone", type="string", example="0556926412"),
     *                  @OA\Property(
     *                      property="employee_type",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="name", type="string", example="miluim")
     *                  )
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *         )
     *     )
     * )
     */

    //? fetch records for only records that has been approved by orde_number
    public function fetchApprovedDistribution(Request $request)
    {
        try {


            // set validation rules
            $rules = [
                'order_number' => 'required|string|exists:distributions,order_number,is_deleted,0',
            ];

            // Define custom error messages

            $customMessages = [
                'order_number.exists' => 'מספר הזמנה אינה קיית במערכת.',
            ];

            // validate the request with custom error messages
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            $result = $this->_distributionService->fetchApprovedDistribution($request);


            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

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
     *      path="/api/distributions/{id}",
     *      tags={"Distributions"},
     *      summary="Get distribution by ID",
     *      description="Returns a single distribution by its ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the distribution",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="comment", type="string", example="Velit veritatis quia vel nemo qui. Eaque commodi expedita enim libero ut. Porro ducimus repellendus tenetur."),
     *              @OA\Property(property="status", type="integer", example=1),
     *              @OA\Property(property="quantity", type="integer", example=44),
     *              @OA\Property(property="inventory_id", type="integer", example=24),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(
     *                  property="inventory",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=24),
     *                  @OA\Property(property="quantity", type="integer", example=10),
     *                  @OA\Property(property="sku", type="string", example="1359395842801"),
     *                  @OA\Property(property="item_type", type="string", example="magni"),
     *                  @OA\Property(property="detailed_description", type="string", example="Velit ut ipsam neque tempora est dicta. Et distinctio eligendi expedita corporis assumenda aspernatur hic.")
     *              ),
     *              @OA\Property(
     *                  property="created_for_user",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Percival Schulist"),
     *                  @OA\Property(property="emp_type_id", type="integer", example=2),
     *                  @OA\Property(property="phone", type="string", example="0556926412"),
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
     *          response=400,
     *          description="Bad request",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="יש לשלוח מספר מזהה של שורה")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *          )
     *      )
     * )
     */

    public function getRecordById($id = null)
    {
        try {




            $result = $this->_distributionService->getRecordById($id);


            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),

            };



        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @OA\Delete(
     *      path="/api/distributions/{id}",
     *      tags={"Distributions"},
     *      summary="Delete an Distributions by ID",
     *      description="Deletes an Distributions based on the provided ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the Distributions to delete",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="שורה נמחקה בהצלחה.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="יש לשלוח מספר מזהה של שורה")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="שורה אינה קיימת במערכת.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *          )
     *      )
     * )
     */

    public function destroy($id = null)
    {
        try {


            $result = $this->_distributionService->destroy($id);


            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json(['message' => $result['message']], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

        } catch (\Exception $e) {

            Log::error($e->getMessage());

        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    /**
     * Store a newly created distribution.
     *
     * This endpoint creates a new distribution record.
     *
     * @param  \App\Http\Requests\StoreDistributionRequest  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/distributions/",
     *     summary="Store a new distribution",
     *     tags={"Distributions"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Distribution data",
     *         @OA\JsonContent(
     *             required={"department_id", "created_for", "items"},
     *             @OA\Property(property="general_comment", type="string", example="general comment for all the items"),
     *             @OA\Property(property="department_id", type="integer", example=1),
     *             @OA\Property(property="employee_type", type="integer", example=1),
     *             @OA\Property(property="phone", type="string", example="05326514585"),
     *             @OA\Property(property="name", type="string", example="Momo"),
     *             @OA\Property(property="personal_number", type="string", example="6548525"),
     *             @OA\Property(property="created_for", type="integer", example=1),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"type_id", "quantity"},
     *                     @OA\Property(property="type_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=5),
     *                     @OA\Property(property="comment", type="string", example="זהו הערה עבור המחשב")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Distribution created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="שורה נוצרה בהצלחה.")
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *         )
     *     )
     * )
     */

    public function store(StoreDistributionRequest $request)
    {
        try {

            $user_auth = Auth::user();

            $result = $this->_distributionService->store($request);


            // Use match to handle different status cases
            return match ($result['status']) {


                Status::CREATED => response()->json(['message' => $result['message']], Response::HTTP_CREATED),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };



        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of any error
            Log::error($e->getMessage());
        }

        // // Send failure email
        // Mail::to($user_auth->email)->send(new DistributionFailure($user_auth));

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @OA\Post(
     *      path="/api/distributions/allocation",
     *      tags={"Distributions"},
     *      summary="Update distribution status by ID",
     *      description="Updates the status of a distribution by its ID.",
     *      @OA\Parameter(
     *          name="id",
     *          description="Distribution ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Request data",
     *          @OA\JsonContent(
     *              required={"status"},
     *              @OA\Property(property="status", type="integer", example="1", description="New status value (0 for pending, 1 for approved, 2 for canceled)"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="שורה התעדכנה בהצלחה."),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Distribution not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="הרשומה לא נמצאה."),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="messages", type="object", description="Validation error messages"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="התרחשה תקלה בשרת. נסה שוב מאוחר יותר."),
     *          ),
     *      ),
     * )
     */

    //? route for admin - to allocate records based on order_number.
    public function allocationRecords(AllocationDistributionRequest $request)
    {
        try {



            $result = $this->_distributionService->allocationRecords($request);


            // Use match to handle different status cases
            return match ($result['status']) {

                Status::CREATED => response()->json(['message' => $result['message']], Response::HTTP_CREATED),

                Status::OK => response()->json(['message' => $result['message']], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };



        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of any error
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @OA\Put(
     *     path="/api/distributions/change-status/{id}",
     *     summary="Change the status of a distribution",
     *     description="This endpoint allows you to change the status of a distribution.",
     *     operationId="changeStatus",
     *     tags={"Distributions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 description="The status of the distribution",
     *                 example=1
     *             ),
     *            @OA\Property(
     *                 property="order_number",
     *                 type="integer",
     *                 description="The status of the distribution",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="quartermaster_comment",
     *                 type="string",
     *                 description="Comment from the quartermaster",
     *                 example="Cancelled due to unavailability"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Distribution status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="שורה התעדכנה בהצלחה."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="יש לשלוח מספר מזהה של שורה."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="messages",
     *                 type="object",
     *                 example={"status": {"חובה לשלוח שדה סטטוס לעידכון."}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר."
     *             )
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */



    public function changeStatus(ChangeStatusDistributionRequest $request)
    {
        try {


            $result = $this->_distributionService->changeStatus($request);


            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json(['message' => $result['message']], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::UNPROCESSABLE_ENTITY => response()->json(['message' => $result['message']], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),

            };



        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of any error
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Get distributions records by query.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/distributions/search-by-query",
     *     summary="Get distributions records by query",
     *     tags={"Distributions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"query"},
     *             @OA\Property(property="query", type="string", example="Pending")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="comment", type="string", example="Velit veritatis quia vel nemo qui. Eaque commodi expedita enim libero ut. Porro ducimus repellendus tenetur."),
     *              @OA\Property(property="status", type="integer", example=1),
     *              @OA\Property(property="quantity", type="integer", example=44),
     *              @OA\Property(property="inventory_id", type="integer", example=24),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(
     *                  property="inventory",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=24),
     *                  @OA\Property(property="quantity", type="integer", example=10),
     *                  @OA\Property(property="sku", type="string", example="1359395842801"),
     *                  @OA\Property(property="item_type", type="string", example="magni"),
     *                  @OA\Property(property="detailed_description", type="string", example="Velit ut ipsam neque tempora est dicta. Et distinctio eligendi expedita corporis assumenda aspernatur hic.")
     *              ),
     *              @OA\Property(
     *                  property="created_for_user",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Percival Schulist"),
     *                  @OA\Property(property="emp_type_id", type="integer", example=2),
     *                  @OA\Property(property="phone", type="string", example="0556926412"),
     *                  @OA\Property(
     *                      property="employee_type",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="name", type="string", example="miluim")
     *                  )
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid search value")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Server error occurred")
     *         )
     *     )
     * )
     */

    public function getRecordsByQuery(Request $request)
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


            $result = $this->_distributionService->getRecordsByQuery($request);


            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::UNPROCESSABLE_ENTITY => response()->json(['message' => $result['message']], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };



        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Retrieve all distributions group by order_number fileds.
     *
     * This endpoint retrieves all distribution records along with their associated inventory and department.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/distributions/fetch-records-by-order",
     *     summary="Retrieve all distributions group by order_number fileds",
     *     tags={"Distributions"},
     *      summary="Get all Distributions group by order_number",
     *      description="Returns a list of all Distributions.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="integer", example=2)
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="comment", type="string", example="Velit veritatis quia vel nemo qui. Eaque commodi expedita enim libero ut. Porro ducimus repellendus tenetur."),
     *              @OA\Property(property="status", type="integer", example=1),
     *              @OA\Property(property="quantity", type="integer", example=44),
     *              @OA\Property(property="inventory_id", type="integer", example=24),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(
     *                  property="inventory",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=24),
     *                  @OA\Property(property="quantity", type="integer", example=10),
     *                  @OA\Property(property="sku", type="string", example="1359395842801"),
     *                  @OA\Property(property="item_type", type="string", example="magni"),
     *                  @OA\Property(property="detailed_description", type="string", example="Velit ut ipsam neque tempora est dicta. Et distinctio eligendi expedita corporis assumenda aspernatur hic.")
     *              ),
     *              @OA\Property(
     *                  property="created_for_user",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Percival Schulist"),
     *                  @OA\Property(property="emp_type_id", type="integer", example=2),
     *                  @OA\Property(property="phone", type="string", example="0556926412"),
     *                  @OA\Property(
     *                      property="employee_type",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="name", type="string", example="miluim"),
     *                      @OA\Property(property="population", type="string", example="מילואים")
     *                  )
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *         )
     *     )
     * )
     */

     //? group-by records  by order_number
    public function fetchDistributionsRecordsByOrderNumber(Request $request)
    {
        try {


            // set custom error messages in Hebrew
            $customMessages = [
                'status.required' => 'יש לשלוח שדה לחיפוש',
                'order_number.integer' => 'ערך השדה שנשלח אינו תקין.',
                'order_number.between' => 'ערך השדה שנשלח אינו תקין.',
                'query.string' => 'שדה חיפוש אינו תקין.',
                'query.min' => 'שדה חיפוש אינו תקין.',
                'query.max' => 'שדה חיפוש אינו תקין.',
            ];

            //set the rules
            $rules = [
                'status' => 'required|integer|between:1,4',
                'query' => 'nullable|string|min:1|max:255',
            ];


            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            $result = $this->_distributionService->getRecordsByQuery($request);


            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::UNPROCESSABLE_ENTITY => response()->json(['message' => $result['message']], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };



        } catch (\Exception $e) {

            Log::error($e->getMessage());

        }

        return response()->json(['message' => 'התרחש בעיית שרת.נסה שוב מאוחר יותר'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Get distributions records by query.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/distributions/search-by-order",
     *     summary="Get distributions records by order_numbe fileds",
     *     tags={"Distributions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_number"},
     *             @OA\Property(property="order_number", type="string", example="425134")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="comment", type="string", example="Velit veritatis quia vel nemo qui. Eaque commodi expedita enim libero ut. Porro ducimus repellendus tenetur."),
     *              @OA\Property(property="status", type="integer", example=1),
     *              @OA\Property(property="quantity", type="integer", example=44),
     *              @OA\Property(property="inventory_id", type="integer", example=24),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *              @OA\Property(
     *                  property="inventory",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=24),
     *                  @OA\Property(property="quantity", type="integer", example=10),
     *                  @OA\Property(property="sku", type="string", example="1359395842801"),
     *                  @OA\Property(property="item_type", type="string", example="magni"),
     *                  @OA\Property(property="detailed_description", type="string", example="Velit ut ipsam neque tempora est dicta. Et distinctio eligendi expedita corporis assumenda aspernatur hic.")
     *              ),
     *              @OA\Property(
     *                  property="created_for_user",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Percival Schulist"),
     *                  @OA\Property(property="emp_type_id", type="integer", example=2),
     *                  @OA\Property(property="phone", type="string", example="0556926412"),
     *                  @OA\Property(
     *                      property="employee_type",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="name", type="string", example="miluim")
     *                  )
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid search value")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Server error occurred")
     *         )
     *     )
     * )
     */

    public function getRecordsByOrder(Request $request)
    {
        try {

            // set custom error messages in Hebrew
            $customMessages = [
                'order_number.required' => 'יש לשלוח שדה לחיפוש',
                'order_number.string' => 'ערך השדה שנשלח אינו תקין.',
                'order_number.exists' => 'מספר הזמנה אינה קיימת.',
            ];

            //set the rules
            $rules = [
                'order_number' => 'required|string|exists:distributions,order_number,is_deleted,0',
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            $result = $this->_distributionService->getRecordsByOrder($request);


            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::UNPROCESSABLE_ENTITY => response()->json(['message' => $result['message']], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };



        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Get distributions records by filter.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/distributions/search-by-filter",
     *     summary="Get distributions records by filter",
     *     tags={"Distributions"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="inventory_id", type="string", example="1"),
     *             @OA\Property(property="status", type="integer", example="1"),
     *             @OA\Property(property="year", type="integer", example="2017"),
     *             @OA\Property(property="department_id", type="string", example="2"),
     *             @OA\Property(property="user_id", type="string", example="3"),
     *             @OA\Property(property="created_at", type="string", format="date", example="2023-05-01"),
     *             @OA\Property(property="updated_at", type="string", format="date", example="2023-05-10"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_number", type="integer", example=5698231),
     *                 @OA\Property(property="inventory_comment", type="string", example="Voluptates officia accusamus autem ex."),
     *                 @OA\Property(property="general_comment", type="string", example="Laborum tempora voluptatum repellendus."),
     *                 @OA\Property(property="status", type="integer", example=0),
     *                 @OA\Property(property="quantity", type="integer", example=21),
     *                 @OA\Property(property="inventory_id", type="integer", example=78),
     *                 @OA\Property(property="department_id", type="integer", example=9),
     *                 @OA\Property(property="created_by", type="integer", example=2),
     *                 @OA\Property(property="created_for", type="integer", example=2),
     *                 @OA\Property(property="created_at_date", type="string", example="09/05/2024"),
     *                 @OA\Property(property="updated_at_date", type="string", example="09/05/2024"),
     *                 @OA\Property(
     *                     property="inventory",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=78),
     *                     @OA\Property(property="quantity", type="integer", example=83),
     *                     @OA\Property(property="sku", type="string", example="8918225192276"),
     *                     @OA\Property(
     *                         property="item_type",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="type", type="string", example="computer"),
     *                         @OA\Property(property="icon_number", type="string", example="1")
     *                     ),
     *                     @OA\Property(property="detailed_description", type="string", example="Nostrum culpa sit blanditiis suscipit placeat eum. Amet aspernatur est et beatae eum aut culpa atque. Amet iusto quaerat nihil enim sed voluptatem reiciendis."),
     *                 ),
     *                 @OA\Property(
     *                     property="department",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=9),
     *                     @OA\Property(property="name", type="string", example="ullam"),
     *                 ),
     *                 @OA\Property(
     *                     property="created_for_user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Cydney Schroeder"),
     *                     @OA\Property(property="emp_type_id", type="integer", example=3),
     *                     @OA\Property(property="phone", type="string", example="0580148483"),
     *                     @OA\Property(
     *                         property="employee_type",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=3),
     *                         @OA\Property(property="name", type="string", example="sadir"),
     *                     ),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid search value")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Server error occurred")
     *         )
     *     )
     * )
     */

   
    public function getRecordsByFilter(Request $request)
    {
        try {




            // set validation rules
            $rules = [
                // 'inventory_id' => 'nullable|string|max:255|exists:inventories,id,is_deleted,0',

                'status' => 'nullable|integer|between:1,4',

                'department_id' => 'nullable|string|exists:departments,id,is_deleted,0',

                'order_number' => 'nullable|string|exists:distributions,order_number,is_deleted,0',

                'clients_id' => 'nullable|array',
                'clients_id.*' => 'nullable|exists:clients,id,is_deleted,0',

                'year' => 'nullable|integer|between:1948,2099',

                'created_at' => ['nullable', 'date'],

                'updated_at' => ['nullable', 'date'],
            ];

            // Define custom error messages
            $customMessages = [
                'clients_id.array' => 'שדה משתמש שנשלח אינו תקין.',
                'clients_id.*.exists' => 'הערך שהוזן לא חוקי.',

                'year.integer' => 'שדה שנה אינו תקין.',
                'year.between' => 'שדה שנה אינו תקין.',

                'department_id.exists' => 'מחלקה אינה קיימת במערכת.',

                'order_number.exists' => 'מספר הזמנה אינה קיית במערכת.',

                'status.between' => 'שדה הסטטוס אינו תקין.',

                'created_at.date' => 'שדה תאריך התחלה אינו תקין.',
                'created_at.exists' => 'שדה תאריך אינו קיים במערכת.',
                'updated_at.date' => 'שדה תאריך סיום אינו תקין.',
                'updated_at.exists' => 'שדה תאריך סיום אינו קיים במערכת.',
            ];

            // validate the request with custom error messages
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }




            $result = $this->_distributionService->getRecordsByFilter($request);


            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::UNPROCESSABLE_ENTITY => response()->json(['message' => $result['message']], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };


        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }




    /**
     * fetch distributions records based on sort query and paginate.
     **/

    public function sortByQuery(Request $request)
    {
        try {
            // Define the fields that are allowed to be sorted by
            $sortableFields = ['order_number', 'year', 'type_id', 'department_id', 'created_at'];

            // Define validation rules
            $rules = [
                'sort' => 'required|array',
                'sort.*.field' => 'required|string|in:' . implode(',', $sortableFields),
                'sort.*.direction' => 'required|string|in:asc,desc',
            ];

            // Define custom error messages
            $messages = [
                'sort.required' => 'יש לשלוח שדה למיון.',
                'sort.array' => 'ערך שדה למיון אינו נשלח בצורה תקינה.',
                'sort.*.field.required' => 'יש לשלוח שדות למיון.',
                'sort.*.field.string' => 'ערכי שדות למיון לא נשלחו בצורה תקינה.',
                'sort.*.direction.required' => 'יש לבחור סדר מיון שורות.',
                'sort.*.direction.string' => 'ערך שדה מיון שורות אינו נשלח בצורה תקינה.',
                'sort.*.direction.in' => 'ערך שדה מיון שורות אינו נשלח בצורה תקינה.',
            ];

            // validate the request with custom error messages
            $validator = Validator::make($request->all(), $rules, $messages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $result = $this->_distributionService->sortByQuery($request);


            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::UNPROCESSABLE_ENTITY => response()->json(['message' => $result['message']], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };


        } catch (\Exception $e) {

            Log::error($e->getMessage());

        }


        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

  
}
