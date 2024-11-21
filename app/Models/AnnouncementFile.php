<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'announceable_id',
        'announceable_type',
        'name',
        'path',
        'mime_type',
        'size',
    ];

    public function announceable()
    {
        return $this->morphTo();
    }
}
