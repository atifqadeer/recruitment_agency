<?php

namespace Horsefly;

use Illuminate\Database\Eloquent\Model;

class ApplicantNote extends Model
{
    protected $table = 'applicant_notes';

    public function scopeCallback($query)
    {
        return $query->where('type', 'callback'); // Adjust 'type' and 'callback' to your actual column and value
    }
}
