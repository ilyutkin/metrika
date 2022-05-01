<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetrikaRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('metrika.connection'))->create(config('metrika.tables.routes'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->string('name');
            $table->string('path');
            $table->string('action');
            $table->string('middleware')->nullable();
            $table->json('parameters')->nullable();
            $table->integer('count')->unsigned()->default(0);

            // Indexes
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::connection(config('metrika.connection'))->dropIfExists(config('metrika.tables.routes'));
    }
}
