<?php

namespace App\Services\Inventory;

use App\Enums\Status;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Foundation\Mix;
use Illuminate\Support\Facades\Auth;


class InventoryService{



    /**
     * fetch all inventories records from inventories table.
     *  @return array
     **/


    public function index()
    {
        try {


            $inventories = Inventory::with(['itemType'])
                ->where('is_deleted', 0)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $inventories->each(function ($inventory) {

                $inventory->available = $inventory->quantity - $inventory->reserved;
            });

            return [
                'status' => Status::OK,
                'data' => $inventories->isEmpty() ? [] : $inventories,
            ];

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];
    }



    /**
     * select id and sku fileds on inventories records from inventories table.
     *  @return array
     **/

    public function getSkuRecords()
    {
        try {



            $inventories = Inventory::select('id', 'sku')
                ->where('is_deleted', 0)
                ->get()
                ->map(function ($inventory) {
                    return [
                        'id' => $inventory->id,
                        'name' => $inventory->sku
                    ];
                });


            return [
                    'status' => Status::OK,
                    'data' => $inventories->isEmpty() ? [] : $inventories,
                ];



        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];

    }


    /**
     * fetch invntory records based on id records.
     *  @return array
     **/

    public function getRecordById($id = null)
    {


        try {

            if (is_null($id)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'יש לשלוח מספר מזהה של שורה.',
                ];
            }

            $inventory = Inventory::with(['itemType'])
                ->where('is_deleted', 0)
                ->where('id', $id)
                ->first();

            if (is_null($inventory)) {
                return [
                    'status' => Status::OK,
                    'data' =>  [],
                ];
            }

            $inventory->available = $inventory->quantity - $inventory->reserved;

            return [
                'status' => Status::OK,
                'data' => $inventory->isEmpty() ? [] : $inventory,
            ];

        } catch (\Exception $e) {

            Log::error($e->getMessage());
        }

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];

    }


    /**
     * fetch inventories records based on type_id and sku query is optinal.
     *  @return array
     **/

    public function fetchBySku(Request $request)
    {
        try {

            $searchQuery = str_replace(' ', '', $request->input('query'));

            // Search users by name (ignoring spaces)
            $invetoriesRecords = Inventory::with(['itemType'])
                ->where('type_id', $request->input('type_id'))
                ->where('is_deleted', false)
                ->where('sku', 'LIKE', '%' . $searchQuery . '%')

                ->orderBy('id', 'asc')
                ->get();

            if (is_null($invetoriesRecords)) {
                return [
                    'status' => Status::OK,
                    'data' =>  [] ,
                ];
            }
            
            $invetoriesRecords->each(function ($inventory) {

                $inventory->available = $inventory->quantity - $inventory->reserved;
            });


            return [
                'status' => Status::OK,
                'data' => $invetoriesRecords->isEmpty() ? [] : $invetoriesRecords,
            ];


        } catch (\Exception $e) {

            Log::error($e->getMessage());
        }

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];

    }


    /**
     * destroy inventory records based on id.
     *  @return array
     **/

    public function destroy($id = null)
    {

        try {

            if (is_null($id)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'יש לשלוח מספר מזהה של שורה.',
                ];                
            }

            $inventory = Inventory::where('is_deleted', 0)
            ->where('id', $id)
                ->first();

            if (is_null($inventory)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'שורה אינה קיימת במערכת.',
                ];            }

            $inventory->update([
                'is_deleted' => true,
            ]);

            return [
                'status' => Status::OK,
                'message' => 'שורה נמחקה בהצלחה.',
            ];


        } catch (\Exception $e) {

            Log::error($e->getMessage());
        }


        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];

    }



    /**
     * store a new inventory records on database.
     *  @return array
     **/

    public function store(StoreInventoryRequest $request)
    {
        try {

            if ($request->input('reserved') > $request->input('quantity')) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'נתונים שנשלחו שגויים.',
                ];   
            }

            Inventory::create($request->validated());


            return [
                'status' => Status::OK,
                'message' => 'שורה נוצרה בהצלחה.',
            ];   



        } catch (\Exception $e) {

            Log::error($e->getMessage());

        }


        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];

    }


    /**
     * update exisit inventory records on database and store a new report records.
     *  @return array
     **/


    public function update(UpdateInventoryRequest $request, $id = null)
    {

        try {

            if (is_null($id)) {


                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'יש לשלוח מספר מזהה של שורה.',
                ];   

            }

            $authUser = Auth::user();


            $inventory = Inventory::where('is_deleted', 0)
            ->where('id', $id)
                ->first();

            if (is_null($inventory)) {


                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'שורה אינה קיימת במערכת.',
                ];   

            }


            $currentTime = Carbon::now()->toDateTimeString();


            DB::beginTransaction();

            if ($request->input('quantity')) {
                //? created new reports records
                Report::create([

                    'hour' => Carbon::now()->format('H:i'), // Current hour and minutes in 'HH:MM' format
                    'created_by' => $authUser->id,
                    'last_quantity' => $inventory->quantity,
                    'new_quantity' => $request->input('quantity'),
                    'sku' => $inventory->sku,
                    'inventory_id' => $inventory->id,
                ]);
            }

            $inventory->update([
                'quantity' => $request->input('quantity') ? $request->input('quantity') : $inventory->quantity,
                'sku' => $request->input('sku') ? $request->input('sku') : $inventory->sku,
                'type_id' => $request->input('type_id') ?  $request->input('type_id') : $inventory->type_id,
                'detailed_description' => $request->input('detailed_description') ? $request->input('detailed_description') : $inventory->detailed_description,
                'updated_at' => $currentTime,
            ]);

            DB::commit();


            return [
                'status' => Status::OK,
                'message' => 'שורה התעדכנה בהצלחה.',
            ];   


        } catch (\Exception $e) {

            DB::rollBack(); // Rollback the transaction in case of any error
            Log::error($e->getMessage());
        }


        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];


    }


    /**
     * fetch reports records associated based on inventory_id records.
     *  @return array
     **/
    public function fetchReport(Request $request)
    {

        try {




            $reports = Report::with(['createdByUser'])
                ->where('inventory_id', $request->input('inventory_id'))
                ->where('is_deleted', false)
                ->get();



            $reports->each(function ($report) {

                // Format the created_at and updated_at timestamps
                $report->created_at_date = $report->created_at->format('d/m/Y');
                $report->updated_at_date = $report->updated_at->format('d/m/Y');

                $report->makeHidden(['inventory_id', 'created_by', 'sku']);


                return $report;

            });


            return [
                'status' => Status::OK,
                'data' => $reports->isEmpty() ? [] : $reports,
            ];


        } catch (\Exception $e) {

            Log::error($e->getMessage());
        }

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];

    }

    /**
     * search inventory records based on query.
     *  @return array
     **/

    public function searchRecords(Request $request)
    {

        try {

            $inventories = $this->fetchInventories($request);//use private function

            if (is_null($inventories)) {

                return [
                    'status' => Status::INTERNAL_SERVER_ERROR,
                    'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
                ];

            }

            $inventories->each(function ($inventory) {

                $inventory->available = $inventory->quantity - $inventory->reserved;
            });


            return [
                'status' => Status::OK,
                'data' => $inventories->isEmpty() ? [] : $inventories,
            ];



        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }


        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];
    }



    /**
     * fetch inventory records based on type_id fileds.
     *  @return array
     **/


    public function fetchByType(Request $request)
    {
        try {




            $inventories = Inventory::where('type_id', $request->input('type_id'))
                ->where('is_deleted', 0)
                ->select('id', 'sku', 'type_id', 'quantity', 'reserved', 'detailed_description')
                ->get();

            $inventories->each(function ($inventory) {

                $inventory->available = $inventory->quantity - $inventory->reserved;
                
                $inventory->makeHidden(['quantity', 'reserved', 'type_id']);
            });

            return [
                'status' => Status::OK,
                'data' => $inventories->isEmpty() ? [] : $inventories,
            ];


        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];

    }





    /**
     * search inventory records based on query input on budy request.
     *  @return mixed
     **/

    private function fetchInventories(Request $request)
    {
        try {

            $query = $request->input('query');

            return Inventory::with(['itemType'])

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

        return null;
    }





}