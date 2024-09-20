<?php

namespace Horsefly;

use Horsefly\Events\Models\Applicant as ApplicantEvent;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
//	 public function getDateFormat()
//     {
//         return 'Y-m-d H:i:s.u';
//     }
	
	protected $fillable = [
        'applicant_u_id',
        'applicant_user_id',
        'applicant_job_title',
        'job_title_prof',
        'applicant_name',
        'applicant_email',
        'applicant_postcode',
        'applicant_phone',
        'applicant_homePhone',
        'job_category',
        'applicant_source',
        'applicant_cv',
        'updated_cv',
        'applicant_notes',
        'applicant_added_date',
        'applicant_added_time',
        'lat',
        'lng',
        'is_blocked',
        'is_no_job',
        'temp_not_interested',
        'no_response',
        'is_callback_enable',
        'is_in_nurse_home',
        'is_cv_in_quality',
        'is_cv_in_quality_clear',
        'is_CV_sent',
        'is_CV_reject',
        'is_interview_confirm',
        // Add other columns as needed
    ];

    /**
     *  The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
//        'created' => ApplicantEvent::class,
        'updated' => ApplicantEvent::class
    ];

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
 

    /**
     * Get the cv_notes for the applicant.
     */
    public function cv_notes()
    {
        return $this->hasMany(Cv_note::class)->select('status', 'applicant_id', 'sale_id','user_id');
    }
   
    public function history()
    {
        return $this->hasMany(history::class)->select('status', 'applicant_id', 'sale_id','stage','sub_stage');
    }
	
	public function cvv_notes()
    {
        return $this->hasMany(Cv_note::class)
            ->join('history', function ($join) {
                $join->on('cv_notes.applicant_id', '=', 'history.applicant_id');
                $join->on('crm_notes.sales_id', '=', 'history.sale_id');
            })->select('history.sub_stage as sub_stage', 'history.status as status')
            ->orderBy("cv_notes.id","DESC");
    }

    /**
     * Get the cv_notes for the applicant.
     */
    public function crm_notes()
    {
        return $this->hasMany(Crm_note::class)
            ->join('history', function ($join) {
                $join->on('crm_notes.applicant_id', '=', 'history.applicant_id');
                $join->on('crm_notes.sales_id', '=', 'history.sale_id');
            })->select('crm_notes.details', 'crm_notes.crm_added_date', 'crm_notes.crm_added_time',
            'history.sub_stage as sub_stage', 'history.status as status')
            ->orderBy("crm_notes.id","DESC");
    }
	
	public function CVNote()
    {
        return $this->hasOne(Cv_note::class)->latest();
    }
	public function applicant_notes()
    {
        return $this->hasOne(ApplicantNote::class)->latest();
    }

    public function CRMNote()
    {
        return $this->hasOne(Crm_note::class)->latest();
    }

    /**
     * Get all audits associated with the applicant.
     */
    public function audits()
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    /**
     * Get all module_notes associated with the applicant.
     */
    public function module_notes()
    {
        return $this->morphMany(ModuleNote::class, 'module_noteable');
    }
	/**
     * Get user associated with the applicant.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'applicant_user_id');
    }
	
	/**
     * Get the callback_notes for the applicant.
     */
    public function callback_notes()
    {
        return $this->hasMany(ApplicantNote::class)->whereIn('moved_tab_to', ['callback','revert_callback'])->orderBy('id', 'desc');
    }
	
	/**
     * Get the no_nursing_home_notes for the applicant.
     */
    public function no_nursing_home_notes()
    {
        return $this->hasMany(ApplicantNote::class)->whereIn('moved_tab_to', ['no_nursing_home','revert_no_nursing_home'])->orderBy('id', 'desc');
    }
	 
}
