<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installments', function (Blueprint $table) {
            if (!Schema::hasColumn('installments', 'sequence')) {
                $table->unsignedSmallInteger('sequence')->default(1)->after('fee_record_id');
            }

            if (!Schema::hasColumn('installments', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)->default(0)->after('amount');
            }

            if (!Schema::hasColumn('installments', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('paid_amount');
            }

            if (!Schema::hasColumn('installments', 'reference')) {
                $table->string('reference', 100)->nullable()->after('payment_method');
            }

            if (!Schema::hasColumn('installments', 'receipt_number')) {
                $table->string('receipt_number', 100)->nullable()->after('reference');
            }

            if (!Schema::hasColumn('installments', 'receipt_issued_at')) {
                $table->timestamp('receipt_issued_at')->nullable()->after('paid_at');
            }

            if (!Schema::hasColumn('installments', 'remarks')) {
                $table->text('remarks')->nullable()->after('receipt_number');
            }
        });

        Schema::table('fee_records', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_records', 'notes')) {
                $table->text('notes')->nullable()->after('is_paid');
            }
        });

        // Populate sensible defaults for existing records.
        if (Schema::hasTable('installments')) {
            DB::table('installments')
                ->whereNotNull('paid_at')
                ->update([
                    'paid_amount' => DB::raw('amount'),
                    'receipt_issued_at' => DB::raw('paid_at'),
                ]);

            $installments = DB::table('installments')
                ->select('id', 'fee_record_id', 'due_date', 'created_at')
                ->orderBy('fee_record_id')
                ->orderBy('due_date')
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $sequenceMap = [];

            foreach ($installments as $installment) {
                $next = ($sequenceMap[$installment->fee_record_id] ?? 0) + 1;
                $sequenceMap[$installment->fee_record_id] = $next;

                DB::table('installments')
                    ->where('id', $installment->id)
                    ->update(['sequence' => $next]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('installments', function (Blueprint $table) {
            if (Schema::hasColumn('installments', 'remarks')) {
                $table->dropColumn('remarks');
            }

            if (Schema::hasColumn('installments', 'receipt_number')) {
                $table->dropColumn('receipt_number');
            }

            if (Schema::hasColumn('installments', 'receipt_issued_at')) {
                $table->dropColumn('receipt_issued_at');
            }

            if (Schema::hasColumn('installments', 'reference')) {
                $table->dropColumn('reference');
            }

            if (Schema::hasColumn('installments', 'payment_method')) {
                $table->dropColumn('payment_method');
            }

            if (Schema::hasColumn('installments', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }

            if (Schema::hasColumn('installments', 'sequence')) {
                $table->dropColumn('sequence');
            }
        });

        Schema::table('fee_records', function (Blueprint $table) {
            if (Schema::hasColumn('fee_records', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
