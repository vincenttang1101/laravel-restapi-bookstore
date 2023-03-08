<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Hidehalo\Nanoid\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $client;

    /**
     * Create a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->client = new Client();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = User::all();
        return $this->resSuccess($user, [
            200, 'User List'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required',
            'phone' => 'required',

            'province_id' => 'required|integer',
            'district_id' => 'required|integer',
            'ward_id' => 'required|integer',
            'address' => 'required',
            'role' => "required|integer",
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
            ])
        );
        return $this->resSuccess($user, [
            200, 'User created successfully'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $this->resSuccess($user, [
            200, 'User found successfully'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required',
            'phone' => 'required',

            'province_id' => 'required|integer',
            'district_id' => 'required|integer',
            'ward_id' => 'required|integer',
            'address' => 'required',
            'role' => 'required|integer',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->resValidator([
                400, $validator->errors(),
            ]);
        }

        $user->update(
            array_merge($data, [
                'password' => Hash::make($data['password']),
            ])
        );
        return $this->resSuccess($user, [
            200, 'User Updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if ($user->role == 0) {
            $user->delete();
            return $this->resSuccess($user, [
                200, 'User deleted successfully',
            ]);
        } else return $this->resError([
            403, 'This is the admin account',
        ]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'currentPassword' => 'required',
            'newPassword' => 'required|same:confirmPassword',
            'confirmPassword' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->resValidator([
                400, $validator->errors(),
            ]);
        }

        if (Hash::check($data['currentPassword'], Auth::user()->password)) {
            $user = User::where('user_id', Auth::user()->user_id)->update(
                ['password' => bcrypt($data['newPassword'])]
            );

            return $this->resSuccess($user, [
                200, 'User changed Password successfully'
            ]);
        } else {
            return $this->resError([
                400, 'Current Password not match'
            ]);
        }
    }

    public function paginateTemplate($perPage)
    {
        $user = User::paginate($perPage);
        return $this->resSuccess($user, [
            200, 'User Pagination',
        ]);
    }
}
