<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Hidehalo\Nanoid\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    protected $client;

    /**
     * Create a new AuthorController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show', 'paginateTemplate']]);
        $this->client = new Client();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $author = Author::all();
        return $this->resSuccess($author, [
            200, 'Author List'
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
            'portrait' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->resValidator([
                400, $validator->errors(),
            ]);
        }

        $fileName = Auth::user()->user_id . '_' . $this->client->generateId($size = 8, $mode = Client::MODE_DYNAMIC) . '.' . $request->file('portrait')->extension();

        if (File::exists(storage_path('app/public/portraits/' . $fileName))) {
            return $this->resError([
                404, 'Upload image failed'
            ]);
        } else {
            $path = $request->file('portrait')->storeAs('public/portraits', $fileName);
            $image_url = substr($path, strlen('public/'));
            $author = Author::create(
                array_merge($data, [
                    'author_id' => $this->client->generateId($size = 7, $mode = Client::MODE_DYNAMIC),
                    'portrait' => $image_url,
                ])
            );

            return $this->resSuccess($author, [
                201, 'Author created successfully'
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Author $author)
    {
        $author->books;
        return $this->resSuccess($author, [
            200, 'Author found successfully'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Author $author)
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
    public function update(Request $request, Author $author)
    {
        $data = $request->except(['author_id']);

        $validator = Validator::make($data, [
            'name' => 'required',
            'portrait' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->resValidator([
                400, $validator->errors(),
            ]);
        }

        $checkFile = storage_path('app/public/' . $author['portrait']);
        $oldImage = substr($author['portrait'], strlen('portraits/'));

        if (File::exists($checkFile)) {
            $path_1 = $data['portrait']->storeAs('public/portraits', $oldImage);

            $author->update(
                array_merge($data, ['portrait' => $author['portrait']])
            );
        } else {
            $newFileName = Auth::user()->user_id . '_' . $this->client->generateId($size = 8, $mode = Client::MODE_DYNAMIC) . '.' . $data['portrait']->extension();
            $path_2 = $data['portrait']->storeAs('public/portraits', $newFileName);
            $newImage = substr($path_2, strlen('public/'));
            $author->update(
                array_merge($data, ['portrait' => $newImage])
            );
        }

        return $this->resSuccess($author, [
            200, 'Author updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Author $author)
    {
        unlink(storage_path('app/public/' . $author['portrait']));
        $author->delete();
        return $this->resSuccess($author, [
            200, 'Author deleted successfully',
        ]);
    }

    public function paginateTemplate($perPage)
    {
        $author = Author::paginate($perPage);
        return $this->resSuccess($author, [
            200, 'Author Pagination',
        ]);
    }
}
