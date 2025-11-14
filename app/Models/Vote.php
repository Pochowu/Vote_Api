<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'candidate_id',
        'amount',
        'voting_name',
        'votes_number',
        'payment_method',
        'phone_number',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
