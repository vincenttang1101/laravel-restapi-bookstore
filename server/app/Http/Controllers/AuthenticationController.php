<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Hidehalo\Nanoid\Client;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    protected $client;

    /**
     * Create a new AuthenticationController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
        $this->client = new Client();
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        $data = [
            'user_id' => Auth::user()->user_id,
            'token_type' => 'Bearer',
            'access_token' => $token,
            'expires_in' => Auth::factory()->getTTL() * 60,
        ];

        return $this->resSuccess($data, [
            200, 'Token generated successfully',
        ]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'status' => 400,
                    'fields' => $validator->errors(),
                    'message' => 'Something is wrong with this field',
                ]
            ], 400);
        }

        if (!$token = Auth::attempt($validator->validated())) {
            return response()->json(['error' => 'Email or Password is invalid'], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required',
            'phone' => 'required',

            'province_id' => 'required|integer',
            'district_id' => 'required|integer',
            'ward_id' => 'required|integer',
            'address' => 'required',

            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->resValidator([
                400, $validator->errors(),
            ]);
        }

        $user = User::create(
            array_merge($data, [
                'user_id' => $this->client->generateId($size = 7, $mode = Client::MODE_DYNAMIC),
                'password' => Hash::make($data['password']),
                'role' => 0,
            ])
        );
        return $this->resSuccess($user, [
            200, 'User created successfully'
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();

        return $this->resSuccess(null, [
            200, 'User signed out successfully'
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken()
    {
        return $this->createNewToken(Auth::refresh());
    }
}
