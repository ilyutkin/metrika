<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetrikaVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('metrika.connection'))->create(config('metrika.tables.visits'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->integer('visitor_id')->unsigned();
            $table->nullableMorphs('user');
            $table->string('session_id');
            $table->integer('geoip_id')->unsigned();
            $table->integer('referer_id')->unsigned()->nullable();
            $table->integer('count')->unsigned()->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('last_view_at')->nullable();

            // Indexes
            $table->foreign('visitor_id')->references('id')->on(config('metrika.tables.visitors'))
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('geoip_id')->references('id')->on(config('metrika.tables.geoips'))
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('referer_id')->references('id')->on(config('metrika.tables.referers'))
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::connection(config('metrika.connection'))->dropIfExists(config('metrika.tables.visits'));
    }
}
