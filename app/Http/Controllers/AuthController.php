<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;


class AuthController extends Controller
{
    //

    /**
     * @OA\Post(
     *      path="/api/login",
     *      tags={"Authentication"},
     *      summary="Login user",
     *      description="Logs in a user and generates an access token.",
     *      @OA\RequestBody(
     *          required=true,
     *          description="User credentials",
     *          @OA\JsonContent(
     *              required={"personal_number"},
     *              @OA\Property(property="personal_number", type="string", example="m7046317"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful login",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="name", type="string", example="Nethanel Efraim"),
     *              @OA\Property(property="permission_name", type="string", example="admin"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Conflict",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="המשתמש כבר מחובר."),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="BadRequest",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="משתמש אינו קיים במערת."),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="אירעה שגיאה בעת ההתחברות."),
     *          ),
     *      ),
     * )
     */

    public function login(Request $request)
    {
        try {

            // set validation rules
            $rules = [
                'personal_number' => 'required|regex:/^[0-9]{7}$/',
            ];

            // Define custom error messages
            $customMessages = [
                'personal_number.required' => 'חובה לשלוח מספר אישי לאימות.',
                'personal_number.regex' => 'מספר אישי אינו תקין. יש לפנות למסגרת אמ"ת.',
            ];

            // validate the request with custom error messages
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $pn = $request->personal_number;

            $user = User::with(['employeeType', 'roles'])
            ->where('personal_number', $pn)
            ->where('is_deleted', false)
            ->first();


            // $user = User::with(['employeeType'])
            //     ->where('personal_number', $pn)
            //     ->where('is_deleted', false)
            //     ->first();

            ///validate the user exsist and has emp_type and permission.
            if (is_null($user)) {
                return response()->json(['message' => 'המשתמש לא קיים במערכת, יש לפנות למסגרת אמ"ת.'], Response::HTTP_BAD_REQUEST);
            }

            // Make sure the user has an associated employeeType record
            if (is_null($user->employeeType)) {
                return response()->json(['message' => 'המשתמש לא מקושר לסוג עובד.'], Response::HTTP_BAD_REQUEST);
            }

            // Revoke old token
            $user->tokens()->delete();

            $tokenName = config('auth.token_name');
            // Create new token
            $token = $user->createToken($tokenName);

            // Get user roles
            $roles = $user->roles->pluck('name'); // Extract the role names

            // // Clear all previous cookies and set only the TokyoToken cookie
            // Cookie::forget('MashaToken');



            return response()
                ->json(
                    [
          
                        'name' => $user->name,
                        'role' => $user->roles->first()->name?? null,
                        // 'employee_type_name' => optional($user->employeeType)->name,
                        // 'token' => $token->accessToken,
                    ],
                    Response::HTTP_OK,
                )
                ->withCookie(Cookie::make($tokenName, $token->accessToken));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    /**
     * @OA\Post(
     *      path="/api/logout",
     *      tags={"Authentication"},
     *      summary="Logout user",
     *      description="Logs out the currently authenticated user by revoking the access token.",
     *      security={{ "api_token": {} }},
     *      @OA\Response(
     *          response=201,
     *          description="Successful logout",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="ההתנתקות בוצעה בהצלחה."),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="אירעה שגיאה בעת ההתנתקות."),
     *          ),
     *      ),
     * )
     */

    public function logout()
    {

        try {

            ///'delete' token
            Token::where('user_id', Auth::id())->update(['revoked' => true]);

            return response()->json([

                'message' => 'ההתנתקות בוצעה בהצלחה.',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


}
