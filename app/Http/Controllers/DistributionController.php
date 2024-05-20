<?php

namespace App\Http\Controllers;

use App\Enums\DistributionStatus;
use App\Http\Requests\StoreDistributionRequest;
use App\Http\Requests\UpdateDistributionRequest;
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

            // Loop through each item and create distribution records
            foreach ($request->input('items') as $item) {
                //? save key & value
                $itemType = $item['type_id'];
                $quantity = $item['quantity'];
                $comment = $item['comment'] ?? null; // Get comment or null if not provided



                Distribution::create([
                    'order_number' => intval($orderNumber),
                    'general_comment' => $request->input('general_comment') ?? null,
                    'inventory_comment' => $comment,
                    'total_quantity' =>  $allQuantity,
                    'quantity_per_item' =>  $quantity,
                    'status' => DistributionStatus::PENDING->value,
                    'type_id' => $itemType,
                    'department_id' => $request->input('department_id'),
                    'created_by' => $user_auth->id,
                    'created_for' => $request->input('created_for'),
                    // 'quantity' =>  $allQuantity,///all quantity per order_number
                    // 'inventory_id' => $inventory->id,
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
            ];

            //set the rules
            $rules = [
                'status' => 'required|integer|between:0,2',
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $distribution_record = Distribution::where('id', $id)->where('is_deleted', false)->first();

            if (is_null($distribution_record)) {
                return response()->json(['message' => 'שורה זו אינה קיימת במערכת.'], Response::HTTP_BAD_REQUEST);
            }

            $inventory = Inventory::where('id', $distribution_record->inventory_id)
                ->where('is_deleted', false)
                ->first();

            if (is_null($inventory)) {
                return response()->json(['message' => 'פריט אני קיים במלאי'], Response::HTTP_BAD_REQUEST);
            }

            $statusValue = (int) $request->input('status');
            $statusValue = match ($statusValue) {
                DistributionStatus::PENDING->value => 0,
                DistributionStatus::APPROVED->value => 1,
                DistributionStatus::CANCELD->value => 2,

                default => throw new \InvalidArgumentException('ערך סטטוס אינו תקין..'),
            };

            $currentTime = Carbon::now()->toDateTimeString();
            DB::beginTransaction(); // Start a database transaction

            //? distribution records has been approved

            if ($statusValue == DistributionStatus::APPROVED->value) {
                $inventory->update([
                    'quantity' => $inventory->quantity - $distribution_record->quantity,
                    'reserved' => $inventory->reserved - $distribution_record->quantity,
                    'updated_at' => $currentTime,
                ]);
                //? distribution records has been canceld
            } elseif ($statusValue == DistributionStatus::CANCELD->value) {
                $inventory->update([
                    'reserved' => $inventory->reserved - $distribution_record->quantity,
                    'updated_at' => $currentTime,
                ]);
            }

            $distribution_record->update([
                'status' => $request->input('status'),
                'updated_at' => $currentTime,
            ]);

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

            //? update the reserved fileds of inventory
            if ($request->input('quantity') && $request->input('quantity') > $distribution->quantity) {
                $inventory->update([
                    'reserved' => $inventory->reserved + abs($request->input('quantity') - $distribution->quantity),
                    'updated_at' => $currentTime,
                ]);
            } elseif ($request->input('quantity') && $request->input('quantity') < $distribution->quantity) {
                $inventory->update([
                    'reserved' => $inventory->reserved - abs($request->input('quantity') - $distribution->quantity),
                    'updated_at' => $currentTime,
                ]);
            }

            if ($request->input('status')) {
                //? user changed status distribtuons record
                // $inventory = Inventory::where('id', $distribution->inventory_id)
                // ->where('is_deleted', false)
                // ->first();

                $statusValue = (int) $request->input('status');

                $statusValue = match ($statusValue) {
                    DistributionStatus::PENDING->value => 0,
                    DistributionStatus::APPROVED->value => 1,
                    DistributionStatus::CANCELD->value => 2,

                    default => throw new \InvalidArgumentException('ערך סטטוס אינו תקין..'),
                };

                //? distribution records has been approved

                if ($statusValue == DistributionStatus::APPROVED->value) {
                    $inventory->update([
                        'quantity' => $inventory->quantity - $distribution->quantity,
                        'reserved' => $inventory->reserved - $distribution->quantity,
                        'updated_at' => $currentTime,
                    ]);
                    //? distribution records has been canceld
                } elseif ($statusValue == DistributionStatus::CANCELD->value) {
                    $inventory->update([
                        'reserved' => $inventory->reserved - $distribution->quantity,
                        'updated_at' => $currentTime,
                    ]);
                }
            }

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
                ->get();

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

                'created_at' => ['nullable', 'date'],

                'updated_at' => ['nullable', 'date'],
            ];

            // Define custom error messages
            $customMessages = [


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

            if ($request->has('user_id') || $request->has('status') || $request->has('order_number') || $request->has('inventory_id') || $request->has('department_id') || $request->has('created_at') || $request->has('updated_at')) {
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
            if ($request->has('inventory_id')) {
                $query->where('inventory_id', $request->input('inventory_id'));
            }

            // Search by order_number
            if ($request->has('order_number')) {
                $query->where('order_number', $request->input('order_number'));
            }

            // Search by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Search by department_id
            if ($request->has('department_id')) {
                $query->where('department_id', $request->input('department_id'));
            }

            // Search by user_id
            if ($request->has('user_id')) {
                $query->where('created_for', $request->input('user_id'));
            }

            // Search by created_at
            if ($request->has('created_at')) {
                $query->whereDate('created_at', $request->created_at);
            }

            // Search by updated_at
            if ($request->has('updated_at')) {
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
