<?php

namespace Horsefly;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $casts = [
        'data' => 'array'
    ];

    protected $fillable = [
        'user_id',
        'data',
        'message',
        'audit_added_date',
        'audit_added_time',
        'auditable_id',
        'auditable_type',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCreatedOn($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }
    

    public function scopeNurse($query)
    {
        return $query->where('job_category', 'nurse');
    }

    public function scopeNonNurse($query)
    {
        return $query->where('job_category', 'non-nurse');
    }

    /**
     * Get all of the owning auditable models.
     */
    public function auditable()
    {
        return $this->morphTo();
    }
	
	/**
     * Get the user that owns the audit.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
