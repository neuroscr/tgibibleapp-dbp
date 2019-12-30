<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateBibleFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('dbp')->hasTable('bible_files')) {
            Schema::connection('dbp')->create('bible_files', function (Blueprint $table) {
                $table->increments('id');
                $table->string('hash_id', 12);
                $table->foreign('hash_id', 'FK_bible_filesets_bible_files')->references('hash_id')->on(config('database.connections.dbp.database') . '.bible_filesets')->onUpdate('cascade')->onDelete('cascade');
                $table->char('book_id', 3);
                $table->foreign('book_id', 'FK_books_bible_files')->references('id')->on(config('database.connections.dbp.database') . '.books');
                $table->tinyInteger('chapter_start')->unsigned()->nullable();
                $table->tinyInteger('chapter_end')->unsigned()->nullable();
                $table->tinyInteger('verse_start')->unsigned()->nullable();
                $table->tinyInteger('verse_end')->unsigned()->nullable();
                $table->string('file_name');
                $table->integer('file_size')->unsigned()->nullable();
                $table->integer('duration')->unsigned()->nullable();
                $table->unique(['hash_id', 'book_id', 'chapter_start', 'verse_start'], 'unique_bible_file_by_reference');
                $table->timestamps();
            });
        }

        if (!Schema::connection('dbp')->hasTable('bible_file_titles')) {
            Schema::connection('dbp')->create('bible_file_titles', function (Blueprint $table) {
                $table->integer('file_id')->unsigned();
                $table->foreign('file_id', 'FK_bible_files_bible_file_titles')->references('id')->on(config('database.connections.dbp.database') . '.bible_files')->onUpdate('cascade')->onDelete('cascade');
                $table->char('iso', 3);
                $table->foreign('iso', 'FK_languages_bible_file_titles')->references('iso')->on(config('database.connections.dbp.database') . '.languages')->onDelete('cascade')->onUpdate('cascade');
                $table->text('title');
                $table->text('description')->nullable();
                $table->text('key_words')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::connection('dbp')->hasTable('bible_file_tags')) {
            Schema::connection('dbp')->create('bible_file_tags', function (Blueprint $table) {
                $table->integer('file_id')->unsigned();
                $table->foreign('file_id', 'FK_bible_files_bible_file_tags')->references('id')->on(config('database.connections.dbp.database') . '.bible_files')->onUpdate('cascade')->onDelete('cascade');
                $table->unique(['file_id', 'tag', 'value'], 'unique_bible_file_tag');
                $table->string('tag', 4);
                $table->string('value');
                $table->boolean('admin_only');
                $table->timestamps();
            });
        }

        if (!Schema::connection('dbp')->hasTable('bible_file_stream_bandwidths')) {
            Schema::connection('dbp')->create('bible_file_stream_bandwidths', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('bible_file_id')->unsigned();
                $table->foreign('bible_file_id', 'FK_bible_files_bible_file_stream_bandwidths')->references('id')->on(config('database.connections.dbp.database') . '.bible_files')->onUpdate('cascade')->onDelete('cascade');
                $table->string('file_name');
                $table->integer('bandwidth')->unsigned();
                $table->integer('resolution_width')->unsigned()->nullable();
                $table->integer('resolution_height')->unsigned()->nullable();
                $table->string('codec', 64);
                $table->boolean('stream');
                $table->timestamps();
            });
        }

        if (!Schema::connection('dbp')->hasTable('bible_file_stream_ts')) {
            Schema::connection('dbp')->create('bible_file_stream_ts', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('stream_bandwidth_id')->unsigned();
                $table->foreign('stream_bandwidth_id', 'FK_stream_bandwidths_stream_ts')->references('id')->on(config('database.connections.dbp.database') . '.bible_file_stream_bandwidths')->onUpdate('cascade')->onDelete('cascade');
                $table->string('file_name')->unique();
                $table->float('runtime');
                $table->timestamps();
            });
        }

        if (!Schema::connection('dbp')->hasTable('bible_file_timestamps')) {
            Schema::connection('dbp')->create('bible_file_timestamps', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('bible_file_id')->unsigned();
                $table->foreign('bible_file_id', 'FK_bible_file_id_bible_file_timestamps')->references('id')->on(config('database.connections.dbp.database') . '.bible_files')->onUpdate('cascade')->onDelete('cascade');
                $table->tinyInteger('verse_start')->unsigned()->nullable();
                $table->tinyInteger('verse_end')->unsigned()->nullable();
                $table->float('timestamp');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('dbp')->dropIfExists('bible_file_timestamps');
        Schema::connection('dbp')->dropIfExists('bible_file_stream_ts');
        Schema::connection('dbp')->dropIfExists('bible_file_stream_bandwidths');
        Schema::connection('dbp')->dropIfExists('bible_file_tags');
        Schema::connection('dbp')->dropIfExists('bible_file_titles');
        Schema::connection('dbp')->dropIfExists('bible_files');
    }
}
