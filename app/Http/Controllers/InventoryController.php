<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;





class InventoryController extends Controller
{
    //

    protected $_inventoryService;

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

     public function __construct()
     {
        $this->_inventoryService = new InventoryService();
     }

    public function index()
    {
        try {


            $result = $this->_inventoryService->index();

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

            // $inventories = Inventory::with(['itemType'])
            // ->where('is_deleted', 0)
            // ->orderBy('created_at','desc')
            // ->paginate(20);

            // $inventories->each(function($inventory){

            //     $inventory->available = $inventory->quantity - $inventory->reserved;
            // });

            // return response()->json($inventories->isEmpty() ? [] : $inventories, Response::HTTP_OK);
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

            $result = $this->_inventoryService->getSkuRecords();

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

            // $inventories = Inventory::select('id', 'sku')
            //     ->where('is_deleted', 0)
            //     ->get()
            //     ->map(function ($inventory) {
            //         return [
            //             'id' => $inventory->id,
            //             'name' => $inventory->sku
            //         ];
            //     });

            // return response()->json($inventories->isEmpty() ? [] : $inventories, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    /**
     * @OA\Get(
     *      path="/api/inventories/fetch-by-sku",
     *      tags={"Inventories"},
     *      summary="fetch inventories records based on type_id and sku",
     *      description="Returns a list of inventories records based on type_id and sku.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              required={"type_id", "query"},
     *              @OA\Property(property="type_id", type="integer", example=10),
     *              @OA\Property(property="query", type="string", example="7845"),
     *          )
     *      ),
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
    public function fetchBySku(Request $request)
    {
        try {


            // set custom error messages in Hebrew
            $customMessages = [
                
                'type_id.required' => 'יש לבחור סוג פריט.',
                'type_id.integer' => 'סוג פריט אינו בפורמט תקין',
                'type_id.exists' => 'סוג פריט אינו קיים.',

                'query.required' => 'יש לשלוח שדה לחיפוש.',
                'query.string' => 'שדה חיפוש אינו תקין.',
                'query.max' => 'שדה חיפוש אינו תקין.',
                
            ];
            //set the rules

            $rules = [
                
                'type_id' => 'required|integer|exists:item_types,id,is_deleted,0',
                'query'=> 'required|string'
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {

                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            $result = $this->_inventoryService->fetchBySku($request);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };


            // $searchQuery = str_replace(' ', '', $request->input('query'));

            // // Search users by name (ignoring spaces)
            // $invetoriesRecords = Inventory::with(['itemType'])
            // ->where('type_id', $request->input('type_id'))
            // ->where('is_deleted', false)
            // ->where('sku', 'LIKE', '%' . $searchQuery . '%')

            // ->orderBy('id', 'asc')
            //     ->get();

            // if(is_null($invetoriesRecords)==false)
            // {
            //     $invetoriesRecords->each(function ($inventory) {
    
            //         $inventory->available = $inventory->quantity - $inventory->reserved;
            //     });

            // }


            // return response()->json($invetoriesRecords->isEmpty()? []: $invetoriesRecords, Response::HTTP_OK);


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


        try {


            $result = $this->_inventoryService->getRecordById($id);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

            // if (is_null($id)) {
            //     return response()->json(['message' => 'יש לשלוח מספר מזהה של שורה'], Response::HTTP_BAD_REQUEST);
            // }

            // $inventory = Inventory::with(['itemType'])
            // ->where('is_deleted', 0)
            //     ->where('id', $id)
            //     ->first();

            // if (is_null($inventory)) {
            //     return response()->json([], Response::HTTP_OK);
            // }
            //     $inventory->available=$inventory->quantity-$inventory->reserved;

            // return response()->json($inventory, Response::HTTP_OK);


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

        try {

            $result = $this->_inventoryService->destroy($id);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json(['message' => $result['message']], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

            // if (is_null($id)) {
            //     return response()->json(['message' => 'יש לשלוח מספר מזהה של שורה'], Response::HTTP_BAD_REQUEST);
            // }

            // $inventory = Inventory::where('is_deleted', 0)
            //     ->where('id', $id)
            //     ->first();
            // if (is_null($inventory)) {
            //     return response()->json(['message' => 'שורה אינה קיימת במערכת.'], Response::HTTP_BAD_REQUEST);
            // }
            // $inventory->update([
            //     'is_deleted' => true,
            // ]);
            // return response()->json(['message' => 'שורה נמחקה בהצלחה.'], Response::HTTP_OK);


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


            $result = $this->_inventoryService->store($request);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json(['message' => $result['message']], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

            // if ($request->input('reserved') >$request->input('quantity')) {
            //     return response()->json(['message' => 'נתוני פריט אינם תקינים.'], Response::HTTP_BAD_REQUEST);
            // }

            // $inventory = Inventory::create($request->validated());


            // return response()->json(['message' => 'שורה נוצרה בהצלחה.'], Response::HTTP_CREATED);

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

        try {




            $result = $this->_inventoryService->update($request);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json(['message' => $result['message']], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

            // if (is_null($id)) {
            //     return response()->json(['message' => 'יש לשלוח מספר מזהה של שורה'], Response::HTTP_BAD_REQUEST);
            // }

            // $authUser = Auth::user();


            // $inventory = Inventory::where('is_deleted', 0)

            //     ->where('id', $id)
            //     ->first();
            // if (is_null($inventory)) {
            //     return response()->json(['message' => 'שורה אינה קיימת במערכת.'], Response::HTTP_BAD_REQUEST);
            // }


            // $currentTime = Carbon::now()->toDateTimeString();


            // DB::beginTransaction();

            // if ($request->input('quantity')) {
            //         //? created new reports records
            //     Report::create([

            //         'hour' => Carbon::now()->format('H:i'), // Current hour and minutes in 'HH:MM' format
            //         'created_by' => $authUser->id,
            //         'last_quantity' => $inventory->quantity,
            //         'new_quantity' => $request->input('quantity'),
            //         'sku' => $inventory->sku,
            //         'inventory_id' => $inventory->id,
            //     ]);

            // }

            // $inventory->update([
            //     'quantity' => $request->input('quantity') ? $request->input('quantity') : $inventory->quantity,
            //     'sku' => $request->input('sku') ? $request->input('sku'): $inventory->sku,
            //     'type_id' => $request->input('type_id') ?  $request->input('type_id') : $inventory->type_id,
            //     'detailed_description' => $request->input('detailed_description') ? $request->input('detailed_description') : $inventory->detailed_description,
            //     'updated_at' => $currentTime,
            // ]);

            // DB::commit();


            // return response()->json(['message' => 'שורה התעדכנה בהצלחה.'], Response::HTTP_OK);


        } catch (\Exception $e) {
            
            DB::rollBack(); // Rollback the transaction in case of any error
        Log::error($e->getMessage());

        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

        /**
     * @OA\Get(
     *     path="/api/reports",
     *     summary="Fetch reports by inventory ID",
     *     description="Retrieve reports associated with a specific inventory ID",
     *     tags={"Reports"},
     *     @OA\Parameter(
     *         name="inventory_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         ),
     *         description="ID of the inventory"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=18),
     *                 @OA\Property(property="new_quantity", type="integer", example=20),
     *                 @OA\Property(property="last_quantity", type="integer", example=29),
     *                 @OA\Property(property="hour", type="string", example="03:48:00"),
     *                 @OA\Property(property="created_at_date", type="string", example="26/05/2024"),
     *                 @OA\Property(property="updated_at_date", type="string", example="26/05/2024"),
     *                 @OA\Property(
     *                     property="created_by_user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=6),
     *                     @OA\Property(property="name", type="string", example="ארנולד גורדון")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="messages", type="object", example={"inventory_id": {"יש לשלוח מוצר פריט."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *         )
     *     )
     * )
     */

    public function fetchReport(Request $request)
    {

        try {


            // set custom error messages in Hebrew
            $customMessages = [
                'inventory_id.required' => 'יש לשלוח מוצר פריט.',
                'inventory_id.integer' => 'יש לשלוח בפורמט תקין.',
                'inventory_id.exists' => 'מוצר פריט לא קיים במלאי.',

            ];
            //set the rules
            $rules = [

                'inventory_id' => 'required|integer|exists:inventories,id,is_deleted,0',
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }





            $result = $this->_inventoryService->fetchReport($request);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };


            // $reports = Report::with(['createdByUser'])
            // ->where('inventory_id',$request->input('inventory_id'))
            // ->where('is_deleted', false)
            // ->get();



            // $reports->each(function ($report) {
            //     // Format the created_at and updated_at timestamps
            //     $report->created_at_date = $report->created_at->format('d/m/Y');
            //     $report->updated_at_date = $report->updated_at->format('d/m/Y');
            //     $report->makeHidden(['inventory_id', 'created_by', 'sku']);




            //     return $report;
            // });

            // return response()->json($reports->isEmpty() ? [] :$reports, Response::HTTP_OK);

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



            $result = $this->_inventoryService->searchRecords($request);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };



            // $inventories = $this->fetchInventories($request);

            // if ($inventories) {


            //     $inventories->each(function ($inventory) {

            //         $inventory->available = $inventory->quantity - $inventory->reserved;
            //     });


            // }

            // return response()->json($inventories->isEmpty() ? [] : $inventories, Response::HTTP_OK);



        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    /**
     * @OA\Get(
     *     path="/api/inventory/fetch-by-type",
     *     tags={"Inventories"},
     *     summary="Search inventory records by type_id",
     *     description="Search inventory records by providing  type_id. Returns matching inventory records.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type_id"},
     *             @OA\Property(property="type_id", type="integer", example=1)
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




    public function fetchByType(Request $request)
    {
        try {


            // set validation rules
            $rules = [
                'type_id' => 'required|integer|exists:item_types,id,is_deleted,0',
            ];

            // Define custom error messages
            $customMessages = [
                'type_id.required' => 'חובה לשלוח מספר פריט.',
                'type_id.integer' => 'ערך הקלט שנשלח אינו תקין.',
                'type_id.exists' => 'סוג פריט שנשלח אינו קיים במערכת.',
            ];

            // validate the request with custom error messages
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $result = $this->_inventoryService->fetchByType($request);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };


            // $inventories = Inventory::where('type_id',$request->input('type_id'))
            // ->where('is_deleted', 0)
            // ->select('id','sku', 'type_id', 'quantity', 'reserved', 'detailed_description')
            // ->get();

            // $inventories->each(function ($inventory) {

            //     $inventory->available = $inventory->quantity - $inventory->reserved;
            //     // Hide the fields 'type', 'quantity', and 'reserved'
            //     $inventory->makeHidden(['quantity', 'reserved', 'type_id']);
            // });

            // return response()->json($inventories->isEmpty() ? [] : $inventories, Response::HTTP_OK);

        } catch (\Exception $e) {

            Log::error($e->getMessage());

        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    //? search based on request->input('query').
    private function fetchInventories(Request $request)
    {
        try {
            $query = $request->input('query');

            return Inventory::with([ 'itemType'])

                ->where('is_deleted', 0)

                ->where(function ($queryBuilder) use ($query) {


                    // Search by item_type type field
                    $queryBuilder->orWhereHas('itemType', function ($itemTypeQuery) use ($query) {
                        $itemTypeQuery->where('type', 'like', "%$query%");
                    });

                    // Search by order sku
                    $queryBuilder->orWhere('sku', 'like', "%$query%");

                    // Search by order sku
                    $queryBuilder->orWhere('detailed_description', 'like', "%$query%");

                })

                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


}