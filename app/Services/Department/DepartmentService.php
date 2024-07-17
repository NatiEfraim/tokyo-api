<?php

namespace App\Services\Department;

use App\Enums\Status;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class DepartmentService{

    /**
     * fetch all departments records from users table.
     * @return array
     **/

    public function fetchDepartmentsRecords()
    {
        try {



            $departments = Department::where('is_deleted', 0)->get();

            return [

                'status' => Status::OK,

                'data' => $departments->isEmpty()?[] :$departments,

            ];

        } catch (\Exception $e) {

            log::error($e->getMessage());

        }
        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];
    }


    /**
     * store new department in database.
     *  @return array
     **/

    public function store(Request $request)
    {
        try {

            $department = Department::where('name', $request->input('name'))
            ->where('is_deleted', true)
            ->first();


            if (is_null($department)) {
                //? create new department record
                Department::create([
                    'name' => $request->input('name'),
  
                ]);
            } else {
                //? updated department records that exist in the depatments table
                $department->update([
                    'name' =>  $request->input('name'),
                    'is_deleted' => false,
                ]);
            }

            return [
                'status' => Status::CREATED ,
                'message' => 'מחלקה נשמרה במערכת.',
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
     * destroy exist department in database.
     *  @return array
     **/

    public function destroy($id = null)
    {
        
        try {

            if (is_null($id)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'יש לשלוח מזהה מחלקה.',
                ];
            }

            $department = Department::where('is_deleted', 0)->where('id', $id)->first();

            if (is_null($department)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'מחלקה אינה קיימת במערכת.',
                ];

            }

            $department->update([

                'is_deleted' => true,

            ]);

            return [
                'status' => Status::OK,
                'message' => 'מחלקה נמחקה בהצלחה.',
            ];

        } catch (\Exception $e) {

            Log::error($e->getMessage());

        }

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];
    }


}