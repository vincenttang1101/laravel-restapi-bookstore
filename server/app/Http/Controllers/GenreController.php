<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Hidehalo\Nanoid\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    protected $client;

    /**
     * Create a new UserController instance.
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
        $genre = Genre::all();
        return $this->resSuccess($genre, [
            200, 'Genre List'
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
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->resValidator([
                400, $validator->errors(),
            ]);
        }

        $genre = Genre::create(
            array_merge($data, [
                'genre_id' => $this->client->generateId($size = 7, $mode = Client::MODE_DYNAMIC),
            ])
        );

        return $this->resSuccess($genre, [
            201, 'Genre created successfully'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Genre $genre)
    {
        $genre->books;
        return $this->resSuccess($genre, [
            200, 'Genre found successfully'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Genre $genre)
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
    public function update(Request $request, Genre $genre)
    {
        $data = $request->except(['genre_id']);

        $validator = Validator::make($data, [
            'name' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->resValidator([
                400, $validator->errors(),
            ]);
        }

        $genre->update($data);
        return $this->resSuccess($genre, [
            200, 'Genre updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Genre $genre)
    {
        $genre->delete();
        return $this->resSuccess($genre, [
            200, 'Genre deleted successfully',
        ]);
    }

    public function paginateTemplate($perPage)
    {
        $genre = Genre::paginate($perPage);
        return $this->resSuccess($genre, [
            200, 'Genre Pagination',
        ]);
    }
}
