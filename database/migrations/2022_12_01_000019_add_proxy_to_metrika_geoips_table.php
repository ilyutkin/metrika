<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProxyToMetrikaGeoipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('metrika.connection'))->table(config('metrika.tables.geoips'), function (Blueprint $table) {
            $table->renameColumn('is_from_trusted_proxy', 'is_proxy');
        });
        Schema::connection(config('metrika.connection'))->table(config('metrika.tables.geoips'), function (Blueprint $table) {
            $table->char('proxy_type', 3)->nullable()->after('is_proxy');
            $table->string('isp')->nullable()->after('proxy_type');
            $table->string('usage_type', 11)->nullable()->after('isp');
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
            $table->renameColumn('is_proxy', 'is_from_trusted_proxy');
        });
        Schema::connection(config('metrika.connection'))->table(config('metrika.tables.geoips'), function (Blueprint $table) {
            $table->dropColumn('proxy_type');
            $table->dropColumn('isp');
            $table->dropColumn('usage_type');
        });
    }
}
