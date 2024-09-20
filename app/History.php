<?php

namespace Horsefly;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
protected $table = 'history';
//    public function getDateFormat()
//    {
//        return 'Y-m-d H:i:s.u';
//    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class,'applicant_id','id')->latest();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeNurse($query)
    {
        return $query->where('job_category', 'nurse');
    }

    public function scopeNonNurse($query)
    {
        return $query->where('job_category', 'non-nurse');
    }

    public function scopeCreatedOn($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }
    
}
