<?php

namespace App\Services\Distribution;

use App\Enums\Status;

use App\Enums\EmployeeType;

use App\Enums\DistributionStatus;
use App\Http\Requests\AllocationDistributionRequest;
use App\Http\Requests\CanceledDistributionRequest;
use App\Http\Requests\ChangeStatusDistributionRequest;
use App\Http\Requests\StoreDistributionRequest;
use App\Http\Requests\UpdateDistributionRequest;
use App\Mail\ApprovedOrder;
use App\Mail\CanceledOrder;
use App\Models\Client;
use App\Models\Distribution;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Mail\DistributionSuccess;
use App\Mail\DistributionFailure;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

// use Illuminate\Http\Response;



class DistributionService{





    /**
     * fetch all distributions records from distributions table.
     **/

    public function index()
    {
        try {

            $distributions = Distribution::with(['itemType', 'createdForUser'])
                ->where('is_deleted', 0)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            $distributions->each(function ($distribution) {

                // Format the created_at and updated_at timestamps

                $distribution->created_at_date = optional($distribution->created_at)->format('d/m/Y') ?? null;
                $distribution->updated_at_date = optional($distribution->updated_at)->format('d/m/Y') ?? null;

                return $distribution;
            });



            return [
                'status' => Status::OK,
                'data' => $distributions->isEmpty() ? [] : $distributions,
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
     * fetch quartermaster that has been updated the the distribution records
     **/
 
    public function fetchQuartermaster($id = null)
    {
        try {



            if (is_null($id)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'יש לשלוח מזהה שורה.',
                ];
            }

            // Fetch distribution by ID
            $distribution = Distribution::with(['quartermaster'])
                ->where('id', $id)
                ->where('is_deleted', 0)
                ->first();

            if (is_null($distribution) || is_null($distribution->quartermaster_id)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'הזמנה זו אינה קיימת במערכת.',
                ];    

            }

            // Format date and time
            $createdAt = $distribution->updated_at->format('H:i:s'); // Time
            $createdAtDate = $distribution->updated_at->format('d/m/Y'); // Date

            // Extract user data
            $quartermasterName = $distribution->quartermaster->name;
            $quartermasterId = $distribution->quartermaster->id;

            // Prepare response data
            $responseData = [
                'quartermaster_name' => $quartermasterName,
                'quartermaster_id' => $quartermasterId,
                'created_at_time' => $createdAt,
                'created_at_date' => $createdAtDate,
            ];


            return [
                'status' => Status::OK,
                'data' =>  $responseData,
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
     * fetch distributions records and group_by by order_number and type_id query is optinal
     **/

    public function fetchRecordsByType(Request $request)
    {
        try {

            $user_auth = auth()->user();
            $roleName = $user_auth->roles->first()->name;

            $query = $request->input('query');

            // Build the base query
            $baseQuery = Distribution::with(['itemType', 'createdForUser'])
            ->where('is_deleted', 0)
                ->orderBy('created_at', 'desc');

            // Add role-based filtering
            if ($roleName == 'user') {
                //? fetch records only what the user has been created.
                $baseQuery->where('created_by', $user_auth->id);
            }

            // Add search query filtering if provided
            if (!empty($query)) {
                $baseQuery->where(function ($queryBuilder) use ($query) {
                    // Search by item_type type field
                    $queryBuilder->orWhereHas('itemType', function ($itemTypeQuery) use ($query) {
                        $itemTypeQuery->where('type', 'like', "%$query%");
                    });

                    // Search by personal number
                    $queryBuilder->orWhereHas('createdForUser', function ($userQuery) use ($query) {
                        $userQuery->where('personal_number', 'like', "%$query%");
                    });

                    // Search by full name
                    $queryBuilder->orWhereHas('createdForUser', function ($userQuery) use ($query) {
                        $userQuery->where('name', 'like', "%$query%");
                    });
                    // // Search by order number
                    // $queryBuilder->orWhere('order_number', 'like', "%$query%");
                });
            }

            // Execute the query and paginate the results
            $distributions = $baseQuery->paginate(20);

            $distributions->each(function ($distribution) {
                // Format the created_at and updated_at timestamps
                $distribution->created_at_date = optional($distribution->created_at)->format('d/m/Y');
                $distribution->updated_at_date = optional($distribution->updated_at)->format('d/m/Y');
                // Translate each name of employee_type fields 
                if ($distribution->createdForUser && $distribution->createdForUser->employeeType) {
                    $distribution->createdForUser->employeeType->population = $distribution->createdForUser->employeeType->translated_employee_type;
                }

                return $distribution;
            });

            // Create a new collection to store unique distributions by order_number
            $uniqueDistributions = collect();

            // Temporary storage to keep track of processed order_number and type_id combinations
            $processedCombinations = [];

            foreach ($distributions as $distribution) {
                //? make sure not duplicate records
                $combinationKey = $distribution->order_number . '_' . $distribution->type_id;

                if (!in_array($combinationKey, $processedCombinations)) {
                    $uniqueDistributions->push($distribution);
                    //? mark that key
                    $processedCombinations[] = $combinationKey;
                }
            }

            return [
                'status' => Status::OK,
                'data' =>  $uniqueDistributions->isEmpty() ? [] : $uniqueDistributions,
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
     * fetch distributions records only where status fileds is 2 approved
     **/    


    public function fetchApprovedDistribution(Request $request)
    {
        try {


            // ? fetch records has been approved based on order_number
            $distributions = Distribution::with(['createdForUser', 'itemType'])
                ->where('is_deleted', 0)
                ->where('order_number', $request->input('order_number'))
                ->where('status',DistributionStatus::APPROVED->value)
                ->get();


            // Loop through each record and add inventory_items object

            $distributions->transform(function ($distribution) {

                //?format each date.
                $distribution->created_at_date = optional($distribution->created_at)->format('d/m/Y');
                $distribution->updated_at_date = optional($distribution->updated_at)->format('d/m/Y');

                return $distribution;

            });

            return [

                'status' => Status::OK,

                'data' =>  $distributions->isEmpty() ? [] : $distributions,

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
     * fetch distribution records based on id
     **/    

    public function getRecordById($id = null)
    {
        try {

            if (is_null($id)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'הזמנה זו אינה קיימת במערכת.',
                ];    

            }

            $distribution = Distribution::with(['itemType', 'createdForUser', 'inventory'])
            ->where('id', $id)
                ->where('is_deleted', 0)
                ->first();


            return [

                'status' => Status::OK,

                'data' =>  $distribution->isEmpty() ? [] : $distribution,

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
     * destroy distribution records based on id
     **/    

    public function destroy($id = null)
    {
        try {

            if (is_null($id)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'יש לשלוח מזהה שורה של הזמנה.',
                ];    

            }

            $distirbution = Distribution::where('is_deleted', 0)->where('id', $id)->first();

            if (is_null($distirbution)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'הזמנה זו אינה קיימת במערכת.',
                ];    
            }

            $distirbution->update([
                'is_deleted' => true,
            ]);

            return [
                'status' => Status::OK,
                'message' => 'הזמנה נמחקה מהמערכת בהצלחה.',
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
     * store a new distribution records and send user email whether it succeed or failure
     **/    

    public function store(StoreDistributionRequest $request)
    {
        try {



            DB::beginTransaction();

            $user_auth = Auth::user();

            //? create new clients records. - and get the client_id

            //casting the value.
            $emp_type = (int) $request->input('employee_type');

            //set the first letter for the persnal_number
            $personal_number = match ($emp_type) {
                EmployeeType::KEVA->value, EmployeeType::SADIR->value => 's' . $request->input('personal_number'),
                EmployeeType::MILUIM->value => 'm' . $request->input('personal_number'),
                EmployeeType::OVED_TZAHAL->value => 'c' . $request->input('personal_number'),
                default => throw new \InvalidArgumentException('סוג עובד לא תקין.'),
            };

            //? try to find the ckient it the records  is exisit.
            $client = Client::where('personal_number', $request->input('personal_number'))->first();

            if ($client) {

                //? update client that has been deleted
                $client->update([
                    'name' => $request->input('name'),
                    'personal_number' => $request->input('personal_number'),
                    'email' => "{$personal_number}@army.idf.il",
                    'phone' => $request->input('phone'),
                    'emp_type_id' => $request->input('employee_type'),
                    'department_id' => $request->input('department_id'),
                    'is_deleted' => '0',
                ]);


            } else {


                //? create client records - from scratch
                $client = Client::create([
                    'name' => $request->input('name'),
                    'personal_number' => $request->input('personal_number'),
                    'email' => "{$personal_number}@army.idf.il",
                    'phone' => $request->input('phone'),
                    'emp_type_id' => $request->input('employee_type'),
                    'department_id' => $request->input('department_id'),
                ]);
            }


            // Fetch all existing order numbers
            $existingOrderNumbersQuery = Distribution::pluck('order_number');

            // Generate a unique 7-digit order number
            do {
                $orderNumber = random_int(1000000, 9999999); // Generates a random integer between 1000000 and 9999999
            } while ($existingOrderNumbersQuery->contains($orderNumber));



            $allQuantity = array_sum(array_column($request->input('items'), 'quantity'));

            foreach ($request->input('items') as $item) {
                $itemType = $item['type_id'];
                $quantity = $item['quantity'];
                $comment = $item['comment'] ?? null;

                Distribution::create([
                    'order_number' => (string) $orderNumber,
                    'user_comment' => $request->input('user_comment') ?? 'אין הערות על ההזמנה.',
                    'type_comment' => $comment ?? 'אין הערות על הפריט.',
                    'total_quantity' => $allQuantity, //? all qty per order_number
                    'quantity_per_item' => $quantity, //? qty per item_type selcted
                    'status' => DistributionStatus::PENDING->value,
                    'type_id' => $itemType,
                    'created_by' => $user_auth->id,
                    'created_for' => $client->id,

                    'sku' => 'לא הוקצה פריט.',
                    'quartermaster_comment' => $request->input('quartermaster_comment') ?? 'אין הערות אפסנאי.',
                    'admin_comment' => $request->input('admin_comment') ?? 'אין הערות מנהל.',
                    'canceled_reason' => $request->input('canceled_reason') ?? 'אין סיבת ביטול.',
                ]);
            }

            $orderNumber = (int) $orderNumber; // Cast to integer

            // Send success email
            Mail::to($user_auth->email)->send(new DistributionSuccess($user_auth, $client, $orderNumber));

            DB::commit();

            return [

                'status' => Status::CREATED,

                'message' => 'הזמנה נשמרה במערכת בהצלחה.',

            ];    

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of any error
            Log::error($e->getMessage());
        }

        // Send failure email
        Mail::to($user_auth->email)->send(new DistributionFailure($user_auth));

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];   

    }



    /**
     * allocation records based on order_number fileds on the budy request
     **/   

    public function allocationRecords(AllocationDistributionRequest $request)
    {
        try {

            if (is_null($request->input('admin_comment')) && $request->input('status') == DistributionStatus::CANCELD->value) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'חובה לשלוח סיבת ביטול.',
                ];   

            }

            if (is_null($request->input('inventory_items')) && $request->input('status') == DistributionStatus::APPROVED->value) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'יש להקצות פריטים.',
                ];   

            }

            // Fetch the records with the given order_number and is_deleted is false
            $distributionRecords = Distribution::where('order_number', $request->input('order_number'))->where('is_deleted', false)->get();

            // Check if records exist
            if ($distributionRecords->isEmpty()) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'לא נמצאו רשומות עם מספר הזמנה זה במערכת.',
                ];   
            }

            $createdByUser = User::where('id', $distributionRecords[0]->created_by)
                ->where('is_deleted', false)
                ->first();

            if (is_null($createdByUser)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'לא נמצא משתמש במערכת עבור הזמנה זו.',
                ];   
            }




            DB::beginTransaction(); // Start a database transaction



            // Track processed type_ids
            $processedTypeIds = [];

            //? distribution records has been approved
            if ($request->input('status') === DistributionStatus::APPROVED->value) {


                // Loop through each type_id in the request
                foreach ($request->input('inventory_items') as $key => $items) {

                    // Skip if this type_id has already been processed
                    if (in_array($items['type_id'], $processedTypeIds)) {
                        continue;
                    }

                    // Mark this type_id as processed
                    $processedTypeIds[] = $items['type_id'];

                    //? make sure admin approved that type order.
                    $sizeArrayItem = count($items['items']);

                    if ((($sizeArrayItem == 0) && (is_null($items['canceled_reason'])))
                        || (($sizeArrayItem !== 0) && (is_null($items['canceled_reason']) == false))
                    ) {

                        DB::rollBack(); // Rollback the transaction
                        return [
                            'status' => Status::BAD_REQUEST,
                            'message' => 'הנתונים שנשלחו אינם תקינים.',
                        ];   
                    }


                    // Find the first distribution record with the matching type_id that has not been processed
                    $distributionRecord = $distributionRecords->firstWhere('type_id', $items['type_id']);

                    if ($distributionRecord) {

                        if ($sizeArrayItem == 0) {

                            //? records has not approved!

                            $distributionRecord->update([
                                'status' => DistributionStatus::CANCELD->value,
                                'canceled_reason' => $items['canceled_reason'], //save canceled_reason for each records that not approved
                            ]);

                        } else {

                            //? records has been approved!
                            // //? make sure sum of qty match with qty_total that admin allocated
                            $allQuantity = array_sum(array_column($items['items'], 'quantity'));

                            // Loop on each item within the type_id
                            foreach ($items['items'] as $inventoryItem) {
                                $idInventory = $inventoryItem['inventory_id']; // Save the inventory_id records
                                $quantity = $inventoryItem['quantity']; //? qty per sku

                                $inventory = Inventory::where('id', $idInventory)->where('is_deleted', false)->first();

                                if (is_null($inventory) || $inventory->type_id !== $items['type_id']) {

                                    DB::rollBack(); // Rollback the transaction

                                    return [
                                        'status' => Status::BAD_REQUEST,
                                        'message' => 'אחד מהפרטים שבמלאי שנשלחו אינם תקינים.',
                                    ];   

                                }

                                $available = $inventory->quantity - $inventory->reserved;

                                if ($quantity > $available) {

                                    DB::rollBack(); // Rollback the transaction
                                    return [
                                        'status' => Status::BAD_REQUEST,
                                        'message' => 'כמות שנשלח עבור ' . $inventory->sku . ' חסרה במלאי.',
                                    ];   
                                }

                                // Update inventory records based on inventory_id
                                $inventory->update([
                                    'reserved' => $inventory->reserved + $quantity, // Increase the reserved
                                ]);

                                //? create a new records per each inveotry records.
                                Distribution::create([
                                    'order_number' => $distributionRecord->order_number,
                                    'user_comment' => $distributionRecord->user_comment ?? null,
                                    'type_comment' => $distributionRecord->type_comment ?? null,
                                    'total_quantity' => $distributionRecord->total_quantity,
                                    'quantity_per_item' => $distributionRecord->quantity_per_item,
                                    'status' => DistributionStatus::APPROVED->value,
                                    'type_id' => $distributionRecord->type_id,
                                    'created_by' => $distributionRecord->created_by,
                                    'created_for' => $distributionRecord->created_for,
                                    'quantity_per_inventory' => $quantity, //set qty per invetory
                                    'quantity_approved' => $allQuantity, //sum-up of qty that approved by admin.
                                    'sku' => $inventory->sku, //set relations
                                    'inventory_id' => $inventory->id, //set relations
                                    'admin_comment' => $request->input('admin_comment') ?? 'אין הערות מנהל.',
                                    'quartermaster_comment' => $request->input('quartermaster_comment') ?? 'אין הערות אפסנאי.',
                                    'canceled_reason' => $distributionRecord->canceled_reason ?? 'אין סיבת ביטול.',
                                ]);
                            }
                            //? deleted records (copy records - as time as admin selcted sku)
                            $distributionRecord->delete();
                        }
                    }
                }

                // Send aproved order email
                Mail::to($createdByUser->email)->send(new ApprovedOrder($createdByUser, $request->input('order_number')));

                //? distribution records has been canceld
            } elseif ($request->input('status') === DistributionStatus::CANCELD->value) {


                //? Loop through each record and update the fields

                foreach ($distributionRecords as $distributionRecord) {
                    $distributionRecord->update([
                        'status' => DistributionStatus::CANCELD->value,
                        'sku' => 'לא הוקצה פריט.',
                        'admin_comment' => $request->input('admin_comment') ?? 'אין הערות מנהל.',
                        'quartermaster_comment' => $request->input('quartermaster_comment') ?? 'אין הערות אפסנאי.',
                    ]);

                }

                // Send cancel order email
                Mail::to($createdByUser->email)->send(new CanceledOrder($createdByUser, $request->input('order_number')));


            }


            DB::commit(); // commit all changes in database.

            return [
                'status' => Status::OK,
                'message' => 'הזמנה התעדכנה במערכת בהצלחה.',
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
     * changed status fileds on distribution records value can be pending or collected
     **/   

    public function changeStatus(ChangeStatusDistributionRequest $request)
    {
        try {


            $user = auth()->user();

            if (($request->input('status') !== DistributionStatus::PENDING->value) && ($request->input('status') !== DistributionStatus::COLLECTED->value)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'ערך סטטוס אינו תקין.',
                ];  
                
            }

            if (is_null($request->input('quartermaster_comment')) && $request->input('status') == DistributionStatus::PENDING->value) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'יש להוסיף הערה על ההזמנה למנהל.',
                ];  
            }
            
            $statusValue = (int) $request->input('status');

            // Fetch the records with the given order_number and is_deleted is false
            $distributionRecords = Distribution::where('order_number', $request->input('order_number'))
                ->where('status', DistributionStatus::APPROVED->value)
                ->where('is_deleted', false)
                ->get();

            // Check if records exist
            if ($distributionRecords->isEmpty()) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'לא נמצאו רשומות עם מספר הזמנה זה במערכת.',
                ];  

            }

            DB::beginTransaction(); // Start a database transaction

            if ($statusValue == DistributionStatus::COLLECTED->value) {

                // Loop through each record and update the fields as collected items

                foreach ($distributionRecords as $distributionRecord) {

                    //? fetch associated inventory_id records
                    $inventoryRecord = Inventory::where('id', $distributionRecord->inventory_id)
                        ->where('is_deleted', false)
                        ->first();

                    if (is_null($inventoryRecord)) {
                        DB::rollBack(); // Rollback the transaction in case of any error

                        return [
                            'status' => Status::BAD_REQUEST,
                            'message' => 'לא נמצא פריט במלאי המערכת.',
                        ];  
                    }

                    //?update each inveotry records reserved & quantity
                    $inventoryRecord->update([
                        'reserved' => $inventoryRecord->reserved - $distributionRecord->quantity_per_inventory,
                        'quantity' => $inventoryRecord->quantity - $distributionRecord->quantity_per_inventory,
                    ]);

                    $distributionRecord->update([
                        'status' => DistributionStatus::COLLECTED->value,
                        'quartermaster_id' => $user->id, ///save the user that sign on that order_number
                        'quartermaster_comment' => $request->input('quartermaster_comment') ?? 'אין הערות אפסנאי.', //can be a comment or Reference Number
                    ]);

                }

            } else {
                //?distributions records back to pending for admin again.

                // Collection to store unique distributions by type_id
                $uniqueDistributions = collect();

                // Loop through the fetched records and ensure unique type_id
                foreach ($distributionRecords as $distribution) {
                    $typeId = $distribution->type_id;

                    //? fetch associated inventory_id records
                    $inventoryRecord = Inventory::where('id', $distribution->inventory_id)
                        ->where('is_deleted', false)
                        ->first();

                    if (is_null($inventoryRecord)) {
                        DB::rollBack(); // Rollback the transaction in case of any error

                        return [
                            'status' => Status::BAD_REQUEST,
                            'message' => 'לא נמצא פריט במלאי המערכת.',
                        ];  
                    }

                    //?update each inveotry records reserved (no longer reserved.)
                    $inventoryRecord->update([
                        'reserved' => $inventoryRecord->reserved - $distribution->quantity_per_inventory,
                    ]);

                    if (!$uniqueDistributions->contains('type_id', $distribution->type_id)) {

                        $newDistribution = Distribution::create([
                            'order_number' => $distribution->order_number,
                            'user_comment' => $distribution->user_comment ?? 'אין הערות על ההזמנה.',
                            'type_comment' => $distribution->type_comment ?? 'אין הערות על הפריט.',
                            'total_quantity' => $distribution->total_quantity, //? all qty per order_number
                            'quantity_per_item' => $distribution->quantity_per_item, //? qty per item_type selcted
                            'status' => DistributionStatus::PENDING->value, ///back to admin.
                            'type_id' => $distribution->type_id,
                            'created_by' => $distribution->created_by,
                            'created_for' => $distribution->created_for,
                            'quantity_per_inventory' => 0,
                            'quantity_approved' => 0,
                            'sku' => null,
                            'quartermaster_comment' => $request->input('quartermaster_comment') ?? 'אין הערות אפסנאי.',
                            'admin_comment' => $distribution->admin_comment ?? 'אין הערות מנהל.',
                            'canceled_reason' => $distribution->canceled_reason ?? 'אין סיבת ביטול.',

                        ]);
                        // Add the type_id to the unique collection
                        $uniqueDistributions->push($newDistribution);
                    }

                    $distribution->delete(); ///remove from the database

                }
            }

            DB::commit(); // commit all changes in database.


            return [
                'status' => Status::OK,
                'message' => 'הזמנה התעדכנה במערכת בהצלחה.',
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





}
