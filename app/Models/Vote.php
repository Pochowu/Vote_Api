<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'candidat_id',
    protected $fillable = [
        'event_id',
        'candidate_id',
        'amount',
        'voting_name',
        'votes_number',
        'payment_method',
        'phone_number',
    ];

    /**
     * Un Vote appartient à un Événement.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Un Vote est fait pour un Candidat.
     */
    public function candidat()
    {
        return $this->belongsTo(Candidate::class);
        'payment_reference', // Nouveau
        'payment_status', // Nouveau: pending, paid, failed
    ];

    protected $casts = [
        'payment_status' => 'string'
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
