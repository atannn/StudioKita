<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('services')) {
            return;
        }

        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'weekday_price')) {
                $table->decimal('weekday_price', 12, 2)->default(0);
            }

            if (!Schema::hasColumn('services', 'weekend_price')) {
                $table->decimal('weekend_price', 12, 2)->default(0);
            }
        });

        if (Schema::hasColumn('services', 'price')) {
            DB::table('services')->update([
                'weekday_price' => DB::raw('COALESCE(weekday_price, price, 0)'),
                'weekend_price' => DB::raw('COALESCE(weekend_price, price, 0)'),
            ]);
        } else {
            DB::table('services')->update([
                'weekday_price' => DB::raw('COALESCE(weekday_price, 0)'),
                'weekend_price' => DB::raw('COALESCE(weekend_price, 0)'),
            ]);
        }

        if (Schema::hasTable('service_day_prices')) {
            $dayPrices = DB::table('service_day_prices')
                ->select(['service_idservice', 'day_type', 'price'])
                ->get();

            foreach ($dayPrices as $row) {
                $column = match ($row->day_type) {
                    'weekdays' => 'weekday_price',
                    'weekend' => 'weekend_price',
                    default => null,
                };

                if (!$column) {
                    continue;
                }

                DB::table('services')
                    ->where('idservice', $row->service_idservice)
                    ->update([$column => $row->price]);
            }

            Schema::dropIfExists('service_day_prices');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('services')) {
            return;
        }

        if (!Schema::hasTable('service_day_prices')) {
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

        $selectColumns = ['idservice', 'weekday_price', 'weekend_price'];
        $hasLegacyPrice = Schema::hasColumn('services', 'price');
        if ($hasLegacyPrice) {
            $selectColumns[] = 'price';
        }

        $services = DB::table('services')
            ->select($selectColumns)
            ->get();

        foreach ($services as $service) {
            $fallbackPrice = $hasLegacyPrice ? ($service->price ?? 0) : 0;

            DB::table('service_day_prices')->updateOrInsert(
                [
                    'service_idservice' => $service->idservice,
                    'day_type' => 'weekdays',
                ],
                [
                    'price' => $service->weekday_price ?? $fallbackPrice,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::table('service_day_prices')->updateOrInsert(
                [
                    'service_idservice' => $service->idservice,
                    'day_type' => 'weekend',
                ],
                [
                    'price' => $service->weekend_price ?? $fallbackPrice,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'weekday_price')) {
                $table->dropColumn('weekday_price');
            }

            if (Schema::hasColumn('services', 'weekend_price')) {
                $table->dropColumn('weekend_price');
            }
        });
    }
};
