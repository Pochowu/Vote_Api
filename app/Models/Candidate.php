<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
     protected $fillable = [
        'event_id',
        'name',
        'description',
        'photo',
        'votes_count',
        
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'candidate_id');
    }
}

