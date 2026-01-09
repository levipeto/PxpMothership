<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_enrichments', function (Blueprint $table) {
            $table->id();

            // Reference to legacy customer (not a real FK since different DB)
            $table->string('ugyfelkod', 50)->unique();
            $table->string('company_name')->nullable();
            $table->string('tax_number', 20)->nullable()->index();

            // Industry classification
            $table->string('teaor_code', 20)->nullable()->index();
            $table->string('teaor_name')->nullable();
            $table->string('industry_sector')->nullable()->index();

            // Company size
            $table->unsignedInteger('employee_count')->nullable();
            $table->string('company_size')->nullable()->index(); // micro, small, medium, large
            $table->decimal('revenue_eur', 15, 2)->nullable();
            $table->decimal('revenue_huf', 18, 0)->nullable();

            // Contact & online presence
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Address (cached from enrichment)
            $table->string('city')->nullable()->index();
            $table->string('postal_code', 10)->nullable();
            $table->string('address')->nullable();

            // Full enrichment data
            $table->json('enrichment_data')->nullable();
            $table->string('enrichment_source')->nullable(); // ceginformacio, web_search, manual
            $table->timestamp('enriched_at')->nullable();
            $table->boolean('enrichment_failed')->default(false);
            $table->text('enrichment_error')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_enrichments');
    }
};
