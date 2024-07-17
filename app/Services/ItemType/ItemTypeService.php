<?php

namespace App\Services\ItemType;

use App\Enums\Status;
use App\Models\Inventory;
use App\Models\ItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


 class ItemTypeService{


    /**
     * fetch all itemType records from item_types table.
     **/
    public function index()
    {
        try {

            $itemTypes = ItemType::where('is_deleted', false)->get();

            return [

                'status' => Status::OK,

                'data' => $itemTypes->isEmpty() ? [] : $itemTypes,
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
     * update exist itemType records on database.
     *  @return array
     **/
    public function update(Request $request, $id = null)
    {


        try {

            if (is_null($id)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'יש לשלוח מספר מזהה של סוג פריט.',
                ];

            }


            $recordsExist= ItemType::where('type',$request->input('type'))
            ->where('is_deleted',false)
            ->first();

            if (is_null($recordsExist) == false) {
                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'סוג פריט קיים במערכת.',
                ];
            }

            $itemTypeRecord = ItemType::where('is_deleted', 0)
            ->where('id', $id)
                ->first();


            if (is_null($itemTypeRecord)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'סוג פריט אינו קיים במערכת.',
                ];

            }


            $itemTypeRecord->update([
                'type' => $request->input('type'),
            ]);


            return [
                'status' => Status::OK,
                'message' => 'שורה התעדכנה בהצלחה.',
            ];

        } catch (\Exception $e) {

            Log::error($e->getMessage());
        }

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'
        ];

    }


    /**
     * sore new itemType records in database.
     *  @return array
     **/
    public function store(Request $request)
    {
        try {




            $itemTypeRecord = ItemType::where('type', $request->input('type'))->where('is_deleted', true)->first();

            if (is_null($itemTypeRecord)) {
                //? create new itemTypeRecord record
                ItemType::create([
                    'type' => $request->input('type'),
                    'icon_number' => $request->input('icon_number'),

                ]);
            } else {
                //? updated itemTypeRecord records that exist in the depatments table
                $itemTypeRecord->update([
                    'type' =>  $request->input('type'),
                    'icon_number' => $request->input('icon_number'),
                    'is_deleted' => 0,
                ]);
            }



            return [
                'status' => Status::CREATED,
                'message' => 'סוג פריט נשמר במערכת.',
            ];


        } catch (\Exception $e) {

            Log::error($e->getMessage());
        }

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'
        ];
    }



    /**
     * search exist itemType records from item_types table.
     *  @return array
     **/

    public function searchRecords(Request $request)
    {

        try {


            // Search by type
            $itemTypeRecord = ItemType::where('type', 'LIKE', '%' . $request->input('query') . '%')
                ->where('is_deleted', false)
                ->get();


            return [
                'status' => Status::OK,
                'data' => $itemTypeRecord->isEmpty() ? [] : $itemTypeRecord,
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
     * destroy itemType records along with all invetories records associated.
     *  @return array
     **/

    public function destroy($id = null)
    {

        try {

            if (is_null($id)) {
                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'יש לשלוח מזהה סוג פריט.',
                ];
            }

            DB::beginTransaction(); // Start a database transaction


            $itemTypeRecord = ItemType::where('is_deleted', 0)->where('id', $id)->first();

            if (is_null($itemTypeRecord)) {

                DB::rollBack(); 


                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'סוג פריט אינו קיים במערכת.',
                ];
            
            }

            //? make sure all associated inveotries records will deleted as well.

            Inventory::whereIn('type_id',$id)->update(['is_deleted'=> true]);

            $itemTypeRecord->update([
                'is_deleted' => true,
            ]);

            DB::commit(); // commit all changes in database.


            return [
                'status' => Status::OK,
                'message' => 'סוג פריט נמחק מהמערכת.',
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