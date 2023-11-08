<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loot', function (Blueprint $table) {
            $table->string('id')->unique()->primary();
            $table->string('player')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('item')->nullable();
            $table->integer('item_id')->nullable();
            $table->string('item_string')->nullable();
            $table->string('response')->nullable();
            $table->integer('votes')->nullable();
            $table->string('class')->nullable();
            $table->string('instance')->nullable();
            $table->string('boss')->nullable();
            $table->tinyInteger('difficulty_id')->nullable();
            $table->integer('map_id')->nullable();
            $table->integer('group_size')->nullable();
            $table->text('gear1')->nullable();
            $table->text('gear2')->nullable();
            $table->tinyInteger('response_id')->nullable();
            $table->boolean('is_award_reason')->default(false);
            $table->string('sub_type')->nullable();
            $table->string('equip_loc')->nullable();
            $table->text('note')->nullable();
            $table->string('owner')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loot');
    }
};
