<?php

namespace App\Http\Controllers;

use App\Models\ItemType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;




class ItemTypeController extends Controller
{
    //
    /**
     * Display a listing of the item types.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *      path="/api/item-type",
     *      tags={"Item type"},
     *      summary="Get all item types",
     *      description="Retrieves all item types available in the system.",
     *      security={{"bearerAuth": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", description="item type ID"),
     *                  @OA\Property(property="name", type="string", example="computer", description="item type name")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="בעיה בשרת. יש לנסות שוב מאוחר יותר.")
     *          )
     *      ),
     * )
     */

    public function index()
    {
        try {

            $itemTypes=ItemType::where('is_deleted',false)->get();
            return response()->json($itemTypes,Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    /**
     * Store a newly created item_type.
     *
     * This endpoint creates a new item_type record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/item-type",
     *     summary="Store a new item_type",
     *     tags={"Item type"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type"},
     *             @OA\Property(property="type", type="string", example="New Department type")
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Department created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Department created successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="אחת מהשדות אינם תקינים")
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


    public function store(Request $request)
    {
        try {


            // Set custom error messages in Hebrew
            $customMessages = [

                'type.required' => 'שדה השם הוא חובה.',
                'type.string' => 'שדה ערך שם מחלקה אינו תקין.',

                'sku.required' => 'שדה מק"ט הוא חובה.',
                'sku.string' => 'שדה מק"ט אינו תקין.',
                'sku.exists' => 'שדה מק"ט קיים במערכת.',
                // 'type.unique' => 'השם שהוזן כבר קיים במערכת.',
            ];

            // Set the rules
            $rules = [
                // 'type' => 'required|unique:item_types,type,NULL,id,is_deleted,0',
                'sku' => 'required|exists:item_types,sku,NULL,id,is_deleted,0',
                'type' => 'required|string|min:2|max:255',
            ];

            // Validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $currentTime = Carbon::now()->toDateTimeString();


            $itemTypeRecord=ItemType::where('type',$request->input('type'))->where('is_deleted',true)->first();
            if (is_null($itemTypeRecord)) {
                //? create new itemTypeRecord record
                ItemType::create([
                    'type' => $request->input('type'),
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime,
                ]);
            }else {
                //? updated itemTypeRecord records that exist in the depatments table
                $itemTypeRecord->update([
                    'type' =>  $request->input('type'),
                    'is_deleted' => 0,
                    'updated_at' =>  $currentTime,
                ]);
            }



            return response()->json(['message' => 'המחלקה נוצרה בהצלחה.'], Response::HTTP_CREATED);
        } catch (\Exception $e) {

            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחשה תקלה בשרת, נסה שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @OA\Delete(
     *      path="/api/item-type/{id}",
     *      tags={"Item type"},
     *      summary="Delete an item_type by ID",
     *      description="Deletes an item_type based on the provided ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the item_type to delete",
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
            $itemTypeRecord = ItemType::where('is_deleted', 0)->where('id', $id)->first();

            if (is_null($itemTypeRecord)) {
                return response()->json(['message' => 'שורה אינה קיימת במערכת.'], Response::HTTP_BAD_REQUEST);
            }
            $itemTypeRecord->update([
                'is_deleted' => true,
            ]);

            return response()->json(['message' => 'שורה נמחקה בהצלחה.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

}
