<?php

namespace App\Http\Controllers;

use App\Enums\EmployeeType;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;




class UserController extends Controller
{
    //

    const MIN_LEN = 1;
    const MAX_LEN = 7;

    /**
     * @OA\Get(
     *      path="/api/users",
     *      tags={"Users"},
     *      summary="Get all users",
     *      description="Retrieves all users from the system.",
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Percival Schulist"),
     *                  @OA\Property(property="personal_number", type="string", example="s5671482"),
     *                  @OA\Property(property="email", type="string", example="s5671482@army.idf.il"),
     *                  @OA\Property(property="phone", type="string", example="0556926412"),
     *                  @OA\Property(property="emp_type_id", type="integer", example=2),
     *                  @OA\Property(
     *                      property="employee_type",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="name", type="string", example="miluim")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="משתמש אינו מחובר")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="התרחש בעיית שרתת יש לנסות שוב מאוחר יותר.")
     *          )
     *      )
     * )
     */


    public function index()
    {

        try {

            $users = User::with(['employeeType'])
            ->where('is_deleted', false)->get();

            return response()->json($users->isEmpty() ? [] :$users, Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    /**
     * @OA\Get(
     *      path="/api/users/search",
     *      tags={"Users"},
     *      summary="Search for users by personal number",
     *      description="Searches for users in the system based on the provided personal number.",
     *      @OA\Parameter(
     *          name="search_string",
     *          in="path",
     *          required=true,
     *          description="Search string (personal number)",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Percival Schulist"),
     *                  @OA\Property(property="personal_number", type="string", example="s5671482"),
     *                  @OA\Property(property="email", type="string", example="s5671482@army.idf.il"),
     *                  @OA\Property(property="phone", type="string", example="0556926412"),
     *                  @OA\Property(property="emp_type_id", type="integer", example=2),
     *                  @OA\Property(
     *                      property="employee_type",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="name", type="string", example="miluim")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="נתונים שנשנלחו אינם בפורמט תקין")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="BadRequest",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="חובה לשלוח מספר אישי לחיפוש")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="התרחשה בעיה בשרת. אנא נסה שוב מאוחר יותר.")
     *          )
     *      ),
     * )
     */


    public function searchUser(Request $request)
    {
        try {


            // set custom error messages in Hebrew
            $customMessages = [
                'query.required' => 'יש לשלוח שדה לחיפוש',
                'query.string' => 'ערך השדה שנשלח אינו תקין.',
            ];
            //set the rules

            $rules = [
                'query' => 'required|string',
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {

                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            $searchQuery = $request->input('query');

            $searchQuery = str_replace(' ', '', $request->input('query'));

            if ((ctype_digit($searchQuery) == true) && (strlen($searchQuery) < self::MIN_LEN || strlen($searchQuery) > self::MAX_LEN)) {
                return response()->json(['message' => 'נתונים שנשנלחו אינם בפורמט תקין'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ((ctype_digit($searchQuery) == true)) {
                //? search user by personal_number
                $user_search_for = User::with(['employeeType'])
                    ->whereRaw('SUBSTRING(personal_number, 2) LIKE ?', ['%' . $searchQuery . '%'])
                    ->where('is_deleted', false)
                    ->orderBy('id', 'asc')
                    ->get();

                    return response()->json($user_search_for,Response::HTTP_OK);
            }


            // Search users by name (ignoring spaces)
            $user_search_for = User::with(['employeeType'])
            ->whereRaw("REPLACE(name, ' ', '') LIKE ?", ['%' . $searchQuery . '%'])
            ->where('is_deleted', false)
                ->orderBy('id', 'asc')
                ->get();

            return response()->json($user_search_for, Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    /**
     * @OA\Post(
     *      path="/api/users",
     *      tags={"Users"},
     *      summary="Create a new user",
     *      description="Creates a new user in the system.",
     *      @OA\RequestBody(
     *          required=true,
     *          description="User data",
     *          @OA\JsonContent(
     *              required={"name", "personal_number", "phone_number", "employee_type"},
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="personal_number", type="string", format="numeric", pattern="^\d{7}$"),
     *              @OA\Property(property="employee_type", type="integer", format="int32", minimum=1, maximum=4),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="המשתמש התווסף בהצלחה.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="המשתמש אינו מורשה לבצע פעולה זו.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Conflict",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="משתמש קיים במערכת.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="משתמש אינו מחובר"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="אחת מהשדות שנשלחו אינם תקינים.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="התרחשה בעיה בשרת. אנא נסה שוב מאוחר יותר.")
     *          )
     *      ),
     * )
     */




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

            if (is_null($user_exsist)==false) {
                return response()->json(['message' => 'משתמש קיים במערכת.'], Response::HTTP_BAD_REQUEST);
            }


            $user_exsist = User::where('personal_number', $personal_number)->where('is_deleted', true)->first();

            if (is_null($user_exsist)==false) {
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
            } else {
                //create a new uesr from scretch
                User::create([
                    'name' => $request->input('name'),
                    'phone' => $request->input('phone'),
                    'personal_number' => $personal_number,
                    'email' => "{$personal_number}@army.idf.il",
                    'emp_type_id' => $request->input('employee_type'), //set the relation
                    'remember_token' => Str::random(10),
                ]);

            }
            return response()->json(['message' => 'משתמש נשמר במערכת'], Response::HTTP_CREATED  );

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    /**
     * @OA\Delete(
     *      path="/api/users/{id}",
     *      tags={"Users"},
     *      summary="Delete a user by ID",
     *      description="Deletes a user from the system based on the provided ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the user to delete",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="המשתמש הוסר בהצלחה.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="לא ניתן למחוק משתמש שמחובר למערכת.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="המשתמש אינו מורשה לבצע פעולה זו.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="התרחש בעיית שרתת יש לנסות שוב מאוחר יותר.")
     *          )
     *      ),
     * )
     */



    public function destroy($id = null)
    {
        try {

            if (!$id) {
                return response()->json(['message' => 'חובה לשלוח מספר מזהה של הבקשה.'], Response::HTTP_BAD_REQUEST);
            }


            $user_exsist = User::where('id', $id)->where('is_deleted', false)->first();



            if (is_null($user_exsist)) {
                return response()->json(['message' => 'משתמש אינו קיים במערכת.'], Response::HTTP_BAD_REQUEST);
            }


            //doft deleted user
            $user_exsist->update(['is_deleted' => true]);

            return response()->json(['message' => 'משתמש נמחק מהמערכת.'], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);

    }


    /**
     * @OA\Post(
     *      path="/api/users/mass-destroy",
     *      tags={"Users"},
     *      summary="Delete multiple users",
     *      description="Deletes multiple users from the system.",
     *      @OA\RequestBody(
     *          required=true,
     *          description="List of user IDs to delete",
     *          @OA\JsonContent(
     *              required={"users"},
     *              @OA\Property(property="users", type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      required={"id"},
     *                      @OA\Property(property="id", type="integer", example=1, description="User ID to delete")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="שורות נמחקו בהצלחה.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent(
     *              @OA\Property(property="messages", type="object",
     *                  @OA\Property(property="users", type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="id", type="string", example="שדה המזהה חובה.")
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *          )
     *      )
     * )
     */


    public function massDestroy(Request $request)
    {




        try {
            // set custom error messages in Hebrew
            $customMessages = [
                'users.required' => 'יש לשלוח שורות למחיקה.',
                'users.array' => 'שורות אינם בפורמט תקין.',
                'users.*.id.required' => 'שדה המזהה חובה.',
                'users.*.id.integer' => 'אחת מהשדות שנשלחו אינו תקין.',
                'users.*.id.exists' => 'המזהה שנבחר לא קיים או שהמשימה נמחקה.',
            ];
            //set the rules
            $rules = [
                'users' => 'required|array',
                'users.*.id' => 'required|integer|exists:users,id,is_deleted,0',
            ];

            // validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {

                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $users = $request->input('users');
            $ids = collect($users)->pluck('id')->toArray();

            // Update the 'is_deleted' column to 1 for the users with the given IDs
            User::whereIn('id', $ids)->update(['is_deleted' => 1]);

            return response()->json(['message' => 'שורות נמחקו בהצלחה.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


}