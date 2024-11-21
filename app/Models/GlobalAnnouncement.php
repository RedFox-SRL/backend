<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalAnnouncement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'semester',
        'year',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->morphMany(AnnouncementFile::class, 'announceable');
    }

    public function links()
    {
        return $this->morphMany(AnnouncementLink::class, 'announceable');
    }

    public function youtubeVideos()
    {
        return $this->morphMany(AnnouncementYoutubeVideo::class, 'announceable');
    }
}
