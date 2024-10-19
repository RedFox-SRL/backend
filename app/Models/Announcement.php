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
        return $this->hasMany(AnnouncementFile::class);
    }

    public function links()
    {
        return $this->hasMany(AnnouncementLink::class);
    }

    public function youtubeVideos()
    {
        return $this->hasMany(AnnouncementYoutubeVideo::class);
    }
}
