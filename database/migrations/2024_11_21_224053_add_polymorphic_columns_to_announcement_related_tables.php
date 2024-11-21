<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPolymorphicColumnsToAnnouncementRelatedTables extends Migration
{
    public function up()
    {
        Schema::table('announcement_files', function (Blueprint $table) {
            $table->dropForeign(['announcement_id']);
            $table->dropColumn('announcement_id');
            $table->morphs('announceable');
        });

        Schema::table('announcement_links', function (Blueprint $table) {
            $table->dropForeign(['announcement_id']);
            $table->dropColumn('announcement_id');
            $table->morphs('announceable');
        });

        Schema::table('announcement_youtube_videos', function (Blueprint $table) {
            $table->dropForeign(['announcement_id']);
            $table->dropColumn('announcement_id');
            $table->morphs('announceable');
        });
    }

    public function down()
    {
        Schema::table('announcement_files', function (Blueprint $table) {
            $table->dropMorphs('announceable');
            $table->unsignedBigInteger('announcement_id');
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
        });

        Schema::table('announcement_links', function (Blueprint $table) {
            $table->dropMorphs('announceable');
            $table->unsignedBigInteger('announcement_id');
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
        });

        Schema::table('announcement_youtube_videos', function (Blueprint $table) {
            $table->dropMorphs('announceable');
            $table->unsignedBigInteger('announcement_id');
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
        });
    }
}
