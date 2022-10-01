<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMetrikaVisitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('metrika.connection'))->table(config('metrika.tables.visitors'), function (Blueprint $table) {
            // Columns
            $table->integer('agent_id')->unsigned()->nullable()->change();
            $table->integer('device_id')->unsigned()->nullable()->change();
            $table->integer('platform_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::connection(config('metrika.connection'))->table(config('metrika.tables.visitors'), function (Blueprint $table) {
            $table->integer('agent_id')->unsigned()->change();
            $table->integer('device_id')->unsigned()->change();
            $table->integer('platform_id')->unsigned()->change();
        });
    }
}
