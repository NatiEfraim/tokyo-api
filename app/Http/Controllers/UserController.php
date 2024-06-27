<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Enums\Status;





class UserController extends Controller
{
    //

    const MIN_LEN = 1;
    const MAX_LEN = 7;


    protected $_userService;

    public function __construct()
    {
        $this->_userService= new UserService();
    }

    /**
     * @OA\Get(
     *      path="/api/users",
     *      tags={"Users"},
     *      summary="Get all users",
     *      description="Retrieves all users from the system.",
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Page number for pagination",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default=1
     *          )
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
            

            $result = $this->_userService->fetchUsersRecords();

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json($result['message'], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json($result['message'], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    public function getRoles()
    {
        try {

            $result = $this->_userService->fetchRolesRecords();

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json($result['message'], Response::HTTP_BAD_REQUEST),

                Status::UNPROCESSABLE_ENTITY => response()->json($result['message'], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json($result['message'], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    /**
     * @OA\Get(
     *     path="/api/users/getuser",
     *     summary="Get the current user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(property="name", type="string", example="Joey Gusikowski"),
     *                 @OA\Property(property="email", type="string", example="m0489495@army.idf.il"),
     *                 @OA\Property(property="employee_type", type="string", example="keva")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="משתמש אינו מחובר למערכת.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="אירעה שגיאה בעת ההתחברות.")
     *         )
     *     )
     * )
     */

    public function user()
    {
        try {

            $result = $this->_userService->fetchCurrentUser();

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json($result['message'], Response::HTTP_BAD_REQUEST),

                Status::CONFLICT => response()->json($result['message'], Response::HTTP_CONFLICT),

                Status::UNPROCESSABLE_ENTITY => response()->json($result['message'], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json($result['message'], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

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

            $result=$this->_userService->searchUsersRecords($request);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json($result['message'], Response::HTTP_BAD_REQUEST),

                Status::UNPROCESSABLE_ENTITY => response()->json($result['message'], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json($result['message'], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

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
     *              @OA\Property(property="role", type="integer", format="int32", minimum=1, maximum=3),
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


            $result = $this->_userService->store($request);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::CREATED => response()->json($result['message'], Response::HTTP_CREATED),

                Status::BAD_REQUEST => response()->json($result['message'], Response::HTTP_BAD_REQUEST),

                Status::UNPROCESSABLE_ENTITY => response()->json($result['message'], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json($result['message'], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

        } catch (\Exception $e) {

            Log::error($e->getMessage());

        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Update user fields.
     *
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update user fields",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="John Doe",
     *                     description="User's name"
     *                 ),
     *                 @OA\Property(
     *                     property="personal_number",
     *                     type="string",
     *                     example="1234567",
     *                     description="User's personal number"
     *                 ),
     *                 @OA\Property(
     *                     property="employee_type",
     *                     type="integer",
     *                     format="int64",
     *                     example=1,
     *                     description="User's employee type (ID)"
     *                 ),
     *                 @OA\Property(
     *                     property="permission_code",
     *                     type="integer",
     *                     format="int64",
     *                     example=2,
     *                     description="User's permission code (ID)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="פירטי משתמש עודכנו בהצלחה."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="חובה לשלוח מספר אישי וסוג עובד יחד"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="המשתמש אינו מורשה לבצע פעולה זו."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="משתמש אינו קיים במערכת."
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="הנתונים שנשלחו אינם תקינים.")
     *          )
     *      ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="התרחש בעיית שרתת יש לנסות שוב מאוחר יותר."
     *             )
     *         )
     *     )
     * )
     */
    
    public function update(Request $request,$id=null)
    {
        try {

            // set validation rules
            $rules = [

                'role' => 'required|integer|exists:roles,id',

            ];

            // Define custom error messages
            $customMessages = [

                'role.required' => 'יש לשלוח שדה תקיד עבור משתמש.',
                'role.integer' => 'שדה תפקיד אינו תקין',
                'role.exists' => 'שדה תפקיד שנשלח אינו קיים במערכת.',

            ];

            // validate the request with custom error messages
            $validator = Validator::make($request->all(), $rules, $customMessages);


            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            $result = $this->_userService->update($request,$id);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['message'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json($result['message'], Response::HTTP_BAD_REQUEST),

                Status::UNPROCESSABLE_ENTITY => response()->json($result['message'], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json($result['message'], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };


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

            $result = $this->_userService->destroy($id);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['message'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json($result['message'], Response::HTTP_BAD_REQUEST),

                Status::UNPROCESSABLE_ENTITY => response()->json($result['message'], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json($result['message'], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),

            };

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);

    }

}