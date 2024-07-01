<?php

namespace App\Http\Controllers;

use App\Enums\Population;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        try {

            $client = new Client();
            $adfsUrl = config('auth.adfs.url');
            $adfsUser = config('auth.adfs.user');
            $adfsPassword = config('auth.adfs.password');

            $adfs = $client->get($adfsUrl . "/api/token/" . $request->token, ['verify' => false, 'auth' => [
                $adfsUser,
                $adfsPassword,
                'ntlm'
            ]]);

            $adfs = json_decode($adfs->getBody());
            $PersonalNumber = $adfs?->personal_number ;

            if (!$this->isValidPersonalNumber($PersonalNumber)) {
                return response()->json('נא להתחבר עם משתמש תקין.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = User::where('personal_number', $PersonalNumber)->first();

            if (!$user) {
                return response()->json('אינך מורשה גישה', Response::HTTP_FORBIDDEN);
            }


            // revoking old token before creating a new one.
            Token::where('user_id', $user->id)->update(['revoked' => true]);
            $accessTokenName = config('auth.token_name');
            $token = $user->createToken($accessTokenName);

            return response()->json([

                'name' => $user->name,
                'personal_number' => $user->personal_number,
                'role' => $user->roles->first()->name ?? null,

            ], Response::HTTP_CREATED)->withCookie(Cookie::make($accessTokenName, $token->accessToken));

        } catch (Exception $e) {

            Log::error($e->getMessage());

            return response()->json('חלה שגיאה בעת ההתחברות', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function user(Request $request): JsonResponse {
        return response()->json($request->user(), Response::HTTP_OK);
    }

 

    private function isValidPersonalNumber(string $personalNumber): bool
    {
        return (bool)preg_match("/\d{7}$/", $personalNumber);
    }

}
