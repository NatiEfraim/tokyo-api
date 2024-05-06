<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Inventory;
use Carbon\Carbon;

class InventoryController extends Controller
{
    //

    /**
     * @OA\Get(
     *      path="/api/inventories",
     *      tags={"Inventories"},
     *      summary="Get all inventories",
     *      description="Returns a list of all inventories.",
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
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="quantity", type="integer", example=33),
     *                  @OA\Property(property="sku", type="string", example="0028221469208"),
     *                  @OA\Property(property="item_type", type="string", example="autem"),
     *                  @OA\Property(property="detailed_description", type="string", example="Neque recusandae corporis totam facere pariatur. Et perspiciatis aut in quia. Placeat quas vero modi magni ut. Voluptas et qui vitae culpa."),
     *                  @OA\Property(property="reserved", type="integer", example=3),
     *                  @OA\Property(property="available", type="integer", example=3),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *          )
     *      )
     * )
     */

    public function index()
    {
        try {


            $inventories = Inventory::where('is_deleted', 0)
            ->orderBy('created_at','desc')
            ->paginate(20);

            $inventories->each(function($inventory){

                $inventory->available = $inventory->quantity - $inventory->reserved;
            });

            return response()->json($inventories, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @OA\Get(
     *     path="/api/sku-records",
     *     tags={"Inventories"},
     *     summary="Get SKU records",
     *     description="Retrieve a list of SKU records from the inventory.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="string",
     *                 example="1486404413070"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */

    public function getSkuRecords()
    {
        try {
            $inventories = Inventory::where('is_deleted', 0)
                ->pluck('sku')->toArray();
            return response()->json($inventories, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }




    /**
     * @OA\Get(
     *      path="/api/inventories/{id}",
     *      tags={"Inventories"},
     *      summary="Get inventory record by ID",
     *      description="Returns a single inventory record based on the provided ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the inventory record",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="integer", example=2),
     *              @OA\Property(property="quantity", type="integer", example=43),
     *              @OA\Property(property="sku", type="string", example="2216255278905"),
     *              @OA\Property(property="item_type", type="string", example="quia"),
     *              @OA\Property(property="available", type="integer", example="quia"),
     *              @OA\Property(property="detailed_description", type="string", example="Vel sunt odit quam qui ut suscipit quo. Ipsum dignissimos totam in totam. Veniam voluptas vitae et repellendus dolores consectetur tempora. Placeat atque provident enim sint et qui.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="יש לשלוח מספר מזה של שורה")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              type="object",
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
            $inventory = Inventory::where('is_deleted', 0)
                ->where('id', $id)
                ->first();

                $inventory->available=$inventory->quantity-$inventory->reserved;

            return response()->json($inventory, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    /**
     * @OA\Delete(
     *      path="/api/inventories/{id}",
     *      tags={"Inventories"},
     *      summary="Delete an inventory by ID",
     *      description="Deletes an inventory based on the provided ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the inventory to delete",
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
            $inventory = Inventory::where('is_deleted', 0)
                ->where('id', $id)
                ->first();
            if (is_null($inventory)) {
                return response()->json(['message' => 'שורה אינה קיימת במערכת.'], Response::HTTP_BAD_REQUEST);
            }
            $inventory->update([
                'is_deleted' => true,
            ]);
            return response()->json(['message' => 'שורה נמחקה בהצלחה.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Store a newly created inventory item in storage.
     *
     * @OA\Post(
     *      path="/api/inventories",
     *      tags={"Inventories"},
     *      summary="Create a new inventory item",
     *      description="Store a newly created inventory item in the database.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              required={"quantity", "sku", "item_type", "detailed_description"},
     *              @OA\Property(property="quantity", type="integer", example=10),
     *              @OA\Property(property="sku", type="string", example="ABC123"),
     *              @OA\Property(property="item_type", type="string", example="Product"),
     *              @OA\Property(property="detailed_description", type="string", example="Detailed description of the item.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Inventory item created successfully.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object", example={"quantity": {"The quantity field is required."}})
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server error response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Internal server error.")
     *          )
     *      )
     * )
     */

    public function store(StoreInventoryRequest $request)
    {
        try {

            if ($request->input('reserved') >$request->input('quantity')) {
                return response()->json(['message' => 'נתוני פריט אינם תקינים.'], Response::HTTP_BAD_REQUEST);
            }

            $inventory = Inventory::create($request->validated());
            // $currentTime = Carbon::now()->toDateTimeString();
            // $inventory->updated_at = $currentTime;
            // $inventory->created_at = $currentTime;
            // $inventory->save();

            return response()->json(['message' => 'שורה נוצרה בהצלחה.'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    /**
     * @OA\Put(
     *     path="/inventory/{id}",
     *     tags={"Inventories"},
     *     summary="Update an inventory item",
     *     description="Updates an inventory item with the provided ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the inventory item to update",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Inventory object to update",
     *         @OA\JsonContent(
     *             @OA\Property(property="quantity", type="integer", example="10"),
     *             @OA\Property(property="sku", type="string", maxLength=255, example="SKU123"),
     *             @OA\Property(property="item_type", type="string", maxLength=255, example="Electronics"),
     *             @OA\Property(property="detailed_description", type="string", example="This is an electronic device.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success message",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="שורה התעדכנה בהצלחה.")
     *         )
     *     ),
     *
     * )
     */


    public function update(UpdateInventoryRequest $request, $id = null)
    {
        if (is_null($id)) {
            return response()->json(['message' => 'יש לשלוח מספר מזהה של שורה'], Response::HTTP_BAD_REQUEST);
        }

        try {


            $inventory = Inventory::where('is_deleted', 0)

                ->where('id', $id)
                ->first();
            if (is_null($inventory)) {
                return response()->json(['message' => 'שורה אינה קיימת במערכת.'], Response::HTTP_BAD_REQUEST);
            }


            // $currentTime = Carbon::now()->toDateTimeString();


            $inventory->update($request->validated());

            // $inventory->updated_at = $currentTime;
            // $inventory->save();

            return response()->json(['message' => 'שורה התעדכנה בהצלחה.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    /**
     * Mass delete inventories.
     *
     * This endpoint deletes multiple distribution records based on the provided IDs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path="/api/inventories/mass-destroy",
     *     summary="Mass delete inventories",
     *     tags={"Inventories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"inventories"},
     *             @OA\Property(property="inventories", type="array",
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
                'inventories.required' => 'יש לשלוח שורות למחיקה.',
                'inventories.array' => 'שורות אינם בפורמט תקין.',
                'inventories.*.id.required' => 'שדה המזהה חובה.',
                'inventories.*.id.integer' => 'אחת מהשדות שנשלחו אינו תקין.',
                'inventories.*.id.exists' => 'המזהה שנבחר לא קיים או שהמשימה נמחקה.',
            ];
            //set the rules
            $rules = [
                'inventories' => 'required|array',
                'inventories.*.id' => 'required|integer|exists:inventories,id,is_deleted,0',
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {

                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $inventories = $request->input('inventories');
            $ids = collect($inventories)->pluck('id')->toArray();

            // Update the 'is_deleted' column to 1 for the inventories with the given IDs
            Inventory::whereIn('id', $ids)->update(['is_deleted' => 1]);

            return response()->json(['message' => 'שורות נמחקו בהצלחה.'], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    /**
     * @OA\Get(
     *     path="/api/inventory/search-records",
     *     tags={"Inventories"},
     *     summary="Search inventory records by SKU or item type",
     *     description="Search inventory records by providing either SKU or item type. Returns matching inventory records.",
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
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="quantity", type="integer", example=33),
     *                  @OA\Property(property="sku", type="string", example="0028221469208"),
     *                  @OA\Property(property="item_type", type="string", example="autem"),
     *                  @OA\Property(property="detailed_description", type="string", example="Neque recusandae corporis totam facere pariatur. Et perspiciatis aut in quia. Placeat quas vero modi magni ut. Voluptas et qui vitae culpa."),
     *                  @OA\Property(property="reserved", type="integer", example=3),
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */


    public function searchRecords(Request $request)
    {

        try {


            // set custom error messages in Hebrew
            $customMessages = [
                'query.required' => 'יש לשלוח ערך לחיפוש' ,
                'query.string' => 'ערך שנשלח אינו תקין'
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


            if (ctype_digit($request->input('query'))) {

                // Search by SKU
                $inventory = Inventory::where('sku', $request->input('query'))
                    ->where('is_deleted', false)
                    ->first();



                return response()->json(is_null($inventory)? []:$inventory, Response::HTTP_OK);
            } else {

                // Search by item type
                $inventories = Inventory::where('item_type', 'LIKE', '%' . $request->input('query') . '%')
                    ->where('is_deleted', false)->get();


                return response()->json($inventories->isEmpty() ? []: $inventories, Response::HTTP_OK);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

}
