<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy compatibility:
        // for fresh tenants with direct columns in `services`, this table is not needed.
        if (
            Schema::hasTable('services')
            && Schema::hasColumn('services', 'weekday_price')
            && Schema::hasColumn('services', 'weekend_price')
        ) {
            return;
        }

        Schema::create('service_day_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_idservice');
            $table->enum('day_type', ['weekdays', 'weekend']);
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('service_idservice')
                ->references('idservice')
                ->on('services')
                ->cascadeOnDelete();

            $table->unique(['service_idservice', 'day_type'], 'service_day_prices_service_day_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_day_prices');
    }
};
