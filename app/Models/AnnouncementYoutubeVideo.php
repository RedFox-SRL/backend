<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementYoutubeVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'video_id',
        'title',
        'description',
        'thumbnail',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}
