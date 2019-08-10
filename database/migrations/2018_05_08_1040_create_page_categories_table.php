<?php

use Illuminate\Database\Migrations\Migration;

class CreatePageCategoriesTable extends Migration
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
        Schema::create('page_categories', function ($table) {
            $table->increments('id')->unsigned();
            $table->string('name', 50)->nullable();
            $table->integer('order')->default(0);
            $table->integer('parent_id')->default(0);
            $table->string('slug', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('categories');
    }
}
