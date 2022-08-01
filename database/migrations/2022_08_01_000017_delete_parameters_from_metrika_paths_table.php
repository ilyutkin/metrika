<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteParametersFromMetrikaPathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('metrika.connection'))->table(config('metrika.tables.paths'), function (Blueprint $table) {
            // Index
            $table->dropUnique(['host', 'path', 'method', 'locale']);

            // Columns
            $table->dropColumn('parameters');
            $table->dropColumn('method');

            // Index
            $table->unique(['host', 'path', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::connection(config('metrika.connection'))->table(config('metrika.tables.paths'), function (Blueprint $table) {
            // Columns
            $table->string('method')->after('path');
            $table->json('parameters')->nullable()->after('method');

            // Indexes
            $table->dropUnique(['host', 'path', 'locale']);
            $table->unique(['host', 'path', 'method', 'locale']);
        });
    }
}
