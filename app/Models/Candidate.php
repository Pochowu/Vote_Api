<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Candidate extends Model
{
     protected $fillable = [
        'event_id',
        'name',
        'description',
        'photo',
        'votes_count',
        
    ];

      public function Event(): BelongsTo 
    {
        return $this->belongsTo(Event::class);
    }
}
