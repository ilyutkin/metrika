<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQueryIdToMetrikaHitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('metrika.connection'))->table(config('metrika.tables.hits'), function (Blueprint $table) {
            $table->integer('query_id')->unsigned()->nullable()->after('path_id');
            $table->integer('route_id')->unsigned()->nullable()->change();

            // Index
            $table->foreign('query_id')->references('id')->on(config('metrika.tables.queries'))
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
        Schema::connection(config('metrika.connection'))->table(config('metrika.tables.hits'), function (Blueprint $table) {
            //Index
            $table->dropForeign(['query_id']);

            $table->dropColumn('query_id');
            $table->integer('route_id')->unsigned()->change();
        });
    }
}
