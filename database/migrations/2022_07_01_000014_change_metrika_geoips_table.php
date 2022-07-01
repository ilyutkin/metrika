<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMetrikaGeoipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('metrika.connection'))->table(config('metrika.tables.geoips'), function (Blueprint $table) {
            $table->renameColumn('division_code', 'subdivision_code');
        });
        Schema::connection(config('metrika.connection'))->table(config('metrika.tables.geoips'), function (Blueprint $table) {
            $table->char('continent', 2)->nullable()->after('is_from_trusted_proxy');
            $table->string('country')->nullable()->after('country_code');
            $table->string('subdivision')->nullable()->after('subdivision_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::connection(config('metrika.connection'))->table(config('metrika.tables.geoips'), function (Blueprint $table) {
            $table->renameColumn('subdivision_code', 'division_code');
        });
        Schema::connection(config('metrika.connection'))->table(config('metrika.tables.geoips'), function (Blueprint $table) {
            $table->dropColumn('continent');
            $table->dropColumn('country');
            $table->dropColumn('subdivision');
        });
    }
}
