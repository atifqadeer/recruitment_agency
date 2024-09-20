<?php
namespace Horsefly\Http\Controllers\Administrator;

use Horsefly\Applicant;
//use Horsefly\Observers\ActionObserver;
use Horsefly\Exports\ApplicantsExport;
use Horsefly\Exports\ResourcesExport;
use Horsefly\Exports\Applicants_nurses_15kmExport;
use Horsefly\Exports\Applicants_nureses_7_days_export;
use Horsefly\Exports\AllRejectedApplicantsExport;
use Maatwebsite\Excel\Facades\Excel;
use Horsefly\ApplicantNote;
use Horsefly\User;
use Illuminate\Http\Request;
use Horsefly\Http\Controllers\Controller;
use Horsefly\Office;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;
use Horsefly\Unit;
use Horsefly\Sale;
use Horsefly\Applicants_pivot_sales;
use Horsefly\Notes_for_range_applicants;
use Horsefly\Cv_note;
use Horsefly\Crm_note;
use Horsefly\ModuleNote;
use Horsefly\Crm_rejected_cv;
use Horsefly\Specialist_job_titles;
use Horsefly\Quality_notes;
use Horsefly\Sales_notes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Session;
use Carbon\Carbon;
use Redirect;
use DateTime;
use Horsefly\EmailCountPerDay;
use Horsefly\Exports\Applicant_21_days_export;
use Horsefly\Exports\Applicant_2M_days_export;

class ResourceController extends Controller
{
    public function __construct()
    {
        
        $this->middleware('auth');
        /*** Sub-Links Permissions */
        $this->middleware('permission:resource_Nurses-list', ['only' => ['getNurseSales','getNursingJob']]);
        $this->middleware('permission:resource_Non-Nurses-list', ['only' => ['getNonNurseSales','getNonNursingJob']]);
		        $this->middleware('permission:resource_Non-Nurses-specialist', ['only' => ['getNonNurseSpecialistSales','getNonNursingSpecialistJob']]);
        $this->middleware('permission:resource_Last-7-Days-Applicants', ['only' => ['getLast7DaysApplicantAdded','get7DaysApplicants']]);
        $this->middleware('permission:resource_Last-21-Days-Applicants', ['only' => ['getLast21DaysApplicantAdded','get21DaysApplicants']]);
        $this->middleware('permission:resource_All-Applicants', ['only' => ['getLast2MonthsApplicantAdded','get2MonthsApplicants']]);
        $this->middleware('permission:resource_Crm-All-Rejected-Applicants', ['only' => ['getAllCrmRejectedApplicantCv','allCrmRejectedApplicantCvAjax']]);
        $this->middleware('permission:resource_Crm-Rejected-Applicants', ['only' => ['getCrmRejectedApplicantCv','getCrmRejectedApplicantCvAjax']]);
        $this->middleware('permission:resource_Crm-Request-Rejected-Applicants', ['only' => ['getCrmRequestRejectedApplicantCv','getCrmRequestRejectedApplicantCvAjax']]);
        $this->middleware('permission:resource_Crm-Not-Attended-Applicants', ['only' => ['getCrmNotAttendedApplicantCv','getCrmNotAttendedApplicantCvAjax']]);
        $this->middleware('permission:resource_Crm-Start-Date-Hold-Applicants', ['only' => ['getCrmStartDateHoldApplicantCv','getCrmStartDateHoldApplicantCvAjax']]);
        $this->middleware('permission:resource_Crm-Paid-Applicants', ['only' => ['getCrmPaidApplicantCv','getCrmPaidApplicantCvAjax']]);
        /*** Callback Permissions */
        $this->middleware('permission:resource_Potential-Callback_list|resource_Potential-Callback_revert-callback', ['only' => ['potentialCallBackApplicants','getPotentialCallBackApplicants']]);
        $this->middleware('permission:resource_Potential-Callback_revert-callback', ['only' => ['getApplicantRevertToSearchList']]);
		                $this->middleware('permission:applicant_export', ['only' => ['export_7_days_applicants_date','export_Last21DaysApplicantAdded','export_Last2MonthsApplicantAdded','export_15_km_applicants','exportAllCrmRejectedApplicantCv','Export_CrmRejectedApplicantCv','exportCrmRequestRejectedApplicantCv','exportCrmNotAttendedApplicantCv','exportCrmStartDateHoldApplicantCv'
            ,'exportCrmPaidApplicantCv','exportPotentialCallBackApplicants']]);
		$this->middleware('permission:resource_Crm-All-Rejected-Applicants', ['only' => ['getRejectedAppDateWise','getRejectedAppDateWiseAjax']]);


    }

    //    public function index(){
    //        $sales = Office::join('sales', 'offices.id', '=', 'sales.head_office')
    //            ->select('sales.*','offices.office_name')->where(['sales.status' => 'active','sales.job_category' => 'nurse'])->get();
    //        echo '<pre>';print_r($sales->toArray());exit;
    //        return view('administrator.resource.direct_listing',compact('sales'));
    //    }
	
	
	public function test()
    {
		echo 'test';exit();
		$lati ='51.381718';
		$longi = '-1.336125';
		$radius = 30;
		$job_title = 'rgn';
		$near_by_applicants = $this->distance($lati, $longi, $radius, $job_title);
		$id=5698;
		$non_interest_response = $this->check_not_interested_applicants($near_by_applicants, $id);
		print_r($non_interest_response);exit();
			


			// to get applicant history

            $applicants_in_crm = Applicant::join('crm_notes', 'crm_notes.applicant_id', '=', 'applicants.id')
            ->join('sales', 'sales.id', '=', 'crm_notes.sales_id')
            ->join('offices', 'offices.id', '=', 'head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->join('history', function($join) {
                $join->on('crm_notes.applicant_id', '=', 'history.applicant_id');
                $join->on('crm_notes.sales_id', '=', 'history.sale_id');
            })
            ->select("applicants.*", "applicants.id as app_id", "crm_notes.*", "crm_notes.id as crm_notes_id", "sales.*", "sales.id as sale_id", "sales.postcode as sale_postcode", "sales.job_title as sale_job_title", "sales.job_category as sales_job_category", "sales.status as sale_status", "history.history_added_date", "history.sub_stage","office_name", "unit_name","history.updated_at")
            ->where(array('applicants.id' => 37134, 'history.status' => 'active'))
            ->whereIn('crm_notes.id', function($query){
                $query->select(DB::raw('MAX(id) FROM crm_notes WHERE sales_id=sales.id and applicants.id=applicant_id'));
            })
            ->get()->last();
            $history_stages = config('constants.history_stages');
		    //echo $history_stages[$applicants_in_crm['sub_stage']];exit();
				//echo '<pre>';print_r($applicants_in_crm);echo '</pre>';exit();
			 	if($history_stages[$applicants_in_crm['sub_stage']]=='Sent CV' || $history_stages[$applicants_in_crm['sub_stage']]=='Request' || $history_stages[$applicants_in_crm['sub_stage']]=='Confirmation' || $history_stages[$applicants_in_crm['sub_stage']]=='Rebook' || $history_stages[$applicants_in_crm['sub_stage']]=='Attended to Pre-Start Date' || 
			  $history_stages[$applicants_in_crm['sub_stage']]=='Attended to Pre-Start Date' || $history_stages[$applicants_in_crm['sub_stage']]=='Start Date' || 
			  $history_stages[$applicants_in_crm['sub_stage']]=='Invoice' ||
			  $history_stages[$applicants_in_crm['sub_stage']]=='Paid' )
			   {   
			   echo $history_stages[$applicants_in_crm['sub_stage']].$applicants_in_crm['id'];exit();
			   //unset($applicants_object[$key]); 
		   }
        		
			            unset( $filter_val['id']);			            
						unset( $filter_val['cv_notes']);
        
        
        //return $applicants_object->toArray();
		       
    }
	
    public function getNurseSales()
    {
        // $sales = Office::join('sales', 'offices.id', '=', 'sales.head_office')
        //     ->join('units', 'units.id', '=', 'sales.head_office_unit')
        //     ->select('sales.*', 'offices.office_name', 'units.contact_name',
        //         'units.contact_email', 'units.unit_name', 'units.contact_phone_number')
        //     ->where(['sales.status' => 'active', 'sales.job_category' => 'nurse'])->get();
        $value = '0';
        return view('administrator.resource.nursing', compact('value'));
    }
    
    public function getNursingJob(Request $request)
    {
        $user = Auth::user();
        $result='';
		if($user->name!=='Super Admin')
        {
            $permissions = $user->getAllPermissions()->pluck('name', 'id');
            $arrays = @json_decode(json_encode($permissions), true);
            $user_permissions = array();
            foreach($arrays as $per){
                if(str_contains($per, 'Hoffice_'))
                {
                    $res = explode("-", $per);

                    $user_permissions[]=$res[1];
    
                }
            }
            if(isset($user_permissions) && count($user_permissions)>0)
            {
				
				$sale_notes = Sales_notes::select('sale_id','sales_notes.sale_note', DB::raw('MAX(created_at) as 
               sale_created_at'))
               ->groupBy('sale_id');
                $result = Office::join('sales', 'offices.id', '=', 'sales.head_office')
                ->join('units', 'units.id', '=', 'sales.head_office_unit')
				
                //->join('sales_notes', 'sales.id', '=', 'sales_notes.sale_id')
                ->select('sales.*', 'offices.office_name', 'units.contact_name',
                    'units.contact_email', 'units.unit_name', 'units.contact_phone_number', DB::raw("(SELECT count(cv_notes.sale_id) from cv_notes
                WHERE cv_notes.sale_id=sales.id AND cv_notes.status='active' group by cv_notes.sale_id) as sale_count"))
                ->where(['sales.status' => 'active', 'sales.is_on_hold' => '0', 'sales.job_category' => 'nurse'
						 //, 'sales_notes.status' => 'active'
						])
					//->whereIn('sales.head_office', $user_permissions)
					->orderBy('id', 'DESC');
				
                
            }
            else
            {
				
				$sale_notes = Sales_notes::select('sale_id','sales_notes.sale_note', DB::raw('MAX(created_at) as 
               sale_created_at'))
               ->groupBy('sale_id');
                $result = Office::join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
			
            //->join('sales_notes', 'sales.id', '=', 'sales_notes.sale_id')
            ->select('sales.*', 'offices.office_name', 'units.contact_name',
                'units.contact_email', 'units.unit_name', 'units.contact_phone_number'
					 ,DB::raw("(SELECT count(cv_notes.sale_id) from cv_notes
                WHERE cv_notes.sale_id=sales.id AND cv_notes.status='active' group by cv_notes.sale_id) as sale_count"))
            ->where(['sales.status' => 'active', 'sales.is_on_hold' => '0', 'sales.job_category' => 'nurse'
					 //, 'sales_notes.status' => 'active'
					])->orderBy('id', 'DESC');
				
            }
    
        }
        else
        {

			
			$sale_notes = Sales_notes::select('sale_id','sales_notes.sale_note', DB::raw('MAX(created_at) as 
               sale_created_at'))
               ->groupBy('sale_id');
            $result = Office::join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
		
            //->join('sales_notes', 'sales.id', '=', 'sales_notes.sale_id')
            ->select('sales.*','offices.office_name', 'units.contact_name',
                'units.contact_email', 'units.unit_name', 'units.contact_phone_number', DB::raw("(SELECT count(cv_notes.sale_id) from cv_notes
                WHERE cv_notes.sale_id=sales.id AND cv_notes.status='active' group by cv_notes.sale_id) as sale_count"))
            ->where(['sales.status' => 'active', 'sales.is_on_hold' => '0', 'sales.job_category' => 'nurse'])
				->orderBy('sales.id', 'DESC');
			
			
        }
		
       

        $aColumns = ['sale_added_date', 'sale_added_time', 'job_title', 'office_name', 'unit_name',
            'postcode', 'job_type', 'experience', 'qualification', 'salary', 'sale_note'];

        $iStart = $request->get('iDisplayStart');
        $iPageSize = $request->get('iDisplayLength');

        $order = 'id';
        $sort = ' DESC';

        if ($request->get('iSortCol_0')) { //iSortingCols

            $sOrder = "ORDER BY  ";

            for ($i = 0; $i < intval($request->get('iSortingCols')); $i++) {
                if ($request->get('bSortable_' . intval($request->get('iSortCol_' . $i))) == "true") {
                    $sOrder .= $aColumns[intval($request->get('iSortCol_' . $i))] . " " . $request->get('sSortDir_' . $i) . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = " id ASC";
            }

            $OrderArray = explode(' ', $sOrder);
            $order = trim($OrderArray[3]);
            $sort = trim($OrderArray[4]);

        }

        $sKeywords = $request->get('sSearch');
        if ($sKeywords != "") {

            $result->Where(function ($query) use ($sKeywords) {
                $query->orWhere('job_title', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('office_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('unit_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('postcode', 'LIKE', "%{$sKeywords}%");
            });
        }

        for ($i = 0; $i < count($aColumns); $i++) {
            $request->get('sSearch_' . $i);
            if ($request->get('bSearchable_' . $i) == "true" && $request->get('sSearch_' . $i) != '') {
                $result->orWhere($aColumns[$i], 'LIKE', "%" . $request->orWhere('sSearch_' . $i) . "%");
            }
        }

        $iFilteredTotal = $result->count();

        if ($iStart != null && $iPageSize != '-1') {
            $result->skip($iStart)->take($iPageSize);
        }

        $result->orderBy($order, trim($sort));
        $result->limit($request->get('iDisplayLength'));
        $saleData = $result->get();
        $iTotal = $iFilteredTotal;
        $output = array(
            "sEcho" => intval($request->get('sEcho')),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $i = 0;

        foreach ($saleData as $sRow) {
			$post_code = strtoupper($sRow->postcode);
            $checkbox = "<label class=\"mt-checkbox mt-checkbox-single mt-checkbox-outline\">
                             <input type=\"checkbox\" class=\"checkbox-index\" value=\"{$sRow->id}\">
                             <span></span>
                          </label>";

            $postcode = "<a href=\"/applicants-within-15-km/{$sRow->id}\">{$post_code}</a>";

            $action = "<div class=\"list-icons\">
            <div class=\"dropdown\">
                <a href=\"#\" class=\"list-icons-item\" data-toggle=\"dropdown\">
                    <i class=\"icon-menu9\"></i>
                </a>
                <div class=\"dropdown-menu dropdown-menu-right\">
                    <a href=\"#\" class=\"dropdown-item\"
                                               data-controls-modal=\"#manager_details{$sRow->id}\"
                                               data-backdrop=\"static\"
                                               data-keyboard=\"false\" data-toggle=\"modal\"
                                               data-target=\"#manager_details{$sRow->id}\"
                                            > Manager Details </a>
                </div>
            </div>
          </div>
          <div id=\"manager_details{$sRow->id}\" class=\"modal fade\" tabindex=\"-1\">
                            <div class=\"modal-dialog modal-sm\">
                                <div class=\"modal-content\">
                                    <div class=\"modal-header\">
                                        <h5 class=\"modal-title\">Manager Details</h5>
                                        <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                    </div>
                                    <div class=\"modal-body\">
                                        <ul class=\"list-group\">
                                            <li class=\"list-group-item active\"><p><b>Name: </b>{$sRow->contact_name}</p>
                                            </li>
                                            <li class=\"list-group-item\"><p><b>Email: </b>{$sRow->contact_email}</p></li>
                                            <li class=\"list-group-item\"><p><b>Phone#: </b>{$sRow->contact_phone_number}</p>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class=\"modal-footer\">
                                        <button type=\"button\" class=\"btn bg-teal legitRipple\" data-dismiss=\"modal\">CLOSE
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>";

            //            $history = "<a href=\"#\" class=\"reject_history\" data-applicant=\"{$result->id}\"   
            //                                 data-controls-modal=\"#reject_history{$result->id}\"
            //                                 data-backdrop=\"static\" data-keyboard=\"false\" data-toggle=\"modal\"
            //                                 data-target=\"#reject_history{$result->id}\">History</a>
            //                        <div id=\"reject_history{$result->id}\" class=\"modal fade\" tabindex=\"-1\">
            //                            <div class=\"modal-dialog modal-lg\">
            //                                <div class=\"modal-content\">
            //                                    <div class=\"modal-header\">
            //                                        <h6 class=\"modal-title\">Rejected History - <span class=\"font-weight-semibold\">{$result->applicant_name}</span></h6>
            //                                        <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
            //                                    </div>
            //                                    <div class=\"modal-body\" id=\"applicant_rejected_history{$result->id}\" style=\"max-height: 500px; overflow-y: auto;\">
            //                                    </div>
            //                                    <div class=\"modal-footer\">
            //                                        <button type=\"button\" class=\"btn bg-teal legitRipple\" data-dismiss=\"modal\">Close</button>
            //                                    </div>
            //                                </div>
            //                            </div>
            //                        </div>";
			 $job_title_desc='';
            if(@$sRow->job_title_prof!='')
                {
                    $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $sRow->job_title_prof)->first();
                    $job_title_desc = $sRow->job_title.' ('.$job_prof_res->specialist_prof.')';
                    // $job_title_desc = @$sRow->job_title.' ('.@$sRow->job_title_prof.')';
                }
                else
                {
                    $job_title_desc = @$sRow->job_title;
                } 
		
           $date=EmailCountPerDay::where('date','=',Carbon::now()->format('Y-m-d'))->first();
            if ($user->is_admin==1){

            if (!isset($date)||$date->Email_count_per_day <= '1500') {
                $action = '<a href="' . url('/sent-email-applicants') . '/' . $sRow->id . '" data-id="' . $sRow->id . '" class="btn bg-teal legitRipple">Send Email</a>';
            }else{
                $action = '<a href="#"  class="btn bg-teal legitRipple disabled" title="Email sending limit completed per day">Send Email</a>';
            }

            }else{
				
                if ($user->hasAnyPermission(['applicant_sent-email-bulk'])){

                   $action = '<a href="' . url('/sent-email-applicants') . '/' . $sRow->id . '" data-id="' . $sRow->id . '" class="btn bg-teal legitRipple">Send Email</a>';

                }else{
				
                    $action='';

                }

            }
            $output['aaData'][] = array(
                "DT_RowId" => "row_{$sRow->id}",
                @$sRow->sale_added_date,
                @$sRow->sale_added_time,
				strtoupper($job_title_desc),
                @ucwords(strtolower($sRow->office_name)),
                @ucwords(strtolower($sRow->unit_name)),
                @$postcode,
                @ucwords($sRow->job_type),
                @$sRow->experience,
                @$sRow->qualification,
                @$sRow->salary,
                //@$sRow->sale_note,
				@$sRow->sale_count==$sRow->send_cv_limit?'<span class="badge w-100 badge-danger" style="font-size:90%">Limit Reached</span>':"<span class='badge w-100 badge-success' style='font-size:90%'>".((int)$sRow->send_cv_limit - (int)$sRow->sale_count)." Cv's limit remaining</span>",
				@$action,


            );
            $i++;
        }

        //  print_r($output);
        echo json_encode($output);
    }

    public function getNonNurseSales()
    {
        // $sales = Office::join('sales', 'offices.id', '=', 'sales.head_office')
        //     ->join('units', 'units.id', '=', 'sales.head_office_unit')
        //     ->select('sales.*', 'offices.office_name', 'units.contact_name',
        //         'units.contact_email', 'units.unit_name', 'units.contact_phone_number')
        //     ->where(['sales.status' => 'active', 'sales.job_category' => 'nonnurse'])->get();
        $value = '1';
        return view('administrator.resource.non_nurse', compact('value'));
    }

    public function getNonNursingJob(Request $request)
    {
        $user = Auth::user();
        $result='';
        if($user->name!=='Super Admin')
        {
            $permissions = $user->getAllPermissions()->pluck('name', 'id');
            $arrays = @json_decode(json_encode($permissions), true);
            $user_permissions = array();
            foreach($arrays as $per){
                if(str_contains($per, 'Hoffice_'))
                {
                    $res = explode("-", $per);
                    $user_permissions[]=$res[1];
    
                }
            }
            if(isset($user_permissions) && count($user_permissions)>0)
            {
				$sale_notes = Sales_notes::select('sale_id','sales_notes.sale_note', DB::raw('MAX(created_at) as 
               sale_created_at'))
               ->groupBy('sale_id');
                $result = Office::join('sales', 'offices.id', '=', 'sales.head_office')
                ->join('units', 'units.id', '=', 'sales.head_office_unit')
				//->join('sales_notes', 'sales.id', '=', 'sales_notes.sale_id')
			
                ->select('sales.*', 'offices.office_name', 'units.contact_name',
                    'units.contact_email', 'units.unit_name', 'units.contact_phone_number', DB::raw("(SELECT count(cv_notes.sale_id) from cv_notes
                    WHERE cv_notes.sale_id=sales.id AND cv_notes.status='active' group by cv_notes.sale_id) as sale_count"))
                ->where(['sales.status' => 'active', 'sales.is_on_hold' => '0', 'sales.job_category' => 'nonnurse'
						 //, 'sales_notes.status' => 'active'
						])
					->whereNotIn('sales.job_title', ['nonnurse specialist'])
					//->whereIn('sales.head_office', $user_permissions)
					->orderBy('id', 'DESC');
            }
            else
            {
				$sale_notes = Sales_notes::select('sale_id','sales_notes.sale_note', DB::raw('MAX(created_at) as 
               sale_created_at'))
               ->groupBy('sale_id');
                $result = Office::join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
					
			//->join('sales_notes', 'sales.id', '=', 'sales_notes.sale_id')
            ->select('sales.*', 'offices.office_name', 'units.contact_name',
                'units.contact_email', 'units.unit_name', 'units.contact_phone_number', DB::raw("(SELECT count(cv_notes.sale_id) from cv_notes
                    WHERE cv_notes.sale_id=sales.id AND cv_notes.status='active' group by cv_notes.sale_id) as sale_count"))
            ->where(['sales.status' => 'active', 'sales.is_on_hold' => '0', 'sales.job_category' => 'nonnurse'
					 //, 'sales_notes.status' => 'active'
					])
					->whereNotIn('sales.job_title', ['nonnurse specialist'])
					->orderBy('id', 'DESC');

            }
    
        }
        else
        {
			$sale_notes = Sales_notes::select('sale_id','sales_notes.sale_note', DB::raw('MAX(created_at) as 
               sale_created_at'))
               ->groupBy('sale_id');

            $result = Office::join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
			// ->joinSub($sale_notes, 'sales_notes', function ($join) {
              //  $join->on('sales.id', '=', 'sales_notes.sale_id');
            //})
			//->join('sales_notes', 'sales.id', '=', 'sales_notes.sale_id')
            ->select('sales.*', 'offices.office_name', 'units.contact_name',
                'units.contact_email', 'units.unit_name', 'units.contact_phone_number',  DB::raw("(SELECT count(cv_notes.sale_id) from cv_notes WHERE cv_notes.sale_id=sales.id AND cv_notes.status='active' group by cv_notes.sale_id) as sale_count"))
            ->where(['sales.status' => 'active', 'sales.is_on_hold' => '0', 'sales.job_category' => 'nonnurse'
					 //, 'sales_notes.status' => 'active'
					])
				->whereNotIn('sales.job_title', ['nonnurse specialist'])
				->orderBy('id', 'DESC');
        }
			
        
        // $result = Office::join('sales', 'offices.id', '=', 'sales.head_office')
        //     ->join('units', 'units.id', '=', 'sales.head_office_unit')
        //     ->select('sales.*', 'offices.office_name', 'units.contact_name',
        //         'units.contact_email', 'units.unit_name', 'units.contact_phone_number')
        //     ->where(['sales.status' => 'active', 'sales.job_category' => 'nonnurse'])->orderBy('id', 'DESC');

        $aColumns = ['sale_added_date', 'sale_added_time', 'job_title', 'office_name', 'unit_name',
            'postcode', 'job_type', 'experience', 'qualification', 'salary', 'sale_notes', 'status', 'Cv Limit'];

        $iStart = $request->get('iDisplayStart');
        $iPageSize = $request->get('iDisplayLength');

        $order = 'id';
        $sort = ' DESC';

        if ($request->get('iSortCol_0')) { //iSortingCols

            $sOrder = "ORDER BY  ";

            for ($i = 0; $i < intval($request->get('iSortingCols')); $i++) {
                if ($request->get('bSortable_' . intval($request->get('iSortCol_' . $i))) == "true") {
                    $sOrder .= $aColumns[intval($request->get('iSortCol_' . $i))] . " " . $request->get('sSortDir_' . $i) . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = " id ASC";
            }

            $OrderArray = explode(' ', $sOrder);
            $order = trim($OrderArray[3]);
            $sort = trim($OrderArray[4]);

        }

        $sKeywords = $request->get('sSearch');
        if ($sKeywords != "") {

            $result->Where(function ($query) use ($sKeywords) {
                $query->orWhere('job_title', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('office_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('unit_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('postcode', 'LIKE', "%{$sKeywords}%");
            });
        }

        for ($i = 0; $i < count($aColumns); $i++) {
            $request->get('sSearch_' . $i);
            if ($request->get('bSearchable_' . $i) == "true" && $request->get('sSearch_' . $i) != '') {
                $result->orWhere($aColumns[$i], 'LIKE', "%" . $request->orWhere('sSearch_' . $i) . "%");
            }
        }

        $iFilteredTotal = $result->count();

        if ($iStart != null && $iPageSize != '-1') {
            $result->skip($iStart)->take($iPageSize);
        }

        $result->orderBy($order, trim($sort));
        $result->limit($request->get('iDisplayLength'));
        $saleData = $result->get();

        $iTotal = $iFilteredTotal;
        $output = array(
            "sEcho" => intval($request->get('sEcho')),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $i = 0;

        foreach ($saleData as $sRow) {
			$post_code = strtoupper($sRow->postcode);
            $checkbox = "<label class=\"mt-checkbox mt-checkbox-single mt-checkbox-outline\">
                             <input type=\"checkbox\" class=\"checkbox-index\" value=\"{$sRow->id}\">
                             <span></span>
                          </label>";
            $postcode = "<a href=\"/applicants-within-15-km/{$sRow->id}\">{$post_code}</a>";
            if ($sRow->status == 'active') {
                $status = '<h5><span class="badge w-100 badge-success">Active</span></h5>';
            } else {
                $status = '<h5><span class="badge w-100 badge-danger">Disable</span></h5>';
            }

            $action = "<div class=\"list-icons\">
            <div class=\"dropdown\">
                <a href=\"#\" class=\"list-icons-item\" data-toggle=\"dropdown\">
                    <i class=\"icon-menu9\"></i>
                </a>
                <div class=\"dropdown-menu dropdown-menu-right\">
                    <a href=\"#\" class=\"dropdown-item\"
                                               data-controls-modal=\"#manager_details{$sRow->id}\"
                                               data-backdrop=\"static\"
                                               data-keyboard=\"false\" data-toggle=\"modal\"
                                               data-target=\"#manager_details{$sRow->id}\"
                                            > Manager Details </a>
                </div>
            </div>
          </div>
          <div id=\"manager_details{$sRow->id}\" class=\"modal fade\" tabindex=\"-1\">
                            <div class=\"modal-dialog modal-sm\">
                                <div class=\"modal-content\">
                                    <div class=\"modal-header\">
                                        <h5 class=\"modal-title\">Manager Details</h5>
                                        <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                    </div>
                                    <div class=\"modal-body\">
                                        <ul class=\"list-group\">
                                            <li class=\"list-group-item active\"><p><b>Name: </b>{$sRow->contact_name}</p>
                                            </li>
                                            <li class=\"list-group-item\"><p><b>Email: </b>{$sRow->contact_email}</p></li>
                                            <li class=\"list-group-item\"><p><b>Phone#: </b>{$sRow->contact_phone_number}</p>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class=\"modal-footer\">
                                        <button type=\"button\" class=\"btn bg-teal legitRipple\" data-dismiss=\"modal\">CLOSE
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>";
				$job_title_desc='';
            if(@$sRow->job_title_prof!='')
                {
                    $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $sRow->job_title_prof)->first();
                    $job_title_desc = $sRow->job_title.' ('.$job_prof_res->specialist_prof.')';
                    // $job_title_desc = @$sRow->job_title.' ('.@$sRow->job_title_prof.')';
                }
                else
                {
                    $job_title_desc = @$sRow->job_title;
                }
            $output['aaData'][] = array(
                "DT_RowId" => "row_{$sRow->id}",
                //    @$checkbox,
                @$sRow->sale_added_date,
                @$sRow->sale_added_time,
                strtoupper($job_title_desc),
                @ucwords(strtolower($sRow->office_name)),
                @ucwords(strtolower($sRow->unit_name)),
                @$postcode,
                @ucwords($sRow->job_type),
                @$sRow->experience,
                @$sRow->qualification,
                @$sRow->salary,
                @$sRow->sale_note,
                @$status,
				@$sRow->sale_count==$sRow->send_cv_limit?'<span class="badge w-100 badge-danger" style="font-size:90%">Limit Reached</span>':"<span class='badge w-100 badge-success' style='font-size:90%'>".((int)$sRow->send_cv_limit - (int)$sRow->sale_count)." Cv's limit remaining</span>",
                @$action
				

            );


            $i++;

        }
        echo json_encode($output);
    }
	
	public function getNonNurseSpecialistSales()
	{
		$value = '1';
		return view('administrator.resource.non_nurse_specialist', compact('value'));
	}

	public function getNonNursingSpecialistJob(Request $request)
	{
		$user = Auth::user();
		$result='';
   
        $sale_notes = Sales_notes::select('sale_id','sales_notes.sale_note', DB::raw('MAX(created_at) as 
        sale_created_at'))
        ->groupBy('sale_id');
        $result = Office::join('sales', 'offices.id', '=', 'sales.head_office')
        ->join('units', 'units.id', '=', 'sales.head_office_unit')
        ->joinSub($sale_notes, 'sales_notes', function ($join) {
            $join->on('sales.id', '=', 'sales_notes.sale_id');
        })
        //->join('sales_notes', 'sales.id', '=', 'sales_notes.sale_id')
        ->select('sales.*', 'offices.office_name', 'units.contact_name',
            'units.contact_email', 'units.unit_name', 'units.contact_phone_number', 'sales_notes.sale_note', DB::raw("(SELECT count(cv_notes.sale_id) from cv_notes
            WHERE cv_notes.sale_id=sales.id AND cv_notes.status='active' group by cv_notes.sale_id) as result"))
        ->where(['sales.status' => 'active', 'sales.is_on_hold' => '0', 'sales.job_category' => 'nonnurse', 'sales.job_title' => 'nonnurse specialist'])->orderBy('id', 'DESC');
        //}

        // (cv_notes.status='active' or cv_notes.status='paid')
        // $result = Office::join('sales', 'offices.id', '=', 'sales.head_office')
        //     ->join('units', 'units.id', '=', 'sales.head_office_unit')
        //     ->select('sales.*', 'offices.office_name', 'units.contact_name',
        //         'units.contact_email', 'units.unit_name', 'units.contact_phone_number')
        //     ->where(['sales.status' => 'active', 'sales.job_category' => 'nonnurse'])->orderBy('id', 'DESC');

        $aColumns = ['sale_added_date', 'sale_added_time', 'job_title', 'office_name', 'unit_name',
            'postcode', 'job_type', 'experience', 'qualification', 'salary', 'sale_note', 'status', 'Cv Limit'];

        $iStart = $request->get('iDisplayStart');
        $iPageSize = $request->get('iDisplayLength');

        $order = 'id';
        $sort = ' DESC';

        if ($request->get('iSortCol_0')) { //iSortingCols

        $sOrder = "ORDER BY  ";

        for ($i = 0; $i < intval($request->get('iSortingCols')); $i++) {
            if ($request->get('bSortable_' . intval($request->get('iSortCol_' . $i))) == "true") {
                $sOrder .= $aColumns[intval($request->get('iSortCol_' . $i))] . " " . $request->get('sSortDir_' . $i) . ", ";
            }
        }

        $sOrder = substr_replace($sOrder, "", -2);
        if ($sOrder == "ORDER BY") {
            $sOrder = " id ASC";
        }

        $OrderArray = explode(' ', $sOrder);
        $order = trim($OrderArray[3]);
        $sort = trim($OrderArray[4]);

        }

        $sKeywords = $request->get('sSearch');
        if ($sKeywords != "") {

            $result->Where(function ($query) use ($sKeywords) {
                $query->orWhere('job_title', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('office_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('unit_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('postcode', 'LIKE', "%{$sKeywords}%");
            });
        }

        for ($i = 0; $i < count($aColumns); $i++) {
            $request->get('sSearch_' . $i);
            if ($request->get('bSearchable_' . $i) == "true" && $request->get('sSearch_' . $i) != '') {
                $result->orWhere($aColumns[$i], 'LIKE', "%" . $request->orWhere('sSearch_' . $i) . "%");
            }
        }

        $iFilteredTotal = $result->count();

        if ($iStart != null && $iPageSize != '-1') {
            $result->skip($iStart)->take($iPageSize);
        }

        $result->orderBy($order, trim($sort));
        $result->limit($request->get('iDisplayLength'));
        $saleData = $result->get();

        $iTotal = $iFilteredTotal;
        $output = array(
            "sEcho" => intval($request->get('sEcho')),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $i = 0;

        foreach ($saleData as $sRow) {
            $post_code = strtoupper($sRow->postcode);
            $checkbox = "<label class=\"mt-checkbox mt-checkbox-single mt-checkbox-outline\">
                            <input type=\"checkbox\" class=\"checkbox-index\" value=\"{$sRow->id}\">
                            <span></span>
                        </label>";
            $postcode = "<a href=\"/applicants-within-15-km/{$sRow->id}\">{$post_code}</a>";
            if ($sRow->status == 'active') {
                $status = '<h5><span class="badge w-100 badge-success">Active</span></h5>';
            } else {
                $status = '<h5><span class="badge w-100 badge-danger">Disable</span></h5>';
            }

            $action = "<div class=\"list-icons\">
            <div class=\"dropdown\">
                <a href=\"#\" class=\"list-icons-item\" data-toggle=\"dropdown\">
                    <i class=\"icon-menu9\"></i>
                </a>
                <div class=\"dropdown-menu dropdown-menu-right\">
                    <a href=\"#\" class=\"dropdown-item\"
                                            data-controls-modal=\"#manager_details{$sRow->id}\"
                                            data-backdrop=\"static\"
                                            data-keyboard=\"false\" data-toggle=\"modal\"
                                            data-target=\"#manager_details{$sRow->id}\"
                                            > Manager Details </a>
                    </div>
                </div>
            </div>
            <div id=\"manager_details{$sRow->id}\" class=\"modal fade\" tabindex=\"-1\">
                                <div class=\"modal-dialog modal-sm\">
                                    <div class=\"modal-content\">
                                        <div class=\"modal-header\">
                                            <h5 class=\"modal-title\">Manager Details</h5>
                                            <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                        </div>
                                        <div class=\"modal-body\">
                                            <ul class=\"list-group\">
                                                <li class=\"list-group-item active\"><p><b>Name: </b>{$sRow->contact_name}</p>
                                                </li>
                                                <li class=\"list-group-item\"><p><b>Email: </b>{$sRow->contact_email}</p></li>
                                                <li class=\"list-group-item\"><p><b>Phone#: </b>{$sRow->contact_phone_number}</p>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class=\"modal-footer\">
                                            <button type=\"button\" class=\"btn bg-teal legitRipple\" data-dismiss=\"modal\">CLOSE
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>";
                            $job_title_desc='';
                if(@$sRow->job_title_prof!='')
                    {
                        $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $sRow->job_title_prof)->first();
                        $job_title_desc = $sRow->job_title.' ('.$job_prof_res->specialist_prof.')';
                        // $job_title_desc = @$sRow->job_title.' ('.@$sRow->job_title_prof.')';
                    }
                    else
                    {
                        $job_title_desc = @$sRow->job_title;
                    }
                $output['aaData'][] = array(
                    "DT_RowId" => "row_{$sRow->id}",
                    //    @$checkbox,
                    @$sRow->sale_added_date,
                    @$sRow->sale_added_time,
                    strtoupper($job_title_desc),
                    @ucwords(strtolower($sRow->office_name)),
                    @ucwords(strtolower($sRow->unit_name)),
                    @$postcode,
                    @ucwords($sRow->job_type),
                    @$sRow->experience,
                    @$sRow->qualification,
                    @$sRow->salary,
                    @$sRow->sale_note,
                    @$status,
                    @$action,
                    @$sRow->result==$sRow->send_cv_limit?'<span style="color:red;">Limit Reached</span>':"<span style='color:green'>".((int)$sRow->send_cv_limit - (int)$sRow->result)." Cv's limit remaining</span>",

                );


			$i++;

		}
		echo json_encode($output);
	}

    public function test1()
    {
		 $id=7173;
		$sale_id=7173;
        $job_result = Sale::find($id);
        $job_title = $job_result->job_title;
    
        $job_postcode = $job_result->postcode;
        $radius = 15;
        $postcode_para = urlencode($job_postcode).',UK';
        $postcode_api = config('app.postcode_api');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$postcode_para}&key={$postcode_api}";
        $resp_json = file_get_contents($url);
        $near_by_applicants = '';

        $resp = json_decode($resp_json, true);
      
        if ($resp['status'] == 'OK') {
            $lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
            $longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";

            $near_by_applicants = $this->distance($lati, $longi, $radius, $job_title);
        } else {
            echo "<strong>ERROR: {$resp['status']}</strong>";
        }

        $non_interest_response = $this->check_not_interested_applicants($near_by_applicants, $id);
        // $not_interest_response = array_unique($non_interest_response);
        $check_applicant_availibility = array_values($non_interest_response);
		 print_r($check_applicant_availibility[0]);exit();
    }
	
	public function testing_find_job_applicants()
    {
        
        $sale_id=10245;
		$id = 10245;
        $job_result = Sale::find($id);
        $job_title = $job_result->job_title;
        // echo $job_title;
        $job_postcode = $job_result->postcode;
        $radius = 15;
        $postcode_para = urlencode($job_postcode).',UK';
        $postcode_api = config('app.postcode_api');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$postcode_para}&key={$postcode_api}";
        $resp_json = file_get_contents($url);
        $near_by_applicants = '';

        $resp = json_decode($resp_json, true);
        if ($resp['status'] == 'OK') {
            $lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
            $longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";

            $near_by_applicants = $this->distance($lati, $longi, $radius, $job_title);
        } else {
            echo "<strong>ERROR: {$resp['status']}</strong>";
        }
		$non_interest_response = $this->check_not_interested_applicants($near_by_applicants, $id);
        $check_applicant_availibility = array_values($non_interest_response);
    }

    public function get15kmApplicantsAjax($id,$radius = null)
    {
        $job_result = Sale::find($id);

        $job_title = $job_result->job_title;
        $job_postcode = $job_result->postcode;
     	//new requiremnt change onl non nurse speciallist job tile prop  id show again data
		 $job_title_prop=null;
        if ($job_title == "nonnurse specialist"){
            $job_title_prop =  $job_result->job_title_prof;

        }
        if($radius==10 || $radius == null)
        {
           // $radius = 8;
			$radius = 10;
        }
        $postcode_para = urlencode($job_postcode).',UK';
        $postcode_api = config('app.postcode_api');

        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$postcode_para}&key={$postcode_api}";
        $resp_json = file_get_contents($url);
        $near_by_applicants = '';

        $resp = json_decode($resp_json, true);
        if ($resp['status'] == 'OK') {
            $lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
            $longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";

            $near_by_applicants = $this->distance($lati, $longi, $radius, $job_title,$job_title_prop);
        } else {
            echo "<strong>ERROR: {$resp['status']}</strong>";
        }
	
	    //	if($near_by_applicants != '')
		if($near_by_applicants != null)
        {
			
        $non_interest_response = $this->check_not_interested_applicants($near_by_applicants, $id);
        $check_applicant_availibility = array_values($non_interest_response);
			
		}
		
        else
        {
            $check_applicant_availibility = array_values([]);
        }

        return datatables($check_applicant_availibility)
            ->addColumn('action', function ($applicant) use ($id) {
                $status_value = 'open';
                if ($applicant['paid_status'] == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant['cv_notes'] as $key => $value) {
                        if ($value['status'] == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif (($value['status'] == 'disable') && ($value['sale_id'] == $id)) {
                            $status_value = 'reject_job';
                            break;
                        } elseif ($value['status'] == 'disable') {
                            $status_value = 'reject';
                        } elseif (($value['status'] == 'paid') && ($value['sale_id'] == $id) && ($applicant['paid_status'] == 'open')) {
                            $status_value = 'paid';
                            break;
                        }
                    }
                }
                /***
                foreach ($applicant['cv_notes'] as $key => $value) {
                    if ($value['status'] == 'active') {
                        $status_value = 'sent';
                        break;
                    } elseif ($value['status'] == 'disable' && $value['sale_id'] == $id) {
                        $status_value = 'reject_job';
                        break;
                    } elseif ($value['status'] == 'disable') {
                        $status_value = 'reject';
                    } elseif ($value['status'] == 'paid') {
                        $status_value = 'paid';
                        break;
                    }
                }
                */
                $content = "";
                $content .= '<div class="list-icons">';
                $content .= '<div class="dropdown">';
                $content .= '<a href="#" class=list-icons-item" data-toggle="dropdown">
                                    <i class="icon-menu9"></i>
                                </a>';
                $content .= '<div class="dropdown-menu dropdown-menu-right">';
                if ($status_value == 'open' || $status_value == 'reject') {
                    $content .= '<a href="#" class="dropdown-item"
                            data-controls-modal="#modal_form_horizontal' . $applicant['id'] . '"
                            data-backdrop="static" data-keyboard="false" data-toggle="modal"
                            data-target="#modal_form_horizontal' . $applicant['id'] . '">
                            NOT INTERESTED</a>';
                  $content .= '<a href="#" ' . ($applicant['temp_not_interested'] == '1' ? 'title="Applicant is temporarily not interested" data-toggle="tooltip" onclick="return false;"' : '') . ' 
                class="dropdown-item" 
                data-controls-modal="#sent_cv' . $applicant['id'] . '" data-backdrop="static" 
                data-keyboard="false" data-toggle="modal" data-target="#sent_cv' . $applicant['id'] . '">
                <span>SEND CV</span></a>';

                    $content .= '<a href="#" class="dropdown-item" 
                        data-controls-modal="#no_nurse_home' . $applicant['id'] . '" 
                        data-backdrop="static" data-keyboard="false" data-toggle="modal"
                        data-target="#no_nurse_home' . $applicant['id'] . '">NO NURSING HOME</a>';
                    $content .= '<a href="#" class="dropdown-item" 
                        data-controls-modal="#call_back' . $applicant['id'] . '" 
                        data-backdrop="static" data-keyboard="false" data-toggle="modal"
                        data-target="#call_back' . $applicant['id'] . '">CALLBACK</a>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    // NOT INTERESTED MODAL
                    $content .= '<div id="modal_form_horizontal' . $applicant['id'] . '" 
                                    class="modal fade" tabindex="-1">';
                    $content .= '<div class="modal-dialog modal-lg">';
                    $content .= '<div class="modal-content">';
                    $content .= '<div class="modal-header">';
                    $content .= '<h5 class="modal-title">Enter Reason Below:</h5>';
                    $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    $content .= '</div>';
                    $url = '/mark-applicant';
                    $csrf = csrf_token();
                    $content .= '<form action="' . $url . '" method="POST" class="form-horizontal">';
                    $content .= '<input type="hidden" name="_token" value="' . $csrf . '">';
                    $content .= '<div class="modal-body">';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Reason</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<input type="hidden" name="applicant_hidden_id" 
                                   value="' . $applicant['id'] . '">';
                    $content .= '<input type="hidden" name="job_hidden_id" value="' . $id . '">';
                    $content .= '<textarea name="reason" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '<div class="modal-footer">';
                    $content .= '<button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>';
                    $content .= '<button type="submit" class="btn bg-teal legitRipple">Save</button>';
                    $content .= '</div>';
                    $content .= '</form>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    // NOT INTERESTED MODAL END

                    // SEND CV MODAL
                    $content .= '<div id="sent_cv' . $applicant['id'] . '" 
                                   class="modal fade" tabindex="-1">';
                    $content .= '<div class="modal-dialog modal-lg">';
                    $content .= '<div class="modal-content">';
                    $content .= '<div class="modal-header">';
                    $content .= '<h5 class="modal-title">Add Notes Below:</h5>';
                    $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    $content .= '</div>';
                    $url2 = '/applicant-cv-to-quality/';
                    $csrf2 = csrf_token();
                    $content .= '<form action="' . $url2 . $applicant['id'] . '" method="GET" class="form-horizontal">';
                    $content .= '<input type="hidden" name="_token" value="' . $csrf2 . '">';
					$content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant['id'] . '">';
                    $content .= '<input type="hidden" name="sale_hidden_id" value="' . $id . '">';
                    $content .= '<div class="modal-body">';
					 $content  .='<div id="interested">'; // Added a container div for the fields to be hidden
                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">1.</strong> Current Employer Name</label>';
                    $content  .='<div class="col-sm-9">';
                    $content  .='<input type="text" name="current_employer_name" class="form-control" placeholder="Enter Employer Name">';
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">2.</strong> PostCode</label>';
                    $content  .='<div class="col-sm-3">';
                    $content  .='<input type="text" name="postcode" class="form-control" placeholder="Enter PostCode">';
                    $content  .='</div>';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">3.</strong> Current/Expected Salary</label>';
                    $content  .='<div class="col-sm-3">';
                    $content  .='<input type="text" name="expected_salary" class="form-control" placeholder="Enter Salary">';
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">4.</strong> Qualification</label>';
                    $content  .='<div class="col-sm-9">';
                    $content  .='<input type="text" name="qualification" class="form-control" placeholder="Enter Qualification">';
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">5.</strong> Transport Type</label>';
                    $content  .='<div class="col-sm-9 d-flex align-items-center">';
                    $content  .='<div class="form-check form-check-inline">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="transport_type[]" id="walk" value="By Walk">';
                    $content  .='<label class="form-check-label" for="walk">By Walk</label>';
                    $content  .='</div>';
					$content  .='<div class="form-check form-check-inline">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="transport_type[]" id="cycle" value="Cycle">';
                    $content  .='<label class="form-check-label" for="cycle">Cycle</label>';
                    $content  .='</div>';
                    $content  .='<div class="form-check form-check-inline ml-3">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="transport_type[]" id="car" value="Car">';
                    $content  .='<label class="form-check-label" for="car">Car</label>';
                    $content  .='</div>';
                    $content  .='<div class="form-check form-check-inline ml-3">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="transport_type[]" id="public_transport" value="Public Transport">';
                    $content  .='<label class="form-check-label" for="public_transport">Public Transport</label>';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">6.</strong> Shift Pattern</label>';
                    $content  .='<div class="col-sm-9 d-flex align-items-center">';
                    $content  .='<div class="form-check form-check-inline">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="shift_pattern[]" id="day" value="Day">';
                    $content  .='<label class="form-check-label" for="day">Day</label>';
                    $content  .='</div>';
					$content  .='<div class="form-check form-check-inline">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="shift_pattern[]" id="night" value="Night">';
                    $content  .='<label class="form-check-label" for="night">Night</label>';
                    $content  .='</div>';
                    $content  .='<div class="form-check form-check-inline ml-3">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="shift_pattern[]" id="full_time" value="Full Time">';
                    $content  .='<label class="form-check-label" for="full_time">Full Time</label>';
                    $content  .='</div>';
                    $content  .='<div class="form-check form-check-inline ml-3">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="shift_pattern[]" id="part_time" value="Part Time">';
                    $content  .='<label class="form-check-label" for="part_time">Part Time</label>';
                    $content  .='</div>';
                    $content  .='<div class="form-check form-check-inline ml-3">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="shift_pattern[]" id="twenty_four_hours" value="24 hours">';
                    $content  .='<label class="form-check-label" for="twenty_four_hours">24 Hours</label>';
                    $content  .='</div>';
                    $content  .='<div class="form-check form-check-inline">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="shift_pattern[]" id="day_night" value="Day/Night">';
                    $content  .='<label class="form-check-label" for="day_night">Day/Night</label>';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">7.</strong> Nursing Home</label>';
                    $content  .='<div class="col-sm-3 d-flex align-items-center">';
                    $content  .='<div class="form-check mt-0">';
                    $content  .='<input type="checkbox" name="nursing_home" style="margin-top:-3px" id="nursing_home_checkbox" class="form-check-input" value="0">';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">8.</strong> Alternate Weekend</label>';
                    $content  .='<div class="col-sm-3 d-flex align-items-center">';
                    $content  .='<div class="form-check mt-0">';
                    $content  .='<input type="checkbox" name="alternate_weekend" style="margin-top:-3px" id="alternate_weekend_checkbox" class="form-check-input" value="0">';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='</div>';
					
					 $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">9.</strong> Interview Availability</label>';
                    $content  .='<div class="col-sm-3 d-flex align-items-center">';
                    $content  .='<div class="form-check mt-0">';
                    $content  .='<input type="text" class="form-control" name="interview_availability" id="interview_availability" class="form-check-input">';
                    $content  .='</div>';
                    $content  .='</div>';
                    
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">10.</strong> No Job</label>';
                    $content  .='<div class="col-sm-3 d-flex align-items-center">';
                    $content  .='<div class="form-check mt-0">';
                    $content  .='<input type="checkbox" name="no_job" id="no_job_checkbox" style="margin-top:-3px" class="form-check-input" value="0">';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">11.</strong> Visa Status</label>';
                    $content  .='<div class="col-sm-3">';
                    $content  .='<div class="form-check form-check-inline">';
                    $content  .='<input type="radio" name="visa_status" id="british" class="form-check-input mt-0" value="British">';
                    $content  .='<label class="form-check-label" for="british">British</label>';
                    $content  .='</div><br>';
                    $content  .='<div class="form-check form-check-inline">';
                    $content  .='<input type="radio" name="visa_status" id="required_sponsorship" class="form-check-input mt-0" value="Required Sponsorship">';
                    $content  .='<label class="form-check-label" for="required_sponsorship">Required Sponsorship</label>';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='</div>'; // Close upper-fields div
					$content  .='<div class="form-group row">';
                    $content  .='<div class="col-sm-1 d-flex justify-content-center align-items-center">';
                    $content  .='<input type="checkbox" name="hangup_call" id="hangup_call" class="form-check-input" value="0">';
                    $content  .='</div>';
                    $content  .='<div class="col-sm-11">';
                    $content  .='<label for="hangup_call" class="col-form-label" style="font-size:16px;">Call Hung up/Not Interested</label>';
                    $content  .='</div>';
                    $content  .='</div>';
					
                   $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3">Other Details <span class="text-danger">*</span></label>';
                    $content  .='<div class="col-sm-9">';
                    $content  .='<input type="hidden" name="module_key" value="'.$applicant['id'].'">';
                    $content  .='<textarea name="details" id="note_details'. $applicant['id'] .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE .." required></textarea>';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='</div>';
					
                     $content  .='<div class="modal-footer">';
                    $content  .='<button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>';
                    $content  .='<button type="submit" data-note_key="'. $applicant['id'] .'" class="btn bg-teal legitRipple note_form_submit">Save</button>';
                    $content  .='</div>';
                    $content .= '</form>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    // SEND CV MODAL END
					
                    // NO NURSING HOME MODAL
                    $content .= '<div id="no_nurse_home' . $applicant['id'] . '" class="modal fade" tabindex="-1">';
                    $content .= '<div class="modal-dialog modal-lg">';
                    $content .= '<div class="modal-content">';
                    $content .= '<div class="modal-header">';
                    $content .= '<h5 class="modal-title">Add No Nursing Home Below:</h5>';
                    $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    $content .= '</div>';
                    $url3 = '/sent-to-nurse-home';
                    $csrf3 = csrf_token();
                    $content .= '<form action="' . $url3 . '" method="GET" class="form-horizontal">';
                    $content .= '<input type="hidden" name="_token" value="' . $csrf3 . '">';
                    $content .= '<div class="modal-body">';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Details</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant['id'] . '">';
                    $content .= '<input type="hidden" name="sale_hidden_id" value="' . $id . '">';
                    $content .= '<textarea name="details" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '<div class="modal-footer">';
                    $content .= '<button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>';
                    $content .= '<button type="submit" class="btn bg-teal legitRipple">Save</button>';
                    $content .= '</div>';
                    $content .= '</form>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    // NO NURSING HOME MODAL END
                    // CALLBACK MODAL
                    $content .= '<div id="call_back' . $applicant['id'] . '" class="modal fade" tabindex="-1">';
                    $content .= '<div class="modal-dialog modal-lg">';
                    $content .= '<div class="modal-content">';
                    $content .= '<div class="modal-header">';
                    $content .= '<h5 class="modal-title">Add Callback Notes Below:</h5>';
                    $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    $content .= '</div>';
                    $url4 = '/sent-applicant-to-call-back-list';
                    $csrf4 = csrf_token();
                    $content .= '<form action="' . $url4 . '" method="GET" class="form-horizontal">';
                    $content .= '<input type="hidden" name="_token" value="' . $csrf4 . '">';
                    $content .= '<div class="modal-body">';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Details</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<input type="hidden" name="applicant_hidden_id"
                            value="' . $applicant['id'] . '">';
                    $content .= '<input type="hidden" name="sale_hidden_id" value="' . $id . '">';
                    $content .= '<textarea name="details" class="form-control" cols="30" rows="4"
                            placeholder="TYPE HERE.." required></textarea>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '<div class="modal-footer">';
                    $content .= '<button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>';
                    $content .= '<button type="submit" class="btn bg-teal legitRipple">Save</button>';
                    $content .= '</div>';
                    $content .= '</form>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    // CALLBACK MODAL END
                } elseif ($status_value == 'sent' || $status_value == 'reject_job' || $status_value == 'paid') {
                    $content .= '<a href="#" class="disabled dropdown-item">LOCKED</a>';
                    $content .= '<a href="#" class="disabled dropdown-item">LOCKED</a>';
                    $content .= '<a href="#" class="disabled dropdown-item">LOCKED</a>';
                    $content .= '<a href="#" class="disabled dropdown-item">LOCKED</a>';
                }
                return $content;
            })
            ->addColumn('status', function ($applicant) use ($id) {
                $status_value = 'open';
                $color_class = 'bg-teal-800';
                if ($applicant['paid_status'] == 'close') {
                    $status_value = 'paid';
                    $color_class = 'bg-slate-700';
                } else {
                    foreach ($applicant['cv_notes'] as $key => $value) {
                        if ($value['status'] == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif (($value['status'] == 'disable') && ($value['sale_id'] == $id)) {
                            $status_value = 'reject_job';
                            break;
                        } elseif ($value['status'] == 'disable') {
                            $status_value = 'reject';
                        } elseif (($value['status'] == 'paid') && ($value['sale_id'] == $id) && ($applicant['paid_status'] == 'open')) {
                            $status_value = 'paid';
                            $color_class = 'bg-slate-700';
                            break;
                        }
                    }
                }
                /***
                foreach ($applicant['cv_notes'] as $key => $value) {
                    if ($value['status'] == 'active') {
                        $status_value = 'sent';
                        break;
                    } elseif ($value['status'] == 'disable' && $value['sale_id'] == $id) {
                        $status_value = 'reject_job';
                        break;
                    } elseif ($value['status'] == 'disable') {
                        $status_value = 'reject';
                    } elseif ($value['status'] == 'paid') {
                        $status_value = 'paid';
                        $color_class = 'bg-slate-700';
                        break;
                    }
                }
                */
                $status = '';
                $status .= '<h3>';
                $status .= '<span class="badge w-100 '.$color_class.'">';
                $status .= strtoupper($status_value);
                $status .= '</span>';
                $status .= '</h3>';
                return $status;
            })
			->addColumn('applicant_notes', function($applicant) use ($id) {

                $app_new_note = ModuleNote::where(['module_noteable_id' =>$applicant['id'], 'module_noteable_type' =>'Horsefly\Applicant'])
                ->select('module_notes.details')
                ->orderBy('module_notes.id', 'DESC')
                ->first();
                $app_notes_final='';
                if($app_new_note){
                    $app_notes_final = $app_new_note->details;

                }
                else{
                    $app_notes_final = $applicant['applicant_notes'];
                }
            $status_value = 'open';
            $postcode = '';
            if ($applicant['paid_status'] == 'close') {
                $status_value = 'paid';
            } else {
                foreach ($applicant['cv_notes'] as $key => $value) {
                    if ($value['status'] == 'active') {
                        $status_value = 'sent';
                        break;
                    } elseif ($value['status'] == 'disable') {
                        $status_value = 'reject';
                    }
                }
            }
               
            if($applicant['is_blocked'] == 0 && $status_value == 'open' || $status_value == 'reject')
            {
                
            $content = '';
            // if ($status_value == 'open' || $status_value == 'reject'){

            $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant['id'].'"
                             data-controls-modal="#clear_cv'.$applicant['id'].'"
                             data-backdrop="static" data-keyboard="false" data-toggle="modal"
                             data-target="#clear_cv' . $applicant['id'] . '">"'.$app_notes_final.'"</a>';
            $content .= '<div id="clear_cv' . $applicant['id'] . '" class="modal fade" tabindex="-1">';
            $content .= '<div class="modal-dialog modal-lg">';
            $content .= '<div class="modal-content">';
            $content .= '<div class="modal-header">';
            $content .= '<h5 class="modal-title">Notes</h5>';
            $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
            $content .= '</div>';
            $content .= '<form action="' . route('block_or_casual_notes') . '" method="POST" id="app_notes_form' . $applicant['id'] . '" class="form-horizontal">';
            $content .= csrf_field();
            $content .= '<div class="modal-body">';
            $content .='<div id="app_notes_alert' . $applicant['id'] . '"></div>';
            $content .= '<div id="sent_cv_alert' . $applicant['id'] . '"></div>';
            $content .= '<div class="form-group row">';
            $content .= '<label class="col-form-label col-sm-3">Details</label>';
            $content .= '<div class="col-sm-9">';
            $content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant['id'] . '">';
            $content .= '<input type="hidden" name="applicant_sale_id" value="' . $id . '">';
            $content .= '<input type="hidden" name="applicant_page' . $applicant['id'] . '" value="15_km_applicants_nurses">';
            $content .= '<textarea name="details" id="sent_cv_details' . $applicant['id'] .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
            $content .= '</div>';
            $content .= '</div>';
                $content .= '<div class="form-group row">';
                $content .= '<label class="col-form-label col-sm-3">Choose type:</label>';
                $content .= '<div class="col-sm-9">';
                $content .= '<select name="reject_reason" class="form-control crm_select_reason" id="reason' . $applicant['id'] .'">';
                $content .= '<option value="0" >Select Reason</option>';
                $content .= '<option value="1">Casual Notes</option>';
                $content .= '<option value="2">Block Applicant Notes</option>';
				$content .= '<option value="3">Temporary Not Interested Applicants Notes</option>';
                $content .= '</select>';
                $content .= '</div>';
                $content .= '</div>';

            $content .= '</div>';
            $content .= '<div class="modal-footer">';
               
                $content .= '<button type="button" class="btn bg-dark legitRipple sent_cv_submit" data-dismiss="modal">Close</button>';
                
                $content .= '<button type="submit" data-note_key="' . $applicant['id'] . '" value="cv_sent_save" class="btn bg-teal legitRipple sent_cv_submit app_notes_form_submit">Save</button>';

            $content .= '</div>';
            $content .= '</form>';
            $content .= '</div>';
            $content .= '</div>';
            $content .= '</div>';
        // } else {
            // $content .= $applicant->applicant_notes;
            // }

                //return $app_notes_final;
               return $content;
        }else
        {
           return $app_notes_final;
        }

            })
            ->addColumn("applicant_postcode", function ($applicant) {
                $status_value = 'open';
                $postcode = '';
                if ($applicant['paid_status'] == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant['cv_notes'] as $key => $value) {
                        if ($value['status'] == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value['status'] == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                /***
                foreach ($applicant['cv_notes'] as $key => $value) {
                    if ($value['status'] == 'active') {
                        $status_value = 'sent'; // alert-success
                        break;
                    } elseif ($value['status'] == 'disable') {
                        $status_value = 'reject'; // alert-danger
                    } elseif ($value['status'] == 'paid') {
                        $status_value = 'paid';
                        break;
                    }
                }
                */
                if($status_value == 'open' || $status_value == 'reject') {
                    $postcode .= '<a href="/available-jobs/'.$applicant['id'].'">';
                    $postcode .= $applicant['applicant_postcode'];
                    $postcode .= '</a>';
                } else {
                    $postcode .= $applicant['applicant_postcode'];
                }
                return $postcode;
            })
			->addColumn('download', function ($applicant) {
                    $download = '<a href="'. route('downloadApplicantCv',$applicant['id']).'">
                       <i class="fas fa-file-download text-teal-400"></i>
                    </a>';
                    return $download;
            })
			->addColumn('updated_cv', function ($applicant) {
                return
                    '<a href="' . route('downloadUpdatedApplicantCv', $applicant['id']) . '">
                       <i class="fas fa-file-download text-teal-400"></i>
                    </a>';
            })
            ->editColumn('applicant_job_title', function ($applicant) {
                $job_title_desc='';
                // $job_title_desc = ($applicant['job_title_prof']!='')?$applicant['applicant_job_title'].' ('.$applicant['job_title_prof'].')':$applicant['applicant_job_title'];
                if($applicant['job_title_prof']!=null)
                {
                   
                  
        $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $applicant['job_title_prof'])->first();
                    $job_title_desc = $applicant['applicant_job_title'].' ('.$job_prof_res->specialist_prof.')';
                }
                else
                {
                    
                    $job_title_desc = $applicant['applicant_job_title'];
                }
                return $job_title_desc;

         })
            ->editColumn('updated_at', function($applicant){
                $updatedAt = new Carbon($applicant['updated_at']);
                return $updatedAt->timestamp;
            })
            ->setRowClass(function ($applicant) {
                $row_class = '';
                if ($applicant['paid_status'] == 'close') {
                    $row_class = 'class_dark';
                } else { /*** $applicant['paid_status'] == 'open' || $applicant['paid_status'] == 'pending' */
                    foreach ($applicant['cv_notes'] as $key => $value) {
                        if ($value['status'] == 'active') {
                            $row_class = 'class_success';
                            break;
                        } elseif ($value['status'] == 'disable') {
                            $row_class = 'class_danger';
                        }
                    }
                }
                /***
                foreach ($applicant['cv_notes'] as $key => $value) {
                    if ($value['status'] == 'active') {
                        $row_class = 'class_success'; // status: sent
                        break;
                    } elseif ($value['status'] == 'disable') {
                        $row_class = 'class_danger'; // status: reject
                    } elseif ($value['status'] == 'paid') {
                        $row_class = 'class_dark';
                        break;
                    }
                }
                */
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','applicant_postcode','download','updated_cv','upload','applicant_notes','status', 'action'])
            ->make(true);
		
	        /*	->addColumn('upload', function ($applicant) {
                return
                '<a href="#"
                data-controls-modal="#import_applicant_cv" class="import_cv"
                data-backdrop="static"
                data-keyboard="false" data-toggle="modal" data-id="'.$applicant['id'].'"
                data-target="#import_applicant_cv">
                 <i class="fas fa-file-upload text-teal-400"></i>
                 &nbsp;</a>';
            }) ***/
    }

	public function export_15_km_applicants($id)
    {
		//echo 'hewr';exit();
        $job_result = Sale::find($id);
        $job_title = $job_result->job_title;
        $job_postcode = $job_result->postcode;
        //$radius = 15;
        //change radius 15 to 8 
        $radius = 8;
        $postcode_para = urlencode($job_postcode);
        $postcode_api = config('app.postcode_api');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$postcode_para}&key={$postcode_api}";
        $resp_json = file_get_contents($url);
        $near_by_applicants = '';

        $resp = json_decode($resp_json, true);
        if ($resp['status'] == 'OK') {
            $lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
            $longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";

            $near_by_applicants = $this->export_applicants_15km_distance($lati, $longi, $radius, $job_title);
        } else {
            echo "<strong>ERROR: {$resp['status']}</strong>";
        }
        $non_interest_response = $this->check_not_interested_applicants_export($near_by_applicants, $id);

        $check_applicant_availibility = array_values($non_interest_response);
        // echo '<pre>';print_r($non_interest_response);echo '</pre>';exit();
        // $check_applicant_availibility = $this->createIndexCollection($id,$check_applicant_availibility);
        return Excel::download(new Applicants_nurses_15kmExport($check_applicant_availibility), 'applicants.csv');

    }
	
	function check_not_interested_applicants_export($applicants_object, $job_id)
    {

        $pivot_result = array();
        $filter_applicant = array();
        $app_id = '';
        $job_db_id='';
        foreach ($applicants_object as $key => $value) {
            $applicant_id = $value->id;
            $pivot_result[] = Applicants_pivot_sales::where("applicant_id", $applicant_id)->where("sales_id", $job_id)->first();
            foreach ($pivot_result as $res) 
            { 
                if(isset($res['applicant_id']) && isset($res['applicant_id']))
                {
                    $app_id = $res['applicant_id'];
                    $job_db_id = $res['sales_id'];
                }
            }
            if (($applicant_id == $app_id) && ($job_id == $job_db_id)) {
                $applicants_object->forget($key);
            }
        }
        foreach ($applicants_object as $key => $filter_val) {
            if (($filter_val['is_in_nurse_home'] == 'yes') || ($filter_val['is_callback_enable'] == 'yes') || ($filter_val['is_blocked'] == '1')) {
                $applicants_object->forget($key);

            }
            unset( $filter_val['distance']);
			


			// to get applicant history

             $applicants_in_crm = Applicant::join('crm_notes', 'crm_notes.applicant_id', '=', 'applicants.id')
            ->join('sales', 'sales.id', '=', 'crm_notes.sales_id')
            ->join('offices', 'offices.id', '=', 'head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->join('history', function($join) {
                $join->on('crm_notes.applicant_id', '=', 'history.applicant_id');
                $join->on('crm_notes.sales_id', '=', 'history.sale_id');
            })
            ->select("applicants.*", "applicants.id as app_id", "crm_notes.*", "crm_notes.id as crm_notes_id", "sales.*", "sales.id as sale_id", "sales.postcode as sale_postcode", "sales.job_title as sale_job_title", "sales.job_category as sales_job_category", "sales.status as sale_status", "history.history_added_date", "history.sub_stage","office_name", "unit_name","history.updated_at")
            ->where(array('applicants.id' => $filter_val['id'], 'history.status' => 'active'))
            ->whereIn('crm_notes.id', function($query){
                $query->select(DB::raw('MAX(id) FROM crm_notes WHERE sales_id=sales.id and applicants.id=applicant_id'));
            })
            ->get()->last();
            $history_stages = config('constants.history_stages');
			if($applicants_in_crm!='')
				{
			
				if($history_stages[$applicants_in_crm['sub_stage']]=='Sent CV' || $history_stages[$applicants_in_crm['sub_stage']]=='Request' || $history_stages[$applicants_in_crm['sub_stage']]=='Confirmation' || $history_stages[$applicants_in_crm['sub_stage']]=='Rebook' || $history_stages[$applicants_in_crm['sub_stage']]=='Attended to Pre-Start Date' || 
			  $history_stages[$applicants_in_crm['sub_stage']]=='Attended to Pre-Start Date' || $history_stages[$applicants_in_crm['sub_stage']]=='Start Date' || 
			  $history_stages[$applicants_in_crm['sub_stage']]=='Invoice' ||
			  $history_stages[$applicants_in_crm['sub_stage']]=='Paid')  
			   {   
			   //echo $history_stages[$applicants_in_crm->sub_stage];exit();
			   unset($applicants_object[$key]); 
		   
			}
				}
			 	
        		
			            unset( $filter_val['id']);			            
					unset( $filter_val['cv_notes']);
        
        }
        return $applicants_object->toArray();

    }
	
    public function get15kmApplicants($id,$radius = null)
    {
        $sent_cv_count = Cv_note::where(['sale_id' => $id, 'status' => 'active'])->count();
		$cv_limit = Cv_note::where(['sale_id'=> $id,'status' => 'active'])
                    ->count();
        $job = Office::join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->select('sales.*', 'offices.office_name', 'units.contact_name',
                'units.contact_email', 'units.unit_name', 'units.contact_phone_number', 'sales.id as sale_id')
            ->where(['sales.status' => 'active', 'sales.id' => $id])->first();
		$sale_export_id= $id;

        $active_applicants = [];
        if ($sent_cv_count == $job['send_cv_limit']) {

            $active_applicants = Applicant::join('history', function ($join) use ($id) {
                    $join->on('history.applicant_id', '=', 'applicants.id');
                    $join->where('history.sale_id', '=', $id);
                })->whereIn('history.sub_stage', ['quality_cvs','quality_cleared','crm_save','crm_request','crm_request_save','crm_request_confirm','crm_interview_save','crm_interview_attended','crm_prestart_save', 'crm_start_date','crm_start_date_save','crm_start_date_back','crm_invoice','crm_final_save'])
                ->where('history.status', '=', 'active')
                ->select('applicants.applicant_name','applicants.applicant_postcode',
                    'history.stage','history.sub_stage','history.history_added_date','history.history_added_time')
                ->get()->toArray();
        }

        return view('administrator.resource.15km_applicants', compact('id', 'job', 'sent_cv_count', 'active_applicants','sale_export_id','radius','cv_limit'));
    }

    public function getActive15kmApplicants($id)
    {

        $applicant = Applicant::find($id);
            //        $applicant_job_title = $applicant->applicant_job_title;
            //        $applicant_postcode = $applicant->applicant_postcode;
            //        $is_applicant_in_quality = $applicant->is_cv_in_quality;
            //        $radius = 15;
            //        $postcode_para = urlencode($applicant_postcode);
            //        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$postcode_para}&key=AIzaSyBPx06p1VPBhS_qz-dw7t0rYkoMbKeoNBM";
            //        $resp_json = file_get_contents($url);
            //        $near_by_jobs = '';

            //        $resp = json_decode($resp_json, true);
            //
            //        if ($resp['status'] == 'OK') {
            //
            //            // get the important data
            //            $lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
            //            $longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";
            //            $near_by_jobs = $this->job_distance($lati, $longi, $radius, $applicant_job_title);
            //
            //        } else {
            //            echo "<strong>ERROR: {$resp['status']}</strong>";
            //        }
            //        $jobs = $this->check_not_interested_in_jobs($near_by_jobs, $id);
            //        foreach ($jobs as &$job) {
            //            $office_id = $job['head_office'];
            //            $unit_id = $job['head_office_unit'];
            //            $office = Office::select("office_name")->where(["id" => $office_id, "status" => "active"])->first();
            //            $office = $office->office_name;
            //            $unit = Unit::select("unit_name")->where(["id" => $unit_id, "status" => "active"])->first();
            //            $unit = $unit->unit_name;
            //            $job['office_name'] = $office;
            //            $job['unit_name'] = $unit;
            //        }

            //        $applicants_crm_accepted = Applicant::join('quality_notes', 'applicants.id', '=', 'quality_notes.applicant_id')
            //            ->where([['applicants.status', 'active'], ["quality_notes.moved_tab_to", "cleared"]]);
            //        $applicants_crm_accepted = $applicants_crm_accepted->where([['is_CV_sent', 'yes'], ["quality_notes.moved_tab_to", "cleared"]])
            //            ->orWhere([['is_interview_confirm', 'yes'], ["quality_notes.moved_tab_to", "cleared"]])
            //            ->orWhere([['is_interview_attend', 'yes'], ["quality_notes.moved_tab_to", "cleared"]])
            //            ->orWhere([['is_in_crm_request', 'yes'], ["quality_notes.moved_tab_to", "cleared"]])
            //            ->orWhere([['is_crm_request_confirm', 'yes'], ["quality_notes.moved_tab_to", "cleared"]])
            //            ->orWhere([['is_crm_interview_attended', 'yes'], ["quality_notes.moved_tab_to", "cleared"]])
            //            ->orWhere([['is_in_crm_start_date', 'yes'], ["quality_notes.moved_tab_to", "cleared"]])
            //            ->orWhere([['is_in_crm_invoice', 'yes'], ["quality_notes.moved_tab_to", "cleared"]])
            //            ->orWhere([['is_in_crm_paid', 'yes'], ["quality_notes.moved_tab_to", "cleared"]])
            //            ->select('applicants.id', 'quality_notes.sale_id as sale_id')
            //            ->get();

            //        $applicants_rejected = Applicant::join('quality_notes', 'applicants.id', '=', 'quality_notes.applicant_id')
            //            ->where('applicants.status', 'active');
            //        $applicants_rejected = $applicants_rejected->where('is_in_crm_reject', 'yes')
            //            ->orWhere('is_in_crm_request_reject', 'yes')
            //            ->orWhere('is_crm_interview_attended', 'no')
            //            ->orWhere('is_in_crm_start_date_hold', 'yes')
            //            ->orWhere('is_in_crm_dispute', 'yes')
            //            ->orWhere([['is_CV_reject', 'yes'], ["quality_notes.moved_tab_to", "rejected"]])
            //            ->select('applicants.id', 'quality_notes.sale_id as sale_id', 'applicants.is_cv_in_quality', 'applicants.is_CV_reject')
            //            ->get();
            //
            //        $applicants_rejected_job = array();
            //        $sales_rejected_job = array();
            //        $x = 0;

            //        foreach ($applicants_rejected as $app) {
            //
            //            $present = 0;
            //            $sales_appls = array();
            //            $sale_object_quality = Quality_notes::select("sale_id")->where("applicant_id", $app->id)->where("moved_tab_to", "rejected")->get();
            //            if (!empty($sale_object_quality)) {
            //
            //                foreach ($sale_object_quality as $sale) {
            //                    if (!empty($sale)) {
            //                        array_push($sales_appls, $sale->sale_id);
            //                        $present = 1;
            //                    }
            //                }
            //
            //            }
            //
            //            //$sale_object_crm = Crm_note::select("sales_id")->where("applicant_id", $app->id);
            //            //$sale_object_crm = $sale_object_crm->where("moved_tab_to", "cv_sent_reject")->orWhere("moved_tab_to", "request_reject")->get();
            //
            //
            //            $sale_object_crm = Crm_note::leftJoin('crm_rejected_cv', 'crm_rejected_cv.applicant_id', '=', 'crm_notes.applicant_id')
            //                ->select('crm_notes.applicant_id', 'crm_notes.sales_id', 'crm_rejected_cv.reason');
            //            $sale_object_crm = $sale_object_crm->where('crm_rejected_cv.applicant_id', $app->id)
            //                ->orWhere([['crm_rejected_cv.reason', '!=', 'position_filled'], ["moved_tab_to", "cv_sent_reject"]])
            //                ->orWhere("moved_tab_to", "request_reject")
            //                ->orWhere("moved_tab_to", "interview_not_attended")
            //                ->orWhere("moved_tab_to", "start_date_hold")
            //                ->orWhere("moved_tab_to", "dispute")
            //                ->get();
            //
            //
            //            if (!empty($sale_object_crm)) {
            //
            //                foreach ($sale_object_crm as $sale) {
            //                    if (!empty($sale)) {
            //                        array_push($sales_appls, $sale->sales_id);
            //                        $present = 1;
            //                    }
            //                }
            //
            //            }
            //            if ($present == 1) {
            //                $applicants_rejected_job[$x] = $app;
            //                $sales_rejected_job[$x] = $sales_appls;
            //                $x++;
            //            }
            //        }
            //        return view('administrator.resource.15km_jobs', compact('jobs', 'applicant', 'is_applicant_in_quality', 'applicants_rejected', 'applicants_rejected_job', 'sales_rejected_job', 'applicants_crm_accepted'));
        return view('administrator.resource.15km_jobs', compact('applicant'));
    }

    function getAllTitles($job_title)
    {
        $title = array();
        if ($job_title === 'rgn/rmn') {
            $title[0] = "rgn";
            $title[1] = "rmn";
            $title[2] = "rmn/rnld";
            $title[3] = "rgn/rmn/rnld";
            $title[4] = $job_title;
            $title[5] = $job_title;
            $title[6] = $job_title;
            $title[7] = $job_title;
            $title[8] = $job_title;
            $title[9] = $job_title;
            $title[10] = $job_title;
        } elseif ($job_title === "rmn/rnld") {
            $title[0] = "rmn";
            $title[1] = "rnld";
            $title[2] = "rgn/rmn";
            $title[3] = "rgn/rmn/rnld";
            $title[4] = $job_title;
            $title[5] = $job_title;
            $title[6] = $job_title;
            $title[7] = $job_title;
            $title[8] = $job_title;
            $title[9] = $job_title;
            $title[10] = $job_title;
        } elseif ($job_title === "rgn/rmn/rnld") {
            $title[0] = "rmn";
            $title[1] = "rgn";
            $title[2] = "rnld";
            $title[3] = "rgn/rmn";
            $title[4] = "rmn/rnld";
            $title[5] = $job_title;
            $title[6] = $job_title;
            $title[7] = $job_title;
            $title[8] = $job_title;
            $title[9] = $job_title;
            $title[10] = $job_title;
        } elseif ($job_title === 'rgn') {
            $title[0] = "rgn/rmn";
            $title[1] = "rgn/rmn/rnld";
            $title[2] = $job_title;
            $title[3] = $job_title;
            $title[4] = $job_title;
            $title[5] = $job_title;
            $title[6] = $job_title;
            $title[7] = $job_title;
            $title[8] = $job_title;
            $title[9] = $job_title;
            $title[10] = $job_title;
        } elseif ($job_title === "rmn") {
            $title[0] = "rgn/rmn";
            $title[1] = "rmn/rnld";
            $title[2] = "rgn/rmn/rnld";
            $title[3] = $job_title;
            $title[4] = $job_title;
            $title[5] = $job_title;
            $title[6] = $job_title;
            $title[7] = $job_title;
            $title[8] = $job_title;
            $title[9] = $job_title;
            $title[10] = $job_title;
        } elseif ($job_title === "rnld") {
            $title[0] = "rmn/rnld";
            $title[1] = "rgn/rmn/rnld";
            $title[2] = $job_title;
            $title[3] = $job_title;
            $title[4] = $job_title;
            $title[5] = $job_title;
            $title[6] = $job_title;
            $title[7] = $job_title;
            $title[8] = $job_title;
            $title[9] = $job_title;
            $title[10] = $job_title;
        } elseif ($job_title === "senior nurse") {
            $title[0] = "rmn";
            $title[1] = "rgn";
            $title[2] = "rnld";
            $title[3] = "rgn/rmn";
            $title[4] = "rmn/rnld";
            $title[5] = "rgn/rmn/rnld";
            $title[6] = "senior nurse";
            $title[7] = "clinical lead";
            $title[8] = $job_title;
            $title[9] = $job_title;
            $title[10] = $job_title;
        } elseif ($job_title === "nurse deputy manager") {
            $title[0] = "rmn";
            $title[1] = "rgn";
            $title[2] = "rnld";
            $title[3] = "rgn/rmn";
            $title[4] = "rmn/rnld";
            $title[5] = "rgn/rmn/rnld";
            $title[6] = "senior nurse";
            $title[7] = "clinical lead";
            $title[8] = $job_title;
            $title[9] = $job_title;
            $title[10] = $job_title;
        } elseif ($job_title === "nurse manager") {
            $title[0] = "rmn";
            $title[1] = "rgn";
            $title[2] = "rnld";
            $title[3] = "rgn/rmn";
            $title[4] = "rmn/rnld";
            $title[5] = "rgn/rmn/rnld";
            $title[6] = "nurse deputy manager";
            $title[7] = "senior nurse";
            $title[8] = "clinical lead";
            $title[9] = $job_title;
            $title[10] = $job_title;
        } elseif ($job_title === "clinical lead") {
            $title[0] = "rmn";
            $title[1] = "rgn";
            $title[2] = "rnld";
            $title[3] = "rgn/rmn";
            $title[4] = "rmn/rnld";
            $title[5] = "rgn/rmn/rnld";
            $title[6] = "nurse deputy manager";
            $title[7] = "senior nurse";
            $title[8] = "clinical lead";
            $title[9] = $job_title;
            $title[10] = $job_title;
        } elseif ($job_title === "rcn") {
            $title[0] = "rmn";
            $title[1] = "rgn";
            $title[2] = "rnld";
            $title[3] = "rgn/rmn";
            $title[4] = "rmn/rnld";
            $title[5] = "rgn/rmn/rnld";
            $title[6] = "nurse deputy manager";
            $title[7] = "senior nurse";
            $title[8] = "clinical lead";
            $title[9] = "rcn";
            $title[10] = $job_title;
        }elseif ($job_title === "head chef") {
            $title[0] = "sous chef";
            $title[1] = "Senior sous chef";
            $title[2] = "junior sous chef";
            $title[3] = "head chef";
            $title[4] = $job_title;
            $title[5] = $job_title;
            $title[6] = $job_title;
            $title[7] = $job_title;
            $title[8] = $job_title;
            $title[9] = $job_title;
            $title[10] = $job_title;
        }elseif ($job_title === "sous chef") {
            $title[0] = "chef de partie";
            $title[1] = "Senior chef de partie";
            $title[2] = "sous chef";
            $title[3] = $job_title;
            $title[4] = $job_title;
            $title[5] = $job_title;
            $title[6] = $job_title;
            $title[7] = $job_title;
            $title[8] = $job_title;
            $title[9] = $job_title;
            $title[10] = $job_title;
        }
		elseif ($job_title === "chef de partie") {
            $title[0] = "junior chef de partie";
            $title[1] = "Demmi chef de partie";
            $title[2] = "chef de partie";
            $title[3] = "commis chef";
            $title[4] = $job_title;
            $title[5] = $job_title;
            $title[6] = $job_title;
            $title[7] = $job_title;
            $title[8] = $job_title;
            $title[9] = $job_title;
            $title[10] = $job_title;
        }
		else {
            $title[0] = $job_title;
            $title[1] = $job_title;
            $title[2] = $job_title;
            $title[3] = $job_title;
            $title[4] = $job_title;
            $title[5] = $job_title;
            $title[6] = $job_title;
            $title[7] = $job_title;
            $title[8] = $job_title;
            $title[9] = $job_title;
            $title[10] = $job_title;
        }
        return $title;
    }
	
    function export_applicants_15km_distance($lat, $lon, $radius, $job_title)
    {
        $title = $this->getAllTitles($job_title);

        $location_distance = Applicant::with('cv_notes')->select(DB::raw("id,applicant_phone,applicant_name,applicant_homePhone,applicant_job_title,
        applicant_postcode,applicant_source,applicant_notes, ((ACOS(SIN($lat * PI() / 180) * SIN(lat * PI() / 180) + 
                COS($lat * PI() / 180) * COS(lat * PI() / 180) * COS(($lon - lng) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) 
                AS distance"))->having("distance", "<", $radius)->orderBy("distance")
            ->where(array("status" => "active", "is_in_nurse_home" => "no", 'is_blocked' => '0', 'is_callback_enable' => 'no')); //->get();

        $location_distance = $location_distance->where("applicant_job_title", $title[0])->orWhere("applicant_job_title", $title[1])->orWhere("applicant_job_title", $title[2])->orWhere("applicant_job_title", $title[3])->orWhere("applicant_job_title", $title[4])->orWhere("applicant_job_title", $title[5])->orWhere("applicant_job_title", $title[6])->orWhere("applicant_job_title", $title[7])->orWhere("applicant_job_title", $title[8])->orWhere("applicant_job_title", $title[9])->orWhere("applicant_job_title", $title[10])->get();

        //$location_distance = $location_distance->where("applicant_job_title", $title1)->get();
        return $location_distance;
    }

    function distance($lat, $lon, $radius, $job_title,$job_title_prop=null)
    {
		//	$radius = 10;
		//echo $lat.' and '.$lon.' and '.$radius.' and '.$job_title;exit(); 
        //$title = $this->getAllTitles($job_title);
        $location_distance = Applicant::with('cv_notes')->select(DB::raw("*, ((ACOS(SIN($lat * PI() / 180) * SIN(lat * PI() / 180) +
                COS($lat * PI() / 180) * COS(lat * PI() / 180) * COS(($lon - lng) * PI() / 180)) * 180 / PI()) * 60 * 1.1515)
                AS distance"))->having("distance", "<", $radius)->orderBy("distance")
            ->where(array("status" => "active", "is_in_nurse_home" => "no", "is_blocked" => "0", 'is_callback_enable' => 'no')); //->get();

		 if ($job_title_prop!=null){
            $job_title_cate=$job_title_prop;
            $location_distance = $location_distance->where("job_title_prof", $job_title_cate)->get();

        }else{
            $title = $this->getAllTitles($job_title);
           $location_distance = $location_distance->where("applicant_job_title", $title[0])->orWhere("applicant_job_title", $title[1])->orWhere("applicant_job_title", $title[2])->orWhere("applicant_job_title", $title[3])->orWhere("applicant_job_title", $title[4])->orWhere("applicant_job_title", $title[5])->orWhere("applicant_job_title", $title[6])->orWhere("applicant_job_title", $title[7])->orWhere("applicant_job_title", $title[8])->orWhere("applicant_job_title", $title[9])->orWhere("applicant_job_title", $title[10])->get();

        }
        //$location_distance = $location_distance->where("applicant_job_title", $title1)->get();
        return $location_distance;
    }

    public function get15kmJobsAvailableAjax($applicant_id, $radius = null)
    {
        $applicant = Applicant::with('cv_notes')->find($applicant_id);
        $applicant_job_title = $applicant->applicant_job_title;
        $applicant_postcode = $applicant->applicant_postcode;
		
        if($radius != null)
        {
            $radius = 10;
        }
        else
        {
            $radius = 15;

        }
	
        $near_by_jobs = '';

		if($applicant->lat == '0.000000' || $applicant->lng == '0.000000')
        {
            $postcode_para = urlencode($applicant_postcode);
            $postcode_api = config('app.postcode_api');
            $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$postcode_para}&key={$postcode_api}";
            $resp_json = file_get_contents($url);

			$resp = json_decode($resp_json, true);
			if ($resp['status'] == 'OK') {

				// get the important data
				$lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
				$longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";
				$near_by_jobs = $this->job_distance($lati, $longi, $radius, $applicant_job_title);

			} else {
				echo "<strong>ERROR: {$resp['status']}</strong>";
			}
        }
        else
        {
			$near_by_jobs = $this->job_distance($applicant->lat, $applicant->lng, $radius, $applicant_job_title);
        }
		if (empty($near_by_jobs))
		{
			$jobs = [];
		}
		else
		{
			$jobs = $this->check_not_interested_in_jobs($near_by_jobs, $applicant_id);
			
            foreach ($jobs as &$job) {
                $office_id = $job['head_office'];
                $unit_id = $job['head_office_unit'];
                $office = Office::select("office_name")->where(["id" => $office_id, "status" => "active"])->first();
                $office = $office->office_name;
                $unit = Unit::select("unit_name")->where(["id" => $unit_id, "status" => "active"])->first();
                $unit = $unit->unit_name;
                $job['office_name'] = $office;
                $job['unit_name'] = $unit;
                $job['cv_notes_count']=$job['cv_notes_count'];
            }
		}

        return datatables($jobs)
			->editColumn('job_title',function($job){
                $job_title_desc='';
                if($job['job_title_prof']!=null)
                {
                    $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $job['job_title_prof'])->first();
                    $job_title_desc = $job['job_title'].' ('.$job_prof_res->specialist_prof.')';
                }
                else
                {
                    
                    $job_title_desc = $job['job_title'];
                }
                return $job_title_desc;

            })
            ->addColumn('action', function ($job) use ($applicant) {
                $option = 'open';
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->sale_id == $job['id']) {
                        if ($value->status == 'active') {
                            $option = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $option = 'reject_job';
                            break;
                        } elseif ($value->status == 'paid') {
                            $option = 'paid';
                            break;
                        }
                    }
                }
                $content = "";
                $content .= '<div class="list-icons">';
                $content .= '<div class="dropdown">';
                $content .= '<a href="#" class=list-icons-item" data-toggle="dropdown">
                                <i class="icon-menu9"></i>
                            </a>';
                $content .= '<div class="dropdown-menu dropdown-menu-right">';
                if($option == 'open') {
                    $content .= '<a href="#" class="dropdown-item"
                                       data-controls-modal="#modal_form_horizontal'.$job['id'].'"
                                       data-backdrop="static"
                                       data-keyboard="false" data-toggle="modal"
                                       data-target="#modal_form_horizontal'.$job['id'].'"> NOT INTERESTED</a>';
                    $content .= '<a href="#" class="dropdown-item"
                                       data-controls-modal="#sent_cv'.$job['id'].'" data-backdrop="static"
                                       data-keyboard="false" data-toggle="modal"
                                       data-target="#sent_cv'.$job['id'].'">SEND CV</a>';
                    if ($applicant->is_in_nurse_home == "no") {
                        $content .= '<a href="#"
                                       class="dropdown-item"
                                       data-controls-modal="#no_nurse_home' . $applicant['id'] . '" data-backdrop="static"
                                       data-keyboard="false" data-toggle="modal"
                                       data-target="#no_nurse_home' . $applicant['id'] . '">NO NURSING HOME</a>';
                    }
                    if ($applicant->is_callback_enable == "no") {
                        $content .= '<a href="#" class="dropdown-item"
                                   data-controls-modal="#call_back' . $applicant['id'] . '" data-backdrop="static"
                                   data-keyboard="false" data-toggle="modal"
                                   data-target="#call_back' . $applicant['id'] . '">CALLBACK</a>';
                    }
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    if ($applicant->is_in_nurse_home == "no") {
                        // No Nursing Home Modal
                        $content .= '<div id="no_nurse_home' . $applicant['id'] . '" class="modal fade" tabindex="-1">';
                        $content .= '<div class="modal-dialog modal-lg">';
                        $content .= '<div class="modal-content">';
                        $content .= '<div class="modal-header">';
                        $content .= '<h5 class="modal-title">Add No Nursing Home Below:</h5>';
                        $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                        $content .= '</div>';
                        $sent_to_nurse_home_url = '/sent-to-nurse-home';
                        $sent_to_nurse_home_csrf = csrf_token();
                        $content .= '<form action="' . $sent_to_nurse_home_url . '" method="GET"
                                      class="form-horizontal">';
                        $content .= '<input type="hidden" name="_token" value="' . $sent_to_nurse_home_csrf . '">';
                        $content .= '<div class="modal-body">';
                        $content .= '<div class="form-group row">';
                        $content .= '<label class="col-form-label col-sm-3">Details</label>';
                        $content .= '<div class="col-sm-9">';
                        $content .= '<input type="hidden" name="applicant_hidden_id"
                             value="' . $applicant['id'] . '">';
                        $content .= '<input type="hidden" name="sale_hidden_id" value="' . $job['id'] . '">';
                        $content .= '<textarea name="details" class="form-control" cols="30" rows="4"
                            placeholder="TYPE HERE.." required></textarea>';
                        $content .= '</div>';
                        $content .= '</div>';
                        $content .= '</div>';
                        $content .= '<div class="modal-footer">';
                        $content .= '<button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>';
                        $content .= '<button type="submit" class="btn bg-teal legitRipple">Save</button>';
                        $content .= '</div>';
                        $content .= '</form>';
                        $content .= '</div>';
                        $content .= '</div>';
                        $content .= '</div>';
                        // /No Nursing Home Modal
                    }
                    if ($applicant->is_callback_enable == "no") {
                        // CallBack Modal
                        $content .= '<div id="call_back' . $applicant['id'] . '" class="modal fade"  tabindex="-1">';
                        $content .= '<div class="modal-dialog modal-lg">';
                        $content .= '<div class="modal-content">';
                        $content .= '<div class="modal-header">';
                        $content .= '<h5 class="modal-title">Add Callback Notes Below:</h5>';
                        $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                        $content .= '</div>';
                        $call_back_list_url = '/sent-applicant-to-call-back-list';
                        $call_back_list_csrf = csrf_token();
                        $content .= '<form action="' . $call_back_list_url . '" method="GET"
                                  class="form-horizontal">';
                        $content .= '<input type="hidden" name="_token" value="' . $call_back_list_csrf . '">';
                        $content .= '<div class="modal-body">';
                        $content .= '<div class="form-group row">';
                        $content .= '<label class="col-form-label col-sm-3">Details</label>';
                        $content .= '<div class="col-sm-9">';
                        $content .= '<input type="hidden" name="applicant_hidden_id"
                        value="' . $applicant['id'] . '">';
                        $content .= '<input type="hidden" name="sale_hidden_id" value="' . $job['id'] . '">';
                        $content .= '<textarea name="details" class="form-control" cols="30" rows="4"
                         placeholder="TYPE HERE.." required></textarea>';
                        $content .= '</div>';
                        $content .= '</div>';
                        $content .= '</div>';
                        $content .= '<div class="modal-footer">';
                        $content .= '<button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>';
                        $content .= '<button type="submit" class="btn bg-teal legitRipple">Save</button>';
                        $content .= '</div>';
                        $content .= '</form>';
                        $content .= '</div>';
                        $content .= '</div>';
                        $content .= '</div>';
                        // /CallBack Modal
                    }
                   // Send CV Modal
                    $sent_cv_count = Cv_note::where(['sale_id' => $job['id'], 'status' => 'active'])->count();
                    $content .= '<div id="sent_cv'.$job['id'].'" class="modal fade" tabindex="-1">';
                    $content .= '<div class="modal-dialog modal-lg">';
                    $content .= '<div class="modal-content">';
                    $content .= '<div class="modal-header">';
                    $content .= '<h5 class="modal-title">Add CV Notes Below:</h5>';
                    $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    $content .= '</div>';
                    $cv_url = '/applicant-cv-to-quality';
                    $cv_csrf = csrf_token();
                    $content .= '<form action="'.$cv_url.'/'.$applicant->id.'" method="GET" class="form-horizontal">';
                    $content .= '<input type="hidden" name="_token" value="' .$cv_csrf.'">';
                    $content .= '<div class="modal-body">';
                    $content  .='<div id="interested">'; // Added a container div for the fields to be hidden
                    $content .= '<input type="hidden" name="applicant_hidden_id" value="'.$applicant->id.'">';
                    $content .= '<input type="hidden" name="sale_hidden_id" value="'.$job['id'].'">';
                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">1.</strong> Current Employer Name</label>';
                    $content  .='<div class="col-sm-9">';
                    $content  .='<input type="text" name="current_employer_name" class="form-control" placeholder="Enter Employer Name">';
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">2.</strong> PostCode</label>';
                    $content  .='<div class="col-sm-3">';
                    $content  .='<input type="text" name="postcode" class="form-control" placeholder="Enter PostCode">';
                    $content  .='</div>';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">3.</strong> Current/Expected Salary</label>';
                    $content  .='<div class="col-sm-3">';
                    $content  .='<input type="text" name="expected_salary" class="form-control" placeholder="Enter Salary">';
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">4.</strong> Qualification</label>';
                    $content  .='<div class="col-sm-9">';
                    $content  .='<input type="text" name="qualification" class="form-control" placeholder="Enter Qualification">';
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">5.</strong> Transport Type</label>';
                    $content  .='<div class="col-sm-9 d-flex align-items-center">';
					$content  .='<div class="form-check form-check-inline">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="transport_type[]" id="walk" value="By Walk">';
                    $content  .='<label class="form-check-label" for="walk">By Walk</label>';
                    $content  .='</div>';
                    $content  .='<div class="form-check form-check-inline">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="transport_type[]" id="cycle" value="Cycle">';
                    $content  .='<label class="form-check-label" for="cycle">Cycle</label>';
                    $content  .='</div>';
                    $content  .='<div class="form-check form-check-inline ml-3">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="transport_type[]" id="car" value="Car">';
                    $content  .='<label class="form-check-label" for="car">Car</label>';
                    $content  .='</div>';
                    $content  .='<div class="form-check form-check-inline ml-3">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="transport_type[]" id="public_transport" value="Public Transport">';
                    $content  .='<label class="form-check-label" for="public_transport">Public Transport</label>';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">6.</strong> Shift Pattern</label>';
                    $content  .='<div class="col-sm-9 d-flex align-items-center">';
                    $content  .='<div class="form-check form-check-inline">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="shift_pattern[]" id="day" value="Day">';
                    $content  .='<label class="form-check-label" for="day">Day</label>';
                    $content  .='</div>';
					$content  .='<div class="form-check form-check-inline">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="shift_pattern[]" id="night" value="Night">';
                    $content  .='<label class="form-check-label" for="night">Night</label>';
                    $content  .='</div>';
                    $content  .='<div class="form-check form-check-inline ml-3">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="shift_pattern[]" id="full_time" value="Full Time">';
                    $content  .='<label class="form-check-label" for="full_time">Full Time</label>';
                    $content  .='</div>';
                    $content  .='<div class="form-check form-check-inline ml-3">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="shift_pattern[]" id="part_time" value="Part Time">';
                    $content  .='<label class="form-check-label" for="part_time">Part Time</label>';
                    $content  .='</div>';
                    $content  .='<div class="form-check form-check-inline ml-3">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="shift_pattern[]" id="twenty_four_hours" value="24 hours">';
                    $content  .='<label class="form-check-label" for="twenty_four_hours">24 Hours</label>';
                    $content  .='</div>';
					 $content  .='<div class="form-check form-check-inline">';
                    $content  .='<input class="form-check-input mt-0" type="checkbox" name="shift_pattern[]" id="day_night" value="Day/Night">';
                    $content  .='<label class="form-check-label" for="day_night">Day/Night</label>';
                    $content  .='</div>';
					
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">7.</strong> Nursing Home</label>';
                    $content  .='<div class="col-sm-3 d-flex align-items-center">';
                    $content  .='<div class="form-check mt-0">';
                    $content  .='<input type="checkbox" name="nursing_home" style="margin-top:-3px" id="nursing_home_checkbox" class="form-check-input" value="0">';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">8.</strong> Alternate Weekend</label>';
                    $content  .='<div class="col-sm-3 d-flex align-items-center">';
                    $content  .='<div class="form-check mt-0">';
                    $content  .='<input type="checkbox" name="alternate_weekend" style="margin-top:-3px" id="alternate_weekend_checkbox" class="form-check-input" value="0">';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">9.</strong> Interview Availability</label>';
                    $content  .='<div class="col-sm-3 d-flex align-items-center">';
                    $content  .='<div class="form-check mt-0">';
                    $content  .='<input type="text" class="form-control" name="interview_availability" id="interview_availability" class="form-check-input">';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">10.</strong> No Job</label>';
                    $content  .='<div class="col-sm-3 d-flex align-items-center">';
                    $content  .='<div class="form-check mt-0">';
                    $content  .='<input type="checkbox" name="no_job" id="no_job_checkbox" style="margin-top:-3px" class="form-check-input" value="0">';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='</div>';
					
					 $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3"><strong style="font-size:18px">11.</strong> Visa Status</label>';
                    $content  .='<div class="col-sm-3">';
                    $content  .='<div class="form-check form-check-inline">';
                    $content  .='<input type="radio" name="visa_status" id="british" class="form-check-input mt-0" value="British">';
                    $content  .='<label class="form-check-label" for="british">British</label>';
                    $content  .='</div><br>';
                    $content  .='<div class="form-check form-check-inline">';
                    $content  .='<input type="radio" name="visa_status" id="required_sponsorship" class="form-check-input mt-0" value="Required Sponsorship">';
                    $content  .='<label class="form-check-label" for="required_sponsorship">Required Sponsorship</label>';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='</div>';

                    $content  .='</div>'; // Close upper-fields div
                    $content  .='<div class="form-group row">';
                    $content  .='<div class="col-sm-1 d-flex justify-content-center align-items-center">';
                    $content  .='<input type="checkbox" name="hangup_call" id="hangup_call" class="form-check-input" value="0">';
                    $content  .='</div>';
                    $content  .='<div class="col-sm-11">';
                    $content  .='<label for="hangup_call" class="col-form-label" style="font-size:16px;">Call Hung up/Not Interested</label>';
                    $content  .='</div>';
                    $content  .='</div>';
                        
                    $content  .='<div class="form-group row">';
                    $content  .='<label class="col-form-label col-sm-3">Other Details <span class="text-danger">*</span></label>';
                    $content  .='<div class="col-sm-9">';
                    $content  .='<input type="hidden" name="module_key" value="'.$applicant->id.'">';
                    $content  .='<textarea name="details" id="note_details'. $applicant->id .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE .." required></textarea>';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='</div>';
                                    
                    $content  .='<div class="modal-footer">';
                    $content  .='<button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>';
                    $content  .='<button type="submit" data-note_key="'. $applicant->id .'" class="btn bg-teal legitRipple note_form_submit">Save</button>';
                    $content  .='</div>';
                    $content  .='</form>';
                    $content  .='</div>';
                    $content  .='</div>';
                    $content  .='</div>';
                    // /Sent CV Modal

                    // Add To Non Interest List Modal
                    $content .= '<div id="modal_form_horizontal'.$job['id'].'" class="modal fade" tabindex="-1">';
                    $content .= '<div class="modal-dialog modal-lg">';
                    $content .= '<div class="modal-content">';
                    $content .= '<div class="modal-header">';
                    $content .= '<h5 class="modal-title">Enter Reason of Not Interest Below:</h5>';
                    $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    $content .= '</div>';
                    $mark_url = '/mark-applicant';
                    $mark_csrf = csrf_token();
                    $content .= '<form action="'.$mark_url.'" method="POST"
                                      class="form-horizontal">';
                    $content .= '<input type="hidden" name="_token" value="' .$mark_csrf.'">';
                    $content .= '<div class="modal-body">';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Reason</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<input type="hidden" name="applicant_hidden_id"
                             value="'.$applicant->id.'">';
                    $content .= '<input type="hidden" name="job_hidden_id"
                             value="'.$job['id'].'">';
                    $content .= '<textarea name="reason" class="form-control" cols="30" rows="4"
                             placeholder="TYPE HERE.." required></textarea>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '<div class="modal-footer">';
                    $content .= '<button type="button" class="btn btn-link legitRipple"
                             data-dismiss="modal">Close</button>';
                    $content .= '<button type="submit" class="btn bg-teal legitRipple">Save</button>';
                    $content .= '</div>';
                    $content .= '</form>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    // /Add To Non Interest List Modal
                } else if($option == 'sent' || $option == 'reject_job' || $option == 'paid'){
                    $content .= '<a href="#" class="disabled dropdown-item"> LOCKED</a>';
                    $content .= '<a href="#" class="disabled dropdown-item"> LOCKED</a>';
                    $content .= '<a href="#" class="disabled dropdown-item"> LOCKED</a>';
                    $content .= '<a href="#" class="disabled dropdown-item"> LOCKED</a>';
                }
                return $content;
            })
            ->addColumn('head_office',function($job){
                return $job['office_name'];
            })
            ->addColumn('head_office_unit',function($job){
                return $job['unit_name'];
            })
            ->editColumn('updated_at', function($job){
                $updatedAt = new Carbon($job['updated_at']);
                return $updatedAt->timestamp;
            })
            ->addColumn('status', function ($job) use($applicant){
                $value_data = 'open';
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->sale_id == $job['id']) {
                        if ($value->status == 'active') {
                            $value_data = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $value_data = 'reject_job';
                            break;
                        } elseif ($value->status == 'paid') {
                            $value_data = 'paid';
                            break;
                        }
                    }
                }
                $status = '';
                $status .= '<h3>';
                $status .= '<span class="badge w-100 bg-teal-800">';
                $status .= strtoupper($value_data);
                $status .= '</span>';
                $status .= '</h3>';
                return $status;
            })
				->editColumn('cv_limit',function($job){                
                if($job['cv_notes_count']==null)
                {
                    $job['cv_notes_count']=0;
                }   
			return $job['cv_notes_count']==$job['send_cv_limit']?'<span class="badge w-100 badge-danger" style="font-size:90%">Limit Reached</span>':"<span class='badge w-100 badge-success' style='font-size:90%'>".((int)$job['send_cv_limit'] - (int)$job['cv_notes_count'])." Cv's limit remaining</span>";
			})
            ->rawColumns(['job_title','head_office','head_office_unit','status','cv_limit','action'])
            ->make(true);
    }

    public function get15kmAvailableJobs($id, $radius = null)
    {
        $applicant = Applicant::find($id);
        $is_applicant_in_quality = $applicant['is_cv_in_quality'];
        if ($applicant->paid_status == 'close') {
            return view('administrator.resource.15km_jobs_for_closed_applicant', compact('applicant', 'is_applicant_in_quality', 'radius'));
        }
        return view('administrator.resource.15km_jobs', compact('applicant', 'is_applicant_in_quality', 'radius'));
    }

    function job_distance($lat, $lon, $radius, $applicant_job_title)
    {
        $title = $this->getAllTitles($applicant_job_title);

        $location_distance = Sale::select(DB::raw("*, ((ACOS(SIN($lat * PI() / 180) * SIN(lat * PI() / 180) + 
                COS($lat * PI() / 180) * COS(lat * PI() / 180) * COS(($lon - lng) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) 
                AS distance"),DB::raw("(SELECT count(cv_notes.sale_id) from cv_notes
                WHERE cv_notes.sale_id=sales.id AND cv_notes.status='active' group by cv_notes.sale_id) as cv_notes_count"))->having("distance", "<", $radius)->orderBy("distance")->where("status", "active")->where("is_on_hold", "0");

        //        $location_distance = $location_distance->where("job_title", $title[0])->orWhere("job_title", $title[1])->orWhere("job_title", $title[2])->orWhere("job_title", $title[3])->orWhere("job_title", $title[4])->orWhere("job_title", $title[5])->orWhere("job_title", $title[6])->orWhere("job_title", $title[7])->get();
        $location_distance = $location_distance->where(function ($query) use ($title) {
            $query->orWhere("job_title", $title[0]);
            $query->orWhere("job_title", $title[1]);
            $query->orWhere("job_title", $title[2]);
            $query->orWhere("job_title", $title[3]);
            $query->orWhere("job_title", $title[4]);
            $query->orWhere("job_title", $title[5]);
            $query->orWhere("job_title", $title[6]);
            $query->orWhere("job_title", $title[7]);
            $query->orWhere("job_title", $title[8]);
            $query->orWhere("job_title", $title[9]);
            $query->orWhere("job_title", $title[10]);
        })->get();

        return $location_distance;
    }

    public function getMarkApplicant(Request $request)
    {
        date_default_timezone_set('Europe/London');
        $audit_data['applicant'] = $applicant_id = $request->input('applicant_hidden_id');
        $audit_data['action'] = "Not Interested";
        $audit_data['sale'] = $job_id = $request->input('job_hidden_id');
        $not_interested_reason_note = $request->input('reason');

        $interest = new Applicants_pivot_sales();
        $audit_data['added_date'] = $interest->interest_added_date = date("jS F Y");
        $audit_data['added_time'] = $interest->interest_added_time = date("h:i A");
        $audit_data['is_interested'] = "no";
        $interest->applicant_id = $applicant_id;
        $interest->sales_id = $job_id;
        $interest->save();
        $last_inserted_interest = $interest->id;
        if ($last_inserted_interest) {
            $interest_uid = md5($last_inserted_interest);
            DB::table('applicants_pivot_sales')->where('id', $last_inserted_interest)->update(['applicants_pivot_sales_uid' => $interest_uid]);
            $notes_for_range = new Notes_for_range_applicants();
            $notes_for_range->applicants_pivot_sales_id = $last_inserted_interest;
            $audit_data['reason'] = $notes_for_range->reason = $not_interested_reason_note;
            $notes_for_range->save();
            $notes_for_range_last_insert_id = $notes_for_range->id;
            if ($notes_for_range_last_insert_id) {
                $range_notes_uid = md5($notes_for_range_last_insert_id);
                Notes_for_range_applicants::where('id', $notes_for_range_last_insert_id)->update(['range_uid' => $range_notes_uid]);
            }
            //$pivot_object = Applicants_pivot_sales::where('id',$last_inserted_interest)->get();
            //            $return_response = $this->check_interest_mark_note($pivot_object);
            //            if($return_response){
            //                return redirect('direct-resource')->with('jobApplicantInterest', 'Job Interest Note Added');
            //            }
            //            else{
            //                return redirect('direct-resource')->with('jobApplicantInterestFail', 'Job Interest Note Cannot be Added');
            //            }
            /*** activity log
             * $action_observer = new ActionObserver();
             * $action_observer->action($audit_data, 'Resource');
             */

            return Redirect::back()->with('jobApplicantInterest', 'Job Interest Note Added');
        } else {
            return Redirect::back()->with('jobApplicantInterestError', 'WHOOPS!! Something went wrong');
        }

    }

    //    function check_interest_mark_note($interest_object){
    //        $data = '';
    //        foreach($interest_object as $object){
    //            $data = Applicant::where("id",$object->applicant_id)->update(['is_interested' => 'no']);
    //        }
    //        if($data)
    //        return true;
    //    }

    function check_not_interested_in_jobs($job_object, $applicant_id)
    {
        $pivot_result = array();
        $app_id = '';
        foreach ($job_object as $key => $value) {
            $job_id = $value->id;
            $pivot_result = Applicants_pivot_sales::where("applicant_id", $applicant_id)->where("sales_id", $job_id)->first();
            if (!empty($pivot_result)) {
                $job_object->forget($key);
            }

            /***
            $pivot_result[] = Applicants_pivot_sales::where("applicant_id", $applicant_id)->where("sales_id", $job_id)->first();
            foreach ($pivot_result as $res) {
                $app_id = $res['applicant_id'];
                $job_db_id = $res['sales_id'];
            }
            if (($applicant_id == $app_id) && ($job_id == $job_db_id)) {
                $job_object->forget($key);
            }
            */
        }
		
        return $job_object->toArray();
    }

    function check_not_interested_applicants($applicants_object, $job_id)
    {

        $pivot_result = array();
        $filter_applicant = array();
        $app_id = '';
        $job_db_id='';
        foreach ($applicants_object as $key => $value) {
            $applicant_id = $value->id;
            $pivot_result[] = Applicants_pivot_sales::where("applicant_id", $applicant_id)->where("sales_id", $job_id)->first();
            foreach ($pivot_result as $res) 
            { 
                if(isset($res['applicant_id']) && isset($res['applicant_id']))
                {
                    $app_id = $res['applicant_id'];
                    $job_db_id = $res['sales_id'];
                }
            }
            if (($applicant_id == $app_id) && ($job_id == $job_db_id)) {
                $applicants_object->forget($key);
            }
        }
        foreach ($applicants_object as $key => $filter_val) {
            if (($filter_val['is_in_nurse_home'] == 'yes') || ($filter_val['is_callback_enable'] == 'yes') || ($filter_val['is_blocked'] == '1')) {
                $applicants_object->forget($key);
            }
						 unset( $filter_val['distance']);

        }
        return $applicants_object->toArray();

    }

    function check_applicant_interest_for_different_job($check_applicant_availibility)
    {
        $pivot_result = array();
        $colors = array();
        foreach ($check_applicant_availibility as $availibility) {
            $applicant_id = $availibility['id'];
            $pivot_result[] = Applicants_pivot_sales::where("applicant_id", $applicant_id)->first();

            foreach ($pivot_result as $res) {
                $app_id = $res['applicant_id'];
                if ($applicant_id == $app_id) {
                    $colors[] = $applicant_id;
                }
            }

        }
        return $colors;
    }

    public function getNotInterestedNoteReason($non_interest_id)
    {
        $reason_note = array();
        $applicants_pivot_sales = Applicants_pivot_sales::select("id")->where('applicant_id', $non_interest_id)->first();

        $response = Notes_for_range_applicants::select("reason")->where("applicants_pivot_sales_id", $applicants_pivot_sales->id)->get();
        foreach ($response as $data) {
            $reason_note[] = $data->reason;
        }
        return view('administrator.resource.show', compact('reason_note'));
    }

    public function getLast7DaysApplicantAdded($id)
    {
        $interval = 7;
        return view('administrator.resource.last_7_days_applicant_added', compact('interval','id'));
    }

	//public function export_7_days_applicants()
    //{
       //$end_date = Carbon::now();
        //$edate = $end_date->format('Y-m-d') . " 23:59:59";
        //$start_date = $end_date->subDays(9);
        //$sdate = $start_date->format('Y-m-d') . " 00:00:00";
        //$job_category='nurse';
        //return Excel::download(new ResourcesExport($sdate,$edate,$job_category), 'applicants.csv');
                    

        
    //}
	
	public function export_7_days_applicants_date(Request $request)
    {
	    //$start_date = $request->input('applicants_date');
        //$sdate = Carbon::parse($start_date)->format('Y-m-d'). " 00:00:00:000";
        //$end_date = $request->input('applicants_date');
        //$edate = Carbon::parse($end_date)->format('Y-m-d'). " 23:59:59:999";
		  $start_date = $request->input('custom_start_date_value');
        $sdate = Carbon::parse($start_date)->format('Y-m-d'). " 00:00:00:000";
        $end_date = $request->input('custom_end_date_value');
        $edate = Carbon::parse($end_date)->format('Y-m-d'). " 23:59:59:999";
		
		
        $saleId=$request->input('hidden_job_value');

        //$job_category='nurse';
       
                    //echo '<pre>';print_r($not_sents);echo '</pre>';exit();
        $result1= Applicant::select(
                'id','applicant_phone', 'applicant_name','applicant_homePhone','applicant_job_title',
                'applicant_postcode','applicant_source','applicant_notes')->where(function($query){
            $query->doesnthave('CVNote');
        })->whereBetween('updated_at', [$sdate, $edate]);
        if($saleId == "45"){
           // $result1= $result1->where("job_category", '=',"nurse");
			$result1= $result1->where("job_category", "=","non-nurse")->whereNotIn('applicant_job_title', ['nonnurse specialist']);
        }elseif ($saleId == "44"){
			$result1 = $result1->where("job_category", '=',"nurse");
            //$result1= $result1->where("job_category", "=","non-nurse")->whereNotIn('applicant_job_title', ['nonnurse specialist']);
        }elseif ($saleId =="46"){
            $result1= $result1->where(["job_category" => "non-nurse", "applicant_job_title" => "nonnurse specialist" ]);
        }

        $not_sents=$result1->where("is_blocked", "=", "0")->where("temp_not_interested", "=", "0")->where('is_no_job',"=","0")->get();
		


		
		
		$result_rej = Applicant::with('cv_notes')
                    ->select('applicants.id','applicant_phone', 'applicant_name','applicant_homePhone','applicant_job_title',
                'applicant_postcode','applicant_source','applicant_notes')
                    ->leftJoin('applicants_pivot_sales', 'applicants.id', '=', 'applicants_pivot_sales.applicant_id')
                    ->where("applicants.status", "=", "active");
            //                    ->where("applicants.is_in_nurse_home", "=", "no")
            //                    ->where("applicants.job_category", "=", "nurse");
               if($saleId == "44"){
                   $result_rej= $result_rej->where("applicants.job_category", '=',"nurse");
               }elseif ($saleId == "45"){
                   $result_rej= $result_rej->where("applicants.job_category", "=","non-nurse")->whereNotIn('applicants.applicant_job_title', ['nonnurse specialist']);
               }elseif ($saleId =="46"){
                   $result_rej= $result_rej->where(["job_category" => "non-nurse", "applicants.applicant_job_title" => "nonnurse specialist" ]);
               }
                   $rejecteds =$result_rej->where("is_blocked", "=", "0")->where("applicants.temp_not_interested", "=", "0")->where("applicants.is_blocked", "=", "0")->where('applicants.is_no_job',"=","0")
					   ->whereBetween('applicants.updated_at', [$sdate, $edate])->get();
		
	
		             //echo '<pre>';print_r($rejecteds);echo '</pre>';exit();


        $not_sents->map(function($row){
        //$row->sub_stage = "Not Sent";
        unset($row->id);
            });

        $arr = array();
                $reslut = array();
                $totalIterations = 0;
        foreach ($rejecteds as $key => $filter_val) {
	 
            $applicants_in_crm = Applicant::join('crm_notes', 'crm_notes.applicant_id', '=', 'applicants.id')
            ->join('sales', 'sales.id', '=', 'crm_notes.sales_id')
            ->join('offices', 'offices.id', '=', 'head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->join('history', function($join) {
                $join->on('crm_notes.applicant_id', '=', 'history.applicant_id');
                $join->on('crm_notes.sales_id', '=', 'history.sale_id');
            })
            ->select("applicants.*", "applicants.id as app_id", "crm_notes.*", "crm_notes.id as crm_notes_id", "sales.*", "sales.id as sale_id", "sales.postcode as sale_postcode", "sales.job_title as sale_job_title", "sales.job_category as sales_job_category", "sales.status as sale_status", "history.history_added_date", "history.sub_stage","office_name", "unit_name","history.id as history_id","history.updated_at as history_updated","crm_notes.updated_at as crm_updated","crm_notes.moved_tab_to")
            ->where(array('applicants.id' => $filter_val['id'],"history.status"=>"active"))
            ->whereIn('crm_notes.id', function($query){
                $query->select(DB::raw('MAX(id) FROM crm_notes WHERE sales_id=sales.id and applicants.id=applicant_id'));
            })->latest('history.updated_at')->get();
	 
           $history_stages = config('constants.history_stages');
	   $quality_array=array("quality_cvs"=>"quality_cvs", "quality_cleared"=>"quality_cleared");
	   $history_stages=array_merge($history_stages, $quality_array);
			if(!empty($applicants_in_crm[0]))
				{
            if($history_stages[$applicants_in_crm[0]->sub_stage]=='Sent CV' || $history_stages[$applicants_in_crm[0]->sub_stage]=='Request' || $history_stages[$applicants_in_crm[0]->sub_stage]=='Confirmation' || $history_stages[$applicants_in_crm[0]->sub_stage]=='Rebook' || $history_stages[$applicants_in_crm[0]->sub_stage]=='Attended to Pre-Start Date' || 
              $history_stages[$applicants_in_crm[0]->sub_stage]=='Attended to Pre-Start Date' || 
			  $history_stages[$applicants_in_crm[0]->sub_stage]=='Start Date' || 
              $history_stages[$applicants_in_crm[0]->sub_stage]=='Invoice' ||
              $history_stages[$applicants_in_crm[0]->sub_stage]=='Paid' || $history_stages[$applicants_in_crm[0]->sub_stage]=='quality_cvs'
			  || $history_stages[$applicants_in_crm[0]->sub_stage]=='quality_cleared')
                {
				if($history_stages[$applicants_in_crm[0]->sub_stage]=='quality_cvs')
				{
					$crm_reject_stages = ["dispute","interview_not_attended","declined","request_reject","cv_sent_reject",
											"start_date_hold", "start_date_hold_save"];
					if(in_array($applicants_in_crm[0]->moved_tab_to, $crm_reject_stages))
						{
						$res = array_add($rejecteds[$key], 'notes', $applicants_in_crm[0]->moved_tab_to);
  						$arr[]=$res;
						}
				}
					 $rejecteds->forget($key);
            }
				else
				{
  						$res = array_add($rejecteds[$key], 'notes', $applicants_in_crm[0]->moved_tab_to);
  						$arr[]=$res;
				}
         }
                        
        unset( $filter_val['id']); 
        }
		
		
        $not_sent[] = $not_sents->toArray();
        $admin_author_collection  = array_merge($arr,$not_sent);
            
        return Excel::download(new Applicants_nureses_7_days_export($admin_author_collection), 'applicants.csv');
		        //return Excel::download(new Applicants_nureses_7_days_export($sdate,$edate,$saleId), 'applicants.csv');

    }

    public function get7DaysApplicants($id)
    {
        $end_date = Carbon::now();
        $edate = $end_date->format('Y-m-d') . " 23:59:59";
        $start_date = $end_date->subDays(16);
        $sdate = $start_date->format('Y-m-d') . " 00:00:00";

        $result1 = Applicant::with(['cv_notes' => function($query) {
                        $query->select('status', 'applicant_id', 'sale_id', 'user_id')
                            ->with(['user:id,name'])->latest('cv_notes.created_at'); // Eager load the 'user' relationship and only select 'id' and 'name'
                    }])
                    ->select('applicants.id', 'applicants.updated_at', 'applicants.is_no_job', 'applicants.applicant_added_time', 'applicants.applicant_name', 'applicants.applicant_job_title','applicants.job_title_prof', 'applicants.job_category', 'applicants.applicant_postcode', 'applicants.applicant_phone','applicants.applicant_homePhone','applicants.applicant_source','applicants.applicant_email','applicants.applicant_notes','applicants.paid_status','applicants.applicant_cv','applicants.updated_cv')
			->leftJoin('applicants_pivot_sales', 'applicants.id', '=', 'applicants_pivot_sales.applicant_id')
			->where("applicants.status", "=", "active");
            //                    ->where("applicants.is_in_nurse_home", "=", "no")
			if ($id == "44"){
				$result1= $result1->where("applicants.job_category", '=',"nurse");
			}elseif ($id == "45"){
				$result1= $result1->where("applicants.job_category", "=","non-nurse")->whereNotIn('applicants.applicant_job_title', ['nonnurse specialist']);
			}elseif ($id =="46"){
				$result1= $result1->where(["applicants.job_category" => "non-nurse", "applicants.applicant_job_title" => "nonnurse specialist" ]);

			}elseif ($id =="47"){
				$result1= $result1->where(["applicants.job_category" => "chef"]);

			}
		
			 $result = $result1->where("applicants.is_blocked", "=", "0")
                        ->whereBetween('applicants.updated_at', [$sdate, $edate])
                        ->where("applicants.temp_not_interested", "=", "0")
                        ->where('applicants_pivot_sales.applicant_id', '=', NULL)
                        ->orderBy('updated_at','DESC');

        return datatables()->of($result)
            //            ->filter(function ($query) {
            //                if (request()->has('created_at')) {
            //                    $date = new DateTime(request('created_at'));
            //                    $date = date_format($date, 'Y-m-d');
            //                    $query->whereDate('applicants.created_at', $date);
            //                }
            //            })
			->addColumn("agent_name", function($applicant) {
                // Since we're fetching only the latest cv_note, this should be straightforward
                if ($applicant->cv_notes->isNotEmpty()) {
                    return $applicant->cv_notes->first()->user->name ?? null;
                }
                return '-';
            })
            ->addColumn('applicant_postcode', function ($applicant) {
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                /*** old logic before open applicant cv
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $status_value = 'sent'; // alert-success
                        break;
                    } elseif ($value->status == 'disable') {
                        $status_value = 'reject'; // alert-danger
                    } elseif ($value->status == 'paid') {
                        $status_value = 'paid';
                        break;
                    }
                }
                */
               if ($status_value == 'open' || $status_value == 'reject') {
                    $postcode .= '<a href="/available-jobs/'.$applicant->id.'">';
                    $postcode .= strtoupper($applicant->applicant_postcode);
                    $postcode .= '</a>';
                } else {
                    $postcode .= strtoupper($applicant->applicant_postcode);
                }
               	return $postcode;
            })
            ->addColumn("updated_at",function($applicant){
                $updated_at = new DateTime($applicant->updated_at);
                $date = date_format($updated_at,'d F Y');
                    return $date;
                })
			->addColumn('applicant_notes', function($applicant){

                    $app_new_note = ModuleNote::where(['module_noteable_id' =>$applicant->id, 'module_noteable_type' =>'Horsefly\Applicant'])
                    ->select('module_notes.details')
                    ->orderBy('module_notes.id', 'DESC')
                    ->first();
                    $app_notes_final='';
                    if($app_new_note){
                        $app_notes_final = $app_new_note->details;

                    }
                    else{
                        $app_notes_final = $applicant->applicant_notes;
                    }
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                   
                if($applicant->is_blocked == 0 && $status_value == 'open' || $status_value == 'reject')
                {
                    
                $content = '';
                // if ($status_value == 'open' || $status_value == 'reject'){

                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                 data-controls-modal="#clear_cv'.$applicant->id.'"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#clear_cv' . $applicant->id . '">"'.$app_notes_final.'"</a>';
                $content .= '<div id="clear_cv' . $applicant->id . '" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h5 class="modal-title">Notes</h5>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<form action="' . route('block_or_casual_notes') . '" method="POST" id="app_notes_form' . $applicant->id . '" class="form-horizontal">';
                $content .= csrf_field();
                $content .= '<div class="modal-body">';
                $content .='<div id="app_notes_alert' . $applicant->id . '"></div>';
                $content .= '<div id="sent_cv_alert' . $applicant->id . '"></div>';
                $content .= '<div class="form-group row">';
                $content .= '<label class="col-form-label col-sm-3">Details</label>';
                $content .= '<div class="col-sm-9">';
                $content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant->id . '">';
                $content .= '<input type="hidden" name="applicant_page' . $applicant->id . '" value="7_days_applicants">';
                $content .= '<textarea name="details" id="sent_cv_details' . $applicant->id .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
                $content .= '</div>';
                $content .= '</div>';
				$content .= '<div class="form-group row">';
				$content .= '<label class="col-form-label col-sm-3">Choose type:</label>';
				$content .= '<div class="col-sm-9">';
				$content .= '<select name="reject_reason" class="form-control crm_select_reason" id="reason' . $applicant->id .'">';
				$content .= '<option value="0" >Select Reason</option>';
				$content .= '<option value="1">Casual Notes</option>';
				$content .= '<option value="2">Block Applicant Notes</option>';
					$content .= '<option value="3">Temporary Not Interested Applicants Notes</option>';
					$content .= '</select>';
					$content .= '</div>';
					$content .= '</div>';

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                   
                    $content .= '<button type="button" class="btn bg-dark legitRipple sent_cv_submit" data-dismiss="modal">Close</button>';
                    
                    $content .= '<button type="submit" data-note_key="' . $applicant->id . '" value="cv_sent_save" class="btn bg-teal legitRipple sent_cv_submit app_notes_form_submit">Save</button>';

                $content .= '</div>';
                $content .= '</form>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
            // } else {
                // $content .= $applicant->applicant_notes;
                // }

                    //return $app_notes_final;
                   return $content;
            }else
            {
              return $app_notes_final;
            }
				return $content;
                })
            ->addColumn('history', function ($applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$applicant->id.'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$applicant->applicant_name.'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;

            })
            ->addColumn('status', function ($applicant) {
                $status_value = 'open';
                $color_class = 'bg-teal-800';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                    $color_class = 'bg-slate-700';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                /*** logic before open-applicant-cv-feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $status_value = 'sent';
                        break;
                    } elseif ($value->status == 'disable') {
                        $status_value = 'reject';
                    } elseif ($value->status == 'paid') {
                        $status_value = 'paid';
                        $color_class = 'bg-slate-700';
                        break;
                    }
                }
                */
                $status = '';
                $status .= '<h3>';
                $status .= '<span class="badge w-100 '.$color_class.'">';
                $status .= strtoupper($status_value);
                $status .= '</span>';
                $status .= '</h3>';
                return $status;
            })
			->addColumn('download', function ($applicant) {
                $filePath = $applicant->applicant_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->applicant_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->applicant_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadApplicantCv', $applicant->id);

                $download = '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';
                return $download;
            })
            ->addColumn('updated_cv', function ($applicant) {
                $filePath = $applicant->updated_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->updated_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->updated_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadUpdatedApplicantCv', $applicant->id);
                return
                    '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';

            })
			->editColumn('applicant_job_title', function ($applicant) {
            if($applicant->applicant_job_title == 'nurse specialist' || $applicant->applicant_job_title == 'nonnurse specialist')
            {
                $selected_prof_data = Specialist_job_titles::select("specialist_prof")->where("id", $applicant->job_title_prof)->first();
                if($selected_prof_data)
                {
                $spec_job_title = ($applicant->job_title_prof!='')?$applicant->applicant_job_title.' ('.$selected_prof_data->specialist_prof.')':$applicant->applicant_job_title;
               return strtoupper($spec_job_title);

                }
                else
                {
                   return strtoupper($applicant->applicant_job_title);
                }
            }
            else
            {
                return strtoupper($applicant->applicant_job_title);
            }

     })
			->addColumn('checkbox', function ($applicant) {
                 return '<input type="checkbox" name="applicant_checkbox[]" class="applicant_checkbox" value="' . $applicant->id . '"/>';
             })
			->addColumn('upload', function ($applicant) {
            return
            '<a href="#"
            data-controls-modal="#import_applicant_cv" class="import_cv"
            data-backdrop="static"
            data-keyboard="false" data-toggle="modal" data-id="'.$applicant->id.'"
            data-target="#import_applicant_cv">
             <i class="fas fa-file-upload text-teal-400"></i>
             &nbsp;</a>';
        }) 
			->setRowClass(function ($applicant) {
                $row_class = '';
                if ($applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
				} elseif ($applicant->is_no_job == '1') {
                    $row_class = 'class_noJob';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success';
                            break;
                        } elseif ($value->status == 'disable') {
                            $row_class = 'class_danger';
                        }
                    }
                }
                /*** logic before open-applicant-cv-feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $row_class = 'class_success'; // status: sent
                        break;
                    } elseif ($value->status == 'disable') {
                        $row_class = 'class_danger'; // status: reject
                    } elseif ($value->status == 'paid') {
                        $row_class = 'class_dark';
                        break;
                    }
                }
                */
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','updated_at','download','updated_cv','upload','applicant_notes','status','checkbox','applicant_postcode', 'history'])
            ->make(true);
    }
	
    public function getlast7DaysAppNotInterested($id)
    {
        $end_date = Carbon::now();
        $edate = $end_date->format('Y-m-d') . " 23:59:59";
        $start_date = $end_date->subDays(16);
        $sdate = $start_date->format('Y-m-d') . " 00:00:00";

        $result1 = Applicant::with(['cv_notes' => function($query) {
                        $query->select('status', 'applicant_id', 'sale_id', 'user_id')
                            ->with(['user:id,name'])->latest('cv_notes.created_at'); // Eager load the 'user' relationship and only select 'id' and 'name'
                    }])
        ->leftJoin('applicants_pivot_sales', 'applicants_pivot_sales.applicant_id', '=', 'applicants.id')
        ->leftJoin('sales', 'sales.id', '=', 'applicants_pivot_sales.sales_id')
        ->leftJoin('notes_for_range_applicants', 'applicants_pivot_sales.id', '=', 'notes_for_range_applicants.applicants_pivot_sales_id')
        ->leftJoin('offices', 'offices.id', '=', 'sales.head_office')
        ->leftJoin('units', 'units.id', '=', 'sales.head_office_unit')
        ->select(
            'applicants.id',
            'applicants.updated_at',
            'applicants.temp_not_interested',
            'applicants.applicant_added_time',
            'applicants.is_no_job',
            'applicants.applicant_name',
            'applicants.applicant_job_title',
            'applicants.job_title_prof',
            'applicants.job_category',
            'applicants.applicant_postcode',
            'applicants.applicant_phone',
            'applicants.applicant_homePhone',
            'applicants.applicant_source',
            'applicants.applicant_email',
            'applicants.applicant_notes',
            'applicants.paid_status',
            'applicants.applicant_cv',
            'applicants.updated_cv',
            'applicants_pivot_sales.sales_id as pivot_sale_id',
            'applicants_pivot_sales.id as pivot_id'
        )
        ->where("applicants.temp_not_interested", "1");

        if ($id == "44") {
            $result1 = $result1->where("applicants.job_category", '=', "nurse");
        } elseif ($id == "45") {
            $result1 = $result1->where("applicants.job_category", "=", "non-nurse")
                ->whereNotIn('applicants.applicant_job_title', ['nonnurse specialist']);
        } elseif ($id == "46") {
            $result1 = $result1->where([
                "applicants.job_category" => "non-nurse",
                "applicant_job_title" => "nonnurse specialist"
            ]);
        } elseif ($id == "47") {
            $result1 = $result1->where("applicants.job_category", "chef");
        }

        $result = $result1
            //->where("applicants.is_blocked", "0")
            ->where("applicants.is_no_job", "=", "0")
            ->where("applicants.status", "active")
            ->whereBetween('applicants.updated_at', ['2024-08-06', $edate])
            ->orderBy('applicants.updated_at', 'DESC')
            ->get();


        return datatables()->of($result)
            ->addColumn('applicant_postcode', function ($applicant) {
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
             //   if ($status_value == 'open' || $status_value == 'reject') {
              //      $postcode .= '<a href="/available-jobs/'.$applicant->id.'">';
              //      $postcode .= strtoupper($applicant->applicant_postcode);
             //       $postcode .= '</a>';
              //  } else {
             //       $postcode .= strtoupper($applicant->applicant_postcode);
           //     }
                return strtoupper($applicant->applicant_postcode);
            })
			->addColumn("agent_name", function($applicant) {
                // Since we're fetching only the latest cv_note, this should be straightforward
                if ($applicant->cv_notes->isNotEmpty()) {
                    return $applicant->cv_notes->first()->user->name ?? null;
                }
                return '-';
            })
            ->addColumn("updated_at",function($applicant){
                $updated_at = new DateTime($applicant->updated_at);
                $date = date_format($updated_at,'d F Y');
                    return $date;
            })
			->addColumn('applicant_notes', function($applicant){
                $app_new_note = ModuleNote::where(['module_noteable_id' =>$applicant->id, 'module_noteable_type' =>'Horsefly\Applicant'])
                ->select('module_notes.details')
                ->orderBy('module_notes.id', 'DESC')
                ->first();
                $app_notes_final='';
                if($app_new_note){
                    $app_notes_final = $app_new_note->details;

                }
                else{
                    $app_notes_final = $applicant->applicant_notes;
                }
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                   
                if($applicant->is_blocked == 0 && $status_value == 'open' || $status_value == 'reject')
                {
                    
                    $content = '';
                    // if ($status_value == 'open' || $status_value == 'reject'){

                    $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                    data-controls-modal="#clear_cv'.$applicant->id.'"
                                    data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                    data-target="#clear_cv' . $applicant->id . '">"'.$app_notes_final.'"</a>';
                    $content .= '<div id="clear_cv' . $applicant->id . '" class="modal fade" tabindex="-1">';
                    $content .= '<div class="modal-dialog modal-lg">';
                    $content .= '<div class="modal-content">';
                    $content .= '<div class="modal-header">';
                    $content .= '<h5 class="modal-title">Notes</h5>';
                    $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    $content .= '</div>';
                    $content .= '<form action="' . route('block_or_casual_notes') . '" method="POST" id="app_notes_form' . $applicant->id . '" class="form-horizontal">';
                    $content .= csrf_field();
                    $content .= '<div class="modal-body">';
                    $content .='<div id="app_notes_alert' . $applicant->id . '"></div>';
                    $content .= '<div id="sent_cv_alert' . $applicant->id . '"></div>';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Details</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant->id . '">';
                    $content .= '<input type="hidden" name="applicant_page' . $applicant->id . '" value="7_days_applicants">';
                    $content .= '<textarea name="details" id="sent_cv_details' . $applicant->id .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Choose type:</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<select name="reject_reason" class="form-control crm_select_reason" id="reason' . $applicant->id .'">';
                    $content .= '<option value="0" >Select Reason</option>';
                    $content .= '<option value="1">Casual Notes</option>';
                    $content .= '<option value="2">Block Applicant Notes</option>';
                    $content .= '<option value="3">Temporary Not Interested Applicants Notes</option>';
                    $content .= '</select>';
                    $content .= '</div>';
                    $content .= '</div>';

                    $content .= '</div>';
                    $content .= '<div class="modal-footer">';
                    
                    $content .= '<button type="button" class="btn bg-dark legitRipple sent_cv_submit" data-dismiss="modal">Close</button>';
                    
                    $content .= '<button type="submit" data-note_key="' . $applicant->id . '" value="cv_sent_save" class="btn bg-teal legitRipple sent_cv_submit app_notes_form_submit">Save</button>';

                    $content .= '</div>';
                    $content .= '</form>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    // } else {
                    // $content .= $applicant->applicant_notes;
                    // }

                        //return $app_notes_final;
                    return $content;
                }else
                {
                return $app_notes_final;
                }
				return $content;
            })
            ->addColumn('history', function ($applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$applicant->id.'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$applicant->applicant_name.'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;

            })
            ->addColumn('status', function ($applicant) {
                $status_value = 'open';
                $color_class = 'bg-teal-800';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                    $color_class = 'bg-slate-700';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                $status = '';
                $status .= '<h3>';
                $status .= '<span class="badge w-100 '.$color_class.'">';
                $status .= strtoupper($status_value);
                $status .= '</span>';
                $status .= '</h3>';
                return $status;
            })
			->addColumn('download', function ($applicant) {
                $filePath = $applicant->applicant_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->applicant_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->applicant_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadApplicantCv', $applicant->id);

                $download = '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';
                return $download;
            })
            ->addColumn('updated_cv', function ($applicant) {
                $filePath = $applicant->updated_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->updated_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->updated_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadUpdatedApplicantCv', $applicant->id);
                return
                    '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';

            })
			->editColumn('applicant_job_title', function ($applicant) {
                    if($applicant->applicant_job_title == 'nurse specialist' || $applicant->applicant_job_title == 'nonnurse specialist')
                    {
                        $selected_prof_data = Specialist_job_titles::select("specialist_prof")->where("id", $applicant->job_title_prof)->first();
                        if($selected_prof_data)
                        {
                        $spec_job_title = ($applicant->job_title_prof!='')?$applicant->applicant_job_title.' ('.$selected_prof_data->specialist_prof.')':$applicant->applicant_job_title;
                        return strtoupper($spec_job_title);

                        }
                        else
                        {
                            return strtoupper($applicant->applicant_job_title);
                        }
                    }
                    else
                    {
                        return strtoupper($applicant->applicant_job_title);
                    }

            })
			->addColumn('upload', function ($applicant) {
                return
                '<a href="#"
                data-controls-modal="#import_applicant_cv" class="import_cv"
                data-backdrop="static"
                data-keyboard="false" data-toggle="modal" data-id="'.$applicant->id.'"
                data-target="#import_applicant_cv">
                <i class="fas fa-file-upload text-teal-400"></i>
                &nbsp;</a>';
            }) 
            ->addColumn('checkbox', function ($applicant) {
                return '<input type="checkbox" name="applicant_checkbox[]" class="applicant_checkbox" value="' . $applicant->id . '"/>';
            })
			->setRowClass(function ($applicant) {
                $row_class = '';
                if ($applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
                } elseif ($applicant->is_no_job == '1') {
                    $row_class = 'class_noJob';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success';
                            break;
                        } elseif ($value->status == 'disable') {
                            $row_class = 'class_danger';
                        }
                    }
                }
                /*** logic before open-applicant-cv-feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $row_class = 'class_success'; // status: sent
                        break;
                    } elseif ($value->status == 'disable') {
                        $row_class = 'class_danger'; // status: reject
                    } elseif ($value->status == 'paid') {
                        $row_class = 'class_dark';
                        break;
                    }
                }
                */
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','updated_at','download','updated_cv','upload','applicant_notes','status','applicant_postcode','checkbox', 'history'])
            ->make(true);
    }
	
    public function getlast7DaysAppBlocked($id)
    {
        $end_date = Carbon::now();
        $edate = $end_date->format('Y-m-d') . " 23:59:59";
        $start_date = $end_date->subDays(16);
        $sdate = $start_date->format('Y-m-d') . " 00:00:00";

        $result1 = Applicant::with(['cv_notes' => function($query) {
                        $query->select('status', 'applicant_id', 'sale_id', 'user_id')
                            ->with(['user:id,name'])->latest(); // Eager load the 'user' relationship and only select 'id' and 'name'
                    }])
        ->leftJoin('applicants_pivot_sales', 'applicants_pivot_sales.applicant_id', '=', 'applicants.id')
        ->leftJoin('sales', 'sales.id', '=', 'applicants_pivot_sales.sales_id')
        ->leftJoin('notes_for_range_applicants', 'applicants_pivot_sales.id', '=', 'notes_for_range_applicants.applicants_pivot_sales_id')
        ->leftJoin('offices', 'offices.id', '=', 'sales.head_office')
        ->leftJoin('units', 'units.id', '=', 'sales.head_office_unit')
        ->select(
            'applicants.id',
            'applicants.updated_at',
            'applicants.temp_not_interested',
            'applicants.applicant_added_time',
            'applicants.is_no_job',
            'applicants.applicant_name',
            'applicants.applicant_job_title',
            'applicants.job_title_prof',
            'applicants.job_category',
            'applicants.applicant_postcode',
            'applicants.applicant_phone',
            'applicants.applicant_homePhone',
            'applicants.applicant_source',
            'applicants.applicant_email',
            'applicants.applicant_notes',
            'applicants.paid_status',
            'applicants.applicant_cv',
            'applicants.updated_cv',
            'applicants_pivot_sales.sales_id as pivot_sale_id',
            'applicants_pivot_sales.id as pivot_id'
        )
        ->where("applicants.temp_not_interested", "0");
    
        if ($id == "44") {
            $result1 = $result1->where("applicants.job_category", '=', "nurse");
        } elseif ($id == "45") {
            $result1 = $result1->where("applicants.job_category", "=", "non-nurse")
                ->whereNotIn('applicants.applicant_job_title', ['nonnurse specialist']);
        } elseif ($id == "46") {
            $result1 = $result1->where([
                "applicants.job_category" => "non-nurse",
                "applicant_job_title" => "nonnurse specialist"
            ]);
        } elseif ($id == "47") {
            $result1 = $result1->where("applicants.job_category", "chef");
        }
        
        $result = $result1->where("applicants.is_blocked", "1")
            ->where("applicants.status", "active")
			->where("applicants.is_no_job", "=", "0")
            ->whereBetween('applicants.updated_at', ['2024-08-06', $edate])
            ->orderBy('applicants.updated_at', 'DESC')
            ->get();

        return datatables()->of($result)
            ->addColumn('applicant_postcode', function ($applicant) {
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
               // if ($status_value == 'open' || $status_value == 'reject') {
                 //   $postcode .= '<a href="/available-jobs/'.$applicant->id.'">';
                //    $postcode .= strtoupper($applicant->applicant_postcode);
                //    $postcode .= '</a>';
              //  } else {
               //     $postcode .= strtoupper($applicant->applicant_postcode);
             //   }
                return strtoupper($applicant->applicant_postcode);
            })
			->addColumn("agent_name", function($applicant) {
                // Since we're fetching only the latest cv_note, this should be straightforward
                if ($applicant->cv_notes->isNotEmpty()) {
                    return $applicant->cv_notes->first()->user->name ?? null;
                }
                return '-';
            })
            ->addColumn("updated_at",function($applicant){
                $updated_at = new DateTime($applicant->updated_at);
                $date = date_format($updated_at,'d F Y');
                    return $date;
            })
			->addColumn('applicant_notes', function($applicant){
                $app_new_note = ModuleNote::where(['module_noteable_id' =>$applicant->id, 'module_noteable_type' =>'Horsefly\Applicant'])
                ->select('module_notes.details')
                ->orderBy('module_notes.id', 'DESC')
                ->first();
                $app_notes_final='';
                if($app_new_note){
                    $app_notes_final = $app_new_note->details;

                }
                else{
                    $app_notes_final = $applicant->applicant_notes;
                }
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                   
                if($applicant->is_blocked == 0 && $status_value == 'open' || $status_value == 'reject')
                {
                    
                    $content = '';
                    // if ($status_value == 'open' || $status_value == 'reject'){

                    $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                    data-controls-modal="#clear_cv'.$applicant->id.'"
                                    data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                    data-target="#clear_cv' . $applicant->id . '">"'.$app_notes_final.'"</a>';
                    $content .= '<div id="clear_cv' . $applicant->id . '" class="modal fade" tabindex="-1">';
                    $content .= '<div class="modal-dialog modal-lg">';
                    $content .= '<div class="modal-content">';
                    $content .= '<div class="modal-header">';
                    $content .= '<h5 class="modal-title">Notes</h5>';
                    $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    $content .= '</div>';
                    $content .= '<form action="' . route('block_or_casual_notes') . '" method="POST" id="app_notes_form' . $applicant->id . '" class="form-horizontal">';
                    $content .= csrf_field();
                    $content .= '<div class="modal-body">';
                    $content .='<div id="app_notes_alert' . $applicant->id . '"></div>';
                    $content .= '<div id="sent_cv_alert' . $applicant->id . '"></div>';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Details</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant->id . '">';
                    $content .= '<input type="hidden" name="applicant_page' . $applicant->id . '" value="7_days_applicants">';
                    $content .= '<textarea name="details" id="sent_cv_details' . $applicant->id .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Choose type:</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<select name="reject_reason" class="form-control crm_select_reason" id="reason' . $applicant->id .'">';
                    $content .= '<option value="0" >Select Reason</option>';
                    $content .= '<option value="1">Casual Notes</option>';
                    $content .= '<option value="2">Block Applicant Notes</option>';
                    $content .= '<option value="3">Temporary Not Interested Applicants Notes</option>';
                    $content .= '</select>';
                    $content .= '</div>';
                    $content .= '</div>';

                    $content .= '</div>';
                    $content .= '<div class="modal-footer">';
                    
                    $content .= '<button type="button" class="btn bg-dark legitRipple sent_cv_submit" data-dismiss="modal">Close</button>';
                    
                    $content .= '<button type="submit" data-note_key="' . $applicant->id . '" value="cv_sent_save" class="btn bg-teal legitRipple sent_cv_submit app_notes_form_submit">Save</button>';

                    $content .= '</div>';
                    $content .= '</form>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                    // } else {
                    // $content .= $applicant->applicant_notes;
                    // }

                        //return $app_notes_final;
                    return $content;
                }else
                {
                return $app_notes_final;
                }
				return $content;
            })
            ->addColumn('history', function ($applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$applicant->id.'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$applicant->applicant_name.'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;

            })
            ->addColumn('status', function ($applicant) {
                $status_value = 'open';
                $color_class = 'bg-teal-800';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                    $color_class = 'bg-slate-700';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                /*** logic before open-applicant-cv-feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $status_value = 'sent';
                        break;
                    } elseif ($value->status == 'disable') {
                        $status_value = 'reject';
                    } elseif ($value->status == 'paid') {
                        $status_value = 'paid';
                        $color_class = 'bg-slate-700';
                        break;
                    }
                }
                */
                $status = '';
                $status .= '<h3>';
                $status .= '<span class="badge w-100 '.$color_class.'">';
                $status .= strtoupper($status_value);
                $status .= '</span>';
                $status .= '</h3>';
                return $status;
            })
			->addColumn('download', function ($applicant) {
                $filePath = $applicant->applicant_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->applicant_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->applicant_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadApplicantCv', $applicant->id);

                $download = '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';
                return $download;
            })
            ->addColumn('updated_cv', function ($applicant) {
                $filePath = $applicant->updated_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->updated_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->updated_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadUpdatedApplicantCv', $applicant->id);
                return
                    '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';

            })
			->editColumn('applicant_job_title', function ($applicant) {
                    if($applicant->applicant_job_title == 'nurse specialist' || $applicant->applicant_job_title == 'nonnurse specialist')
                    {
                        $selected_prof_data = Specialist_job_titles::select("specialist_prof")->where("id", $applicant->job_title_prof)->first();
                        if($selected_prof_data)
                        {
                        $spec_job_title = ($applicant->job_title_prof!='')?$applicant->applicant_job_title.' ('.$selected_prof_data->specialist_prof.')':$applicant->applicant_job_title;
                        return strtoupper($spec_job_title);

                        }
                        else
                        {
                            return strtoupper($applicant->applicant_job_title);
                        }
                    }
                    else
                    {
                        return strtoupper($applicant->applicant_job_title);
                    }

            })
			->addColumn('upload', function ($applicant) {
                return
                '<a href="#"
                data-controls-modal="#import_applicant_cv" class="import_cv"
                data-backdrop="static"
                data-keyboard="false" data-toggle="modal" data-id="'.$applicant->id.'"
                data-target="#import_applicant_cv">
                <i class="fas fa-file-upload text-teal-400"></i>
                &nbsp;</a>';
            }) 
            ->addColumn('checkbox', function ($applicant) {
                return '<input type="checkbox" name="applicant_checkbox[]" class="applicant_checkbox" value="' . $applicant->id . '"/>';
            })
			->setRowClass(function ($applicant) {
                $row_class = '';
                if ($applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
                } elseif ($applicant->is_no_job == '1') {
                    $row_class = 'class_noJob';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success';
                            break;
                        } elseif ($value->status == 'disable') {
                            $row_class = 'class_danger';
                        }
                    }
                }
                /*** logic before open-applicant-cv-feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $row_class = 'class_success'; // status: sent
                        break;
                    } elseif ($value->status == 'disable') {
                        $row_class = 'class_danger'; // status: reject
                    } elseif ($value->status == 'paid') {
                        $row_class = 'class_dark';
                        break;
                    }
                }
                */
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','updated_at','download','updated_cv','upload','applicant_notes','status','applicant_postcode','checkbox', 'history'])
            ->make(true);
		

    }
	
    public function export_Last21DaysApplicantAdded(Request $request)
    {
        $end_date = Carbon::now();
        $edate7 = $end_date->subDays(16);
        $edate = $edate7->format('Y-m-d') . " 23:59:59";
        $start_date = $end_date->subDays(21);
        $sdate = $start_date->format('Y-m-d') . " 00:00:00";
        // echo '21 days';exit();
        //$job_category='nurse';
	      $job_category=$request->input('hidden_job_value');
        return Excel::download(new Applicant_21_days_export($sdate,$edate,$job_category), 'applicants.csv');

        //        return Excel::download(new ApplicantsExport($sdate,$edate,$job_category), 'applicants.csv');
    }

    public function getLast21DaysApplicantAdded($id)
    {
        $interval = 21;

        return view('administrator.resource.last_21_days_applicant_added', compact('interval','id'));
    }
    
    public function get21DaysApplicants($id)
    {
        $end_date = Carbon::now();
        $edate7 = $end_date->subDays(16);
        $edate = $edate7->format('Y-m-d') . " 23:59:59";
        $start_date = $end_date->subDays(21);
        $sdate = $start_date->format('Y-m-d') . " 00:00:00";

        $result1 = Applicant::with(['cv_notes' => function($query) {
                        $query->select('status', 'applicant_id', 'sale_id', 'user_id')
                            ->with(['user:id,name'])->latest(); // Eager load the 'user' relationship and only select 'id' and 'name'
                    }])
            ->select('applicants.id', 'applicants.updated_at', 'applicants.applicant_added_time', 'applicants.applicant_name', 'applicants.applicant_job_title','applicants.job_title_prof', 'applicants.job_category', 'applicants.applicant_postcode', 'applicants.applicant_phone','applicants.applicant_homePhone','applicants.applicant_source','applicants.applicant_notes','applicants.paid_status','applicants.applicant_cv','applicants.updated_cv')
            ->leftJoin('applicants_pivot_sales', 'applicants.id', '=', 'applicants_pivot_sales.applicant_id')
            ->whereBetween('applicants.updated_at', [$sdate, $edate])
            ->where("applicants.status", "=", "active")
                //           ->where("applicants.is_in_nurse_home", "=", "no")
			 ->where("applicants.temp_not_interested", "=", "0");
           if ($id == "44"){
                  $result1= $result1->where("applicants.job_category", '=',"nurse");
              }elseif ($id == "45"){
                  $result1= $result1->where("applicants.job_category", "=","non-nurse")->whereNotIn('applicants.applicant_job_title', ['nonnurse specialist']);
              }elseif ($id =="46"){
                  $result1= $result1->where(["applicants.job_category" => "non-nurse", "applicants.applicant_job_title" => "nonnurse specialist" ]);

              }elseif ($id =="47"){
                  $result1= $result1->where(["applicants.job_category" => "chef"]);

              }
			$result=$result1->where("applicants.is_blocked", "=", "0")
            ->where('applicants_pivot_sales.applicant_id', '=', NULL)->orderBy('applicants.updated_at', 'DESC');
		//->where('applicants.is_no_job',"=","0")

        return datatables()->of($result)
            ->addColumn('applicant_postcode', function ($applicant) {
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                /*** logic before open-applicant-cv-feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $status_value = 'sent';
                        break;
                    } elseif ($value->status == 'disable') {
                        $status_value = 'reject';
                    } elseif ($value->status == 'paid') {
                        $status_value = 'paid';
                        break;
                    }
                }
                */
                if($status_value == 'open' || $status_value == 'reject'){
                    $postcode .= '<a href="/available-jobs/'.$applicant->id.'">';
                    $postcode .= $applicant->applicant_postcode;
                    $postcode .= '</a>';
                } else {
                    $postcode .= $applicant->applicant_postcode;
                }
                return $postcode;
            })
			->addColumn("agent_name", function($applicant) {
                // Since we're fetching only the latest cv_note, this should be straightforward
                if ($applicant->cv_notes->isNotEmpty()) {
                    return $applicant->cv_notes->first()->user->name ?? null;
                }
                return '-';
            })
            ->addColumn("updated_at",function($result){
                $updated_at = new DateTime($result->updated_at);
                $date = date_format($updated_at,'d F Y');
                    return $date;
                })
			->addColumn('applicant_notes', function($applicant){

                    $app_new_note = ModuleNote::where(['module_noteable_id' =>$applicant->id, 'module_noteable_type' =>'Horsefly\Applicant'])
                    ->select('module_notes.details')
                    ->orderBy('module_notes.id', 'DESC')
                    ->first();
                    $app_notes_final='';
                    if($app_new_note){
                        $app_notes_final = $app_new_note->details;

                    }
                    else{
                        $app_notes_final = $applicant->applicant_notes;
                    }
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                   
                if($applicant->is_blocked == 0 && $status_value == 'open' || $status_value == 'reject')
                {
                    
                $content = '';
                // if ($status_value == 'open' || $status_value == 'reject'){

                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                 data-controls-modal="#clear_cv'.$applicant->id.'"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#clear_cv' . $applicant->id . '">"'.$app_notes_final.'"</a>';
                $content .= '<div id="clear_cv' . $applicant->id . '" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h5 class="modal-title">Notes</h5>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<form action="' . route('block_or_casual_notes') . '" method="POST" id="app_notes_form' . $applicant->id . '" class="form-horizontal">';
                $content .= csrf_field();
                $content .= '<div class="modal-body">';
                $content .='<div id="app_notes_alert' . $applicant->id . '"></div>';
                $content .= '<div id="sent_cv_alert' . $applicant->id . '"></div>';
                $content .= '<div class="form-group row">';
                $content .= '<label class="col-form-label col-sm-3">Details</label>';
                $content .= '<div class="col-sm-9">';
                $content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant->id . '">';
                $content .= '<input type="hidden" name="applicant_page' . $applicant->id . '" value="21_days_applicants">';
                $content .= '<textarea name="details" id="sent_cv_details' . $applicant->id .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
                $content .= '</div>';
                $content .= '</div>';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Choose type:</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<select name="reject_reason" class="form-control crm_select_reason" id="reason' . $applicant->id .'">';
                    $content .= '<option value="0" >Select Reason</option>';
                    $content .= '<option value="1">Casual Notes</option>';
                    $content .= '<option value="2">Block Applicant Notes</option>';
					$content .= '<option value="3">Temporary Not Interested Applicants Notes</option>';
                    $content .= '</select>';
                    $content .= '</div>';
                    $content .= '</div>';

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                   
                    $content .= '<button type="button" class="btn bg-dark legitRipple sent_cv_submit" data-dismiss="modal">Close</button>';
                    
                    $content .= '<button type="submit" data-note_key="' . $applicant->id . '" value="cv_sent_save" class="btn bg-teal legitRipple sent_cv_submit app_notes_form_submit">Save</button>';

                $content .= '</div>';
                $content .= '</form>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
            // } else {
                // $content .= $applicant->applicant_notes;
                // }

                    //return $app_notes_final;
                   return $content;
            }else
            {
               return $app_notes_final;
            }

                })
            ->addColumn('history', function ($crm_start_date_hold_applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$crm_start_date_hold_applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$crm_start_date_hold_applicant->id.'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $crm_start_date_hold_applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$crm_start_date_hold_applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$crm_start_date_hold_applicant->applicant_name.'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$crm_start_date_hold_applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;

            })
            ->addColumn('status', function ($applicant) {
                $status_value = 'open';
                $color_class = 'bg-teal-800';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                    $color_class = 'bg-slate-700';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                /*** logic before open-applicant-cv-feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $status_value = 'sent';
                        break;
                    } elseif ($value->status == 'disable') {
                        $status_value = 'reject';
                    } elseif ($value->status == 'paid') {
                        $status_value = 'paid';
                        $color_class = 'bg-slate-700';
                        break;
                    }
                }
                */
                $status = '';
                $status .= '<h3>';
                $status .= '<span class="badge w-100 '.$color_class.'">';
                $status .= strtoupper($status_value);
                $status .= '</span>';
                $status .= '</h3>';
                return $status;
            })
			->addColumn('download', function ($applicant) {
                $filePath = $applicant->applicant_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->applicant_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->applicant_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadApplicantCv', $applicant->id);

                $download = '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';
                return $download;
            })
            ->addColumn('updated_cv', function ($applicant) {
                $filePath = $applicant->updated_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->updated_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->updated_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadUpdatedApplicantCv', $applicant->id);
                return
                    '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';

            })
			->editColumn('applicant_job_title', function ($applicant) {
				$job_title_desc='';
				if($applicant->job_title_prof!=null)
				{
					$job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $applicant->job_title_prof)->first();
					$job_title_desc = $applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
				}
				else
				{

					$job_title_desc = $applicant->applicant_job_title;
				}
				return strtoupper($job_title_desc);

			})
       
			->addColumn('upload', function ($applicant) {
				return
				'<a href="#"
				data-controls-modal="#import_applicant_cv" class="import_cv"
				data-backdrop="static"
				data-keyboard="false" data-toggle="modal" data-id="'.$applicant->id.'"
				data-target="#import_applicant_cv">
				 <i class="fas fa-file-upload text-teal-400"></i>
				 &nbsp;</a>';
			})
            ->setRowClass(function ($applicant) {
                $row_class = '';
                if ($applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
				} elseif ($applicant->is_no_job == '1') {
                    $row_class = 'class_noJob';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success';
                            break;
                        } elseif ($value->status == 'disable') {
                            $row_class = 'class_danger';
                        }
                    }
                }
                /*** logic before open-applicant-cv-feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $row_class = 'class_success'; // status: sent
                        break;
                    } elseif ($value->status == 'disable') {
                        $row_class = 'class_danger'; // status: reject
                    } elseif ($value->status == 'paid') {
                        $row_class = 'class_dark';
                        break;
                    }
                }
                */
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','updated_at','download','updated_cv','upload','applicant_notes','status','applicant_postcode','history'])
        ->make(true);
    }
	
	public function getlast21DaysAppNotInterested($id)
    {
        $end_date = Carbon::now();
        $edate7 = $end_date->subDays(16);
        $edate = $edate7->format('Y-m-d') . " 23:59:59";
        $start_date = $end_date->subDays(21);
        $sdate = $start_date->format('Y-m-d') . " 00:00:00";

        $result1 = Applicant::with(['cv_notes' => function($query) {
                        $query->select('status', 'applicant_id', 'sale_id', 'user_id')
                            ->with(['user:id,name'])->latest(); // Eager load the 'user' relationship and only select 'id' and 'name'
                    }])
            ->leftJoin('applicants_pivot_sales', 'applicants_pivot_sales.applicant_id', '=', 'applicants.id')
            ->leftJoin('sales', 'sales.id', '=', 'applicants_pivot_sales.sales_id')
            ->leftJoin('notes_for_range_applicants', 'applicants_pivot_sales.id', '=', 'notes_for_range_applicants.applicants_pivot_sales_id')
            ->leftJoin('offices', 'offices.id', '=', 'sales.head_office')
            ->leftJoin('units', 'units.id', '=', 'sales.head_office_unit')
            ->select(
                'applicants.id',
                'applicants.updated_at',
                'applicants.temp_not_interested',
                'applicants.applicant_added_time',
                'applicants.is_no_job',
                'applicants.applicant_name',
                'applicants.applicant_job_title',
                'applicants.job_title_prof',
                'applicants.job_category',
                'applicants.applicant_postcode',
                'applicants.applicant_phone',
                'applicants.applicant_homePhone',
                'applicants.applicant_source',
                'applicants.applicant_email',
                'applicants.applicant_notes',
                'applicants.paid_status',
                'applicants.applicant_cv',
                'applicants.updated_cv',
                'applicants_pivot_sales.sales_id as pivot_sale_id',
                'applicants_pivot_sales.id as pivot_id'
            )
            ->where("applicants.temp_not_interested", "1");

            if ($id == "44"){
                $result1= $result1->where("applicants.job_category", '=',"nurse");
            }elseif ($id == "45"){
                $result1= $result1->where("applicants.job_category", "=","non-nurse")->whereNotIn('applicants.applicant_job_title', ['nonnurse specialist']);
            }elseif ($id =="46"){
                $result1= $result1->where(["applicants.job_category" => "non-nurse", "applicants.applicant_job_title" => "nonnurse specialist" ]);
            }elseif ($id =="47"){
                $result1= $result1->where(["applicants.job_category" => "chef"]);
            }

            $result = $result1->where("applicants.is_no_job", "=", "0")
            ->where("applicants.status", "active")
            ->whereBetween('applicants.updated_at', ['2024-08-06', $edate])
            ->orderBy('applicants.updated_at', 'DESC')
            ->get();

        return datatables()->of($result)
            ->addColumn('applicant_postcode', function ($applicant) {
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                if($status_value == 'open' || $status_value == 'reject'){
                    $postcode .= '<a href="/available-jobs/'.$applicant->id.'">';
                    $postcode .= $applicant->applicant_postcode;
                    $postcode .= '</a>';
                } else {
                    $postcode .= $applicant->applicant_postcode;
                }
                return $postcode;
            })
            ->addColumn("updated_at",function($result){
                $updated_at = new DateTime($result->updated_at);
                $date = date_format($updated_at,'d F Y');
                    return $date;
                })
			->addColumn("agent_name", function($applicant) {
                // Since we're fetching only the latest cv_note, this should be straightforward
                if ($applicant->cv_notes->isNotEmpty()) {
                    return $applicant->cv_notes->first()->user->name ?? null;
                }
                return '-';
            })
			->addColumn('applicant_notes', function($applicant){

                    $app_new_note = ModuleNote::where(['module_noteable_id' =>$applicant->id, 'module_noteable_type' =>'Horsefly\Applicant'])
                    ->select('module_notes.details')
                    ->orderBy('module_notes.id', 'DESC')
                    ->first();
                    $app_notes_final='';
                    if($app_new_note){
                        $app_notes_final = $app_new_note->details;

                    }
                    else{
                        $app_notes_final = $applicant->applicant_notes;
                    }
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                   
                if($applicant->is_blocked == 0 && $status_value == 'open' || $status_value == 'reject')
                {
                    
                $content = '';
                // if ($status_value == 'open' || $status_value == 'reject'){

                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                 data-controls-modal="#clear_cv'.$applicant->id.'"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#clear_cv' . $applicant->id . '">"'.$app_notes_final.'"</a>';
                $content .= '<div id="clear_cv' . $applicant->id . '" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h5 class="modal-title">Notes</h5>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<form action="' . route('block_or_casual_notes') . '" method="POST" id="app_notes_form' . $applicant->id . '" class="form-horizontal">';
                $content .= csrf_field();
                $content .= '<div class="modal-body">';
                $content .='<div id="app_notes_alert' . $applicant->id . '"></div>';
                $content .= '<div id="sent_cv_alert' . $applicant->id . '"></div>';
                $content .= '<div class="form-group row">';
                $content .= '<label class="col-form-label col-sm-3">Details</label>';
                $content .= '<div class="col-sm-9">';
                $content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant->id . '">';
                $content .= '<input type="hidden" name="applicant_page' . $applicant->id . '" value="21_days_applicants">';
                $content .= '<textarea name="details" id="sent_cv_details' . $applicant->id .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
                $content .= '</div>';
                $content .= '</div>';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Choose type:</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<select name="reject_reason" class="form-control crm_select_reason" id="reason' . $applicant->id .'">';
                    $content .= '<option value="0" >Select Reason</option>';
                    $content .= '<option value="1">Casual Notes</option>';
                    $content .= '<option value="2">Block Applicant Notes</option>';
					$content .= '<option value="3">Temporary Not Interested Applicants Notes</option>';
                    $content .= '</select>';
                    $content .= '</div>';
                    $content .= '</div>';

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                   
                    $content .= '<button type="button" class="btn bg-dark legitRipple sent_cv_submit" data-dismiss="modal">Close</button>';
                    
                    $content .= '<button type="submit" data-note_key="' . $applicant->id . '" value="cv_sent_save" class="btn bg-teal legitRipple sent_cv_submit app_notes_form_submit">Save</button>';

                $content .= '</div>';
                $content .= '</form>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
            // } else {
                // $content .= $applicant->applicant_notes;
                // }

                    //return $app_notes_final;
                   return $content;
            }else
            {
               return $app_notes_final;
            }

                })
            ->addColumn('history', function ($crm_start_date_hold_applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$crm_start_date_hold_applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$crm_start_date_hold_applicant->id.'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $crm_start_date_hold_applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$crm_start_date_hold_applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$crm_start_date_hold_applicant->applicant_name.'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$crm_start_date_hold_applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;

            })
            ->addColumn('status', function ($applicant) {
                $status_value = 'open';
                $color_class = 'bg-teal-800';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                    $color_class = 'bg-slate-700';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                /*** logic before open-applicant-cv-feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $status_value = 'sent';
                        break;
                    } elseif ($value->status == 'disable') {
                        $status_value = 'reject';
                    } elseif ($value->status == 'paid') {
                        $status_value = 'paid';
                        $color_class = 'bg-slate-700';
                        break;
                    }
                }
                */
                $status = '';
                $status .= '<h3>';
                $status .= '<span class="badge w-100 '.$color_class.'">';
                $status .= strtoupper($status_value);
                $status .= '</span>';
                $status .= '</h3>';
                return $status;
            })
			->addColumn('download', function ($applicant) {
                $filePath = $applicant->applicant_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->applicant_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->applicant_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadApplicantCv', $applicant->id);

                $download = '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';
                return $download;
            })
            ->addColumn('updated_cv', function ($applicant) {
                $filePath = $applicant->updated_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->updated_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->updated_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadUpdatedApplicantCv', $applicant->id);
                return
                    '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';

            })
			->editColumn('applicant_job_title', function ($applicant) {
				$job_title_desc='';
				if($applicant->job_title_prof!=null)
				{
					$job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $applicant->job_title_prof)->first();
					$job_title_desc = $applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
				}
				else
				{

					$job_title_desc = $applicant->applicant_job_title;
				}
				return strtoupper($job_title_desc);

			})
       
			->addColumn('upload', function ($applicant) {
				return
				'<a href="#"
				data-controls-modal="#import_applicant_cv" class="import_cv"
				data-backdrop="static"
				data-keyboard="false" data-toggle="modal" data-id="'.$applicant->id.'"
				data-target="#import_applicant_cv">
				 <i class="fas fa-file-upload text-teal-400"></i>
				 &nbsp;</a>';
			})
            ->setRowClass(function ($applicant) {
                $row_class = '';
                if ($applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success';
                            break;
                        } elseif ($value->status == 'disable') {
                            $row_class = 'class_danger';
                        }
                    }
                }
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','updated_at','download','updated_cv','upload','applicant_notes','status','applicant_postcode','history'])
        ->make(true);
    }

    public function getlast21DaysAppBlocked($id)
    {
        $end_date = Carbon::now();
        $edate7 = $end_date->subDays(16);
        $edate = $edate7->format('Y-m-d') . " 23:59:59";
        $start_date = $end_date->subDays(21);
        $sdate = $start_date->format('Y-m-d') . " 00:00:00";

        $result1 = Applicant::with(['cv_notes' => function($query) {
                        $query->select('status', 'applicant_id', 'sale_id', 'user_id')
                            ->with(['user:id,name'])->latest(); // Eager load the 'user' relationship and only select 'id' and 'name'
                    }])
        ->leftJoin('applicants_pivot_sales', 'applicants_pivot_sales.applicant_id', '=', 'applicants.id')
        ->leftJoin('sales', 'sales.id', '=', 'applicants_pivot_sales.sales_id')
        ->leftJoin('notes_for_range_applicants', 'applicants_pivot_sales.id', '=', 'notes_for_range_applicants.applicants_pivot_sales_id')
        ->leftJoin('offices', 'offices.id', '=', 'sales.head_office')
        ->leftJoin('units', 'units.id', '=', 'sales.head_office_unit')
        ->select(
            'applicants.id',
            'applicants.updated_at',
            'applicants.temp_not_interested',
            'applicants.applicant_added_time',
            'applicants.is_no_job',
            'applicants.applicant_name',
            'applicants.applicant_job_title',
            'applicants.job_title_prof',
            'applicants.job_category',
            'applicants.applicant_postcode',
            'applicants.applicant_phone',
            'applicants.applicant_homePhone',
            'applicants.applicant_source',
            'applicants.applicant_email',
            'applicants.applicant_notes',
            'applicants.paid_status',
            'applicants.applicant_cv',
            'applicants.updated_cv',
            'applicants_pivot_sales.sales_id as pivot_sale_id',
            'applicants_pivot_sales.id as pivot_id'
        )
        ->where("applicants.temp_not_interested", "0");

    
        if ($id == "44") {
            $result1 = $result1->where("applicants.job_category", '=', "nurse");
        } elseif ($id == "45") {
            $result1 = $result1->where("applicants.job_category", "=", "non-nurse")
                ->whereNotIn('applicants.applicant_job_title', ['nonnurse specialist']);
        } elseif ($id == "46") {
            $result1 = $result1->where([
                "applicants.job_category" => "non-nurse",
                "applicants.applicant_job_title" => "nonnurse specialist"
            ]);
        } elseif ($id == "47") {
            $result1 = $result1->where("applicants.job_category", "chef");
        }
        
        $result = $result1->where("applicants.is_blocked", "1")
            ->where("applicants.is_no_job", "=", "0")
            ->where("applicants.status", "active")
            ->whereBetween('applicants.updated_at', ['2024-08-06', $edate])
            ->orderBy('applicants.updated_at', 'DESC')
            ->get();

        return datatables()->of($result)
            ->addColumn('applicant_postcode', function ($applicant) {
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
             //   if($status_value == 'open' || $status_value == 'reject'){
              //      $postcode .= '<a href="/available-jobs/'.$applicant->id.'">';
              //      $postcode .= $applicant->applicant_postcode;
              //      $postcode .= '</a>';
              //  } else {
              //      $postcode .= $applicant->applicant_postcode;
              //  }
                return $applicant->applicant_postcode;
            })
            ->addColumn("updated_at",function($result){
                $updated_at = new DateTime($result->updated_at);
                $date = date_format($updated_at,'d F Y');
                    return $date;
                })
			->addColumn("agent_name", function($applicant) {
                // Since we're fetching only the latest cv_note, this should be straightforward
                if ($applicant->cv_notes->isNotEmpty()) {
                    return $applicant->cv_notes->first()->user->name ?? null;
                }
                return '-';
            })
			->addColumn('applicant_notes', function($applicant){

                    $app_new_note = ModuleNote::where(['module_noteable_id' =>$applicant->id, 'module_noteable_type' =>'Horsefly\Applicant'])
                    ->select('module_notes.details')
                    ->orderBy('module_notes.id', 'DESC')
                    ->first();
                    $app_notes_final='';
                    if($app_new_note){
                        $app_notes_final = $app_new_note->details;

                    }
                    else{
                        $app_notes_final = $applicant->applicant_notes;
                    }
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                   
                if($applicant->is_blocked == 0 && $status_value == 'open' || $status_value == 'reject')
                {
                    
                $content = '';

                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                 data-controls-modal="#clear_cv'.$applicant->id.'"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#clear_cv' . $applicant->id . '">"'.$app_notes_final.'"</a>';
                $content .= '<div id="clear_cv' . $applicant->id . '" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h5 class="modal-title">Notes</h5>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<form action="' . route('block_or_casual_notes') . '" method="POST" id="app_notes_form' . $applicant->id . '" class="form-horizontal">';
                $content .= csrf_field();
                $content .= '<div class="modal-body">';
                $content .='<div id="app_notes_alert' . $applicant->id . '"></div>';
                $content .= '<div id="sent_cv_alert' . $applicant->id . '"></div>';
                $content .= '<div class="form-group row">';
                $content .= '<label class="col-form-label col-sm-3">Details</label>';
                $content .= '<div class="col-sm-9">';
                $content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant->id . '">';
                $content .= '<input type="hidden" name="applicant_page' . $applicant->id . '" value="21_days_applicants">';
                $content .= '<textarea name="details" id="sent_cv_details' . $applicant->id .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
                $content .= '</div>';
                $content .= '</div>';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Choose type:</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<select name="reject_reason" class="form-control crm_select_reason" id="reason' . $applicant->id .'">';
                    $content .= '<option value="0" >Select Reason</option>';
                    $content .= '<option value="1">Casual Notes</option>';
                    $content .= '<option value="2">Block Applicant Notes</option>';
					$content .= '<option value="3">Temporary Not Interested Applicants Notes</option>';
                    $content .= '</select>';
                    $content .= '</div>';
                    $content .= '</div>';

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                   
                    $content .= '<button type="button" class="btn bg-dark legitRipple sent_cv_submit" data-dismiss="modal">Close</button>';
                    
                    $content .= '<button type="submit" data-note_key="' . $applicant->id . '" value="cv_sent_save" class="btn bg-teal legitRipple sent_cv_submit app_notes_form_submit">Save</button>';

                $content .= '</div>';
                $content .= '</form>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                   return $content;
            }else
            {
               return $app_notes_final;
            }

                })
            ->addColumn('history', function ($crm_start_date_hold_applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$crm_start_date_hold_applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$crm_start_date_hold_applicant->id.'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $crm_start_date_hold_applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$crm_start_date_hold_applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$crm_start_date_hold_applicant->applicant_name.'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$crm_start_date_hold_applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;

            })
            ->addColumn('status', function ($applicant) {
                $status_value = 'open';
                $color_class = 'bg-teal-800';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                    $color_class = 'bg-slate-700';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                $status = '';
                $status .= '<h3>';
                $status .= '<span class="badge w-100 '.$color_class.'">';
                $status .= strtoupper($status_value);
                $status .= '</span>';
                $status .= '</h3>';
                return $status;
            })
			->addColumn('download', function ($applicant) {
                $filePath = $applicant->applicant_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->applicant_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->applicant_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadApplicantCv', $applicant->id);

                $download = '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';
                return $download;
            })
            ->addColumn('updated_cv', function ($applicant) {
                $filePath = $applicant->updated_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->updated_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->updated_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadUpdatedApplicantCv', $applicant->id);
                return
                    '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';

            })
			->editColumn('applicant_job_title', function ($applicant) {
				$job_title_desc='';
				if($applicant->job_title_prof!=null)
				{
					$job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $applicant->job_title_prof)->first();
					$job_title_desc = $applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
				}
				else
				{

					$job_title_desc = $applicant->applicant_job_title;
				}
				return strtoupper($job_title_desc);

			})
       
			->addColumn('upload', function ($applicant) {
				return
				'<a href="#"
				data-controls-modal="#import_applicant_cv" class="import_cv"
				data-backdrop="static"
				data-keyboard="false" data-toggle="modal" data-id="'.$applicant->id.'"
				data-target="#import_applicant_cv">
				 <i class="fas fa-file-upload text-teal-400"></i>
				 &nbsp;</a>';
			})
            ->setRowClass(function ($applicant) {
                $row_class = '';
                if ($applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success';
                            break;
                        } elseif ($value->status == 'disable') {
                            $row_class = 'class_danger';
                        }
                    }
                }
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','updated_at','download','updated_cv','upload','applicant_notes','status','applicant_postcode','history'])
        ->make(true);
    }
	
	public function export_Last2MonthsApplicantAdded(Request $request)
    {
        //$end_date = Carbon::now();
        //$edate21 = $end_date->subMonth(1)->subDays(6); // 9 + 21 + excluding last_day . 00:00:00
        //$edate = $edate21->format('Y-m-d');
        //$start_date = $end_date->subMonths(60);
        //$sdate = $start_date->format('Y-m-d');
		 //$job_category=$request->input('hidden_job_value');
        //return Excel::download(new Applicant_21_days_export($sdate,$edate,$job_category), 'applicants.csv');
        //$job_category='nurse';
        //return Excel::download(new ResourcesExport($sdate,$edate,$job_category), 'applicants.csv');
		//new code
		 $end_date = Carbon::now();
        $edate = $end_date->format('Y-m-d');

        $start_date = $end_date->subMonth(1)->subDays(6);
        $sdate = $start_date->format('Y-m-d');
        $job_category=$request->input('hidden_job_value');
        return Excel::download(new Applicant_2M_days_export($sdate,$edate,$job_category), 'applicants.csv');

    }
	
    public function getLast2MonthsBlockedApplicantAdded()
    {
        // $end_date = Carbon::now();
        // //$edate21 = $end_date->subDays(31); // 9 + 21 + excluding last_day . 00:00:00
        // $edate = $end_date->format('Y-m-d');
        // $start_date = $end_date->subMonths(60);
        // $sdate = $start_date->format('Y-m-d');
        // echo $edate.' and '.$sdate;exit();
        $interval = 60;
        return view('administrator.resource.last_2_months_blocked_applicants', compact('interval'));
    }

    public function getLast2MonthsBlockedApplicantAddedAjax()
    {

        $end_date = Carbon::now();
        $edate = $end_date->format('Y-m-d');

        $start_date = $end_date->subMonths(60);
        $sdate = $start_date->format('Y-m-d');
        $result = Applicant::with(['cv_notes' => function($query) {
                        $query->select('status', 'applicant_id', 'sale_id', 'user_id')
                            ->with(['user:id,name'])->latest(); // Eager load the 'user' relationship and only select 'id' and 'name'
                    }])
            ->select('applicants.id', 'applicants.updated_at', 'applicants.applicant_added_time', 'applicants.applicant_name', 'applicants.applicant_job_title', 'applicants.job_title_prof', 'applicants.job_category', 'applicants.applicant_postcode', 'applicants.applicant_phone','applicants.applicant_homePhone','applicants.applicant_source','applicants.applicant_notes','applicants.paid_status')
            ->leftJoin('applicants_pivot_sales', 'applicants.id', '=', 'applicants_pivot_sales.applicant_id')
            ->whereDate('applicants.updated_at', '<=', $edate)
            ->where("applicants.status", "=", "active")
            ->where("applicants.is_blocked", "=", "1")
            ->where('applicants_pivot_sales.applicant_id', '=', NULL);

        return datatables()->of($result)
            ->addColumn('applicant_postcode', function ($applicant) {
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                if ($status_value == 'open' || $status_value == 'reject'){
                    $postcode .= '<a href="/available-jobs/'.$applicant->id.'">';
                    $postcode .= $applicant->applicant_postcode;
                    $postcode .= '</a>';
                } else {
                    $postcode .= $applicant->applicant_postcode;
                }
                return strtoupper($postcode);
            })
			->addColumn("agent_name", function($applicant) {
                // Since we're fetching only the latest cv_note, this should be straightforward
                if ($applicant->cv_notes->isNotEmpty()) {
                    return $applicant->cv_notes->first()->user->name ?? null;
                }
                return '-';
            })
            ->addColumn('applicant_notes', function($applicant){

                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                   
                
                    
                $content = '';
				
				/*** Export Applicants Modal */
                $content .= '<div id="export_applicant_action" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-sm">';
                $content .= '<div class="modal-content">';

                $content .= '<div class="modal-header">';
                $content .= '<h3 class="modal-title">Export Applicants</h3>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body">';
                $content .= '<form action="' . route('export_blocked_applicants') . '" method="POST" id="export_block_applicants" class="form-horizontal">';
                $content .= csrf_field();

                $content .= '<div class="mb-4">';
                $content .= '<div class="input-group">';
                $content .= '<span class="input-group-prepend">';
                $content .= '<span class="input-group-text"><i class="icon-calendar5"></i></span>';
                $content .= '</span>';
                $content .= '<input type="text" class="form-control pickadate-year" name="start_date" id="start_date" placeholder="Select From Date">';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '<div class="mb-4">';
                $content .= '<div class="input-group">';
                $content .= '<span class="input-group-prepend">';
                $content .= '<span class="input-group-text"><i class="icon-calendar5"></i></span>';
                $content .= '</span>';

                $content .= '<input type="text" class="form-control pickadate-year" name="end_date" id="end_date" placeholder="Select To Date">';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '<button type="submit" class="btn bg-teal legitRipple btn-block">Submit</button>';
                $content .= '</form>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
				
				/*** Unblock Applicants Modal */
                 $content .= '<div id="applicant_action" class="modal fade" tabindex="-1">';
                 $content .= '<div class="modal-dialog modal-sm">';
                 $content .= '<div class="modal-content">';
 
                 $content .= '<div class="modal-header">';
                 $content .= '<h3 class="modal-title" >Unblock Applicants</h3>';
                 $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                 $content .= '</div>';
                 $content .= '<div class="modal-body">';
                 $content .= '<div id="applicant_unblock_alert"></div>';
                 $content .= '<form action="' . route('scheduleInterview') . '" method="POST" id="applicant_unblock_form" class="form-horizontal">';
                 $content .= csrf_field();

                 $content .= '<div class="mb-4">';
                 $content .= '<div class="input-group">';
                 $content .= '<span class="input-group-prepend">';
                 $content .= '<span class="input-group-text"><i class="icon-calendar5"></i></span>';
                 $content .= '</span>';
                 $content .= '<input type="text" class="form-control pickadate-year" name="from_date" id="from_date" placeholder="Select From Date">';
                 $content .= '</div>';
                 $content .= '</div>';
                 $content .= '<div class="mb-4">';
                 $content .= '<div class="input-group">';
                 $content .= '<span class="input-group-prepend">';
                 $content .= '<span class="input-group-text"><i class="icon-calendar5"></i></span>';
                 $content .= '</span>';
                //                $content .= '<input type="text" class="form-control time_pickerrrr" id="anytime-time'.$applicant->id.'-'.$applicant->sale_id.'" name="schedule_time" placeholder="Select Schedule Time e.g., 00:00">';
                 $content .= '<input type="text" class="form-control pickadate-year" name="to_date" id="to_date" placeholder="Select To Date">';
                 $content .= '</div>';
                 $content .= '</div>';
                 $content .= '<button type="submit" class="btn bg-teal legitRipple btn-block applicant_action_submit" data-app_sale="">Submit</button>';
                 $content .= '</form>';
                 $content .= '</div>';
                 $content .= '</div>';
                 $content .= '</div>';
                 $content .= '</div>';
				if ($status_value == 'open' || $status_value == 'reject'){

                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                 data-controls-modal="#clear_cv'.$applicant->id.'"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#clear_cv' . $applicant->id . '">"'.$applicant->applicant_notes.'"</a>';
                $content .= '<div id="clear_cv' . $applicant->id . '" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h5 class="modal-title">Notes</h5>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<form action="' . route('unblock_notes') . '" method="POST" class="form-horizontal">';
                $content .= csrf_field();
                $content .= '<div class="modal-body">';
                $content .= '<div id="sent_cv_alert' . $applicant->id . '"></div>';
                $content .= '<div class="form-group row">';
                $content .= '<label class="col-form-label col-sm-3">Details</label>';
                $content .= '<div class="col-sm-9">';
                $content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant->id . '">';
                $content .= '<textarea name="details" id="sent_cv_details' . $applicant->id .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
                $content .= '</div>';
                $content .= '</div>';
                 

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                   
                    $content .= '<button type="button" class="btn bg-dark legitRipple sent_cv_submit" data-dismiss="modal">Close</button>';
                    
                    $content .= '<button type="submit" value="cv_sent_save" class="btn bg-teal legitRipple sent_cv_submit">Unblock</button>';

                $content .= '</div>';
                $content .= '</form>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
            } else {
                $content .= $applicant->applicant_notes;
                }
               
                return $content;

			})
                
            ->addColumn("history", function ($applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                 data-controls-modal="#reject_history'.$applicant->id.'"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History';
                $content .= '<span class="font-weight-semibold">';
                $content .=  utf8_encode($applicant->applicant_name);
                $content .= '</span>';
                $content .= '</h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                return $content;
            })
			->editColumn('applicant_job_title', function ($applicant) {
            $job_title_desc='';
            if($applicant->job_title_prof!=null)
            {
                $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $applicant->job_title_prof)->first();
                            $job_title_desc = $applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
            }
            else
            {
                
                $job_title_desc = $applicant->applicant_job_title;
            }
            return strtoupper($job_title_desc);

     })
            ->addColumn("updated_at",function($applicant){
                $updated_at = new DateTime($applicant->updated_at);
                $date = date_format($updated_at,'d F Y');
                    return $date;
            })
            ->addColumn('status', function ($applicant) {
                $status_value = 'open';
                $color_class = 'bg-teal-800';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                    $color_class = 'bg-slate-700';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }

                $status = '';
                $status .= '<h3>';
                $status .= '<span class="badge w-100 '.$color_class.'">';
                $status .= strtoupper($status_value);
                $status .= '</span>';
                $status .= '</h3>';
                return $status;
            })
			->addColumn('checkbox', function ($applicant) {
                 return '<input type="checkbox" name="applicant_checkbox[]" class="applicant_checkbox" value="' . $applicant->id . '"/>';
             })
            ->setRowClass(function ($applicant) {
                $row_class = '';
                if ($applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success';
                            break;
                        } elseif ($value->status == 'disable') {
                            $row_class = 'class_danger';
                        }
                    }
                }

                return $row_class;
            })
            ->rawColumns(['applicant_job_title','history','applicant_notes','updated_at','status','applicant_postcode','checkbox'])
            ->make(true);

    }

    public function getLast2MonthsApplicantAdded($id)
    {
        $interval = 60;
        return view('administrator.resource.last_2_months_applicant_added', compact('interval','id'));
    }

    public function get2MonthsApplicants($id)
    {
		       // date_default_timezone_set('Europe/London');

        $end_date = Carbon::now();
        $edate21 = $end_date->subMonth(1)->subDays(6); // 16 + 21 + excluding last_day . 00:00:00
        $edate = $edate21->format('Y-m-d');

        // $start_date = $end_date->subMonths(60);
        // $sdate = $start_date->format('Y-m-d');
        $result1 = Applicant::with(['cv_notes' => function($query) {
                        $query->select('status', 'applicant_id', 'sale_id', 'user_id')
                            ->with(['user:id,name'])->latest(); // Eager load the 'user' relationship and only select 'id' and 'name'
                    }])
            ->select('applicants.id', 'applicants.updated_at', 'applicants.is_no_job', 'applicants.applicant_added_time', 'applicants.applicant_name', 'applicants.applicant_job_title', 'applicants.job_title_prof', 'applicants.job_category', 'applicants.applicant_postcode', 'applicants.applicant_phone','applicants.applicant_homePhone','applicants.applicant_source','applicants.applicant_notes','applicants.paid_status','applicants.applicant_cv','applicants.updated_cv')
            ->leftJoin('applicants_pivot_sales', 'applicants.id', '=', 'applicants_pivot_sales.applicant_id')
            //            ->whereBetween('applicants.updated_at', [$sdate, $edate])
            ->whereDate('applicants.updated_at', '<=', $edate)
            ->where("applicants.status", "=", "active")
            //            ->where("applicants.is_in_nurse_home", "=", "no")
			 ->where("applicants.temp_not_interested", "=", "0")
			->orderBy('applicants.updated_at','DESC');
		
              if ($id == "44"){
                $result1= $result1->where("applicants.job_category", '=',"nurse");
            }elseif ($id == "45"){
                $result1= $result1->where("applicants.job_category", "=","non-nurse")
					->whereNotIn('applicants.applicant_job_title', ['nonnurse specialist']);
            }elseif ($id =="46"){
                $result1= $result1->where(["applicants.job_category" => "non-nurse", "applicants.applicant_job_title" => "nonnurse specialist" ]);

            }elseif ($id =="47"){
                $result1= $result1->where(["applicants.job_category" => "chef"]);

            }
			$result=$result1->where('applicants_pivot_sales.applicant_id', '=', NULL);
		//->where('applicants.is_no_job',"=", "0")

        return datatables()->of($result)
            ->addColumn('applicant_postcode', function ($applicant) {
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                /*** logic before open-appllicant-cv feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $status_value = 'sent';
                        break;
                    } elseif ($value->status == 'disable') {
                        $status_value = 'reject';
                    } elseif ($value->status == 'paid') {
                        $status_value = 'paid';
                        break;
                    }
                }
                */
                if ($status_value == 'open' || $status_value == 'reject'){
                    $postcode .= '<a href="/available-jobs/'.$applicant->id.'">';
                    $postcode .= $applicant->applicant_postcode;
                    $postcode .= '</a>';
                } else {
                    $postcode .= $applicant->applicant_postcode;
                }
                return $postcode;
            })
			->addColumn("agent_name", function($applicant) {
                // Since we're fetching only the latest cv_note, this should be straightforward
                if ($applicant->cv_notes->isNotEmpty()) {
                    return $applicant->cv_notes->first()->user->name ?? null;
                }
                return '-';
            })
			->addColumn('applicant_notes', function($applicant){

                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                   
                $content = '';
                if ($status_value == 'open' || $status_value == 'reject'){

                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                 data-controls-modal="#clear_cv'.$applicant->id.'"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#clear_cv' . $applicant->id . '">"'.$applicant->applicant_notes.'"</a>';
                $content .= '<div id="clear_cv' . $applicant->id . '" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h5 class="modal-title">Notes</h5>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<form action="' . route('block_or_casual_notes') . '" method="POST" id="notes_form' . $applicant->id . '" class="form-horizontal">';
                $content .= csrf_field();
                $content .= '<div class="modal-body">';
                $content .='<div id="notes_alert' . $applicant->id . '"></div>';

                $content .= '<div id="sent_cv_alert' . $applicant->id . '"></div>';
                $content .= '<div class="form-group row">';
                $content .= '<label class="col-form-label col-sm-3">Details</label>';
                $content .= '<div class="col-sm-9">';
                $content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant->id . '">';
                $content .= '<input type="hidden" name="applicant_page' . $applicant->id . '" value="2_months_applicants">';
                $content .= '<textarea name="details" id="sent_cv_details' . $applicant->id .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
                $content .= '</div>';
                $content .= '</div>';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Choose type:</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<select name="reject_reason" class="form-control crm_select_reason" id="reason' . $applicant->id .'">';
                    $content .= '<option value="0">Select Reason</option>';
                    $content .= '<option value="1">Casual Notes</option>';
                    $content .= '<option value="2">Block Applicant Notes</option>';
					$content .= '<option value="3">Temporary Not Interested Applicants Notes</option>';
                    $content .= '</select>';
                    $content .= '</div>';
                    $content .= '</div>';

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                   
                    $content .= '<button type="button" class="btn bg-dark legitRipple sent_cv_submit" data-dismiss="modal">Close</button>';
                    
                    $content .= '<button type="submit" data-note_key="' . $applicant->id . '" value="cv_sent_save" class="btn bg-teal legitRipple sent_cv_submit notes_form_submit">Save</button>';

                $content .= '</div>';
                $content .= '</form>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
            } else {
                $content .= $applicant->applicant_notes;
                }
               
                return $content;

            })
            ->addColumn("history", function ($applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                 data-controls-modal="#reject_history'.$applicant->id.'"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History';
                $content .= '<span class="font-weight-semibold">';
                $content .=  utf8_encode($applicant->applicant_name);
                $content .= '</span>';
                $content .= '</h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                return $content;
            })
            ->addColumn("updated_at",function($applicant){
                $updated_at = new DateTime($applicant->updated_at);
                $date = date_format($updated_at,'d F Y');
                    return $date;
				
				  //  $updated_at = Carbon::parse($applicant->updated_at)->timestamp;
                    //return $updated_at;
            })
            ->addColumn('status', function ($applicant) {
                $status_value = 'open';
                $color_class = 'bg-teal-800';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                    $color_class = 'bg-slate-700';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                /*** logic before open-applicant-cv feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $status_value = 'sent';
                        break;
                    } elseif ($value->status == 'disable') {
                        $status_value = 'reject';
                    } elseif ($value->status == 'paid') {
                        $status_value = 'paid';
                        $color_class = 'bg-slate-700';
                        break;
                    }
                }
                */
                $status = '';
                $status .= '<h3>';
                $status .= '<span class="badge w-100 '.$color_class.'">';
                $status .= strtoupper($status_value);
                $status .= '</span>';
                $status .= '</h3>';
                return $status;
            })
			->addColumn('download', function ($applicant) {
                $filePath = $applicant->applicant_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->applicant_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->applicant_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadApplicantCv', $applicant->id);

                $download = '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';
                return $download;
            })
			->addColumn('updated_cv', function ($applicant) {
                $filePath = $applicant->updated_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->updated_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->updated_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadUpdatedApplicantCv', $applicant->id);
                return
                    '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';

            })
			->editColumn('applicant_job_title', function ($applicant) {
            $job_title_desc='';
            if($applicant->job_title_prof!=null)
            {
                $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $applicant->job_title_prof)->first();
                            $job_title_desc = $applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
            }
            else
            {
                
                $job_title_desc = $applicant->applicant_job_title;
            }
            return strtoupper($job_title_desc);

     })

			->addColumn('upload', function ($applicant) {
            return
            '<a href="#"
            data-controls-modal="#import_applicant_cv" class="import_cv"
            data-backdrop="static"
            data-keyboard="false" data-toggle="modal" data-id="'.$applicant->id.'"
            data-target="#import_applicant_cv">
             <i class="fas fa-file-upload text-teal-400"></i>
             &nbsp;</a>';
        })
            ->setRowClass(function ($applicant) {
                $row_class = '';
                if ($applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
				} elseif ($applicant->is_no_job == '1') {
                    $row_class = 'class_noJob';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success';
                            break;
                        } elseif ($value->status == 'disable') {
                            $row_class = 'class_danger';
                        }
                    }
                }
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','history','download','updated_cv','upload','applicant_notes','updated_at','status','applicant_postcode'])
            ->make(true);
		
	 
    }
	
	 public function getlast2MonthsAppNotInterested($id)
    {
		       // date_default_timezone_set('Europe/London');

        $end_date = Carbon::now();
        $edate21 = $end_date->subMonth(1)->subDays(6); // 16 + 21 + excluding last_day . 00:00:00
        $edate = $edate21->format('Y-m-d');

        $result1 = Applicant::with(['cv_notes' => function($query) {
                        $query->select('status', 'applicant_id', 'sale_id', 'user_id')
                            ->with(['user:id,name'])->latest(); // Eager load the 'user' relationship and only select 'id' and 'name'
                    }])
            ->leftJoin('applicants_pivot_sales', 'applicants_pivot_sales.applicant_id', '=', 'applicants.id')
            ->leftJoin('sales', 'sales.id', '=', 'applicants_pivot_sales.sales_id')
            ->leftJoin('notes_for_range_applicants', 'applicants_pivot_sales.id', '=', 'notes_for_range_applicants.applicants_pivot_sales_id')
            ->leftJoin('offices', 'offices.id', '=', 'sales.head_office')
            ->leftJoin('units', 'units.id', '=', 'sales.head_office_unit')
            ->select(
                'applicants.id',
                'applicants.updated_at',
                'applicants.temp_not_interested',
                'applicants.applicant_added_time',
                'applicants.is_no_job',
                'applicants.applicant_name',
                'applicants.applicant_job_title',
                'applicants.job_title_prof',
                'applicants.job_category',
                'applicants.applicant_postcode',
                'applicants.applicant_phone',
                'applicants.applicant_homePhone',
                'applicants.applicant_source',
                'applicants.applicant_email',
                'applicants.applicant_notes',
                'applicants.paid_status',
                'applicants.applicant_cv',
                'applicants.updated_cv',
                'applicants_pivot_sales.sales_id as pivot_sale_id',
                'applicants_pivot_sales.id as pivot_id'
            )
            ->where("applicants.temp_not_interested", "1");
        
            if ($id == "44") {
                $result1 = $result1->where("applicants.job_category", '=', "nurse");
            } elseif ($id == "45") {
                $result1 = $result1->where("applicants.job_category", "=", "non-nurse")
                    ->whereNotIn('applicants.applicant_job_title', ['nonnurse specialist']);
            } elseif ($id == "46") {
                $result1 = $result1->where([
                    "applicants.job_category" => "non-nurse",
                    "applicant_job_title" => "nonnurse specialist"
                ]);
            } elseif ($id == "47") {
                $result1 = $result1->where("applicants.job_category", "chef");
            }
    
        $result = $result1->where("applicants.is_no_job", "=", "0")
            ->where("applicants.status", "active")
            ->whereDate('applicants.updated_at', '<=', $edate)
            ->orderBy('applicants.updated_at', 'DESC')
            ->get();

        return datatables()->of($result)
            ->addColumn('applicant_postcode', function ($applicant) {
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                // if ($status_value == 'open' || $status_value == 'reject'){
                //     $postcode .= '<a href="/available-jobs/'.$applicant->id.'">';
                //     $postcode .= $applicant->applicant_postcode;
                //     $postcode .= '</a>';
                // } else {
                //     $postcode .= $applicant->applicant_postcode;
                // }
                return $applicant->applicant_postcode;
            })
			->addColumn("agent_name", function($applicant) {
                // Since we're fetching only the latest cv_note, this should be straightforward
                if ($applicant->cv_notes->isNotEmpty()) {
                    return $applicant->cv_notes->first()->user->name ?? null;
                }
                return '-';
            })
			->addColumn('applicant_notes', function($applicant){

                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                    
                $content = '';
                if ($status_value == 'open' || $status_value == 'reject'){

                    $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                    data-controls-modal="#clear_cv'.$applicant->id.'"
                                    data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                    data-target="#clear_cv' . $applicant->id . '">"'.$applicant->applicant_notes.'"</a>';
                    $content .= '<div id="clear_cv' . $applicant->id . '" class="modal fade" tabindex="-1">';
                    $content .= '<div class="modal-dialog modal-lg">';
                    $content .= '<div class="modal-content">';
                    $content .= '<div class="modal-header">';
                    $content .= '<h5 class="modal-title">Notes</h5>';
                    $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    $content .= '</div>';
                    $content .= '<form action="' . route('block_or_casual_notes') . '" method="POST" id="notes_form' . $applicant->id . '" class="form-horizontal">';
                    $content .= csrf_field();
                    $content .= '<div class="modal-body">';
                    $content .='<div id="notes_alert' . $applicant->id . '"></div>';

                    $content .= '<div id="sent_cv_alert' . $applicant->id . '"></div>';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Details</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant->id . '">';
                    $content .= '<input type="hidden" name="applicant_page' . $applicant->id . '" value="2_months_applicants">';
                    $content .= '<textarea name="details" id="sent_cv_details' . $applicant->id .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Choose type:</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<select name="reject_reason" class="form-control crm_select_reason" id="reason' . $applicant->id .'">';
                    $content .= '<option value="0">Select Reason</option>';
                    $content .= '<option value="1">Casual Notes</option>';
                    $content .= '<option value="2">Block Applicant Notes</option>';
                    $content .= '<option value="3">Temporary Not Interested Applicants Notes</option>';
                    $content .= '</select>';
                    $content .= '</div>';
                    $content .= '</div>';

                    $content .= '</div>';
                    $content .= '<div class="modal-footer">';
                    
                    $content .= '<button type="button" class="btn bg-dark legitRipple sent_cv_submit" data-dismiss="modal">Close</button>';
                    
                    $content .= '<button type="submit" data-note_key="' . $applicant->id . '" value="cv_sent_save" class="btn bg-teal legitRipple sent_cv_submit notes_form_submit">Save</button>';

                    $content .= '</div>';
                    $content .= '</form>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                } else {
                    $content .= $applicant->applicant_notes;
                }
               
                return $content;

            })
            ->addColumn("history", function ($applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                 data-controls-modal="#reject_history'.$applicant->id.'"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History';
                $content .= '<span class="font-weight-semibold">';
                $content .=  utf8_encode($applicant->applicant_name);
                $content .= '</span>';
                $content .= '</h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                return $content;
            })
            ->addColumn("updated_at",function($applicant){
                $updated_at = new DateTime($applicant->updated_at);
                $date = date_format($updated_at,'d F Y');
                    return $date;
            })
            ->addColumn('status', function ($applicant) {
                $status_value = 'open';
                $color_class = 'bg-teal-800';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                    $color_class = 'bg-slate-700';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                $status = '';
                $status .= '<h3>';
                $status .= '<span class="badge w-100 '.$color_class.'">';
                $status .= strtoupper($status_value);
                $status .= '</span>';
                $status .= '</h3>';
                return $status;
            })
			->addColumn('download', function ($applicant) {
                $filePath = $applicant->applicant_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->applicant_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->applicant_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadApplicantCv', $applicant->id);

                $download = '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';
                return $download;
            })
			->addColumn('updated_cv', function ($applicant) {
                $filePath = $applicant->updated_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->updated_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->updated_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadUpdatedApplicantCv', $applicant->id);
                return
                    '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';

            })
			->editColumn('applicant_job_title', function ($applicant) {
                $job_title_desc='';
                if($applicant->job_title_prof!=null)
                {
                    $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $applicant->job_title_prof)->first();
                                $job_title_desc = $applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
                }
                else
                {
                    
                    $job_title_desc = $applicant->applicant_job_title;
                }
                return strtoupper($job_title_desc);

            })

			->addColumn('upload', function ($applicant) {
                return
                '<a href="#"
                data-controls-modal="#import_applicant_cv" class="import_cv"
                data-backdrop="static"
                data-keyboard="false" data-toggle="modal" data-id="'.$applicant->id.'"
                data-target="#import_applicant_cv">
                <i class="fas fa-file-upload text-teal-400"></i>
                &nbsp;</a>';
            })
            ->setRowClass(function ($applicant) {
                $row_class = '';
                if ($applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
                } elseif ($applicant->is_no_job == '1') {
                    $row_class = 'class_noJob';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success';
                            break;
                        } elseif ($value->status == 'disable') {
                            $row_class = 'class_danger';
                        }
                    }
                }
                /*** logic before open-applicant feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $row_class = 'class_success'; // status: sent
                        break;
                    } elseif ($value->status == 'disable') {
                        $row_class = 'class_danger'; // status: reject
                    } elseif ($value->status == 'paid') {
                        $row_class = 'class_dark';
                        break;
                    }
                }
                */
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','history','download','updated_cv','upload','applicant_notes','updated_at','status','applicant_postcode'])
            ->make(true);
    }

    public function getlast2MonthsAppBlocked($id)
    {
		       // date_default_timezone_set('Europe/London');

        $end_date = Carbon::now();
        $edate21 = $end_date->subMonth(1)->subDays(6); // 16 + 21 + excluding last_day . 00:00:00
        $edate = $edate21->format('Y-m-d');

        $result1 = Applicant::with(['cv_notes' => function($query) {
                        $query->select('status', 'applicant_id', 'sale_id', 'user_id')
                            ->with(['user:id,name'])->latest(); // Eager load the 'user' relationship and only select 'id' and 'name'
                    }])
            ->leftJoin('applicants_pivot_sales', 'applicants_pivot_sales.applicant_id', '=', 'applicants.id')
            ->leftJoin('sales', 'sales.id', '=', 'applicants_pivot_sales.sales_id')
            ->leftJoin('notes_for_range_applicants', 'applicants_pivot_sales.id', '=', 'notes_for_range_applicants.applicants_pivot_sales_id')
            ->leftJoin('offices', 'offices.id', '=', 'sales.head_office')
            ->leftJoin('units', 'units.id', '=', 'sales.head_office_unit')
            ->select(
                'applicants.id',
                'applicants.updated_at',
                'applicants.temp_not_interested',
                'applicants.applicant_added_time',
                'applicants.is_no_job',
                'applicants.applicant_name',
                'applicants.applicant_job_title',
                'applicants.job_title_prof',
                'applicants.job_category',
                'applicants.applicant_postcode',
                'applicants.applicant_phone',
                'applicants.applicant_homePhone',
                'applicants.applicant_source',
                'applicants.applicant_email',
                'applicants.applicant_notes',
                'applicants.paid_status',
                'applicants.applicant_cv',
                'applicants.updated_cv',
                'applicants_pivot_sales.sales_id as pivot_sale_id',
                'applicants_pivot_sales.id as pivot_id'
            )
            ->where("applicants.temp_not_interested", "0");
        
            if ($id == "44") {
                $result1 = $result1->where("applicants.job_category", '=', "nurse");
            } elseif ($id == "45") {
                $result1 = $result1->where("applicants.job_category", "=", "non-nurse")
                    ->whereNotIn('applicants.applicant_job_title', ['nonnurse specialist']);
            } elseif ($id == "46") {
                $result1 = $result1->where([
                    "applicants.job_category" => "non-nurse",
                    "applicant_job_title" => "nonnurse specialist"
                ]);
            } elseif ($id == "47") {
                $result1 = $result1->where("applicants.job_category", "chef");
            }
    
        $result = $result1->where("applicants.is_no_job", "=", "0")
            ->where("applicants.is_blocked", "=", "1")
            ->where("applicants.status", "active")
            ->whereDate('applicants.updated_at', '<=', $edate)
            ->orderBy('applicants.updated_at', 'DESC')
            ->get();

        return datatables()->of($result)
            ->addColumn('applicant_postcode', function ($applicant) {
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                // if ($status_value == 'open' || $status_value == 'reject'){
                //     $postcode .= '<a href="/available-jobs/'.$applicant->id.'">';
                //     $postcode .= $applicant->applicant_postcode;
                //     $postcode .= '</a>';
                // } else {
                //     $postcode .= $applicant->applicant_postcode;
                // }
                return $applicant->applicant_postcode;
            })
			->addColumn("agent_name", function($applicant) {
                // Since we're fetching only the latest cv_note, this should be straightforward
                if ($applicant->cv_notes->isNotEmpty()) {
                    return $applicant->cv_notes->first()->user->name ?? null;
                }
                return '-';
            })
			->addColumn('applicant_notes', function($applicant){

                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                    
                $content = '';
                if ($status_value == 'open' || $status_value == 'reject'){

                    $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                    data-controls-modal="#clear_cv'.$applicant->id.'"
                                    data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                    data-target="#clear_cv' . $applicant->id . '">"'.$applicant->applicant_notes.'"</a>';
                    $content .= '<div id="clear_cv' . $applicant->id . '" class="modal fade" tabindex="-1">';
                    $content .= '<div class="modal-dialog modal-lg">';
                    $content .= '<div class="modal-content">';
                    $content .= '<div class="modal-header">';
                    $content .= '<h5 class="modal-title">Notes</h5>';
                    $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                    $content .= '</div>';
                    $content .= '<form action="' . route('block_or_casual_notes') . '" method="POST" id="notes_form' . $applicant->id . '" class="form-horizontal">';
                    $content .= csrf_field();
                    $content .= '<div class="modal-body">';
                    $content .='<div id="notes_alert' . $applicant->id . '"></div>';

                    $content .= '<div id="sent_cv_alert' . $applicant->id . '"></div>';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Details</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant->id . '">';
                    $content .= '<input type="hidden" name="applicant_page' . $applicant->id . '" value="2_months_applicants">';
                    $content .= '<textarea name="details" id="sent_cv_details' . $applicant->id .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '<div class="form-group row">';
                    $content .= '<label class="col-form-label col-sm-3">Choose type:</label>';
                    $content .= '<div class="col-sm-9">';
                    $content .= '<select name="reject_reason" class="form-control crm_select_reason" id="reason' . $applicant->id .'">';
                    $content .= '<option value="0">Select Reason</option>';
                    $content .= '<option value="1">Casual Notes</option>';
                    $content .= '<option value="2">Block Applicant Notes</option>';
                    $content .= '<option value="3">Temporary Not Interested Applicants Notes</option>';
                    $content .= '</select>';
                    $content .= '</div>';
                    $content .= '</div>';

                    $content .= '</div>';
                    $content .= '<div class="modal-footer">';
                    
                    $content .= '<button type="button" class="btn bg-dark legitRipple sent_cv_submit" data-dismiss="modal">Close</button>';
                    
                    $content .= '<button type="submit" data-note_key="' . $applicant->id . '" value="cv_sent_save" class="btn bg-teal legitRipple sent_cv_submit notes_form_submit">Save</button>';

                    $content .= '</div>';
                    $content .= '</form>';
                    $content .= '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                } else {
                    $content .= $applicant->applicant_notes;
                }
               
                return $content;

            })
            ->addColumn("history", function ($applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                 data-controls-modal="#reject_history'.$applicant->id.'"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History';
                $content .= '<span class="font-weight-semibold">';
                $content .=  utf8_encode($applicant->applicant_name);
                $content .= '</span>';
                $content .= '</h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                return $content;
            })
            ->addColumn("updated_at",function($applicant){
                $updated_at = new DateTime($applicant->updated_at);
                $date = date_format($updated_at,'d F Y');
                    return $date;
				
				  //  $updated_at = Carbon::parse($applicant->updated_at)->timestamp;
                    //return $updated_at;
            })
            ->addColumn('status', function ($applicant) {
                $status_value = 'open';
                $color_class = 'bg-teal-800';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                    $color_class = 'bg-slate-700';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                /*** logic before open-applicant-cv feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $status_value = 'sent';
                        break;
                    } elseif ($value->status == 'disable') {
                        $status_value = 'reject';
                    } elseif ($value->status == 'paid') {
                        $status_value = 'paid';
                        $color_class = 'bg-slate-700';
                        break;
                    }
                }
                */
                $status = '';
                $status .= '<h3>';
                $status .= '<span class="badge w-100 '.$color_class.'">';
                $status .= strtoupper($status_value);
                $status .= '</span>';
                $status .= '</h3>';
                return $status;
            })
			->addColumn('download', function ($applicant) {
                $filePath = $applicant->applicant_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->applicant_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->applicant_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadApplicantCv', $applicant->id);

                $download = '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';
                return $download;
            })
			->addColumn('updated_cv', function ($applicant) {
                $filePath = $applicant->updated_cv;

                // Check if the file exists
                $disabled = (!file_exists($filePath) || $applicant->updated_cv == null ) ? 'disabled' : '';
                $disabled_color = (!file_exists($filePath) || $applicant->updated_cv == null) ? 'text-grey-400' : 'text-teal-400';
                $href = ($disabled == 'disabled') ? 'javascript:void(0);' : route('downloadUpdatedApplicantCv', $applicant->id);
                return
                    '<a class="download-link ' . $disabled . '" href="' . $href . '">
                       <i class="fas fa-file-download '. $disabled_color .'"></i>
                    </a>';

            })
			->editColumn('applicant_job_title', function ($applicant) {
                $job_title_desc='';
                if($applicant->job_title_prof!=null)
                {
                    $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $applicant->job_title_prof)->first();
                                $job_title_desc = $applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
                }
                else
                {
                    
                    $job_title_desc = $applicant->applicant_job_title;
                }
                return strtoupper($job_title_desc);

            })

			->addColumn('upload', function ($applicant) {
                return
                '<a href="#"
                data-controls-modal="#import_applicant_cv" class="import_cv"
                data-backdrop="static"
                data-keyboard="false" data-toggle="modal" data-id="'.$applicant->id.'"
                data-target="#import_applicant_cv">
                <i class="fas fa-file-upload text-teal-400"></i>
                &nbsp;</a>';
            })
            ->setRowClass(function ($applicant) {
                $row_class = '';
                if ($applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
                } elseif ($applicant->is_no_job == '1') {
                    $row_class = 'class_noJob';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success';
                            break;
                        } elseif ($value->status == 'disable') {
                            $row_class = 'class_danger';
                        }
                    }
                }
                /*** logic before open-applicant feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $row_class = 'class_success'; // status: sent
                        break;
                    } elseif ($value->status == 'disable') {
                        $row_class = 'class_danger'; // status: reject
                    } elseif ($value->status == 'paid') {
                        $row_class = 'class_dark';
                        break;
                    }
                }
                */
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','history','download','updated_cv','upload','applicant_notes','updated_at','status','applicant_postcode'])
            ->make(true);
    }
	
    public function getAllCrmRejectedApplicantCv()
    {
        return view('administrator.resource.all_rejected_applicants');
    } 

	public function getTempNotInterestedApplicants(){
        $interval = 60;
        return view('administrator.resource.temp_not_interested', compact('interval'));
    }
	
	public function get_temp_not_interested_applicants_ajax()
    {
        $end_date = Carbon::now();
        //$edate21 = $end_date->subDays(31); // 9 + 21 + excluding last_day . 00:00:00
        $edate = $end_date->format('Y-m-d');

        $start_date = $end_date->subMonths(60);
        $sdate = $start_date->format('Y-m-d');
        $result = Applicant::with('cv_notes')
            ->select('applicants.id', 'applicants.updated_at', 'applicants.applicant_added_time', 'applicants.applicant_name', 'applicants.applicant_job_title','applicants.job_title_prof', 'applicants.job_category', 'applicants.applicant_postcode', 'applicants.applicant_phone','applicants.applicant_homePhone','applicants.applicant_source','applicants.applicant_notes','applicants.paid_status')
            ->leftJoin('applicants_pivot_sales', 'applicants.id', '=', 'applicants_pivot_sales.applicant_id')
            //            ->whereBetween('applicants.updated_at', [$sdate, $edate])
            ->whereDate('applicants.updated_at', '<=', $edate)
            ->where("applicants.status", "=", "active")
            ->where("applicants.temp_not_interested", "=", "1")
            //            ->where("applicants.is_in_nurse_home", "=", "no")
            // ->where("applicants.job_category", "=", "nurse")
            ->where('applicants_pivot_sales.applicant_id', '=', NULL);

        return datatables()->of($result)
            ->addColumn('applicant_postcode', function ($applicant) {
                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                /*** logic before open-appllicant-cv feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $status_value = 'sent';
                        break;
                    } elseif ($value->status == 'disable') {
                        $status_value = 'reject';
                    } elseif ($value->status == 'paid') {
                        $status_value = 'paid';
                        break;
                    }
                }
                */
                if ($status_value == 'open' || $status_value == 'reject'){
                    $postcode .= '<a href="/available-jobs/'.$applicant->id.'">';
                    $postcode .= $applicant->applicant_postcode;
                    $postcode .= '</a>';
                } else {
                    $postcode .= $applicant->applicant_postcode;
                }
                return $postcode;
            })
            ->addColumn('applicant_notes', function($applicant){

                $status_value = 'open';
                $postcode = '';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                   
                
                    
                $content = '';


                /*** Export Applicants Modal */
                $content .= '<div id="export_temp_not_interest_applicants" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-sm">';
                $content .= '<div class="modal-content">';

                $content .= '<div class="modal-header">';
                $content .= '<h3 class="modal-title">Export Applicants</h3>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body">';
                $content .= '<form action="' . route('export_temp_not_interested_applicants') . '" method="POST" id="export_block_applicants" class="form-horizontal">';
                $content .= csrf_field();
                // $content .= '<input type="hidden" name="applicant_id" value="' . $applicant->id . '">';
                // $content .= '<input type="hidden" name="sale_id" value="' . $applicant->sale_id . '">';
                $content .= '<div class="mb-4">';
                $content .= '<div class="input-group">';
                $content .= '<span class="input-group-prepend">';
                $content .= '<span class="input-group-text"><i class="icon-calendar5"></i></span>';
                $content .= '</span>';
                $content .= '<input type="text" class="form-control pickadate-year" name="start_date" id="start_date" placeholder="Select From Date">';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '<div class="mb-4">';
                $content .= '<div class="input-group">';
                $content .= '<span class="input-group-prepend">';
                $content .= '<span class="input-group-text"><i class="icon-calendar5"></i></span>';
                $content .= '</span>';
            //                $content .= '<input type="text" class="form-control time_pickerrrr" id="anytime-time'.$applicant->id.'-'.$applicant->sale_id.'" name="schedule_time" placeholder="Select Schedule Time e.g., 00:00">';
                $content .= '<input type="text" class="form-control pickadate-year" name="end_date" id="end_date" placeholder="Select To Date">';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '<button type="submit" class="btn bg-teal legitRipple btn-block">Submit</button>';
                $content .= '</form>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';



                 /*** Unblock Applicants Modal */
                 $content .= '<div id="applicant_action" class="modal fade" tabindex="-1">';
                 $content .= '<div class="modal-dialog modal-sm">';
                 $content .= '<div class="modal-content">';
 
                 $content .= '<div class="modal-header">';
                 $content .= '<h3 class="modal-title" >Unblock Applicants</h3>';
                 $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                 $content .= '</div>';
                 $content .= '<div class="modal-body">';
                 $content .= '<div id="applicant_unblock_alert"></div>';
                 $content .= '<form action="' . route('scheduleInterview') . '" method="POST" id="applicant_unblock_form" class="form-horizontal">';
                 $content .= csrf_field();
                 // $content .= '<input type="hidden" name="applicant_id" value="' . $applicant->id . '">';
                 // $content .= '<input type="hidden" name="sale_id" value="' . $applicant->sale_id . '">';
                 $content .= '<div class="mb-4">';
                 $content .= '<div class="input-group">';
                 $content .= '<span class="input-group-prepend">';
                 $content .= '<span class="input-group-text"><i class="icon-calendar5"></i></span>';
                 $content .= '</span>';
                 $content .= '<input type="text" class="form-control pickadate-year" name="from_date" id="from_date" placeholder="Select From Date">';
                 $content .= '</div>';
                 $content .= '</div>';
                 $content .= '<div class="mb-4">';
                 $content .= '<div class="input-group">';
                 $content .= '<span class="input-group-prepend">';
                 $content .= '<span class="input-group-text"><i class="icon-calendar5"></i></span>';
                 $content .= '</span>';
                //                $content .= '<input type="text" class="form-control time_pickerrrr" id="anytime-time'.$applicant->id.'-'.$applicant->sale_id.'" name="schedule_time" placeholder="Select Schedule Time e.g., 00:00">';
                 $content .= '<input type="text" class="form-control pickadate-year" name="to_date" id="to_date" placeholder="Select To Date">';
                 $content .= '</div>';
                 $content .= '</div>';
                 $content .= '<button type="submit" class="btn bg-teal legitRipple btn-block applicant_action_submit" data-app_sale="">Submit</button>';
                 $content .= '</form>';
                 $content .= '</div>';
                 $content .= '</div>';
                 $content .= '</div>';
                 $content .= '</div>';



                if ($status_value == 'open' || $status_value == 'reject'){

                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                 data-controls-modal="#clear_cv'.$applicant->id.'"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#clear_cv' . $applicant->id . '">"'.$applicant->applicant_notes.'"</a>';
                $content .= '<div id="clear_cv' . $applicant->id . '" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h5 class="modal-title">Notes</h5>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<form action="' . route('interested_notes') . '" method="POST" class="form-horizontal">';
                $content .= csrf_field();
                $content .= '<div class="modal-body">';
                $content .= '<div id="sent_cv_alert' . $applicant->id . '"></div>';
                $content .= '<div class="form-group row">';
                $content .= '<label class="col-form-label col-sm-3">Details</label>';
                $content .= '<div class="col-sm-9">';
                $content .= '<input type="hidden" name="applicant_hidden_id" value="' . $applicant->id . '">';
                $content .= '<textarea name="details" id="sent_cv_details' . $applicant->id .'" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>';
                $content .= '</div>';
                $content .= '</div>';
                 

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                   
                    $content .= '<button type="button" class="btn bg-dark legitRipple sent_cv_submit" data-dismiss="modal">Close</button>';
                    
                    $content .= '<button type="submit" value="cv_sent_save" class="btn bg-teal legitRipple sent_cv_submit">Interested</button>';

                $content .= '</div>';
                $content .= '</form>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
            } else {
                $content .= $applicant->applicant_notes;
                }
               
                return $content;

            })
            ->addColumn("history", function ($applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"
                                 data-controls-modal="#reject_history'.$applicant->id.'"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History';
                $content .= '<span class="font-weight-semibold">';
                $content .=  utf8_encode($applicant->applicant_name);
                $content .= '</span>';
                $content .= '</h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                return $content;
            })
            ->editColumn('applicant_job_title', function ($applicant) {
                $job_title_desc='';
                if($applicant->job_title_prof!=null)
                {
                    $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $applicant->job_title_prof)->first();
                                $job_title_desc = $applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
                }
                else
                {
                    
                    $job_title_desc = $applicant->applicant_job_title;
                }
                return $job_title_desc;
    
         })
            ->addColumn("updated_at",function($applicant){
                $updated_at = new DateTime($applicant->updated_at);
                $date = date_format($updated_at,'d F Y');
                    return $date;
            })
            ->addColumn('status', function ($applicant) {
                $status_value = 'open';
                $color_class = 'bg-teal-800';
                if ($applicant->paid_status == 'close') {
                    $status_value = 'paid';
                    $color_class = 'bg-slate-700';
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $status_value = 'sent';
                            break;
                        } elseif ($value->status == 'disable') {
                            $status_value = 'reject';
                        }
                    }
                }
                /*** logic before open-applicant-cv feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $status_value = 'sent';
                        break;
                    } elseif ($value->status == 'disable') {
                        $status_value = 'reject';
                    } elseif ($value->status == 'paid') {
                        $status_value = 'paid';
                        $color_class = 'bg-slate-700';
                        break;
                    }
                }
                */

                








                $status = '';
                $status .= '<h3>';
                $status .= '<span class="badge w-100 '.$color_class.'">';
                $status .= strtoupper($status_value);
                $status .= '</span>';
                $status .= '</h3>';
                return $status;
            })
            ->setRowClass(function ($applicant) {
                $row_class = '';
                if ($applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success';
                            break;
                        } elseif ($value->status == 'disable') {
                            $row_class = 'class_danger';
                        }
                    }
                }
                /*** logic before open-applicant feature
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $row_class = 'class_success'; // status: sent
                        break;
                    } elseif ($value->status == 'disable') {
                        $row_class = 'class_danger'; // status: reject
                    } elseif ($value->status == 'paid') {
                        $row_class = 'class_dark';
                        break;
                    }
                }
                */
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','history','applicant_notes','updated_at','status','applicant_postcode'])
            ->make(true);
    }
	
    public function exportAllCrmRejectedApplicantCv(Request $request)
    {
        
        // $end_date = Carbon::now();
        // $edate7 = $end_date->subDays(10);
        // $edate = $end_date->format('Y-m-d') . " 23:59:59";
        // $start_date = $end_date->subDays(42);
        // $sdate = $start_date->format('Y-m-d') . " 00:00:00";
        // $sdate = '2020-01-01 00:00:00';



        $start_date = $request->input('start_date');
        $sdate = Carbon::parse($start_date)->format('Y-m-d'). " 00:00:01";

        $end_date = $request->input('end_date');
        $edate = Carbon::parse($end_date)->format('Y-m-d'). " 23:59:59";   
        // echo $sdate.' and'.$edate;exit(); 
        $job_category='nurse';
        return Excel::download(new AllRejectedApplicantsExport($sdate,$edate,$job_category), 'applicants.csv');


    }
 
    public function getallCrmRejectedApplicantCvAjax()
    {
        $end_date = Carbon::now();
        // $edate7 = $end_date->subDays(10);
        $edate = $end_date->format('Y-m-d') . " 23:59:59";
        $start_date = $end_date->subDays(42);
        $sdate = $start_date->format('Y-m-d') . " 00:00:00";
        $crm_rejected_applicants = Applicant::with('cv_notes')
        ->join('crm_notes', 'applicants.id', '=', 'crm_notes.applicant_id')
        ->join('history', function ($join) {
            $join->on('crm_notes.applicant_id', '=', 'history.applicant_id');
            $join->on('crm_notes.sales_id', '=', 'history.sale_id');
        })->select('crm_notes.details', 'crm_notes.crm_added_date', 'crm_notes.crm_added_time',
            'applicants.id', 'applicants.applicant_name', 'applicants.applicant_job_title', 'applicants.job_title_prof',
            'applicants.job_category',
            'applicants.applicant_postcode', 'applicants.applicant_phone', 'applicants.paid_status',
            'applicants.applicant_homePhone','applicants.applicant_source',
            DB::raw('
        CASE 
            WHEN history.sub_stage="crm_reject"
            THEN "Rejected CV" 
            WHEN history.sub_stage="crm_request_reject"
            THEN "Rejected By Request"
            WHEN history.sub_stage="crm_declined"
            THEN "Declined"
            WHEN history.sub_stage="crm_interview_not_attended"
            THEN "Not Attended"
            WHEN history.sub_stage="crm_start_date_hold" OR history.sub_stage = "crm_start_date_hold_save"
            THEN "Start Date Hold"
            WHEN history.sub_stage="crm_dispute"
            THEN "Dispute" 
            END AS sub_stage'))->whereIn("history.sub_stage", ["crm_dispute","crm_interview_not_attended","crm_declined","crm_request_reject","crm_reject","crm_start_date_hold", "crm_start_date_hold_save"])
            ->whereIn("crm_notes.moved_tab_to", ["dispute","interview_not_attended","declined","request_reject","cv_sent_reject","start_date_hold", "start_date_hold_save"])
            //->whereBetween('crm_notes.updated_at', [$sdate, $edate])
        ->where([
            "applicants.status" => "active", "history.status" => "active"
        ])->orderBy("crm_notes.id","DESC")->groupBy('applicants.applicant_phone');

        return datatables()->of($crm_rejected_applicants)
            ->addColumn("applicant_postcode",function($crm_rejected_applicant) {
                if ($crm_rejected_applicant->paid_status == 'close') {
                    return $crm_rejected_applicant->applicant_postcode;
                } 
                else {
                    foreach ($crm_rejected_applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            return $crm_rejected_applicant->applicant_postcode;
                        }
                    }
                    return '<a href="/available-jobs/'.$crm_rejected_applicant->id.'">'.$crm_rejected_applicant->applicant_postcode.'</a>';
                }
            })
			->editColumn('applicant_job_title', function ($crm_rejected_applicant) {
                $job_title_desc='';
                if($crm_rejected_applicant->job_title_prof!=null)
                {
                    $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $crm_rejected_applicant->job_title_prof)->first();
                                $job_title_desc = $crm_rejected_applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
                }
                else
                {
                    
                    $job_title_desc = $crm_rejected_applicant->applicant_job_title;
                }
                return $job_title_desc;
    
         })
            ->addColumn('history', function ($crm_rejected_applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$crm_rejected_applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$crm_rejected_applicant->id.'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $crm_rejected_applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$crm_rejected_applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$crm_rejected_applicant->applicant_name.'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$crm_rejected_applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
				
				/*** Export Applicants Modal */
                $content .= '<div id="export_all_rejected_applicant_action" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-sm">';
                $content .= '<div class="modal-content">';

                $content .= '<div class="modal-header">';
                $content .= '<h3 class="modal-title">Export Applicants</h3>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body">';
                $content .= '<form action="' . route('export_all_rejected_applicants') . '" method="POST" id="export_block_applicants" class="form-horizontal">';
                $content .= csrf_field();
                // $content .= '<input type="hidden" name="applicant_id" value="' . $applicant->id . '">';
                // $content .= '<input type="hidden" name="sale_id" value="' . $applicant->sale_id . '">';
                $content .= '<div class="mb-4">';
                $content .= '<div class="input-group">';
                $content .= '<span class="input-group-prepend">';
                $content .= '<span class="input-group-text"><i class="icon-calendar5"></i></span>';
                $content .= '</span>';
                $content .= '<input type="text" class="form-control pickadate-year" name="start_date" id="start_date" placeholder="Select From Date">';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '<div class="mb-4">';
                $content .= '<div class="input-group">';
                $content .= '<span class="input-group-prepend">';
                $content .= '<span class="input-group-text"><i class="icon-calendar5"></i></span>';
                $content .= '</span>';
            //                $content .= '<input type="text" class="form-control time_pickerrrr" id="anytime-time'.$applicant->id.'-'.$applicant->sale_id.'" name="schedule_time" placeholder="Select Schedule Time e.g., 00:00">';
                $content .= '<input type="text" class="form-control pickadate-year" name="end_date" id="end_date" placeholder="Select To Date">';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '<button type="submit" class="btn bg-teal legitRipple btn-block">Submit</button>';
                $content .= '</form>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
				
                return $content;

            })
            ->setRowClass(function ($crm_rejected_applicant) {
                $row_class = '';
                if ($crm_rejected_applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($crm_rejected_applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success'; // status: sent
                            break;
                        }
                    }
                }
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','applicant_postcode', 'history'])
            ->make(true);
    }
	
    public function storeUnblockNotes(Request $request)
    {
        $applicant_id = $request->Input('applicant_hidden_id');
        $applicant_notes = $request->Input('details');
        // $notes_reason = $request->Input('reject_reason');
        // $updated_at = Carbon::now();

            Applicant::where('id', $applicant_id)
            ->update(['is_blocked' => '0','applicant_notes' => $applicant_notes]);
        // echo $applicant_id.' notes: '.$applicant_notes.' reason : '.$notes_reason.' date: '.$end_date;exit();
        // return redirect()->route('getlast2MonthsApp');[+]
        $interval = 60;
        return view('administrator.resource.last_2_months_blocked_applicants', compact('interval'));
        $interval = 60;
        return view('administrator.resource.last_2_months_blocked_applicants', compact('interval'));
    }

	public function store_interested_notes(Request $request)
    {
        $applicant_id = $request->Input('applicant_hidden_id');
        $applicant_notes = $request->Input('details');
        // $notes_reason = $request->Input('reject_reason');
        // $updated_at = Carbon::now();

            Applicant::where('id', $applicant_id)
            ->update(['temp_not_interested' => '0','applicant_notes' => $applicant_notes]);
        // echo $applicant_id.' notes: '.$applicant_notes.' reason : '.$notes_reason.' date: '.$end_date;exit();
        // return redirect()->route('getlast2MonthsApp');[+]
        $interval = 60;
        return view('administrator.resource.temp_not_interested', compact('interval'));
    }
	 
    public function getCrmRejectedApplicantCv()
    {
        return view('administrator.resource.rejected_applicants');
    }
	
    public function Export_CrmRejectedApplicantCv()
    {
        // echo 'herer is crm export';exit();
        $crm_rejected_applicants = Applicant::with('cv_notes')
        ->join('crm_rejected_cv', 'applicants.id', '=', 'crm_rejected_cv.applicant_id')
        ->join('history', function ($join) {
            $join->on('crm_rejected_cv.applicant_id', '=', 'history.applicant_id');
            $join->on('crm_rejected_cv.sale_id', '=', 'history.sale_id');
        })->select('applicants.applicant_phone', 'applicants.applicant_name', 'applicants.applicant_homePhone','applicants.applicant_job_title',
        'applicants.applicant_postcode','applicants.applicant_source','applicants.applicant_notes')
        ->where([
            "applicants.status" => "active",
            "history.sub_stage" => "crm_reject", "history.status" => "active","is_blocked" => "0"
        ])->orderBy("crm_rejected_cv.id","DESC")->get();
        return Excel::download(new Applicants_nureses_7_days_export($crm_rejected_applicants), 'applicants.csv');
    }

    public function getCrmRejectedApplicantCvAjax()
    {
        $crm_rejected_applicants = Applicant::with('cv_notes')
            ->join('crm_rejected_cv', 'applicants.id', '=', 'crm_rejected_cv.applicant_id')
            ->join('history', function ($join) {
                $join->on('crm_rejected_cv.applicant_id', '=', 'history.applicant_id');
                $join->on('crm_rejected_cv.sale_id', '=', 'history.sale_id');
            })->select('crm_rejected_cv.crm_rejected_cv_note', 'crm_rejected_cv.crm_rejected_cv_date', 'crm_rejected_cv.crm_rejected_cv_time',
                'applicants.id', 'applicants.applicant_name', 'applicants.applicant_job_title','applicants.job_title_prof', 'applicants.job_category', 'applicants.paid_status',
                'applicants.applicant_postcode', 'applicants.applicant_phone',
                'applicants.applicant_homePhone','applicants.applicant_source')
            ->where([
                "applicants.status" => "active",
                "history.sub_stage" => "crm_reject", "history.status" => "active"
            ])->orderBy("crm_rejected_cv.id","DESC");
        return datatables()->of($crm_rejected_applicants)
            ->addColumn("applicant_postcode",function($crm_rejected_applicant) {
                if ($crm_rejected_applicant->paid_status == 'close') {
                    return $crm_rejected_applicant->applicant_postcode;
                } 
                else {
                    foreach ($crm_rejected_applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            return $crm_rejected_applicant->applicant_postcode;
                        }
                    }
                    return '<a href="/available-jobs/'.$crm_rejected_applicant->id.'">'.$crm_rejected_applicant->applicant_postcode.'</a>';
                }
            })
			->editColumn('applicant_job_title', function ($crm_rejected_applicant) {
                $job_title_desc='';
                if($crm_rejected_applicant->job_title_prof!=null)
                {
                    $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $crm_rejected_applicant->job_title_prof)->first();
                                $job_title_desc = $crm_rejected_applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
                }
                else
                {
                    
                    $job_title_desc = $crm_rejected_applicant->applicant_job_title;
                }
                return $job_title_desc;
    
         })
            ->addColumn('history', function ($crm_rejected_applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$crm_rejected_applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$crm_rejected_applicant->id.'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $crm_rejected_applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$crm_rejected_applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$crm_rejected_applicant->applicant_name.'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$crm_rejected_applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;

            })
            ->setRowClass(function ($crm_rejected_applicant) {
                $row_class = '';
                if ($crm_rejected_applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($crm_rejected_applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success'; // status: sent
                            break;
                        }
                    }
                }
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','applicant_postcode', 'history'])
            ->make(true);
    }

    public function getCrmRequestRejectedApplicantCv()
    {
        return view('administrator.resource.crm_rejected_request_applicants');
    }

	public function exportCrmRequestRejectedApplicantCv()
    {
        $crm_request_rejected_applicants = Applicant::with('cv_notes')
            ->join('crm_notes', 'applicants.id', '=', 'crm_notes.applicant_id')
            ->join('history', function ($join) {
                $join->on('crm_notes.applicant_id', '=', 'history.applicant_id');
                $join->on('crm_notes.sales_id', '=', 'history.sale_id');
            })->select('applicants.applicant_phone', 'applicants.applicant_name', 'applicants.applicant_homePhone','applicants.applicant_job_title',
            'applicants.applicant_postcode','applicants.applicant_source','applicants.applicant_notes')
            ->where([
                "applicants.status" => "active",
                "crm_notes.moved_tab_to" => "request_reject",
                "history.sub_stage" => "crm_request_reject", "history.status" => "active"
            ])->orderBy("crm_notes.id","DESC")->get();

        return Excel::download(new Applicants_nureses_7_days_export($crm_request_rejected_applicants), 'applicants.csv');

    }

    public function getCrmRequestRejectedApplicantCvAjax()
    {
        $crm_request_rejected_applicants = Applicant::with('cv_notes')
            ->join('crm_notes', 'applicants.id', '=', 'crm_notes.applicant_id')
            ->join('history', function ($join) {
                $join->on('crm_notes.applicant_id', '=', 'history.applicant_id');
                $join->on('crm_notes.sales_id', '=', 'history.sale_id');
            })->select('crm_notes.details', 'crm_notes.crm_added_date', 'crm_notes.crm_added_time',
                'applicants.id', 'applicants.applicant_name', 'applicants.applicant_job_title','applicants.job_title_prof',
                'applicants.job_category',
                'applicants.applicant_postcode', 'applicants.applicant_phone', 'applicants.paid_status',
                'applicants.applicant_homePhone','applicants.applicant_source')
            ->where([
                "applicants.status" => "active",
                "crm_notes.moved_tab_to" => "request_reject",
                "history.sub_stage" => "crm_request_reject", "history.status" => "active"
            ])->orderBy("crm_notes.id","DESC");

        return datatables()->of($crm_request_rejected_applicants)
            ->addColumn("applicant_postcode",function($crm_request_rejected_applicant) {
                if ($crm_request_rejected_applicant->paid_status == 'close') {
                    return $crm_request_rejected_applicant->applicant_postcode;
                } else {
                    foreach ($crm_request_rejected_applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            return $crm_request_rejected_applicant->applicant_postcode;
                        }
                    }
                    return '<a href="/available-jobs/' . $crm_request_rejected_applicant->id . '">' . $crm_request_rejected_applicant->applicant_postcode . '</a>';
                }
            })
			->editColumn('applicant_job_title', function ($crm_request_rejected_applicant) {
                $job_title_desc='';
                if($crm_request_rejected_applicant->job_title_prof!=null)
                {
                    $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $crm_request_rejected_applicant->job_title_prof)->first();
                                $job_title_desc = $crm_request_rejected_applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
                }
                else
                {
                    
                    $job_title_desc = $crm_request_rejected_applicant->applicant_job_title;
                }
                return $job_title_desc;
    
         })
            ->addColumn('history', function ($crm_request_rejected_applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$crm_request_rejected_applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$crm_request_rejected_applicant->id.'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $crm_request_rejected_applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$crm_request_rejected_applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$crm_request_rejected_applicant->applicant_name.'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$crm_request_rejected_applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;

            })
            ->setRowClass(function ($crm_request_rejected_applicants) {
                $row_class = '';
                if ($crm_request_rejected_applicants->paid_status == 'close') {
                    $row_class = 'class_dark';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($crm_request_rejected_applicants->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success'; // status: sent
                            break;
                        }
                    }
                }
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','applicant_postcode', 'history'])
            ->make(true);
    }

    public function potentialCallBackApplicants()
    {
        return view('administrator.resource.callback_applicants');
    }
	
    public function exportPotentialCallBackApplicants()
    {
        $auth_user = Auth::user();
        $callBackApplicants = Applicant::with('cv_notes')
            ->join('applicant_notes', 'applicant_notes.applicant_id', '=', 'applicants.id')
            ->select('applicants.applicant_phone', 'applicants.applicant_name', 'applicants.applicant_homePhone','applicants.applicant_job_title',
            'applicants.applicant_postcode','applicants.applicant_source','applicants.applicant_notes')
            ->where([
                'applicants.status' => 'active', "applicants.is_callback_enable" => "yes",
                'applicant_notes.moved_tab_to' => 'callback','applicant_notes.status' => 'active'
            ])->orderBy('applicant_notes.id', 'DESC')->get();
        return Excel::download(new Applicants_nureses_7_days_export($callBackApplicants), 'applicants.csv');

    }

    public function getPotentialCallBackApplicants()
    {
        $auth_user = Auth::user();
        $callBackApplicants = Applicant::with('cv_notes')
            ->join('applicant_notes', 'applicant_notes.applicant_id', '=', 'applicants.id')
            ->select("applicants.id", "applicants.applicant_job_title","applicants.job_title_prof", "applicants.applicant_name", "applicants.applicant_postcode",
                "applicants.applicant_phone", "applicants.applicant_homePhone", "applicants.job_category", "applicants.applicant_source", "applicants.paid_status",
                "applicant_notes.details", "applicant_notes.added_date", "applicant_notes.added_time")
            ->where([
                'applicants.status' => 'active', "applicants.is_callback_enable" => "yes",'applicants.is_no_job' => '0',
                'applicant_notes.moved_tab_to' => 'callback','applicant_notes.status' => 'active'
            ])->orderBy('applicant_notes.id', 'DESC');
        $raw_columns = ['applicant_job_title','history','postcode'];
        $datatable = datatables()->of($callBackApplicants)
			->editColumn('applicant_job_title', function ($applicant) {
            $job_title_desc='';
            if($applicant->job_title_prof!=null)
            {
                $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $applicant->job_title_prof)->first();
                            $job_title_desc = $applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
            }
            else
            {
                
                $job_title_desc = $applicant->applicant_job_title;
            }
            return $job_title_desc;

     })
            ->addColumn('postcode', function ($applicant) {
                if ($applicant->paid_status == 'close') {
                    return $applicant->applicant_postcode;
                } else {
                    foreach ($applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            return $applicant->applicant_postcode;
                        }
                    }
                    return '<a href="/available-jobs/'.$applicant->id.'" class="btn-link legitRipple">'.$applicant->applicant_postcode.'</a>';
                }
            })
            ->addColumn('history', function ($applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$applicant['id'].'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$applicant['applicant_name'].'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;
            });
        if ($auth_user->hasPermissionTo('resource_Potential-Callback_revert-callback')) {
            $datatable = $datatable->addColumn('checkbox', function ($applicant) {
                return '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                             <input type="checkbox" class="checkbox-index" value="'.$applicant->id.'">
                             <span></span>
                          </label>';
                })
                ->addColumn('action',  function ($applicant) {
                    return
                    '<a href="#"
                       class="btn bg-teal legitRipple"
                       data-controls-modal="#revert_call_back'.$applicant->id.'" data-backdrop="static"
                       data-keyboard="false" data-toggle="modal"
                       data-target="#revert_call_back'.$applicant->id.'">Revert
                    </a>
                    <div id="revert_call_back'.$applicant->id.'" class="modal fade" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Callback Notes Below:</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <form action="'.route('revertCallBackApplicants').'" method="GET" class="form-horizontal">
                                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                                    <div class="modal-body">
                                        <div class="form-group row">
                                            <label class="col-form-label col-sm-3">Details</label>
                                            <div class="col-sm-9">
                                                <input type="hidden" name="applicant_hidden_id" value="'.$applicant->id.'">
                                                <textarea name="details" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-link legitRipple" data-dismiss="modal">
                                            Close
                                        </button>
                                        <button type="submit" class="btn bg-teal legitRipple">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>';
                });
            $raw_columns = ['history','postcode','checkbox','action'];
        }
        return $datatable->setRowClass(function ($applicant) {
            $row_class = '';
            if ($applicant->paid_status == 'close') {
                $row_class = 'class_dark';
            } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $row_class = 'class_success'; // status: sent
                        break;
                    } elseif ($value->status == 'disable') {
                        $row_class = 'class_danger'; // status: reject
                    }
                }
            }
            return $row_class;
        })->rawColumns($raw_columns)->make(true);
    }

    public function getApplicantSentToCallBackList()
    {
        date_default_timezone_set('Europe/London');
        $audit_data['action'] = "Callback";
        $details = request()->details;
        $audit_data['applicant'] = $applicant_id = request()->applicant_hidden_id;

        $user = Auth::user();
        ApplicantNote::where('applicant_id', $applicant_id)
            ->whereIn('moved_tab_to', ['callback','revert_callback'])
            ->update(['status' => 'disable']);
        $applicant_note = new ApplicantNote();
        $applicant_note->user_id = $user->id;
        $applicant_note->applicant_id = $applicant_id;
        $audit_data['added_date'] = $applicant_note->added_date = date("jS F Y");
        $audit_data['added_time'] = $applicant_note->added_time = date("h:i A");
        $audit_data['details'] = $applicant_note->details = $details;
        $applicant_note->moved_tab_to = "callback";
        $applicant_note->status = "active";
        $applicant_note->save();
        $last_inserted_note = $applicant_note->id;
        if ($last_inserted_note > 0) {
            $note_uid = md5($last_inserted_note);
            ApplicantNote::where('id', $last_inserted_note)->update(['note_uid' => $note_uid]);
            Applicant::where(['id' => $applicant_id])->update(['is_callback_enable' => 'yes']);
            /*** activity log
             * $action_observer = new ActionObserver();
             * $action_observer->action($audit_data, 'Resource');
             */
            return Redirect::back()->with('potentialCallBackSuccess', 'Added');
        }
        return redirect()->back();
    }

    public function getApplicantRevertToSearchList()
    {
        date_default_timezone_set('Europe/London');
        $audit_data['action'] = "Revert Callback";
        $details = request()->details;
        $audit_data['applicant'] = $applicant_id = request()->applicant_hidden_id;
        $user = Auth::user();
        ApplicantNote::where('applicant_id', $applicant_id)
            ->whereIn('moved_tab_to', ['callback','revert_callback'])
            ->update(['status' => 'disable']);
        $applicant_note = new ApplicantNote();
        $applicant_note->user_id = $user->id;
        $applicant_note->applicant_id = $applicant_id;
        $audit_data['added_date'] = $applicant_note->added_date = date("jS F Y");
        $audit_data['added_time'] = $applicant_note->added_time = date("h:i A");
        $audit_data['details'] = $applicant_note->details = $details;
        $applicant_note->moved_tab_to = "revert_callback";
        $applicant_note->status = "active";
        $applicant_note->save();
        $last_inserted_note = $applicant_note->id;
        if ($last_inserted_note > 0) {
            $note_uid = md5($last_inserted_note);
            ApplicantNote::where('id', $last_inserted_note)->update(['note_uid' => $note_uid]);
            Applicant::where(['id' => $applicant_id])->update(['is_callback_enable' => 'no']);
            /*** activity log
             * $action_observer = new ActionObserver();
             * $action_observer->action($audit_data, 'Resource');
             */
            return Redirect::back()->with('potentialCallBackSuccess', 'Added');
        }
        return redirect()->back();
    }

    public function getCrmNotAttendedApplicantCv()
    {
        return view('administrator.resource.crm_not_attended_applicants');
    }

    public function exportCrmNotAttendedApplicantCv()
    {
        $crm_not_attended_applicants = Applicant::with('cv_notes')
            ->join('crm_notes', 'applicants.id', '=', 'crm_notes.applicant_id')
            ->join('history', function ($join) {
                $join->on('crm_notes.applicant_id', '=', 'history.applicant_id');
                $join->on('crm_notes.sales_id', '=', 'history.sale_id');
            })
            ->select(
                'applicants.applicant_phone', 'applicants.applicant_name', 'applicants.applicant_homePhone','applicants.applicant_job_title',
            'applicants.applicant_postcode','applicants.applicant_source','applicants.applicant_notes')
            ->where([
                "applicants.status" => "active",
                "crm_notes.moved_tab_to" => "interview_not_attended",
                "history.sub_stage" => "crm_interview_not_attended", "history.status" => "active"
            ])->orderBy("crm_notes.id","DESC")->get();
        return Excel::download(new Applicants_nureses_7_days_export($crm_not_attended_applicants), 'applicants.csv');

    }

    public function getCrmNotAttendedApplicantCvAjax()
    {
        $crm_not_attended_applicants = Applicant::with('cv_notes')
            ->join('crm_notes', 'applicants.id', '=', 'crm_notes.applicant_id')
            ->join('history', function ($join) {
                $join->on('crm_notes.applicant_id', '=', 'history.applicant_id');
                $join->on('crm_notes.sales_id', '=', 'history.sale_id');
            })
            ->select(
                'crm_notes.details', 'crm_notes.crm_added_date', 'crm_notes.crm_added_time',
                'applicants.id', 'applicants.applicant_name', 'applicants.applicant_job_title','applicants.job_title_prof', 'applicants.job_category',
                'applicants.applicant_postcode', 'applicants.applicant_phone', 'applicants.paid_status',
                'applicants.applicant_homePhone','applicants.applicant_source')
            ->where([
                "applicants.status" => "active",
                "crm_notes.moved_tab_to" => "interview_not_attended",
                "history.sub_stage" => "crm_interview_not_attended", "history.status" => "active"
            ])->orderBy("crm_notes.id","DESC");

        return datatables()->of($crm_not_attended_applicants)
            ->addColumn("applicant_postcode",function($crm_not_attended_applicant) {
                if ($crm_not_attended_applicant->paid_status == 'close') {
                    return $crm_not_attended_applicant->applicant_postcode;
                } else {
                    foreach ($crm_not_attended_applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            return $crm_not_attended_applicant->applicant_postcode;
                        }
                    }
                    return '<a href="/available-jobs/' . $crm_not_attended_applicant->id . '">' . $crm_not_attended_applicant->applicant_postcode . '</a>';
                }
            })
			->editColumn('applicant_job_title', function ($crm_not_attended_applicant) {
                $job_title_desc='';
                if($crm_not_attended_applicant->job_title_prof!=null)
                {
                    $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $crm_not_attended_applicant->job_title_prof)->first();
                                $job_title_desc = $crm_not_attended_applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
                }
                else
                {
                    
                    $job_title_desc = $crm_not_attended_applicant->applicant_job_title;
                }
                return $job_title_desc;
    
         })
            ->addColumn('history', function ($crm_not_attended_applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$crm_not_attended_applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$crm_not_attended_applicant->id.'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $crm_not_attended_applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$crm_not_attended_applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$crm_not_attended_applicant->applicant_name.'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$crm_not_attended_applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;

            })
            ->setRowClass(function ($crm_not_attended_applicant) {
                $row_class = '';
                if ($crm_not_attended_applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($crm_not_attended_applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success'; // status: sent
                            break;
                        }
                    }
                }
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','applicant_postcode', 'history'])
            ->make(true);
    }

    public function getCrmStartDateHoldApplicantCv()
    {
        return view('administrator.resource.crm_start_date_hold_applicants');
    }
	
	public function exportCrmStartDateHoldApplicantCv()
    {
        $crm_start_date_hold_applicants = Applicant::with('cv_notes')
            ->join('crm_notes', 'applicants.id', '=', 'crm_notes.applicant_id')
            ->join('history', function ($join) {
                $join->on('crm_notes.applicant_id', '=', 'history.applicant_id');
                $join->on('crm_notes.sales_id', '=', 'history.sale_id');
            })->select('applicants.applicant_phone', 'applicants.applicant_name', 'applicants.applicant_homePhone','applicants.applicant_job_title',
            'applicants.applicant_postcode','applicants.applicant_source','applicants.applicant_notes')
            ->where([
                'applicants.status' => 'active',
                'crm_notes.moved_tab_to' => 'start_date_hold',
                'history.status' => 'active',
            ])->whereIn('history.sub_stage', ['crm_start_date_hold', 'crm_start_date_hold_save'])
            ->orderBy("crm_notes.id","DESC")->get();
        return Excel::download(new Applicants_nureses_7_days_export($crm_start_date_hold_applicants), 'applicants.csv');

    }

    public function getCrmStartDateHoldApplicantCvAjax()
    {
        /*** query for crm: start date hold tab */

        $crm_start_date_hold_applicants = Applicant::with('cv_notes')
            ->join('crm_notes', 'applicants.id', '=', 'crm_notes.applicant_id')
            ->join('history', function ($join) {
                $join->on('crm_notes.applicant_id', '=', 'history.applicant_id');
                $join->on('crm_notes.sales_id', '=', 'history.sale_id');
            })->select('crm_notes.details', 'crm_notes.crm_added_date', 'crm_notes.crm_added_time',
                'applicants.id', 'applicants.applicant_name', 'applicants.applicant_job_title','applicants.job_title_prof', 'applicants.job_category',
                'applicants.applicant_postcode', 'applicants.applicant_phone', 'applicants.paid_status',
                'applicants.applicant_homePhone','applicants.applicant_source')
            ->where([
                'applicants.status' => 'active',
                'crm_notes.moved_tab_to' => 'start_date_hold',
                'history.status' => 'active',
            ])->whereIn('history.sub_stage', ['crm_start_date_hold', 'crm_start_date_hold_save'])
            ->orderBy("crm_notes.id","DESC");

        return datatables()->of($crm_start_date_hold_applicants)
            ->addColumn("applicant_postcode",function($crm_start_date_hold_applicant) {
                if ($crm_start_date_hold_applicant->paid_status == 'close') {
                    return $crm_start_date_hold_applicant->applicant_postcode;
                } else {
                    foreach ($crm_start_date_hold_applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            return $crm_start_date_hold_applicant->applicant_postcode;
                        }
                    }
                    return '<a href="/available-jobs/' . $crm_start_date_hold_applicant->id . '">' . $crm_start_date_hold_applicant->applicant_postcode . '</a>';
                }
            })
			->editColumn('applicant_job_title', function ($crm_start_date_hold_applicant) {
                $job_title_desc='';
                if($crm_start_date_hold_applicant->job_title_prof!=null)
                {
                    $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $crm_start_date_hold_applicant->job_title_prof)->first();
                                $job_title_desc = $crm_start_date_hold_applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
                }
                else
                {
                    
                    $job_title_desc = $crm_start_date_hold_applicant->applicant_job_title;
                }
                return $job_title_desc;
    
         })
            ->addColumn('history', function ($crm_start_date_hold_applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$crm_start_date_hold_applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$crm_start_date_hold_applicant->id.'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $crm_start_date_hold_applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$crm_start_date_hold_applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$crm_start_date_hold_applicant->applicant_name.'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$crm_start_date_hold_applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;

            })
            ->setRowClass(function ($result) {
                $row_class = '';
                if ($result->paid_status == 'close') {
                    $row_class = 'class_dark';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($result->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success'; // status: sent
                            break;
                        }
                    }
                }
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','applicant_postcode', 'history'])
            ->make(true);
    }

    public function getCrmPaidApplicantCv()
    {
        return view('administrator.resource.crm_paid_applicants');
    }

	public function exportCrmPaidApplicantCv()
    {
        $crm_paid_applicants = Applicant::with('cv_notes')
            ->join('crm_notes', 'applicants.id', '=', 'crm_notes.applicant_id')
            ->select('applicants.applicant_phone', 'applicants.applicant_name', 'applicants.applicant_homePhone','applicants.applicant_job_title',
            'applicants.applicant_postcode','applicants.applicant_source','applicants.applicant_notes')
            ->where([
                'applicants.status' => 'active', 'applicants.paid_status' => 'open',
                'crm_notes.moved_tab_to' => 'paid'
            ])
            ->whereIn('crm_notes.id', function($query){
                $query->select(\Illuminate\Support\Facades\DB::raw('MAX(id) FROM crm_notes WHERE moved_tab_to="paid" and applicants.id=applicant_id'));
            })
            ->orderBy("crm_notes.id","DESC")->get();
        return Excel::download(new Applicants_nureses_7_days_export($crm_paid_applicants), 'applicants.csv');

    }

    public function getCrmPaidApplicantCvAjax(Request $request)
    {
        /*** query for crm: paid tab */
     date_default_timezone_set('Europe/London');
        $job_category = $request->filled('job_category') ? $request->get('job_category') : null;

        $end_date = Carbon::now();
		$edate21 = $end_date->subMonth(3);

        $edate = $edate21->format('Y-m-d') . " 23:59:59";
        //$edate = $end_date->format('Y-m-d') . " 23:59:59";
        $start_date = $end_date->subMonths(1);
        $sdate = $start_date->format('Y-m-d') . " 00:00:00";
        //        $range_date=[$start_date, $end_dated];
        $result = Applicant::with('cv_notes')
            ->join('crm_notes', 'applicants.id', '=', 'crm_notes.applicant_id')
            ->select('crm_notes.details', 'crm_notes.crm_added_date', 'crm_notes.crm_added_time',
                'applicants.id', 'applicants.applicant_name', 'applicants.applicant_job_title','applicants.job_title_prof', 'applicants.job_category',
                'applicants.applicant_postcode', 'applicants.applicant_phone', 'applicants.applicant_homePhone',
                'applicants.applicant_source', 'applicants.paid_status', 'applicants.paid_timestamp')
            ->where([
            //                'applicants.status' => 'active', 'applicants.paid_status' => 'open',
                'applicants.status' => 'active',
            //                'crm_notes.moved_tab_to' => 'paid','applicants.is_no_job' => '0'
               'applicants.is_no_job' => '0'
            ])
           //->whereBetween('crm_notes.created_at', [$sdate,$edate])
			 ->whereDate('crm_notes.updated_at', '<=', $edate)

            ->whereIn('applicants.paid_status',['open','pending'])
            ->whereIn( 'crm_notes.moved_tab_to',['paid','dispute','start_date_hold','declined','start_date'])
            ->whereIn('crm_notes.id', function($query){
            //                $query->select(\Illuminate\Support\Facades\DB::raw('MAX(id) FROM crm_notes WHERE moved_tab_to="paid" and applicants.id=applicant_id'));
                $query->select(DB::raw('MAX(id) FROM crm_notes'))
                    ->whereIn('moved_tab_to', ['paid','dispute','start_date_hold','declined','start_date'])
                    ->where('applicants.id', '=', DB::raw('applicant_id'));
            });
         if ($job_category=="nurse") {
            $result = $result->where('applicants.job_category', '=', $job_category);
        }elseif ($job_category=="non-nurse"){
            $result = $result->whereIn('applicants.job_category',['chef','non-nurse','nonnurse']);

        }

            $crm_paid_applicants=$result->orderBy("crm_notes.id","DESC");

        return datatables()->of($crm_paid_applicants)
            ->addColumn("applicant_postcode",function ($applicant) {
                foreach ($applicant->cv_notes as $key => $value) {
                    if ($value->status == 'active') { return $applicant->applicant_postcode; }
                }
                return '<a href="/available-jobs/'.$applicant->id.'">'.$applicant->applicant_postcode.'</a>';
            })
			->editColumn('applicant_job_title', function ($applicant) {
                $job_title_desc='';
                if($applicant->job_title_prof!=null)
                {
                    $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $applicant->job_title_prof)->first();
                                $job_title_desc = $applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
                }
                else
                {
                    
                    $job_title_desc = $applicant->applicant_job_title;
                }
                return $job_title_desc;
    
         })
            ->addColumn('history', function ($applicant) {
                $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$applicant->id.'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$applicant->applicant_name.'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;

            })
            ->setRowClass(function ($result) {
                $row_class = '';
                foreach ($result->cv_notes as $key => $value) {
                    if ($value->status == 'active') {
                        $row_class = 'class_success'; // status: sent
                        break;
                    } elseif ($value->status == 'disable') {
                        $row_class = 'class_danger'; // status: reject
                    }
                }
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','applicant_postcode', 'history'])
            ->make(true);
    }

    public function applicantRejectedHistory(Request $request)
    {
        $applicant_id = $request->input('applicant');

        $applicants_rejected_history = Crm_note::join('sales', 'sales.id', '=', 'crm_notes.sales_id')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->select('sales.job_title', 'sales.postcode', 'sales.id', 'units.unit_name','crm_notes.created_at', 'crm_notes.details', 'crm_notes.moved_tab_to')
            ->whereIn('crm_notes.moved_tab_to', ['cv_sent_reject', 'request_reject', 'interview_not_attended', 'start_date_hold', 'dispute'])
            ->where('crm_notes.applicant_id', '=', $applicant_id)
            ->get();
        $history_modal_body = view('administrator.resource.partial.applicant_rejected_history', compact('applicants_rejected_history'))->render();
        return $history_modal_body;
        //        return $applicants_rejected_history;
    }
	
	public function getRejectedAppDateWise($id, $month)
    {
        $range_val = '';
        if($month == 3)
        {
            $range_val = '3 Months';
        }
        elseif($month == 6)
        {
            $range_val = '6 Months';
        }
        elseif($month == 9)
        {
            $range_val = '9 Months';
        }
        else
        {
            $range_val= 'Remaining';
        }
        return view('administrator.resource.crm_rejected_app.crm_3_months_rejected_app', compact('id','month','range_val'));
    }

    public function getRejectedAppDateWiseAjax($id, $month)
    {
        $category = '';
        if($id == 44)
        {
            $category = 'nurse';
        }
        else
        {
            $category = 'non-nurse';
        }
        $start_date ='';
        $end_date ='';
        if($month == 3)
        {
            $start_date = Carbon::now()->subMonth(3)->format('Y-m-d') . " 00:00:01";
             $end_date = Carbon::now()->format('Y-m-d') . " 23:59:59";
        }
        elseif($month == 6)
        {
            $month_val_3 = Carbon::now()->subMonth(3);
            $start_date = $month_val_3->subMonth(6)->format('Y-m-d') . " 00:00:01";
             $end_date = Carbon::now()->subMonth(3)->format('Y-m-d') . " 23:59:59";
        }
		elseif($month == 9)
        {
            $month_val_3 = Carbon::now()->subMonth(6);
            $start_date = $month_val_3->subMonth(9)->format('Y-m-d') . " 00:00:01";
             $end_date = Carbon::now()->subMonth(6)->format('Y-m-d') . " 23:59:59";
        }
		else
        {
            $start_date = "2020-01-01 00:00:01";
            $end_date = Carbon::now()->subMonth(9);
        }
        $crm_rejected_applicants = Applicant::with('cv_notes')
        ->join('crm_notes', 'applicants.id', '=', 'crm_notes.applicant_id')
        ->join('history', function ($join) {
            $join->on('crm_notes.applicant_id', '=', 'history.applicant_id');
            $join->on('crm_notes.sales_id', '=', 'history.sale_id');
        })->select('crm_notes.details', 'crm_notes.crm_added_date', 'crm_notes.crm_added_time',
            'applicants.id', 'applicants.applicant_name', 'applicants.applicant_job_title', 'applicants.job_title_prof', 'applicants.job_category',
            'applicants.applicant_postcode', 'applicants.applicant_phone', 'applicants.paid_status',
            'applicants.applicant_homePhone','applicants.applicant_source','applicants.lat','applicants.lng',
				   'applicants.applicant_email',
            DB::raw('
        CASE 
            WHEN history.sub_stage="crm_reject"
            THEN "Rejected CV" 
            WHEN history.sub_stage="crm_request_reject"
            THEN "Rejected By Request"
            WHEN history.sub_stage="crm_interview_not_attended"
            THEN "Not Attended"
            WHEN history.sub_stage="crm_start_date_hold" OR history.sub_stage = "crm_start_date_hold_save"
            THEN "Start Date Hold"
            END AS sub_stage'))->whereIn("history.sub_stage", ["crm_interview_not_attended","crm_request_reject","crm_reject","crm_start_date_hold", "crm_start_date_hold_save"])
            ->whereIn("crm_notes.moved_tab_to", ["interview_not_attended","request_reject","cv_sent_reject","start_date_hold", "start_date_hold_save"])
            ->whereBetween('crm_notes.updated_at', [$start_date, $end_date])
        ->where([
            "applicants.status" => "active", "applicants.job_category" => $category, "history.status" => "active",
            "applicants.is_in_nurse_home" => "no", "applicants.is_blocked" => "0", 'applicants.is_callback_enable' => 'no',"is_no_job"=>"0"
                    ])
            ->where("applicants.lat", "!=", 0.000000)->where("applicants.lng", "!=", 0.000000)			
            ->orderBy("crm_notes.id","DESC")->groupBy('applicants.applicant_phone')->get();
                    


        $data = Sale::select('job_title','lat', 'lng')
        ->where("status", "active")->where("is_on_hold", "0")->where("lat", "!=", 0.000000)->where("lng", "!=", 0.000000)
        ->get();
		
        $data = collect($data->toArray());
        $crm_rejected_app = [];
         foreach ($crm_rejected_applicants as $key => $value) {
            $lat_val = $value->lat;
            $lng_val = $value->lng;
			
                foreach($data as $d)
                {
				
                    $res = ((ACOS(SIN($lat_val * PI() / 180) * SIN($d['lat'] * PI() / 180) +
                    COS($lat_val * PI() / 180) * COS($d['lat'] * PI() / 180) * COS(($lng_val - $d['lng']) * PI() / 180)) * 180 / PI()) * 60 * 1.1515);
					
                    if($res <= 15)
                    {
                    $title = $this->getAllTitles($value->applicant_job_title);
						
        if($d['job_title'] == $title[0] || $d['job_title'] == $title[1] || $d['job_title'] == $title[2] || $d['job_title'] == $title[3] || $d['job_title'] == $title[4] ||
        $d['job_title'] == $title[5] || $d['job_title'] == $title[6] || $d['job_title'] == $title[7] || $d['job_title'] == $title[8] || $d['job_title'] == $title[9])
        {
            $crm_rejected_app[] = $crm_rejected_applicants[$key];
                    break;
        }
                    }
                }

            }
	

        return datatables()->of($crm_rejected_app)
            ->addColumn("applicant_postcode",function($crm_rejected_applicant) {
                if ($crm_rejected_applicant->paid_status == 'close') {
                    return $crm_rejected_applicant->applicant_postcode;
                } 
                else {
                    foreach ($crm_rejected_applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            return $crm_rejected_applicant->applicant_postcode;
                        }
                    }
                    return '<a href="/available-jobs/'.$crm_rejected_applicant->id.'">'.$crm_rejected_applicant->applicant_postcode.'</a>';
                }
            })
            ->editColumn('applicant_job_title', function ($crm_rejected_applicant) {
                $job_title_desc='';
                if($crm_rejected_applicant->job_title_prof!=null)
                {
                    $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $crm_rejected_applicant->job_title_prof)->first();
                                $job_title_desc = $crm_rejected_applicant->applicant_job_title.' ('.$job_prof_res->specialist_prof.')';
                }
                else
                {
                    
                    $job_title_desc = $crm_rejected_applicant->applicant_job_title;
                }
                return $job_title_desc;
                
         })
         

         
            ->addColumn('history', function ($crm_rejected_applicant) {
            $content = '';
                $content .= '<a href="#" class="reject_history" data-applicant="'.$crm_rejected_applicant->id.'"; 
                                 data-controls-modal="#reject_history'.$crm_rejected_applicant->id.'" 
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#reject_history' . $crm_rejected_applicant->id . '">History</a>';

                $content .= '<div id="reject_history'.$crm_rejected_applicant->id.'" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-lg">';
                $content .= '<div class="modal-content">';
                $content .= '<div class="modal-header">';
                $content .= '<h6 class="modal-title">Rejected History - <span class="font-weight-semibold">'.$crm_rejected_applicant->applicant_name.'</span></h6>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body" id="applicant_rejected_history'.$crm_rejected_applicant->id.'" style="max-height: 500px; overflow-y: auto;">';

                /*** Details are fetched via ajax request */

                $content .= '</div>';
                $content .= '<div class="modal-footer">';
                $content .= '<button type="button" class="btn bg-teal legitRipple" data-dismiss="modal">Close</button>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';



        /*** Export Applicants Modal */
                $content .= '<div id="export_all_rejected_applicant_action" class="modal fade" tabindex="-1">';
                $content .= '<div class="modal-dialog modal-sm">';
                $content .= '<div class="modal-content">';

                $content .= '<div class="modal-header">';
                $content .= '<h3 class="modal-title">Export Applicants</h3>';
                $content .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
                $content .= '</div>';
                $content .= '<div class="modal-body">';
                $content .= '<form action="' . route('export_all_rejected_applicants') . '" method="POST" id="export_block_applicants" class="form-horizontal">';
                $content .= csrf_field();
                // $content .= '<input type="hidden" name="applicant_id" value="' . $applicant->id . '">';
                // $content .= '<input type="hidden" name="sale_id" value="' . $applicant->sale_id . '">';
                $content .= '<div class="mb-4">';
                $content .= '<div class="input-group">';
                $content .= '<span class="input-group-prepend">';
                $content .= '<span class="input-group-text"><i class="icon-calendar5"></i></span>';
                $content .= '</span>';
                $content .= '<input type="text" class="form-control pickadate-year" name="start_date" id="start_date" placeholder="Select From Date">';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '<div class="mb-4">';
                $content .= '<div class="input-group">';
                $content .= '<span class="input-group-prepend">';
                $content .= '<span class="input-group-text"><i class="icon-calendar5"></i></span>';
                $content .= '</span>';
            //                $content .= '<input type="text" class="form-control time_pickerrrr" id="anytime-time'.$applicant->id.'-'.$applicant->sale_id.'" name="schedule_time" placeholder="Select Schedule Time e.g., 00:00">';
                $content .= '<input type="text" class="form-control pickadate-year" name="end_date" id="end_date" placeholder="Select To Date">';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '<button type="submit" class="btn bg-teal legitRipple btn-block">Submit</button>';
                $content .= '</form>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';
                $content .= '</div>';

                return $content;

            })
            ->setRowClass(function ($crm_rejected_applicant) {
                $row_class = '';
                if ($crm_rejected_applicant->paid_status == 'close') {
                    $row_class = 'class_dark';
                } else { /*** $applicant->paid_status == 'open' || $applicant->paid_status == 'pending' */
                    foreach ($crm_rejected_applicant->cv_notes as $key => $value) {
                        if ($value->status == 'active') {
                            $row_class = 'class_success'; // status: sent
                            break;
                        }
                    }
                }
                return $row_class;
            })
            ->rawColumns(['applicant_job_title','applicant_postcode', 'history','applicant_notes'])
            ->make(true);
    }
	
	public function getChefSales()
    {
        // $sales = Office::join('sales', 'offices.id', '=', 'sales.head_office')
        //     ->join('units', 'units.id', '=', 'sales.head_office_unit')
        //     ->select('sales.*', 'offices.office_name', 'units.contact_name',
        //         'units.contact_email', 'units.unit_name', 'units.contact_phone_number')
        //     ->where(['sales.status' => 'active', 'sales.job_category' => 'nonnurse'])->get();
        $value = '1';
        return view('administrator.resource.chef', compact('value'));
    }
	
	public function getChefJob(Request $request)
    {
        $user = Auth::user();
        $result='';
       
            $sale_notes = Sales_notes::select('sale_id','sales_notes.sale_note', DB::raw('MAX(created_at) as 
            sale_created_at'))
                ->groupBy('sale_id');
            $result = Office::join('sales', 'offices.id', '=', 'sales.head_office')
                ->join('units', 'units.id', '=', 'sales.head_office_unit')
                ->select('sales.*', 'offices.office_name', 'units.contact_name',
                    'units.contact_email', 'units.unit_name', 'units.contact_phone_number', DB::raw("(SELECT count(cv_notes.sale_id) from cv_notes
                WHERE cv_notes.sale_id=sales.id AND cv_notes.status='active' group by cv_notes.sale_id) as result"))
                ->where(['sales.status' => 'active', 'sales.is_on_hold' => '0', 'sales.job_category' => 'chef'])
                ->whereNotIn('sales.job_title', ['nonnurse specialist'])
                ->orderBy('id', 'DESC');

        

        // (cv_notes.status='active' or cv_notes.status='paid')
        // $result = Office::join('sales', 'offices.id', '=', 'sales.head_office')
        //     ->join('units', 'units.id', '=', 'sales.head_office_unit')
        //     ->select('sales.*', 'offices.office_name', 'units.contact_name',
        //         'units.contact_email', 'units.unit_name', 'units.contact_phone_number')
        //     ->where(['sales.status' => 'active', 'sales.job_category' => 'nonnurse'])->orderBy('id', 'DESC');

        $aColumns = ['sale_added_date', 'sale_added_time', 'job_title', 'office_name', 'unit_name',
            'postcode', 'job_type', 'experience', 'qualification', 'salary', 'sale_notes', 'status', 'Cv Limit'];

        $iStart = $request->get('iDisplayStart');
        $iPageSize = $request->get('iDisplayLength');

        $order = 'id';
        $sort = ' DESC';

        if ($request->get('iSortCol_0')) { //iSortingCols

            $sOrder = "ORDER BY  ";

            for ($i = 0; $i < intval($request->get('iSortingCols')); $i++) {
                if ($request->get('bSortable_' . intval($request->get('iSortCol_' . $i))) == "true") {
                    $sOrder .= $aColumns[intval($request->get('iSortCol_' . $i))] . " " . $request->get('sSortDir_' . $i) . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = " id ASC";
            }

            $OrderArray = explode(' ', $sOrder);
            $order = trim($OrderArray[3]);
            $sort = trim($OrderArray[4]);

        }

        $sKeywords = $request->get('sSearch');
        if ($sKeywords != "") {

            $result->Where(function ($query) use ($sKeywords) {
                $query->orWhere('job_title', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('office_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('unit_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('postcode', 'LIKE', "%{$sKeywords}%");
            });
        }

        for ($i = 0; $i < count($aColumns); $i++) {
            $request->get('sSearch_' . $i);
            if ($request->get('bSearchable_' . $i) == "true" && $request->get('sSearch_' . $i) != '') {
                $result->orWhere($aColumns[$i], 'LIKE', "%" . $request->orWhere('sSearch_' . $i) . "%");
            }
        }

        $iFilteredTotal = $result->count();

        if ($iStart != null && $iPageSize != '-1') {
            $result->skip($iStart)->take($iPageSize);
        }

        $result->orderBy($order, trim($sort));
        $result->limit($request->get('iDisplayLength'));
        $saleData = $result->get();

        $iTotal = $iFilteredTotal;
        $output = array(
            "sEcho" => intval($request->get('sEcho')),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $i = 0;

        foreach ($saleData as $sRow) {
			$post_code = strtoupper($sRow->postcode);
            $checkbox = "<label class=\"mt-checkbox mt-checkbox-single mt-checkbox-outline\">
                             <input type=\"checkbox\" class=\"checkbox-index\" value=\"{$sRow->id}\">
                             <span></span>
                          </label>";
            $postcode = "<a href=\"/applicants-within-15-km/{$sRow->id}\">{$post_code}</a>";
            if ($sRow->status == 'active') {
                $status = '<h5><span class="badge w-100 badge-success">Active</span></h5>';
            } else {
                $status = '<h5><span class="badge w-100 badge-danger">Disable</span></h5>';
            }

            $action = "<div class=\"list-icons\">
            <div class=\"dropdown\">
                <a href=\"#\" class=\"list-icons-item\" data-toggle=\"dropdown\">
                    <i class=\"icon-menu9\"></i>
                </a>
                <div class=\"dropdown-menu dropdown-menu-right\">
                    <a href=\"#\" class=\"dropdown-item\"
                                               data-controls-modal=\"#manager_details{$sRow->id}\"
                                               data-backdrop=\"static\"
                                               data-keyboard=\"false\" data-toggle=\"modal\"
                                               data-target=\"#manager_details{$sRow->id}\"
                                            > Manager Details </a>
                </div>
            </div>
          </div>
          <div id=\"manager_details{$sRow->id}\" class=\"modal fade\" tabindex=\"-1\">
                            <div class=\"modal-dialog modal-sm\">
                                <div class=\"modal-content\">
                                    <div class=\"modal-header\">
                                        <h5 class=\"modal-title\">Manager Details</h5>
                                        <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                    </div>
                                    <div class=\"modal-body\">
                                        <ul class=\"list-group\">
                                            <li class=\"list-group-item active\"><p><b>Name: </b>{$sRow->contact_name}</p>
                                            </li>
                                            <li class=\"list-group-item\"><p><b>Email: </b>{$sRow->contact_email}</p></li>
                                            <li class=\"list-group-item\"><p><b>Phone#: </b>{$sRow->contact_phone_number}</p>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class=\"modal-footer\">
                                        <button type=\"button\" class=\"btn bg-teal legitRipple\" data-dismiss=\"modal\">CLOSE
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>";
            $job_title_desc='';
            if(@$sRow->job_title_prof!='')
            {
                $job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where('id', $sRow->job_title_prof)->first();
                $job_title_desc = $sRow->job_title.' ('.$job_prof_res->specialist_prof.')';
                // $job_title_desc = @$sRow->job_title.' ('.@$sRow->job_title_prof.')';
            }
            else
            {
                $job_title_desc = @$sRow->job_title;
            }
            $output['aaData'][] = array(
                "DT_RowId" => "row_{$sRow->id}",
                //    @$checkbox,
                @$sRow->sale_added_date,
                @$sRow->sale_added_time,
				strtoupper($job_title_desc),
                @ucwords(strtolower($sRow->office_name)),
                @ucwords(strtolower($sRow->unit_name)),
                @$postcode,
                @ucwords($sRow->job_type),
                @$sRow->experience,
                @$sRow->qualification,
                @$sRow->salary,
                @$sRow->sale_notes,
                @$status,
                @$sRow->result==$sRow->send_cv_limit?'<span class="badge w-100 badge-danger" style="font-size:90%">Limit Reached</span>':"<span class='badge w-100 badge-success' style='font-size:90%'>". ((int)$sRow->send_cv_limit - (int)$sRow->result)." Cv's limit remaining</span>",
				@$action,
            );


            $i++;

        }

        //  print_r($output);
        echo json_encode($output);
    }
}