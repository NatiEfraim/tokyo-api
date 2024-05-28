<?php

namespace App\Http\Controllers;

use App\Enums\EmployeeType;

use App\Enums\DistributionStatus;
use App\Http\Requests\StoreDistributionRequest;
use App\Http\Requests\UpdateDistributionRequest;
use App\Models\Client;
// use App\Models\Department;
use App\Models\Distribution;
use App\Models\Inventory;
// use App\Models\User;
// use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

// use Illuminate\Support\Str;

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

    public function index()
    {
        try {
            $distributions = Distribution::with(['inventory', 'itemType', 'department', 'createdForUser'])
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

            $distribution = Distribution::with(['inventory', 'itemType', 'department', 'createdForUser'])
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



        // // Fetch associated roles for the authenticated user
        // $userRoles = $user_auth->roles->first()->name;


            //? create new clients records. - and get the client_id


            //casting the value.
            $emp_type = (int) $request->input('employee_type');


            //set the first letter for the persnal_number
            $personal_number = match ($emp_type) {
                EmployeeType::KEVA->value, EmployeeType::SADIR->value => 's' . $request->personal_number,
                EmployeeType::MILUIM->value => 'm' . $request->personal_number,
                EmployeeType::OVED_TZAHAL->value => 'c' . $request->personal_number,
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


            $allQuantity = 0;
            foreach ($request->input('items') as $item) {
                $allQuantity += $item['quantity'];
            }


            // Get the current year
            $currentYear = Carbon::now()->year;

            $allQuantity = array_sum(array_column($request->input('items'), 'quantity'));


            foreach ($request->input('items') as $item) {
                $itemType = $item['type_id'];
                $quantity = $item['quantity'];
                $comment = $item['comment'] ?? null;

                // Prepare inventory items array
                $inventoryItems[] = [
                    'type_id' => $itemType,
                    'quantity' => $quantity,
                    'comment' => $comment,
                ];


          

                Distribution::create([
                    'order_number' => intval($orderNumber),
                    'user_comment' => $request->input('user_comment') ?? null,
                    'type_comment' => $comment,
                    'total_quantity' => $allQuantity,
                    'quantity_per_item' => $quantity,
                    'status' => DistributionStatus::PENDING->value,
                    'type_id' => $itemType,
                    'year' => $currentYear,
                    'department_id' => $request->input('department_id'),
                    'created_by' => $user_auth->id,
                    'created_for' => $client->id,
                    'inventory_items' => json_encode($inventoryItems), // Save inventory items as JSON
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
     * @OA\Put(
     *      path="/changed-status/{id}",
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

    public function allocationStatus(Request $request, $id = null)
    {
        try {

            if (is_null($id)) {
                return response()->json(['message' => 'יש לשלוח מספר מזהה של שורה.'], Response::HTTP_BAD_REQUEST);
            }

            // set custom error messages in Hebrew
            $customMessages = [

                'status.required' => 'חובה לשלוח שדה סטטוס לעידכון.',
                'status.integer' => 'שדה סטטוס שנשלח אינו בפורמט תקין.',
                'status.between' => 'ערך הסטטוס שנשלח אינו תקין.',

                'admin_comment.string' => 'אחת מהשדות שנשלחו אינם תקינים.',
                'admin_comment.min' => 'אחת מהשדות שנשלחו אינם תקינים.',
                'admin_comment.max' => 'אחת מהשדות שנשלחו אינם תקינים.',

                'inventory_items.array' => 'נתון שנשלח אינו תקין.',
                'inventory_items.*.inventory_id.required' => 'חובה לשלוח מזהה פריט במערך הפריטים.',
                'inventory_items.*.inventory_id.exists' => 'מזהה הפריט שנשלח במערך אינו קיים או נמחק.',
                'inventory_items.*.quantity.required' => 'חובה לשלוח כמות לכל פריט במערך.',
                'inventory_items.*.quantity.integer' => 'הכמות שנשלחה עבור פריט במערך אינה בפורמט תקין.',
                'inventory_items.*.quantity.min' => 'הכמות שנשלחה עבור פריט במערך חייבת להיות גדולה או שווה ל-0.',
            ];

            //set the rules
            $rules = [

                'status' => 'required|integer|between:1,2',
                'admin_comment' => 'nullable|string|min:2|max:255',
                'inventory_items' => 'nullable|array',
                'inventory_items.*.inventory_id' => 'required|exists:inventories,id,is_deleted,0',
                'inventory_items.*.quantity' => 'required|integer|min:0',
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (is_null($request->input('inventory_items')) && $request->input('status')==DistributionStatus::APPROVED->value) {
                return response()->json(['message' => 'נתונים אינם תקינים.'], Response::HTTP_BAD_REQUEST);
            }

            if (is_null($request->input('admin_comment')) && $request->input('status')==DistributionStatus::CANCELD->value) {
                return response()->json(['message' => 'חובה לשלוח סיבת ביטול.'], Response::HTTP_BAD_REQUEST);
            }

            $distribution_record = Distribution::where('id', $id)->where('is_deleted', false)->first();

            if (is_null($distribution_record)) {
                return response()->json(['message' => 'שורה זו אינה קיימת במערכת.'], Response::HTTP_BAD_REQUEST);
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

                foreach ($request->inventory_items as $invetories) {

                    $idInvetory = $invetories['inventory_id']; //save the invetory_id records
                    $quantity = $invetories['quantity'];



                    $inventory = Inventory::where('id',  $idInvetory)
                        ->where('is_deleted', false)
                        ->first();

                    $available = $inventory->quantity - $inventory->reserved;


                    if ($inventory->type_id !== $distribution_record->type_id) {
                        DB::rollBack(); // Rollback the transaction
                        return response()->json(['message' => 'פריט עבור מק"ט' . $inventory->sku . '.אינו תקין'], Response::HTTP_OK);
                    }

                    if ($quantity< $available) {
                        DB::rollBack(); // Rollback the transaction
                        return response()->json(['message' => 'כמות שנשלח עבור'. $inventory->sku .' חסרה במלאי.'], Response::HTTP_OK);
                    }

                    //? update invetory records based on invetory_id
                    $inventory->update([
                        // 'quantity' => $inventory->quantity - $quantity,
                        'reserved' => $inventory->reserved - $quantity,
                        'updated_at' => $currentTime,
                    ]);

                }

                   $distribution_record->update([
                        'inventory_items' => json_encode($request->inventory_items),
                        'updated_at' => $currentTime,

                   ]);


                //? distribution records has been canceld
            } elseif ($statusValue == DistributionStatus::CANCELD->value) {



                $distribution_record->update([
                    'status' => DistributionStatus::CANCELD->value,
                    'admin_comment'=> $request->input('admin_comment'),
                    'updated_at' => $currentTime,

                ]);

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
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the distribution",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
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

    public function changeStatus(Request $request, $id = null)
    {
        try {

            if (is_null($id)) {
                return response()->json(['message' => 'יש לשלוח מספר מזהה של שורה.'], Response::HTTP_BAD_REQUEST);
            }

            // set custom error messages in Hebrew
            $customMessages = [

                'status.required' => 'חובה לשלוח שדה סטטוס לעידכון.',
                'status.integer' => 'שדה סטטוס שנשלח אינו בפורמט תקין.',
                'status.between' => 'ערך הסטטוס שנשלח אינו תקין.',

                'quartermaster_comment.string' => 'אחת מהשדות שנשלחו אינם תקינים.',
                'quartermaster_comment.min' => 'אחת מהשדות שנשלחו אינם תקינים.',
                'quartermaster_comment.max' => 'אחת מהשדות שנשלחו אינם תקינים.',

            ];

            //set the rules
            $rules = [

                'status' => 'required|integer|between:0,3',
                'quartermaster_comment' => 'nullable|string|min:2|max:255',
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($request->input('status')!==DistributionStatus::PENDING->value && $request->input('status')!==DistributionStatus::COLLECTED->value) {
                return response()->json(['message' => 'נתונים אינם תקינים.'], Response::HTTP_BAD_REQUEST);
            }

            if (is_null($request->input('quartermaster_comment')) && $request->input('status')==DistributionStatus::PENDING->value) {
                return response()->json(['message' => 'חובה לשלוח סיבת ביטול.'], Response::HTTP_BAD_REQUEST);
            }

            $distribution_record = Distribution::where('id', $id)->where('is_deleted', false)->first();

            if (is_null($distribution_record)) {
                return response()->json(['message' => 'שורה זו אינה קיימת במערכת.'], Response::HTTP_BAD_REQUEST);
            }


            $statusValue = (int) $request->input('status');
            $statusValue = match ($statusValue) {
                DistributionStatus::PENDING->value => 0,
                DistributionStatus::COLLECTED->value => 3,

                default => throw new \InvalidArgumentException('ערך סטטוס אינו תקין..'),
            };



            $currentTime = Carbon::now()->toDateTimeString();

               $distribution_record->update([
                    'status' =>  $statusValue,
                    'admin_comment'=> $request->input('quartermaster_comment')??null,
                    'updated_at' => $currentTime,

                ]);



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

            // //? update the reserved fileds of inventory
            // if ($request->input('quantity') && $request->input('quantity') > $distribution->quantity) {
            //     $inventory->update([
            //         'reserved' => $inventory->reserved + abs($request->input('quantity') - $distribution->quantity),
            //         'updated_at' => $currentTime,
            //     ]);
            // } elseif ($request->input('quantity') && $request->input('quantity') < $distribution->quantity) {
            //     $inventory->update([
            //         'reserved' => $inventory->reserved - abs($request->input('quantity') - $distribution->quantity),
            //         'updated_at' => $currentTime,
            //     ]);
            // }




            // if ($request->input('status')) {


            //     // $statusValue = (int) $request->input('status');

            //     // $statusValue = match ($statusValue) {
            //     //     DistributionStatus::PENDING->value => 0,
            //     //     // DistributionStatus::APPROVED->value => 1,
            //     //     // DistributionStatus::CANCELD->value => 2,
            //     //     DistributionStatus::COLLECTED->value => 3,

            //     //     default => throw new \InvalidArgumentException('ערך סטטוס אינו תקין..'),
            //     // };

            //     //? distribution records has been approved

            //     // if ($statusValue == DistributionStatus::APPROVED->value) {
            //     //     $inventory->update([
            //     //         'quantity' => $inventory->quantity - $distribution->quantity,
            //     //         'reserved' => $inventory->reserved - $distribution->quantity,
            //     //         'updated_at' => $currentTime,
            //     //     ]);
            //     //     //? distribution records has been canceld
            //     // } elseif ($statusValue == DistributionStatus::CANCELD->value) {
            //     //     $inventory->update([
            //     //         'reserved' => $inventory->reserved - $distribution->quantity,
            //     //         'updated_at' => $currentTime,
            //     //     ]);
            //     // }
            // }

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

            ];


            //set the rules
            $rules = [

                'status' => 'required|integer|between:0,3',

            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            // Fetch all distribution records with their relations
            $distributions = Distribution::with(['inventory', 'itemType', 'department', 'createdForUser'])
                ->where('status', $request->input('status'))
                ->where('is_deleted', 0)
                ->paginate(20);


            // if ($distributions) {
            //     $distributions->each(function ($distribution) {
            //         // Format the created_at and updated_at timestamps
            //         $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
            //         $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');

            //         return $distribution;
            //     });
            // }



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
            return response()->json(['message' => 'Server error, please try again later.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
            $distributions= Distribution::with(['inventory', 'itemType', 'department', 'createdForUser'])
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

                'user_id' => 'nullable|string|exists:users,id,is_deleted,0',

                'order_number' => 'nullable|string|exists:distributions,order_number,is_deleted,0',

                'year' => 'nullable|integer|between:1948,2099',

                'created_at' => ['nullable', 'date'],

                'updated_at' => ['nullable', 'date'],
            ];

            // Define custom error messages
            $customMessages = [

                'year.integer' => 'שדה שנה אינו תקין.',
                'year.between' => 'שדה שנה אינו תקין.',

                'inventory_id.string' => 'שדה שהוזן אינו בפורמט תקין',
                'inventory_id.max' => 'אורך שדה מק"ט חייב להכיל לכל היותר 255 תווים',
                'inventory_id.exists' => 'שדה מק"ט שנשלח אינו קיים במערכת.',

                'department_id.exists' => 'מחלקה אינה קיימת במערכת.',

                'order_number.exists' => 'מספר הזמנה אינה קיית במערכת.',

                'status.between' => 'שדה הסטטוס אינו תקין.',

                'user_id.exists' => 'משתמש אינו קיים במערכת.',

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

            if ($request->has('user_id') || $request->has('year')  || $request->has('status') || $request->has('order_number') || $request->has('inventory_id') || $request->has('department_id') || $request->has('created_at') || $request->has('updated_at')) {
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

                $distributions = Distribution::with(['inventory', 'department', 'createdForUser'])
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

        // Define the valid fields that can be used for sorting
        $validFields = [
            'order_number',
            'year',
            'inventory_id',
            'type_id',
            'department_id',

        ];

        // Validate the input to ensure the query parameter is valid
        $validator = Validator::make($request->all(), [
            'sort_by' => ['required', 'string', 'in:' . implode(',', $validFields)],
        ]);

        if ($validator->fails()) {
            return response()->json(['messages' => 'הנתונים למיון שגויים.'], Response::HTTP_BAD_REQUEST);
        }

        // Retrieve the sort_by parameter
        $sortBy = $request->input('sort_by');

        // Initialize the query builder
        $query = Distribution::query();

        // Add joins if sorting by a related field
        if ($sortBy == 'department_id') {
            $query->join('departments', 'distributions.department_id', '=', 'departments.id')
            ->select('distributions.*')
            ->orderBy('departments.name'); // order by name depratment records
        }
        if ($sortBy == 'inventory_id') {
            $query->join('inventories', 'distributions.inventory_id', '=', 'departments.id')
            ->select('inventories.*')
            ->orderBy('inventories.sku'); // order by sku depratment records
        }
        if ($sortBy == 'type_id') {
            $query->join('item_types', 'distributions.type_id', '=', 'item_types.id')
            ->select('distributions.*')
            ->orderBy('item_types.type '); // order by type item_type records
        }
        if($sortBy=='year' || $sortBy=='order_number') {
            $query->orderBy($sortBy);
        }

        // Define the number of records per page
        $perPage = 20;

        // Fetch the sorted records with pagination
        $distributions = $query->with(['inventory', 'itemType', 'department', 'createdForUser'])
                               ->paginate($perPage);

        return response()->json($distributions->isEmpty() ? [] : $distributions, Response::HTTP_OK);

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

            return Distribution::with(['inventory', 'itemType', 'department', 'createdForUser'])

                ->where('is_deleted', 0)

                ->where(function ($queryBuilder) use ($query) {
                    // Search by personal number
                    $queryBuilder->orWhereHas('createdForUser', function ($userQuery) use ($query) {
                        $userQuery->where('personal_number', 'like', "%$query%");
                    });

                    // Search by SKU
                    $queryBuilder->orWhereHas('inventory', function ($inventoryQuery) use ($query) {
                        $inventoryQuery->where('sku', 'like', "%$query%");
                    });

                    // Search by item_type type field
                    $queryBuilder->orWhereHas('inventory.itemType', function ($itemTypeQuery) use ($query) {
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

            // Search by inventory_id
            if ($request->has('inventory_id') && empty($request->input('inventory_id'))==false) {
                $query->where('inventory_id', $request->input('inventory_id'));
            }

            // Search by order_number
            if ($request->has('order_number') && empty($request->input('order_number'))==false) {
                $query->where('order_number', $request->input('order_number'));
            }

            // Search by status
            if ($request->has('status')  && empty($request->input('status'))==false) {
                $query->where('status', $request->status);
            }


            // Search by department_id
            if ($request->has('department_id') && empty($request->input('department_id'))==false) {
                $query->where('department_id', $request->input('department_id'));
            }

            // Search by year
            if ($request->has('year') && empty($request->input('year'))==false) {
                $query->where('year', $request->input('year'));
            }

            // Search by user_id
            if ($request->has('user_id') && empty($request->input('user_id'))==false) {
                $query->where('created_for', $request->input('user_id'));
            }

            // Search by created_at
            if ($request->has('created_at') && empty($request->input('created_at'))==false) {
                $query->whereDate('created_at', $request->created_at);
            }

            // Search by updated_at
            if ($request->has('updated_at') && empty($request->input('updated_at'))==false) {
                $query->whereDate('updated_at', $request->updated_at);
            }

            // Ensure is_deleted is 0
            $query->where('is_deleted', 0);

            return $query
                ->with(['inventory', 'itemType', 'department', 'createdForUser'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'התרחשה בעיה בשרת. נסה שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



}