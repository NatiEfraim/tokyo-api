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
     *      path="/api/distributions",
     *      tags={"Distributions"},
     *      summary="Get all distributions",
     *      description="Returns a list of all distributions.",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="comment", type="string", example="Velit veritatis quia vel nemo qui. Eaque commodi expedita enim libero ut. Porro ducimus repellendus tenetur."),
     *                  @OA\Property(property="status", type="integer", example=1),
     *                  @OA\Property(property="quantity", type="integer", example=44),
     *                  @OA\Property(property="inventory_id", type="integer", example=24),
     *                  @OA\Property(property="created_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time", example="2024-04-07T11:42:45.000000Z"),
     *                  @OA\Property(property="inventory", type="object",
     *                      @OA\Property(property="id", type="integer", example=24),
     *                      @OA\Property(property="quantity", type="integer", example=10),
     *                      @OA\Property(property="sku", type="string", example="1359395842801"),
     *                      @OA\Property(property="item_type", type="string", example="magni"),
     *                      @OA\Property(property="detailed_description", type="string", example="Velit ut ipsam neque tempora est dicta. Et distinctio eligendi expedita corporis assumenda aspernatur hic.")
     *                  )
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
            $inventories = Inventory::where('is_deleted', 0)->get();
            return \response()->json($inventories, Response::HTTP_OK);
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



            $inventory = Inventory::create($request->validated());
            $currentTime = Carbon::now()->toDateTimeString();
            $inventory->updated_at = $currentTime;
            $inventory->created_at = $currentTime;
            $inventory->save();

            return response()->json(['message' => 'שורה נוצרה בהצלחה.'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    /**
     * @OA\Patch(
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
     *         required=true,
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


            $currentTime = Carbon::now()->toDateTimeString();


            $inventory->update($request->validated());

            $inventory->updated_at =  $currentTime;
            $inventory->save();

            return response()->json(['message' => 'שורה התעדכנה בהצלחה.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
