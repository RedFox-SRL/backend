<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupName extends Model
{
    use HasFactory;

    protected $fillable = ['short_name', 'long_name', 'management', 'teacher'];
}
