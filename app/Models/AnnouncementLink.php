<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'url',
        'title',
        'description',
        'image',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}
