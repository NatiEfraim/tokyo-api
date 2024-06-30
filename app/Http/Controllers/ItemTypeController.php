<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Services\ItemType\ItemTypeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;







class ItemTypeController extends Controller
{
    //

    protected $_itemTypeSerivce;

    public function __construct()
    {
       $this->_itemTypeSerivce = new ItemTypeService();
    }


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
     *                  @OA\Property(property="type", type="string", example="computer", description="item type name"),
     *                  @OA\Property(property="icon_number", type="integer", example="7", description="icon-type"),
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

            $result =$this->_itemTypeSerivce->index();

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
     *             @OA\Property(property="type", type="string", example="New Department type"),
     *             @OA\Property(property="icon_number", type="integer", example="2"),
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
                'type.unique' => 'סוג הפריט כבר קיים עבור פריטים שאינם נמחקו.',

                'icon_number.required' => 'שדה אייקון הוא חובה.',
                'icon_number.integer' => 'שדה אייקון אינו תקין.',
                'icon_number.between' => 'שדה אייקון אינו תקין.',
            ];

            // Set the rules
            $rules = [

                // 'type' => 'required|string|min:2|max:255',
                'type' => [
                    'required',
                    'string',
                    'min:2',
                    'max:255',
                    'unique:item_types,type,NULL,id,is_deleted,0', // Custom unique rule

                ],
                'icon_number' => 'required|integer|between:1,7',
            ];

            // Validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $result = $this->_itemTypeSerivce->store($request);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::CREATED => response()->json(['message' => $result['message']], Response::HTTP_CREATED),

                Status::BAD_REQUEST => response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST),

                Status::UNPROCESSABLE_ENTITY => response()->json(['message' => $result['message']], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json(['message' => $result['message']], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };


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
        
        try {

            $result = $this->_itemTypeSerivce->destroy($id);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json(['message' => $result['message']], Response::HTTP_OK),

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
     * @OA\Put(
     *     path="/api/item-type/{id}",
     *     tags={"Item type"},
     *     summary="Update an item-type item",
     *     description="Updates an item-type item with the provided ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the item-type item to update",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Inventory object to update",
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", maxLength=255, example="Electronics"),
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

    public function update(Request $request, $id = null)
    {
  

        try {

            if (is_null($id)) {
                return response()->json(['message' => 'יש לשלוח מספר מזהה של שורה'], Response::HTTP_BAD_REQUEST);
            }


            // Set custom error messages in Hebrew
            $customMessages = [

                'type.required' => 'שדה השם הוא חובה.',
                'type.string' => 'שדה ערך שם מחלקה אינו תקין.',
                'type.min' => 'שדה ערך שם מחלקה אינו תקין.',
                'type.max' => 'שדה ערך שם מחלקה אינו תקין.',


            ];

            // Set the rules
            $rules = [

                'type' => 'required|string|min:2|max:255',

            ];

            // Validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            $result = $this->_itemTypeSerivce->update($request,$id);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json(['message' => $result['message']], Response::HTTP_OK),

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
     * @OA\Get(
     *     path="/api/item-type/search-records",
     *     tags={"Item type"},
     *     summary="Search item-type records item type",
     *     description="Search item-type records by providing either SKU or item type. Returns matching item-type records.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"query"},
     *             @OA\Property(property="query", type="string", example="computer")
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
     *                  @OA\Property(property="id", type="integer", description="item type ID"),
     *                  @OA\Property(property="type", type="string", example="computer", description="item type name"),
     *                  @OA\Property(property="icon_number", type="integer", example="7", description="icon-type"),
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
                'query.required' => 'יש לשלוח ערך לחיפוש',
                'query.string' => 'ערך שנשלח אינו תקין',
                'query.min' => 'ערך שנשלח אינו תקין',
                'query.max' => 'ערך שנשלח אינו תקין',
            ];
            //set the rules
            $rules = [
                'query' => 'required|string|min:1|max:255',
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {

                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            $result = $this->_itemTypeSerivce->searchRecords($request);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

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
