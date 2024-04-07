<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Inventory;




class InventoryController extends Controller
{
    //

    /**
 * @OA\Get(
 *      path="/api/inventories",
 *      tags={"Inventory"},
 *      summary="Get all inventories",
 *      description="Returns a list of all inventories.",
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
 *                  @OA\Property(property="detailed_description", type="string", example="Neque recusandae corporis totam facere pariatur. Et perspiciatis aut in quia. Placeat quas vero modi magni ut. Voluptas et qui vitae culpa.")
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
            $inventories=Inventory::where('is_deleted',0)->get();
            return \response()->json($inventories,Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function getRecordById($id=null)
    {
        try {
            $inventory = Inventory::where('is_deleted',0)
            ->where('id',$id)
            ->first();
            return \response()->json($inventory,Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
