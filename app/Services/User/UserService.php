<?php

namespace App\Services\User;


use App\Enums\EmployeeType;
use App\Enums\Status;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\MissionInhibitMail;
use App\Mail\UserMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class UserService
{


    const MIN_LEN = 1;
    const MAX_LEN = 7;

    /**
     * fetch all users records from users table.
     *
     */

public function fetchUsersRecords()
{
        try {

            // Fetch users with their employeeType and roles
            $users = User::with(['employeeType', 'roles'])
                ->where('is_deleted', false)
                ->paginate(10);

            // Initialize an empty array to hold the formatted users
            $formattedUsers = [];

            // Use foreach to format the users data to include role name
            foreach ($users as $user) {
                $formattedUsers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'personal_number' => $user->personal_number,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'employee_type' => $user->getTranslatedEmployeeTypeAttribute() ?? null,
                    'role' => $user->translateRoleAttribute() ?? null, //set asscoiae
                ];
            }

            $data= [
                'data' => $formattedUsers,
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ];

            return [
                'status' => Status::OK,
                'data' => $data
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return ['status' => Status::INTERNAL_SERVER_ERROR,];
    }

    /**
     * fetch current user auth to the app.
     *
     */
public function fetchCurrentUser()
{
        try {

            $user = auth()->user();


            // Make sure the user has an associated employeeType record
            if (is_null($user->employeeType)) {

                return ['status' => Status::BAD_REQUEST,];

            }

            $user->population = match ($user->emp_type_id) {
                EmployeeType::KEVA->value, EmployeeType::SADIR->value => 's' . $user->personal_number,
                EmployeeType::MILUIM->value => 'm' . $user->personal_number,
                EmployeeType::OVED_TZAHAL->value => 'c' . $user->personal_number,
                default => throw new \InvalidArgumentException('סוג עובד לא תקין.')
            };

            $userData = [
                'name' => $user->name,
                'personal_number' =>   $user->population,
                'role' => $user->roles->first()->name ?? null,
            ];

            return [
                'status' => Status::OK,
                'data' => $userData,
        ];

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return ['status' => Status::INTERNAL_SERVER_ERROR,];
    }

    /**
     * search users records based on personl_number or name.
     *
     */
public function searchUsersRecords(Request $request)
{
        try {


            $searchQuery = $request->input('query');

            $searchQuery = str_replace(' ', '', $request->input('query'));

            if ((ctype_digit($searchQuery) == true) && (strlen($searchQuery) < self::MIN_LEN || strlen($searchQuery) > self::MAX_LEN)) {
                // return response()->json(['message' => 'נתונים שנשנלחו אינם בפורמט תקין'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ((ctype_digit($searchQuery) == true)) {

                //? search user by personal_number
                $user_search_for = User::with(['employeeType', 'roles'])
                    ->where('personal_number', 'like', '%' . $searchQuery . '%')
                    ->where('is_deleted', false)
                    ->orderBy('id', 'asc')
                    ->get();

                // Initialize an empty array to hold the formatted users
                $formattedUsers = [];

                // Use foreach to format the users data to include role name
                foreach ($user_search_for as $user) {
                    $formattedUsers[] = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'personal_number' => $user->personal_number,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'employee_type' => $user->getTranslatedEmployeeTypeAttribute() ?? null,
                        'role' => $user->translateRoleAttribute() ?? null, //set role associated.

                    ];
                }

                return [
                    'status' => Status::OK,
                    'data' =>  $formattedUsers,
                ];
            }


            // Search users by name (ignoring spaces)
            $user_search_for = User::with(['employeeType', 'roles'])
                ->whereRaw("REPLACE(name, ' ', '') LIKE ?", ['%' . $searchQuery . '%'])
                ->where('is_deleted', false)
                ->orderBy('id', 'asc')
                ->get();



            // Initialize an empty array to hold the formatted users
            $formattedUsers = [];

            // Use foreach to format the users data to include role name
            foreach ($user_search_for as $user) {
                $formattedUsers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'personal_number' => $user->personal_number,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'employee_type' => $user->getTranslatedEmployeeTypeAttribute() ?? null,
                    'role' => $user->translateRoleAttribute() ?? null, //set asscoiae
                ];
            }
                


            return [
                'status' => Status::OK,
                'data' =>  $formattedUsers,
        ];

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return ['status' => Status::INTERNAL_SERVER_ERROR,];
    }




}