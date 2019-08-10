<?php

use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        /*
         * Table: pages
         */
        Schema::create('pages', function ($table) {
            $table->increments('id')->unsigned();
            $table->string('name', 50)->nullable();
            $table->text('title')->nullable();
            $table->text('heading')->nullable();
            $table->text('sub_heading')->nullable();
            $table->string('abstract')->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_keyword')->nullable();
            $table->text('meta_description')->nullable();
            $table->mediumText('banner')->nullable();
            $table->string('image')->nullable();
            $table->text('images')->nullable();
            $table->boolean('compile')->default(false)->nullable();
            $table->string('view', 20)->default('default')->nullable();
            $table->string('category', 20)->default('default')->nullable();
            $table->integer('order')->nullable();
            $table->string('slug', 200)->nullable();
            $table->enum('status', ['show', 'hide'])->default('show')->nullable();
            $table->string('upload_folder', 100)->nullable();
            $table->char('recommend_type', 10)->nullable();
            $table->integer('views_count')->default(0)->nullable();
            $table->foreign('category_id')->references('id')->on('page_categories')->onDelete('cascade');
            $table->softDeletes();
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pages');
    }
}
