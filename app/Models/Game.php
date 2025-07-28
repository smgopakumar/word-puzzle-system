<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['student_name', 'puzzle_string', 'used_letters', 'score', 'is_completed'];

    protected $casts = [
        'used_letters' => 'array',
        'is_completed' => 'boolean',
    ];

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}
