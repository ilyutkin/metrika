<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetrikaVisitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('metrika.connection'))->create(config('metrika.tables.visitors'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->nullableMorphs('user');
            $table->string('cookie_id')->unique();
            $table->integer('agent_id')->unsigned();
            $table->integer('device_id')->unsigned();
            $table->integer('platform_id')->unsigned();
            $table->string('language');
            $table->integer('count')->unsigned()->default(0);
            $table->timestamp('created_at')->nullable();

            // Indexes
            $table->foreign('agent_id')->references('id')->on(config('metrika.tables.agents'))
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('device_id')->references('id')->on(config('metrika.tables.devices'))
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('platform_id')->references('id')->on(config('metrika.tables.platforms'))
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
        Schema::connection(config('metrika.connection'))->dropIfExists(config('metrika.tables.visitors'));
    }
}
