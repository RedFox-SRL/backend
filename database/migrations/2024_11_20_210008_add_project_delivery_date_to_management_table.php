<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectDeliveryDateToManagementTable extends Migration
{
    public function up()
    {
        Schema::table('management', function (Blueprint $table) {
            $table->dateTime('project_delivery_date')->nullable()->after('end_date');
        });
    }

    public function down()
    {
        Schema::table('management', function (Blueprint $table) {
            $table->dropColumn('project_delivery_date');
        });
    }
}
