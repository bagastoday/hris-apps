<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('reimbursement_amount', 15, 2)->nullable();
            $table->string('reimbursement_status')->nullable()->index();
            $table->text('reimbursement_review_note')->nullable();
            $table->foreignId('reimbursement_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reimbursement_reviewed_at')->nullable();
            $table->foreignId('finance_transaction_id')->nullable()->unique()->constrained('finance_transactions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('finance_transaction_id');
            $table->dropConstrainedForeignId('reimbursement_reviewed_by');
            $table->dropColumn([
                'reimbursement_amount',
                'reimbursement_status',
                'reimbursement_review_note',
                'reimbursement_reviewed_at',
            ]);
        });
    }
};