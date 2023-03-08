<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->string('book_id')->primary();
            $table->string('author_id');
            $table->foreign('author_id')
                ->references('author_id')
                ->on('authors');

            $table->string('genre_id');
            $table->foreign('genre_id')
                ->references('genre_id')
                ->on('genres');

            $table->string('publisher_id');
            $table->foreign('publisher_id')
                ->references('publisher_id')
                ->on('publishers');

            $table->string('name');
            $table->integer('countInStock');
            $table->double('price');
            $table->string('image');
            $table->integer('page');
            $table->text('description');

            $table->integer('height');
            $table->integer('length');
            $table->integer('width');
            $table->integer('weight');

            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
