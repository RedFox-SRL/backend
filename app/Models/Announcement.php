<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'management_id',
        'user_id',
        'content',
    ];

    public function management()
    {
        return $this->belongsTo(Management::class);
    }

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
