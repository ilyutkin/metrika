<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetrikaReferersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('metrika.connection'))->create(config('metrika.tables.referers'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->integer('domain_id')->unsigned();
            $table->string('url')->index();
            $table->string('medium')->nullable()->index();
            $table->string('source')->nullable()->index();
            $table->integer('count')->unsigned()->default(0);

            // Indexes
            $table->foreign('domain_id')->references('id')->on(config('metrika.tables.domains'))
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
        Schema::connection(config('metrika.connection'))->dropIfExists(config('metrika.tables.referers'));
    }
}
