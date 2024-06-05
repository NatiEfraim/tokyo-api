<?php

namespace App\Http\Controllers;

use App\Enums\EmployeeType;

use App\Enums\DistributionStatus;
use App\Http\Requests\StoreDistributionRequest;
use App\Http\Requests\UpdateDistributionRequest;
use App\Models\Client;
use App\Models\Distribution;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

// use Illuminate\Validation\Rule;
// use Illuminate\Support\Str;
// use App\Models\Department;
// use App\Models\User;
// use App\Models\Inventory;

class DistributionController extends Controller
{
    //

    const MIN_LEN = 1;
    const MAX_LEN = 7;



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


            $distributions = Distribution::with(['itemType','inventory','department','createdForUser', 'quartermaster'])
                ->where('is_deleted', 0)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $distributions->each(function ($distribution) {
                // Format the created_at and updated_at timestamps
                $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
                $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');

                return $distribution;
            });

            return response()->json($distributions, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    //? fetch associated quartermaster
    public function fetchQuartermaster($id=null)
    {
        try {

            if (is_null($id)) {
                return response()->json(['message' => 'יש לשלוח מזהה שורה .'], Response::HTTP_BAD_REQUEST);
            }

            // Fetch distribution by ID
            $distribution = Distribution::with(['quartermaster'])
            ->where('id', $id)
            ->where('is_deleted', 0)
            ->first();


            if (is_null($distribution) || is_null($distribution->quartermaster_id)) {
                return response()->json(['message' => 'הזמנה זו אינה קיימת במערכת.'], Response::HTTP_BAD_REQUEST);
            }

            // Format date and time
            $createdAt = $distribution->updated_at->format('H:i:s'); // Time
            $createdAtDate = $distribution->updated_at->format('d/m/Y'); // Date

            // Extract user data
            $quartermasterName = $distribution->quartermaster->name;
            $quartermasterId = $distribution->quartermaster->id;

            // Prepare response data
            $responseData = [
                'quartermaster_name' => $quartermasterName,
                'quartermaster_id' => $quartermasterId,
                'created_at_time' => $createdAt,
                'created_at_date' => $createdAtDate
            ];

            return response()->json($responseData, Response::HTTP_OK);


        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    /**
     * Retrieve all distributions.
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





    //? fetch records for only records that has been approved
    public function fetchApprovedDistribution(Request $request)
    {



        try {


            // set validation rules
            $rules = [


                'inventory_id' => 'nullable|string|max:255|exists:inventories,id,is_deleted,0',

                'status' => 'nullable|integer|between:0,3',

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

            if (
                $request->has('clients_id')
                || $request->has('year')  ||
                $request->has('status') ||
                $request->has('order_number') ||
                $request->has('inventory_id') ||
                $request->has('department_id') ||
                $request->has('created_at') ||
                $request->has('updated_at')
            ) {
                //? search records by filter
                $distributions = $this->fetchDistributionsByFilter($request);//use private function to fillter records based on filter input
            } else {

                // Fetch records with associated relations and conditions
                $distributions = Distribution::with(['createdForUser', 'itemType','department'])
                ->where('is_deleted', 0)
                ->where('status', DistributionStatus::APPROVED->value)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            }


            // $distributions->makeHidden(['department_id', 'year', 'quartermaster_comment']);

            // Loop through each record and add inventory_items object
            $distributions->transform(function ($distribution) {
                $inventoryItems = json_decode($distribution->inventory_items, true);

                // If inventory_items is not null, process it
                if ($inventoryItems) {
                $inventoryItems = array_map(function ($item) {
                    return [
                        'sku' => $item['sku'],
                        'quantity' => $item['quantity'],
                    ];
                }, $inventoryItems);
            }

            $distribution->inventory_items = $inventoryItems;

                //?format each date.
                $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
                $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');

            return $distribution;
        });

        return response()->json($distributions, Response::HTTP_OK);

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
            if (is_null($id)) {
                return response()->json(['message' => 'יש לשלוח מספר מזהה של שורה'], Response::HTTP_BAD_REQUEST);
            }

            $distribution = Distribution::with(['itemType','createdForUser'])
                ->where('id', $id)
                ->where('is_deleted', 0)
                ->first();

            return response()->json($distribution, Response::HTTP_OK);
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
            if (is_null($id)) {
                return response()->json(['message' => 'יש לשלוח מספר מזהה של שורה'], Response::HTTP_BAD_REQUEST);
            }

            $distirbution = Distribution::where('is_deleted', 0)->where('id', $id)->first();
            if (is_null($distirbution)) {
                return response()->json(['message' => 'שורה אינה קיימת במערכת.'], Response::HTTP_BAD_REQUEST);
            }
            $distirbution->update([
                'is_deleted' => true,
            ]);
            return response()->json(['message' => 'שורה נמחקה בהצלחה.'], Response::HTTP_OK);
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



            DB::beginTransaction();

            $user_auth = Auth::user();

            //? create new clients records. - and get the client_id


            //casting the value.
            $emp_type = (int) $request->input('employee_type');


            //set the first letter for the persnal_number
            $personal_number = match ($emp_type) {
                EmployeeType::KEVA->value, EmployeeType::SADIR->value => 's' . $request->input('personal_number'),
                EmployeeType::MILUIM->value => 'm' . $request->input('personal_number'),
                EmployeeType::OVED_TZAHAL->value => 'c' . $request->input('personal_number'),
                default => throw new \InvalidArgumentException('סוג עובד לא תקין.')
            };


            $client=Client::where('personal_number',$request->input('personal_number'))
            ->where('is_deleted',1)->first();

            if($client)
            {
                //? update client that has been deleted
               $client->update([
                    'name' => $request->input('name'),
                   'personal_number' => $request->input('personal_number'),
                   'email' => "{$personal_number}@army.idf.il",
                   'phone' => $request->input('phone'),
                   'emp_type_id' =>  $request->input('employee_type'),
                   'department_id' => $request->input('department_id'),
                   'is_deleted' => '0',

               ]);
            }

            $client=Client::where('personal_number',$request->input('personal_number'))
            ->where('is_deleted',0)
            ->first();

             if(is_null($client)){

                //? update client records - from scratch
                $client = Client::create([
                    'name' => $request->input('name'),
                   'personal_number' => $request->input('personal_number'),
                   'email' => "{$personal_number}@army.idf.il",
                   'phone' => $request->input('phone'),
                   'emp_type_id' =>  $request->input('employee_type'),
                   'department_id' => $request->input('department_id'),

                ]);
            }

            // Fetch all existing order numbers
            $existingOrderNumbersQuery = Distribution::pluck('order_number');

            // Generate a unique 7-digit order number
            do {
                $orderNumber = random_int(1000000, 9999999); // Generates a random integer between 1000000 and 9999999
            } while ($existingOrderNumbersQuery->contains($orderNumber));

            $orderNumber = (int)$orderNumber; // Cast to integer


            // Get the current year
            $currentYear = Carbon::now()->year;

            $allQuantity = array_sum(array_column($request->input('items'), 'quantity'));


            foreach ($request->input('items') as $item) {
                $itemType = $item['type_id'];
                $quantity = $item['quantity'];
                $comment = $item['comment'] ?? null;



                Distribution::create([
                    'order_number' => intval($orderNumber),
                    'user_comment' => $request->input('user_comment') ?? null,
                    'type_comment' => $comment??null,
                    'total_quantity' => $allQuantity,
                    'quantity_per_item' => $quantity,
                    'status' => DistributionStatus::PENDING->value,
                    'type_id' => $itemType,
                    'year' => $currentYear,
                    'department_id' => $request->input('department_id'),
                    'created_by' => $user_auth->id,
                    'created_for' => $client->id,
                ]);
            }



            DB::commit();

            return response()->json(['message' => 'שורה נוצרה בהצלחה.'], Response::HTTP_CREATED);
        } catch (\Exception $e) {

            DB::rollBack(); // Rollback the transaction in case of any error
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Mass delete distributions.
     *
     * This endpoint deletes multiple distribution records based on the provided IDs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path="/api/distributions/mass-destroy",
     *     summary="Mass delete distributions",
     *     tags={"Distributions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"distributions"},
     *             @OA\Property(property="distributions", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id"},
     *                     @OA\Property(property="id", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="שורות נמחקו בהצלחה.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="messages", type="object",
     *                 @OA\Property(property="distributions", type="array",
     *                     @OA\Items(type="string", example="יש לשלוח שורות למחיקה."),
     *                     @OA\Items(type="string", example="שורות אינם בפורמט תקין."),
     *                     @OA\Items(type="string", example="שדה המזהה חובה."),
     *                     @OA\Items(type="string", example="אחת מהשדות שנשלחו אינו תקין."),
     *                     @OA\Items(type="string", example="המזהה שנבחר לא קיים או שהמשימה נמחקה.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *         )
     *     )
     * )
     */

    public function massDestroy(Request $request)
    {
        try {
            // set custom error messages in Hebrew
            $customMessages = [
                'distributions.required' => 'יש לשלוח שורות למחיקה.',
                'distributions.array' => 'שורות אינם בפורמט תקין.',
                'distributions.*.id.required' => 'שדה המזהה חובה.',
                'distributions.*.id.integer' => 'אחת מהשדות שנשלחו אינו תקין.',
                'distributions.*.id.exists' => 'המזהה שנבחר לא קיים או שהמשימה נמחקה.',
            ];
            //set the rules
            $rules = [
                'distributions' => 'required|array',
                'distributions.*.id' => 'required|integer|exists:distributions,id,is_deleted,0',
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $distributions = $request->input('distributions');
            $ids = collect($distributions)->pluck('id')->toArray();

            // Update the 'is_deleted' column to 1 for the distributions with the given IDs
            Distribution::whereIn('id', $ids)->update(['is_deleted' => 1]);

            return response()->json(['message' => 'שורות נמחקו בהצלחה.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
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
    public function allocationRecords(Request $request)
    {

        try {



            // set custom error messages in Hebrew
            $customMessages = [

                'status.required' => 'חובה לשלוח שדה סטטוס לעידכון.',
                'status.integer' => 'שדה סטטוס שנשלח אינו בפורמט תקין.',
                'status.between' => 'ערך הסטטוס שנשלח אינו תקין.',

                'admin_comment.string' => 'אחת מהשדות שנשלחו אינם תקינים.',
                'admin_comment.required' => '.',
                'admin_comment.min' => 'אחת מהשדות שנשלחו אינם תקינים.',
                'admin_comment.max' => 'אחת מהשדות שנשלחו אינם תקינים.',

                'inventory_items.array' => 'נתון שנשלח אינו תקין.',
                'inventory_items.*.inventory_id.required' => 'חובה לשלוח מזהה פריט במערך הפריטים.',
                'inventory_items.*.inventory_id.exists' => 'מזהה הפריט שנשלח במערך אינו קיים או נמחק.',
                'inventory_items.*.quantity.required' => 'חובה לשלוח כמות לכל פריט במערך.',
                'inventory_items.*.quantity.integer' => 'הכמות שנשלחה עבור פריט במערך אינה בפורמט תקין.',
                'inventory_items.*.quantity.min' => 'הכמות שנשלחה עבור פריט במערך חייבת להיות גדולה או שווה ל-0.',

                'order_number.required' => 'חובה לשלוח מספר הזמנה.',
                'order_number.integer' => 'אחת מהשדות שנשלחו אינם תקינים.',
                'order_number.exists' => 'מספר הזמנה אינה קיימת במערכת.',

            ];

            //set the rules
            $rules = [

                'status' => 'required|integer|between:1,2',

                'admin_comment' => 'nullable|string|min:2|max:255',

                'inventory_items' => 'nullable|array',

                'inventory_items.*.type_id' => 'required|exists:item_types,id,is_deleted,0',

                'inventory_items.*.items' => 'required|array',

                'inventory_items.*.items.*.inventory_id' => 'required|exists:inventories,id,is_deleted,0',

                'inventory_items.*.items.*.quantity' => 'required|integer|min:0',

                'order_number' => 'nullable|integer|exists:distributions,order_number,is_deleted,0',

            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (is_null($request->input('admin_comment')) && $request->input('status') == DistributionStatus::CANCELD->value) {
                return response()->json(['message' => 'חובה לשלוח סיבת ביטול.'], Response::HTTP_BAD_REQUEST);
            }

            if (is_null($request->input('inventory_items')) && $request->input('status') == DistributionStatus::APPROVED->value) {
                return response()->json(['message' => 'חובה לשלוח סיבת ביטול.'], Response::HTTP_BAD_REQUEST);
            }



            // Fetch the records with the given order_number and is_deleted is false
            $distributionRecords = Distribution::where('order_number', $request->input('order_number'))
            ->where('is_deleted', false)
            ->get();



            // Check if records exist
            if ($distributionRecords->isEmpty()) {
                return response()->json(['message' => 'לא נמצאו רשומות עם מספר הזמנה זה במערכת.'], Response::HTTP_BAD_REQUEST);
            }


            $statusValue = (int) $request->input('status');
            $statusValue = match ($statusValue) {

                DistributionStatus::APPROVED->value => 1,

                DistributionStatus::CANCELD->value => 2,

                default => throw new \InvalidArgumentException('ערך סטטוס אינו תקין..'),
            };


            $currentTime = Carbon::now()->toDateTimeString();

            DB::beginTransaction(); // Start a database transaction

            //? distribution records has been approved

            if ($statusValue == DistributionStatus::APPROVED->value) {

                // Track processed type_ids
                $processedTypeIds = [];
                // Loop through each type_id in the request
                foreach ($request->input('inventory_items') as $key => $items) {


                    // Skip if this type_id has already been processed
                    if (in_array($items['type_id'], $processedTypeIds)) {
                        continue;
                    }


                    // Find the first distribution record with the matching type_id that has not been processed
                    $distributionRecord = $distributionRecords->firstWhere('type_id', $items['type_id']);


                    if ($distributionRecord) {
                        $inventoryUpdates = []; // To store updated inventory items


                        // Loop on each item within the type_id
                        foreach ($items['items'] as $inventoryItem) {


                            $idInventory = $inventoryItem['inventory_id']; // Save the inventory_id records
                            $quantity = $inventoryItem['quantity'];


                            $inventory = Inventory::where('id', $idInventory)
                            ->where('is_deleted', false)
                                ->first();


                            if(  (is_null($inventory)) || ($inventory->type_id!== $items['type_id']) )
                            {
                                DB::rollBack(); // Rollback the transaction
                                return response()->json(['message' => 'אחד מהפרטים שבמלאי שנשלחו אינם תקינים.'], Response::HTTP_BAD_REQUEST);
                            }

                            $available = $inventory->quantity - $inventory->reserved;

                            if ($quantity > $available) {
                                DB::rollBack(); // Rollback the transaction
                                return response()->json(['message' => 'כמות שנשלח עבור ' . $inventory->sku . ' חסרה במלאי.'], Response::HTTP_OK);
                            }

                            // Update inventory records based on inventory_id
                            $inventory->update([
                                'reserved' => $inventory->reserved + $quantity, // Increase the reserved
                                'updated_at' => $currentTime,
                            ]);

                            // Add to the list of inventory updates
                            $inventoryUpdates[] = [
                                'sku' => $inventory->sku,//save sku
                                'quantity' => $quantity,//save qty
                            ];

                            ///here need to created a new distributions records - with the same values fileds - nd set relation with the invetory_id - that Liran choosd
                            
                        }

                        // Update the distribution record with the updated inventory items - to to set that deleted.
                        $distributionRecord->update([
                            'status' => $statusValue,
                            'admin_comment' => $request->input('admin_comment'),
                            'inventory_items' => json_encode($inventoryUpdates), // Save the inventory items
                            'updated_at' => $currentTime,
                        ]);

                        // Mark this type_id as processed
                        $processedTypeIds[] = $items['type_id'];
                    }
                }


                //? distribution records has been canceld
            } elseif ($statusValue == DistributionStatus::CANCELD->value) {

                //? Loop through each record and update the fields
                foreach ($distributionRecords as $distributionRecord) {
                    $distributionRecord->update([
                        'status' => $statusValue,
                        'admin_comment' => $request->input('admin_comment') ?? null,
                        'updated_at' => $currentTime,
                    ]);


            }
        }


            DB::commit(); // commit all changes in database.

            return response()->json(['message' => 'שורה התעדכנה בהצלחה.'], Response::HTTP_OK);
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

    public function changeStatus(Request $request)
    {
        try {

            $user = auth()->user();

            // set custom error messages in Hebrew
            $customMessages = [

                'status.required' => 'חובה לשלוח שדה סטטוס לעידכון.',
                'status.integer' => 'שדה סטטוס שנשלח אינו בפורמט תקין.',
                'status.between' => 'ערך הסטטוס שנשלח אינו תקין.',

                'quartermaster_comment.string' => 'אחת מהשדות שנשלחו אינם תקינים.',
                'quartermaster_comment.required' => 'אחת מהשדות שנשלחו אינם תקינים.',
                'quartermaster_comment.min' => 'אחת מהשדות שנשלחו אינם תקינים.',
                'quartermaster_comment.max' => 'אחת מהשדות שנשלחו אינם תקינים.',

                'order_number.required' => 'חובה לשלוח מספר הזמנה.',
                'order_number.integer' => 'אחת מהשדות שנשלחו אינם תקינים.',
                'order_number.exists' => 'מספר הזמנה אינה קיימת במערכת.',

            ];

            //set the rules
            $rules = [

                'status' => 'required|integer|between:0,3',
                'quartermaster_comment' => 'required|string|min:2|max:255',
                'order_number' => 'required|integer|exists:distributions,order_number,is_deleted,0',

            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            if ($request->input('status')!==DistributionStatus::PENDING->value && $request->input('status')!==DistributionStatus::COLLECTED->value) {
                return response()->json(['message' => 'ערך סטטוס אינו תקין.'], Response::HTTP_BAD_REQUEST);
            }

            if (is_null($request->input('quartermaster_comment')) && $request->input('status')==DistributionStatus::PENDING->value) {
                return response()->json(['message' => 'יש לשלוח הערה על ההזמנה למנהל.'], Response::HTTP_BAD_REQUEST);
            }


            // Fetch the records with the given order_number and is_deleted is false
            $distributionRecords = Distribution::where('order_number', $request->input('order_number'))
                ->where('is_deleted', false)
                ->get();


            // Check if records exist
            if ($distributionRecords->isEmpty()) {
                return response()->json(['message' => 'לא נמצאו רשומות עם מספר הזמנה זה במערכת.'], Response::HTTP_BAD_REQUEST);
            }

            $statusValue = (int) $request->input('status');
            $statusValue = match ($statusValue) {
                DistributionStatus::PENDING->value => 0,
                DistributionStatus::COLLECTED->value => 3,

                default => throw new \InvalidArgumentException('ערך סטטוס אינו תקין..'),
            };



            $currentTime = Carbon::now()->toDateTimeString();

            // Loop through each record and update the fields
            foreach ($distributionRecords as $distributionRecord) {
                $distributionRecord->update([
                'status' => $statusValue,
                'quartermaster_id' => $user->id,///save the user that sign on that order_number
                'quartermaster_comment' => $request->input('quartermaster_comment'),//can be a comment or Reference Number
                'updated_at' => $currentTime,
            ]);
    }


            return response()->json(['message' => 'שורה התעדכנה בהצלחה.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }




    /**
     * @OA\Put(
     *     path="/api/distribution/{id}",
     *     tags={"Distributions"},
     *     summary="Update distribution record",
     *     description="Update an existing distribution record by providing the ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the distribution record to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Updated distribution record data",
     *         @OA\JsonContent(
     *             required={"comment"},
     *             @OA\Property(
     *                 property="comment",
     *                 type="string",
     *                 example="This is an updated comment"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="quantity",
     *                 type="integer",
     *                 example=10
     *             ),
     *             @OA\Property(
     *                 property="inventory_id",
     *                 type="integer",
     *                 example=123
     *             ),
     *             @OA\Property(
     *                 property="department_id",
     *                 type="integer",
     *                 example=456
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success: Distribution record updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="שורה התעדכנה בהצלחה."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request: Missing or invalid input"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */

     //? that function route for quartermaster - to update of collected or baeck to admin for approved
    public function update(UpdateDistributionRequest $request, $id = null)
    {
        try {

            if (is_null($id)) {

                return response()->json(['message' => 'יש לשלוח מספר מזהה של שורה'], Response::HTTP_BAD_REQUEST);
            }

            //? fetch distributions records based id.
            $distribution = Distribution::where('is_deleted', 0)->where('id', $id)->first();

            if (is_null($distribution)) {
                return response()->json(['message' => 'שורה אינה קיימת במערכת.'], Response::HTTP_BAD_REQUEST);
            }

            //? fetch inventory associated records based distrbution->inventory_id
            $inventory = Inventory::where('id', $distribution->inventory_id)
                ->where('is_deleted', false)
                ->first();

            if (is_null($inventory)) {
                return response()->json(['message' => 'פריט אינו קיים במלאי'], Response::HTTP_BAD_REQUEST);
            }

            if ($request->input('quantity') && $request->input('quantity') > $inventory->quantity - $inventory->reserved && $request->input('quantity') > $distribution->quantity) {
                return response()->json(['message' => 'אין מספיק מלאי זמין עבור כמות שנשלחה .'], Response::HTTP_BAD_REQUEST);
            }

            $currentTime = Carbon::now()->toDateTimeString();

            DB::beginTransaction(); // Start a database transaction

            //? updated all fileds for distribution record

            $distribution->update($request->validated());

            DB::commit(); // commit all changes in database.

            return response()->json(['message' => 'שורה התעדכנה בהצלחה.'], Response::HTTP_OK);
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

            //? one or more of th search based on value filter send
            $distributions = $this->fetchDistributions($request); ///private function

            if ($distributions) {
                $distributions->map(function ($distribution) {
                    $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
                    $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');

                    return $distribution;
                });
            }

            return response()->json($distributions->isEmpty() ? [] : $distributions, Response::HTTP_OK);
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

                'status' => 'required|integer|between:0,3',
                'query' => 'nullable|string|min:1|max:255',

            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($request->input('query')) {


                //? search records based on query and given status
                $distributions = $this->fetchDistributionsByStatus($request); ///private function

            }else{
                //? fetch all records without any query to search
                $distributions = Distribution::with(['itemType', 'createdForUser','department'])
                    ->where('status', $request->input('status'))
                    ->where('is_deleted', 0)
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);
            }



            $distributions->each(function ($distribution) {
                // Format the created_at and updated_at timestamps
                $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
                $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');

                return $distribution;
            });





            // Create a new collection to store unique distributions by order_number
            $uniqueDistributions = collect();



            // Create a set to track seen order_numbers
            $seenOrderNumbers = [];

            // Loop through the fetched distributions
            foreach ($distributions as $distribution) {
                // make sure the order_number has been seen before
                if (!in_array($distribution->order_number, $seenOrderNumbers)) {
                    $uniqueDistributions->push($distribution);
                    // Mark this order_number as seen
                    $seenOrderNumbers[] = $distribution->order_number;
                }
            }

            return response()->json($uniqueDistributions, Response::HTTP_OK);


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

            //? fetch all distribution records based on order_number
            $distributions= Distribution::with(['itemType',  'createdForUser','department'])
                ->where('order_number', $request->input('order_number'))
                ->where('is_deleted',0)
                ->get();

            return response()->json($distributions->isEmpty() ? [] : $distributions, Response::HTTP_OK);
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

    //? fetch distributions records - based on filter
    public function getRecordsByFilter(Request $request)
    {



        try {



            // set validation rules
            $rules = [


                'inventory_id' => 'nullable|string|max:255|exists:inventories,id,is_deleted,0',

                'status' => 'nullable|integer|between:0,3',

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



            if (
                $request->has('clients_id')
                || $request->has('year')
                || $request->has('status')
                || $request->has('order_number')
                || $request->has('inventory_id')
                || $request->has('department_id')
                || $request->has('created_at')
                || $request->has('updated_at')
            ) {
                //? one or more of th search based on value filter send

                $distributions = $this->fetchDistributionsByFilter($request);

                if ($distributions) {
                    $distributions->map(function ($distribution) {
                        //? format date on each records
                        $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
                        $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');

                        return $distribution;
                    });
                }
            } else {
                //? fetch all distributions records.

                $distributions = Distribution::with(['department', 'createdForUser', 'inventory','itemType','quartermaster'])
                ->where('is_deleted', 0)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($distribution) {
                        // Format the created_at and updated_at timestamps
                        $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
                        $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');

                        return $distribution;
                    });
            }

            return response()->json($distributions->isEmpty() ? [] : $distributions, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    //? fetch distributions records based on sort query and paginate
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

            // fetch all distributions records with associations
            $distributions = Distribution::with(['itemType', 'createdForUser','department'])
            ->where('is_deleted', 0)
            ->get();

            //? format date fileds
            $distributions->each(function ($distribution) {
                $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
                $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');
                return $distribution;
            });

            //? decode - json of invetory_item fileds
            $distributions->transform(function ($distribution) {
                $inventoryItems = json_decode($distribution->inventory_items, true);

                if ($inventoryItems) {
                    $inventoryItems = array_map(function ($item) {
                        return [
                            'sku' => $item['sku'],
                            'quantity' => $item['quantity'],
                        ];
                    }, $inventoryItems);
                }

                $distribution->inventory_items = $inventoryItems;
                return $distribution;
            });

            // Get sorting parameters from the request
            $sortParams = $request->input('sort', []);

            // Apply multiple sorting parameters
            if (!empty($sortParams)) {
                $distributions = $distributions->sortBy(function ($distribution) use ($sortParams) {
                    $sortValues = [];

                    foreach ($sortParams as $sort) {
                        $sortField = $sort['field'];
                        if ($sortField== 'order_number') {
                            //? sort the records by order_number fileds
                            $sortValues[] = $distribution->order_number;

                        } else if($sortField == 'year') {
                            //? sort by year
                            $sortValues[] = $distribution->year;
                        }else if($sortField == 'type_id') {
                            //? sort the records by type of item_types associated records.
                            $sortValues[] = $distribution->itemType->type ?? '';

                        }elseif($sortField == 'department_id'){
                            //? sort by department name associated by department_id
                            $sortValues[] = $distribution->createdForUser->department->name ?? '';
                        } else if($sortField == 'created_at') {
                            $sortValues[] = $distribution->created_at;
                        }

                    }

                    return $sortValues;
                });

                foreach ($sortParams as $sort) {
                    $sortField = $sort['field'];
                    $sortDirection = strtolower($sort['direction']) === 'desc' ? 'desc' : 'asc';

                    $distributions = $sortDirection === 'asc' ? $distributions->sortBy($sortField) : $distributions->sortByDesc($sortField);
                }
            }




            // Convert to collection after sorting to maintain collection methods
            $distributions = $distributions->values();


            // Paginate the sorted collection
            $perPage = 20;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentItems = $distributions->slice(($currentPage - 1) * $perPage, $perPage)->all();
            $paginatedDistributions = new LengthAwarePaginator($currentItems, $distributions->count(), $perPage, $currentPage);


            // Return the paginated and sorted results
            return response()->json($paginatedDistributions, Response::HTTP_OK);



        } catch (\Exception $e) {

            Log::error($e->getMessage());

        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);

    }






    //? search based on request->input('query').
    private function fetchDistributions(Request $request)
    {
        try {

            $query = $request->input('query');

            return Distribution::with(['itemType', 'createdForUser','department','inventory'])
            ->where('status', DistributionStatus::APPROVED->value)
            ->where('is_deleted', 0)
                //? fetch records - by query - can be type or order_number
                ->where(function ($queryBuilder) use ($query) {
                    // // Search by personal number
                    // $queryBuilder->orWhereHas('createdForUser', function ($userQuery) use ($query) {
                    //     $userQuery->where('personal_number', 'like', "%$query%");
                    // });

                    // Search by item_type type field
                    $queryBuilder->orWhereHas('itemType', function ($itemTypeQuery) use ($query) {
                        $itemTypeQuery->where('type', 'like', "%$query%");
                    });

                    // Search by order number
                    $queryBuilder->orWhere('order_number', 'like', "%$query%");

                    // Search by year
                    // $queryBuilder->orWhere('year', 'like', "%$query%");

                    // // Search by full name
                    // $queryBuilder->orWhereHas('createdForUser', function ($userQuery) use ($query) {
                    //     $userQuery->where('name', 'like', "%$query%");
                    // });
                })
                ->orderBy('created_at', 'desc')
                ->get();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    //? search based on request->input('query'). based on status given
    private function fetchDistributionsByStatus(Request $request)
    {
        try {

            $query = $request->input('query');

            return Distribution::with(['itemType', 'createdForUser','department'])

                ->where('status', $request->input('status'))

                ->where('is_deleted', 0)

                ->where(function ($queryBuilder) use ($query) {
                    // Search by personal number
                    $queryBuilder->orWhereHas('createdForUser', function ($userQuery) use ($query) {
                        $userQuery->where('personal_number', 'like', "%$query%");
                    });

                    // Search by item_type type field
                    $queryBuilder->orWhereHas('itemType', function ($itemTypeQuery) use ($query) {
                        $itemTypeQuery->where('type', 'like', "%$query%");
                    });

                    // Search by order number
                    $queryBuilder->orWhere('order_number', 'like', "%$query%");

                    // Search by year
                    $queryBuilder->orWhere('year', 'like', "%$query%");

                    // Search by full name
                    $queryBuilder->orWhereHas('createdForUser', function ($userQuery) use ($query) {
                        $userQuery->where('name', 'like', "%$query%");
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }




    //? fillter & fetch distributions records based on filter input
    private function fetchDistributionsByFilter(Request $request)
    {

        try {


            $query = Distribution::query();


            // Search by order_number
            if ($request->has('order_number') && empty($request->input('order_number'))==false) {
                $query->where('order_number', $request->input('order_number'));
            }

            // Search by status
            if ($request->has('status')  && empty($request->input('status'))==false) {
                $query->where('status', $request->input('status'));
            }

            //? fetch by department
            if ($request->has('department_id') && empty($request->input('department_id')) == false) {

                $query->where('department_id', $request->input('department_id'));
                // $query->whereHas('createdForUser', function ($q) use ($request) {
                //     $q->where('department_id', $request->input('department_id'));
                // });
            }
            // Search by year
            if ($request->has('year') && empty($request->input('year'))==false) {
                $query->where('year', $request->input('year'));
            }

            // Search by user_id
            if ($request->has('clients_id') && empty($request->input('clients_id'))==false) {
                $query->whereIn('created_for', $request->input('clients_id'));
            }

            // Search by created_at
            if ($request->has('created_at') && empty($request->input('created_at'))==false) {
                $query->whereDate('created_at', $request->created_at);
            }

            // Search by updated_at
            if ($request->has('updated_at') && empty($request->input('updated_at'))==false) {
                $query->whereDate('updated_at', $request->updated_at);
            }



            return $query
                ->with(['itemType', 'department', 'createdForUser','inventory','quartermaster'])
                ->where('is_deleted',false)
                // ->orderBy('created_at', 'desc')
                ->get();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'התרחשה בעיה בשרת. נסה שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



}