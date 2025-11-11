<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'vote_amount',
        'status',
    ];

    public function admins()
    {
        return $this->belongsTo(Admin::class);
    }


    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'event_id');
    }
   
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
   
}