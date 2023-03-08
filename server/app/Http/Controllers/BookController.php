<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Hidehalo\Nanoid\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;


class BookController extends Controller
{
    protected $client;

    /**
     * Create a new BookController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show', 'paginateTemplate', 'searchMain', 'filterMain', 'statisticMain']]);
        $this->client = new Client();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $book = Book::all();
        return $this->resSuccess($book, [
            200, 'Book List'
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
            'author_id' => 'required|exists:authors',
            'genre_id' => 'required|exists:genres',
            'publisher_id' => 'required|exists:publishers',

            'name' => 'required',
            'countInStock' => 'required|integer',
            'price' => 'required|numeric|min:0',
            'image' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
            'page' => 'required|integer|gt:0',
            'description' => 'required',

            'height' => 'required|integer|gt:0',
            'length' => 'required|integer|gt:0',
            'width' => 'required|integer|gt:0',
            'weight' => 'required|integer|gt:0',
        ]);

        if ($validator->fails()) {
            return $this->resValidator([
                400, $validator->errors(),
            ]);
        }

        $fileName = Auth::user()->user_id . '_' . $this->client->generateId($size = 8, $mode = Client::MODE_DYNAMIC) . '.' . $request->file('image')->extension();

        if (File::exists(storage_path('app/public/images_book/' . $fileName))) {
            return $this->resError([
                404, 'Upload image failed'
            ]);
        } else {
            $path = $request->file('image')->storeAs('public/images_book', $fileName);
            $image_url = substr($path, strlen('public/'));

            $book = Book::create(
                array_merge($data, [
                    'book_id' => $this->client->generateId($size = 7, $mode = Client::MODE_DYNAMIC),
                    'image' => $image_url,
                ])
            );

            return $this->resSuccess($book, [
                201, 'Book created successfully'
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Book $book)
    {
        return $this->resSuccess($book, [
            200, 'Book found successfully'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Book $book)
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
    public function update(Request $request, Book $book)
    {
        $data = $request->except(['book_id']);
        $validator = Validator::make($data, [
            'author_id' => 'required',
            'genre_id' => 'required',
            'publisher_id' => 'required',

            'name' => 'required',
            'countInStock' => 'required|integer',
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
            'page' => 'required|integer',
            'description' => 'required',

            'height' => 'required|integer|gt:0',
            'length' => 'required|integer|gt:0',
            'width' => 'required|integer|gt:0',
            'weight' => 'required|integer|gt:0',
        ]);
        if ($validator->fails()) {
            return $this->resValidator([
                400, $validator->errors(),
            ]);
        }

        $checkFile = storage_path('app/public/' . $book['image']);
        $oldImage = substr($book['image'], strlen('images_book/'));

        if (File::exists($checkFile)) {
            $path_1 = $data['image']->storeAs('public/images_book', $oldImage);

            $book->update(
                array_merge($data, ['image' => $book['image']])
            );
        } else {
            $newFileName = Auth::user()->user_id . '_' . $this->client->generateId($size = 8, $mode = Client::MODE_DYNAMIC) . '.' . $data['image']->extension();
            $path_2 = $data['image']->storeAs('public/images_book', $newFileName);
            $newImage = substr($path_2, strlen('public/'));
            $book->update(
                array_merge($data, ['image' => $newImage])
            );
        }

        return $this->resSuccess($book, [
            200, 'Book updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Book $book)
    {
        unlink(storage_path('app/public/' . $book['image']));
        $book->delete();
        return $this->resSuccess($book, [
            200, 'Book deleted successfully',
        ]);
    }

    public function paginateTemplate($perPage)
    {
        $book = Book::paginate($perPage);
        return $this->resSuccess($book, [
            200, 'Book Pagination',
        ]);
    }


    public function searchBook($keyword_type, $keyword, $from, $to)
    {
        switch ($keyword_type) {
            case ('LARGE_PRICE'):
                $book = Book::where('price', '>', $keyword)->get();
                break;
            case ('SMALL_PRICE'):
                $book = Book::where('price', '<', $keyword)->get();
                break;
            case ('BETWEEN_PRICE'):
                $book = Book::whereBetween('price', [$from, $to])->get();
                break;
            case ('NAME'):
                $book = Book::where('name', 'like', '%' . $keyword . '%')->get();
            default:
                $book = 'Something went wrong !';
                break;
        }
        return $book;
    }

    public function searchMain(Request $request, $keyword)
    {
        $data = $request->all();

        $book = $this->searchBook($data['keyword_type'], $keyword, $data['from_val1'], $data['to_val2']);
        return $this->resSuccess($book, [
            200, 'Searched Book',
        ]);
    }

    public function filterBook($keyword_type)
    {
        switch ($keyword_type) {
            case ('NEWBOOK'):
                $book = Book::orderBy('createdAt', 'DESC')->get();
                break;
            case ('OLDBOOK'):
                $book = Book::orderBy('createdAt', 'ASC')->get();
                break;
            case ('HIGHTOLOW_PRICE'):
                $book = Book::orderBy('price', 'DESC')->get();
                break;
            case ('LOWTOHIGH_PRICE'):
                $book = Book::orderBy('price', 'ASC')->get();
                break;
            default:
                $book = 'Something went wrong !';
                break;
        }
        return $book;
    }

    public function filterMain(Request $request)
    {
        $data = $request->all();
        $book = $this->filterBook($data['keyword_type']);
        return $this->resSuccess($book, [
            200, 'Filtered Book',
        ]);
    }

    public function statisticBook($type)
    {
        switch ($type) {
            case ('SUMBOOK'):
                $book = Book::sum('countInStock');
                break;
            case ('SOLDOUT_BOOK'):
                $book = Book::where('countInStock', '=', 0)->get();
                break;
            default:
                $book = 'Something went wrong !';
                break;
        }
        return $book;
    }

    public function statisticMain(Request $request)
    {
        $data = $request->all();
        $book = $this->statisticBook($data['statistic_type']);
        return $this->resSuccess($book, [
            200, 'Statistical Book',
        ]);
    }
}
