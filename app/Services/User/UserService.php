<?php

namespace App\Services\User;


use App\Enums\EmployeeType;
use App\Enums\Role as EnumsRole;
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
use Spatie\Permission\Models\Role;


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

            $data = [
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

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];
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

                return [

                    'status' => Status::CONFLICT,

                    'message' => 'משתמש אינו תקין במערכת.',
                ];
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

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];

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

                return [

                    'status' => Status::UNPROCESSABLE_ENTITY,

                    'message' => 'נתונים שנשלחו שגויים.',
                ];

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

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];    
    }


    /**
     * fetch all roles records from database.
     **/

    public function fetchRolesRecords()
    {
        try {

            // Fetch all roles
            $roles = Role::all(['id', 'name']);

            // Define the translations
            $translations = [
                'admin' => 'מנהל',
                'quartermaster' => 'אפסנאי',
                'user' => 'ראש מדור',
            ];

            // Map through roles and translate the names
            $translatedRoles = $roles->map(function ($role) use ($translations) {
                return [
                    'id' => $role->id,
                    'name' => $translations[$role->name] ?? $role->name,
                ];
            });

            return [
                'status' => Status::OK,
                'data' =>  $translatedRoles,
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
     * destroy user records from database.
     **/

    public function destroy($id = null)
    {
        try {

            if (is_null($id)) {

                return [

                    'status' => Status::UNPROCESSABLE_ENTITY,
                    'message' => 'יש לשלוח מספר מזהה של משתמש.',

                ];

            }

            $user_exsist = User::where('id', $id)->where('is_deleted', false)->first();

            if (is_null($user_exsist)) {

                return [

                    'status' => Status::OK,

                    'message' => 'משתמש אינו קיים במערכת.',
                ];                
            }

            //doft deleted user
            $user_exsist->update(['is_deleted' => true]);

            return [
                    'status' => Status::OK,
                    'message' => 'משתמש נמחק מהמערכת.',
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
     * store new user records on the database.
     **/

    public function store(StoreUserRequest $request)
    {
        try {


            //casting the value.
            $emp_type = (int) $request->input('employee_type');

            //set the first letter for the persnal_number
            $personal_number = match ($emp_type) {

                EmployeeType::KEVA->value, EmployeeType::SADIR->value => 's' . $request->personal_number,

                EmployeeType::MILUIM->value => 'm' . $request->personal_number,

                EmployeeType::OVED_TZAHAL->value => 'c' . $request->personal_number,

                default => throw new \InvalidArgumentException('סוג עובד לא תקין.')
            };

            $user_exsist = User::where('personal_number', $personal_number)->where('is_deleted', false)->first();

            if (is_null($user_exsist) == false) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'משתמש קיים במערכת.',

                ];
            }


            $user_exsist = User::where('personal_number', $personal_number)->where('is_deleted', true)->first();

            if (is_null($user_exsist) == false) {
                ///need to update the user fileds
                $user_exsist->update([
                    'name' => $request->input('name'),
                    'personal_number' => $personal_number,
                    'phone' => $request->input('phone'),
                    'email' => "{$personal_number}@army.idf.il",
                    'emp_type_id' => $request->input('employee_type'), //set the relation
                    'remember_token' => Str::random(10),
                    'is_deleted' => 0, //back to false.
                ]);


                $roleValue = (int)$request->input('role');

                // Assign role based on the received value using match expression
                $role = match ($roleValue) {

                    EnumsRole::ADMIN->value => Role::where('name', 'admin')->first(),
                    EnumsRole::USER->value => Role::where('name', 'user')->first(),
                    EnumsRole::QUARTERMASTER->value => Role::where('name', 'quartermaster')->first(),
                    default => throw new \InvalidArgumentException('Invalid role value.'),
                };

                // Assign the role to the new user.
                $user_exsist->assignRole($role);
            } else {
                //?create a new uesr from scretch
                $newUser = User::create([
                    'name' => $request->input('name'),
                    'phone' => $request->input('phone'),
                    'personal_number' => $personal_number,
                    'email' => "{$personal_number}@army.idf.il",
                    'emp_type_id' => $request->input('employee_type'), //set the relation
                ]);



                $roleValue = (int) $request->input('role');

                // Assign role based on the received value using match expression
                $role = match ($roleValue) {

                    EnumsRole::ADMIN->value => Role::where('name', 'admin')->first(),
                    EnumsRole::USER->value => Role::where('name', 'user')->first(),
                    EnumsRole::QUARTERMASTER->value => Role::where('name', 'quartermaster')->first(),
                    default => throw new \InvalidArgumentException('Invalid role value.'),
                };

                // Assign the role to the new user.
                $newUser->assignRole($role);
            }

            return [
                'status' => Status::CREATED,
                'message' => 'משתמש נשמר במערכת.',

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
     * update exisit user records on the database.
     **/


    public function update(Request $request, $id = null)
    {
        try {


            if (is_null($id)) {
                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'יש לשלוח מזהה משתמש.',
                ];

            }


            $user = User::where('id', $id)->where('is_deleted', false)->first();

            if (is_null($user)) {

                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'משתמש אינו קיים במערכת.',
                ];
            }

            // Fetch the role directly based on the ID provided in the request
            $role = Role::find($request->input('role'));

            if (is_null($role)) {


                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'תפקיד שנשלח אינו קיים במערכת.',
                ];

            }

            // Detach all existing roles
            $user->roles()->detach();

            // Assign the new role to the user
            $user->assignRole($role);

            return [

                'status' => Status::OK,

                'message' => 'שינויים עבור המשתמש נשמרו במערכת.',

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