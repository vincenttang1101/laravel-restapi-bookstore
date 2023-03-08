<?php

namespace App\Http\Controllers;

use App\Models\Publisher;
use Hidehalo\Nanoid\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
    protected $client;

    /**
     * Create a new PublisherController instance.
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
        $publisher = Publisher::all();

        return $this->resSuccess($publisher, [
            200, 'Publisher List'
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

        $publisher = Publisher::create(
            array_merge($data, [
                'publisher_id' => $this->client->generateId($size = 7, $mode = Client::MODE_DYNAMIC),
            ])
        );

        return $this->resSuccess($publisher, [
            201, 'Publisher created successfully'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Publisher $publisher)
    {
        return $this->resSuccess($publisher, [
            200, 'Publisher found successfully'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
    public function update(Request $request, Publisher $publisher)
    {
        $data = $request->except(['publisher_id']);

        $validator = Validator::make($data, [
            'name' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->resValidator([
                400, $validator->errors(),
            ]);
        }

        $publisher = Publisher::findOrFail($id);
        $publisher->update($data);

        return $this->resSuccess($publisher, [
            200, 'Publisher updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Publisher $publisher)
    {
        $publisher->delete();
        return $this->resSuccess($publisher, [
            200, 'Publisher deleted successfully',
        ]);
    }

    public function paginateTemplate($perPage)
    {
        $publisher = Publisher::paginate($perPage);
        return $this->resSuccess($publisher, [
            200, 'Publisher Pagination',
        ]);
    }
}
