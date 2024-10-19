<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'name',
        'path',
        'mime_type',
        'size',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}
