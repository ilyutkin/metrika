<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetrikaDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('metrika.connection'))->create(config('metrika.tables.data'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->string('cookie_id')->nullable();
            $table->string('session_id');
            $table->nullableMorphs('user');
            $table->integer('status_code');
            $table->text('uri');
            $table->string('method');
            $table->json('server');
            $table->json('input')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::connection(config('metrika.connection'))->dropIfExists(config('metrika.tables.data'));
    }
}
