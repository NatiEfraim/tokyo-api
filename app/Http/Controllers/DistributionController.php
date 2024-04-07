<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DistributionController extends Controller
{
    //

    /**
     * Display a listing of the distributions.
     *
     * @return \Illuminate\Http\Response
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 1,
     *       "comment": "Velit veritatis quia vel nemo qui. Eaque commodi expedita enim libero ut. Porro ducimus repellendus tenetur.",
     *       "status": 1,
     *       "quantity": 44,
     *       "inventory_id": 24,
     *       "created_at": "2024-04-07T11:42:45.000000Z",
     *       "updated_at": "2024-04-07T11:42:45.000000Z",
     *       "inventory": {
     *         "id": 24,
     *         "quantity": 10,
     *         "sku": "1359395842801",
     *         "item_type": "magni",
     *         "detailed_description": "Velit ut ipsam neque tempora est dicta. Et distinctio eligendi expedita corporis assumenda aspernatur hic."
     *       }
     *     }
     *   ]
     * }
     * @response 500 {
     *   "message": "התרחש בעיית שרת יש לנסות שוב מאוחר יותר."
     * }
     */


    public function index()
    {
        try {

            $distributions = Distribution::with('inventory')
                ->where('is_deleted', 0)
                ->get()
                ->map(function ($distribution) {
                    // Format the timestamps - not changed.
                    $distribution->created_at = $distribution->created_at->format('Y-m-d H:i:s');
                    $distribution->updated_at = $distribution->updated_at->format('Y-m-d H:i:s');
                    return $distribution;
                });

            return response()->json($distributions, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
