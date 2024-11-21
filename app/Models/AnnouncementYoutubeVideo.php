<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementYoutubeVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'announceable_id',
        'announceable_type',
        'video_id',
        'title',
    ];

    public function announceable()
    {
        return $this->morphTo();
    }
}
