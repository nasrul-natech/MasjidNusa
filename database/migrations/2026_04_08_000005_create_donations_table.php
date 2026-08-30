<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('donation_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->bigInteger('target_amount')->default(0);
            $table->bigInteger('collected_amount')->default(0);
            $table->foreignId('category_id')->nullable()->constrained('donation_categories')->onDelete('set null');
            $table->string('banner')->nullable();
            $table->enum('status', ['draft', 'active', 'selesai', 'arsip'])->default('draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number', 50);
            $table->string('bank_logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['manual', 'gateway'])->default('manual');
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('payment_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general');
            $table->string('type', 30)->default('string');
            $table->timestamps();
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('donation_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('campaign_id')->nullable()->constrained('donation_campaigns')->onDelete('set null');
            $table->bigInteger('amount');
            $table->string('donor_name');
            $table->string('donor_email')->nullable();
            $table->string('donor_phone', 20)->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled', 'waiting_verification', 'verified', 'rejected'])->default('pending');
            $table->string('payment_method_type', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_method_id')->nullable()->constrained()->onDelete('set null');
            $table->bigInteger('amount');
            $table->string('reference_number')->nullable()->unique();
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled', 'expired'])->default('pending');
            $table->json('gateway_response')->nullable();
            $table->string('payment_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('payment_transactions')->onDelete('cascade');
            $table->string('image');
            $table->text('notes')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('duitku_configs', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_code')->nullable();
            $table->string('api_key')->nullable();
            $table->string('callback_url')->nullable();
            $table->string('return_url')->nullable();
            $table->integer('expiry_period')->default(60);
            $table->enum('environment', ['sandbox', 'production'])->default('sandbox');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duitku_configs');
        Schema::dropIfExists('payment_proofs');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('payment_configurations');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('donation_campaigns');
        Schema::dropIfExists('donation_categories');
    }
};
