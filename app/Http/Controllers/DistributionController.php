<?php

namespace App\Http\Controllers;

use App\Enums\DistributionStatus;
use App\Http\Requests\StoreDistributionRequest;
use App\Http\Requests\UpdateDistributionRequest;
// use App\Models\Department;
use App\Models\Distribution;
// use App\Models\User;
// use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
// use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;




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

            $distributions = Distribution::with(['inventory', 'department', 'createdForUser'])
            ->where('is_deleted', 0)
            ->orderBy('created_at', 'desc')->paginate(10)

                ->map(function ($distribution) {

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
        if (is_null($id)) {
            return response()->json(['message' => 'יש לשלוח מספר מזהה של שורה'], Response::HTTP_BAD_REQUEST);
        }


        try {
            $distribution = Distribution::with(['inventory','', 'department', 'createdForUser'])
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
        if (is_null($id)) {
            return response()->json(['message' => 'יש לשלוח מספר מזהה של שורה'], Response::HTTP_BAD_REQUEST);
        }


        try {
            $distirbution = Distribution::where('is_deleted', 0)
                ->where('id', $id)
                ->first();
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
     *         @OA\JsonContent(
     *             @OA\Property(property="comment", type="string", example="This is a distribution comment."),
     *             @OA\Property(property="status", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=10),
     *             @OA\Property(property="inventory_id", type="integer", example=123),
     *             @OA\Property(property="department_id", type="integer", example=456),
     *             @OA\Property(property="user_id", type="integer", example=2)
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
            $user_auth=Auth::user();

             Distribution::create([

                'comment' => $request->input('comment'),
                'status' => $request->input('status') ? $request->input('status')  : DistributionStatus::PENDING->value,
                'quantity' => $request->input('quantity'),
                //?set relation
                'inventory_id' => $request->input('inventory_id'),
                'department_id' => $request->input('department_id'),
                'created_by' => $user_auth->id,
                'user_id' => $request->input('user_id'),
             ]);

            return response()->json(['message' => 'שורה נוצרה בהצלחה.'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
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
                'status' => 'required|integer|between:0,2'
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {

                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $currentTime = Carbon::now()->toDateTimeString();


            $distribution_record = Distribution::where('id', $id)->where('is_deleted', false)->first();

            if (is_null($distribution_record)) {
                return response()->json(['message' => 'שורה זו אינה קיימת במערכת.'], Response::HTTP_BAD_REQUEST);
            }

            $distribution_record->update([
                'status' => $request->input('status'),
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
     *         required=true,
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
        if (is_null($id)) {
            return response()->json(['message' => 'יש לשלוח מספר מזהה של שורה'], Response::HTTP_BAD_REQUEST);
        }

        try {

            $distribution = Distribution::where('is_deleted', 0)
                ->where('id', $id)
                ->first();
            if (is_null($distribution)) {
                return response()->json(['message' => 'שורה אינה קיימת במערכת.'], Response::HTTP_BAD_REQUEST);
            }


            $currentTime = Carbon::now()->toDateTimeString();


            $distribution->update($request->validated());

            $distribution->updated_at = $currentTime;
            $distribution->save();

            return response()->json(['message' => 'שורה התעדכנה בהצלחה.'], Response::HTTP_OK);
        } catch (\Exception $e) {
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


                'users.required' => 'חובה לשלוח משתמש אחד לפחות.',
                'users.array' => 'שדה משתמש שנשלח אינו תקין.',
                'users.*.required' => 'שדה זה נדרש.',
                'users.*.exists' => 'הערך שהוזן לא חוקי.',



                'sku.string' => 'שדה שהוזן אינו בפורמט תקין',
                'sku.max' => 'אורך שדה מק"ט חייב להכיל לכל היותר 255 תווים',
                'sku.exists' => 'שדה מק"ט שנשלח אינו קיים במערכת.',


                'name.string' => 'שדה ערך שם מחלקה אינו תקין.',

                'status.between' => 'שדה הסטטוס אינו תקין.',


                'personal_number.min' => 'מספר אישי אינו תקין.',
                'personal_number.max' => 'מספר אישי אינו תקין.',



                'created_at.date' => 'שדה תאריך התחלה אינו תקין.',
                'created_at.exists' => 'שדה תאריך אינו קיים במערכת.',
                'updated_at.date' => 'שדה תאריך סיום אינו תקין.',
                'updated_at.exists' => 'שדה תאריך סיום אינו קיים במערכת.',
            ];
            //set the rules

            $rules = [
                // 'query' => 'required|string',

                // 'users' => 'required|array',
                // 'users.*' => 'required|exists:users,id,is_deleted,0',

                'sku' => 'nullable|string|max:255|exists:inventories,sku,is_deleted,0',

                'status' => 'nullable|integer|between:0,2',

                'name' => 'nullable|string|exists:departments,name,is_deleted,0',

                'personal_number' => 'nullable|min:1|max:7',


                'created_at' => [
                    'nullable',
                    'date',
                    // Rule::exists('distributions')->where(function ($query) {
                    //     return $query->where('is_deleted', 0);
                    // }),
                ],

                'updated_at' => [
                    'nullable',
                    'date',
                    // Rule::exists('distributions')->where(function ($query) {
                    //     return $query->where('is_deleted', 0);
                    // }),
                ],


            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {

                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }



            if (
                $request->has('sku') ==false
                && $request->has('status') == false
                && $request->has('name') == false
                && $request->has('personal_number') == false
                && $request->has('created_at') == false
                && $request->has('updated_at') == false
            ) {

                return response()->json(['messages' => 'חובה לשלוח ערך לחיפוש.'], Response::HTTP_BAD_REQUEST);
            }

            //? one or more of th search based on value filter send
            $distributions = $this->fetchDistributions($request);
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



    //? search based on request->input('query').
    private function fetchDistributions(Request $request)
    {
        try {


            $query = Distribution::query();

            if ($request->has('sku')) {
                $query->whereHas('inventory', function ($q) use ($request) {
                    $q->where('sku', $request->sku);
                });
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('name')) {
                $query->whereHas('department', function ($q) use ($request) {
                    $q->where('name', $request->name);
                });
            }


            if ($request->has('personal_number')) {
                // $pnInput = $request->personal_number;
                $query->whereHas('createdForUser', function ($q) use ($request) {
                    $q->whereRaw('SUBSTRING(personal_number, 2) LIKE ?', ['%' . $request->input('personal_number') . '%']);
                });
            }


            if ($request->has('created_at')) {
                $query->whereDate('created_at', $request->created_at);
            }

            if ($request->has('updated_at')) {
                $query->whereDate('updated_at', $request->updated_at);
            }

            // Ensure is_deleted is 0
            $query->where('is_deleted', 0);

            return $query->with(['inventory', 'department', 'createdForUser'])
            ->orderBy('created_at', 'desc')->paginate(10);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

}
