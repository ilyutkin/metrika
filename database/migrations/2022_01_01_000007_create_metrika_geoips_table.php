<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetrikaGeoipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('metrika.connection'))->create(config('metrika.tables.geoips'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->string('client_ip');
            $table->string('latitude');
            $table->string('longitude');
            $table->char('country_code', 2)->nullable();
            $table->json('client_ips')->nullable();
            $table->boolean('is_from_trusted_proxy')->default(0);
            $table->string('division_code')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('timezone')->nullable();
            $table->string('city')->nullable();
            $table->integer('count')->unsigned()->default(0);

            // Indexes
            $table->unique(['client_ip', 'latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::connection(config('metrika.connection'))->dropIfExists(config('metrika.tables.geoips'));
    }
}
