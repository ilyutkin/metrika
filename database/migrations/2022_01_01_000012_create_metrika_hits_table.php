<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetrikaHitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('metrika.connection'))->create(config('metrika.tables.hits'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->integer('visitor_id')->unsigned();
            $table->integer('visit_id')->unsigned();
            $table->integer('route_id')->unsigned();
            $table->integer('path_id')->unsigned();
            $table->integer('referer_id')->unsigned()->nullable();
            $table->integer('status_code');
            $table->string('method');
            $table->string('protocol_version')->nullable();
            $table->boolean('is_no_cache')->default(0);
            $table->boolean('wants_json')->default(0);
            $table->boolean('is_secure')->default(0);
            $table->boolean('is_json')->default(0);
            $table->boolean('is_ajax')->default(0);
            $table->boolean('is_pjax')->default(0);
            $table->timestamp('created_at')->nullable();

            // Indexes
            $table->foreign('visitor_id')->references('id')->on(config('metrika.tables.visitors'))
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('visit_id')->references('id')->on(config('metrika.tables.visits'))
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('route_id')->references('id')->on(config('metrika.tables.routes'))
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('path_id')->references('id')->on(config('metrika.tables.paths'))
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
        Schema::connection(config('metrika.connection'))->dropIfExists(config('metrika.tables.hits'));
    }
}
