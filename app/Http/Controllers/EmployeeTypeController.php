<?php

namespace App\Http\Controllers;

use App\Models\EmployeeType;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class EmployeeTypeController extends Controller
{
    //


    /**
     * Display a listing of the employee types.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *      path="/api/employeetypes",
     *      tags={"Employee Types"},
     *      summary="Get all employee types",
     *      description="Retrieves all employee types available in the system.",
     *      security={{"bearerAuth": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", description="Employee type ID"),
     *                  @OA\Property(property="name", type="string", example="keva", description="Employee type name")
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

            $empTypeRecords=EmployeeType::where('is_deleted',false)->get();
            return response()->json($empTypeRecords->isEmpty()? []:$empTypeRecords,Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}