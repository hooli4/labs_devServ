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
        Schema::create('LogsRequests', function (Blueprint $table) {
            $table->id();
            
            $table->text('URL_API_METHOD');
            $table->text('METHOD_HTTP_REQUEST');
            $table->text('CONTROLLER_PATH');
            $table->text('NAME_METHOD_CONTROLLER');
            $table->text('BODY_REQUEST');
            $table->text('HEADERS_REQUEST');
            $table->bigInteger('USER_ID')->nullable();
            $table->text('USER_IP_ADDRESS');
            $table->text('USER_USER-AGENT');
            $table->text('CODE_STATUS_RESPONSE');
            $table->text('BODY_RESPONSE');
            $table->text('HEADERS_RESPONSE');
            $table->timestamp('TIME_CALL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('LogsRequests');
    }
};
