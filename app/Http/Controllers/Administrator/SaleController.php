<?php

namespace Horsefly\Http\Controllers\Administrator;

//use Horsefly\Observers\SaleObserver;
use Carbon\Carbon;
use Horsefly\Audit;
use Horsefly\Exports\ApplicantEmailExport;
use Horsefly\Exports\ClosedSalesEmailExport;
use Horsefly\Observers\ActionObserver;
use Illuminate\Http\Request;
use Horsefly\Http\Controllers\Controller;
use Horsefly\Office;
use Horsefly\Unit;
use Horsefly\Sale;
use Horsefly\Cv_note;
use Horsefly\Applicant;
use Horsefly\History;
use Horsefly\Quality_notes;
use Horsefly\Crm_note;
use Horsefly\Sales_notes;
use Horsefly\ModuleNote;
use Horsefly\Specialist_job_titles;

// use DB;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Redirect;
use Illuminate\Support\Facades\Validator;
// use Validator;
use \stdClass;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        /*** Sales - Open */
        $this->middleware('permission:sale_list|sale_import|sale_create|sale_edit|sale_view|sale_close|sale_manager-detail|sale_history|sale_notes|sale_note-create|sale_note-history', ['only' => ['index','getSales']]);
        $this->middleware('permission:sale_import', ['only' => ['getUploadSaleCsv']]);
        $this->middleware('permission:sale_create', ['only' => ['create','store']]);
        $this->middleware('permission:sale_edit', ['only' => ['edit','update']]);
        $this->middleware('permission:sale_view', ['only' => ['show']]);
        $this->middleware('permission:sale_close', ['only' => ['getCloseSale']]);
		$this->middleware('permission:sale_on-hold', ['only' => ['onHoldSale','unHoldSale']]);
        $this->middleware('permission:sale_history', ['only' => ['getSaleHistory','getSaleFullHistory']]);
        $this->middleware('permission:sale_notes', ['only' => ['getAllOpenedSalesNotes']]);
        /*** Sales - Close */
		$this->middleware('permission:sale_closed-sales-list|sale_open|sale_closed-sale-notes', ['only' => ['getAllClosedSales']]);
		$this->middleware('permission:sale_on-hold', ['only' => ['getAllOnHoldSales']]);
		$this->middleware('permission:sale_on-hold', ['only' => ['getOnHoldSales']]);
        $this->middleware('permission:sale_open', ['only' => ['getOpenSale']]);
        $this->middleware('permission:sale_closed-sale-notes', ['only' => ['getAllClosedSalesNotes']]);
        /*** Sales - PSL */
        $this->middleware('permission:sale_psl-offices-list|sale_psl-office-details|sale_psl-office-units', ['only' => ['getAllPslClientSale']]);
        $this->middleware('permission:sale_psl-office-units', ['only' => ['getAllPslUnitDetails']]);
        /*** Sales - NON PSL */
        $this->middleware('permission:sale_non-psl-offices-list|sale_non-psl-office-details|sale_non-psl-office-units', ['only' => ['getAllNonPslClientSale']]);
        $this->middleware('permission:sale_non-psl-office-units', ['only' => ['getAllNonPslUnitDetails']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $head_offices = Office::where("status","active")->get();
        $items = array();
        foreach($head_offices as $office){
            $items[$office->id] = $office->office_name;
        }
        $head_office_users = Office::join('users', 'users.id', '=', 'offices.user_id')
            ->select('users.id', 'users.name')
            ->distinct('user_id')
            ->get()->toArray();
            
        return view('administrator.sale.open.index', compact('head_offices', 'head_office_users'));
    }

    public function userOffices(Request $request)
    {
        $user_id = $request->input('user_key');
        $offices = Office::where('user_id', '=', $user_id)->get();
        $options_html = '<option value="">Select Head Office</option>';
        foreach ($offices as $office) {
            $options_html .= '<option value="'.$office->id.'">'.$office->office_name.'</option>';
        }
        echo $options_html;
    }

    public function getSales(Request $request)
    {
        $job_category = $request->filled('job_category') ? $request->get('job_category') : null;

		$specialist_title = $request->filled('job_specialist') ? $request->get('job_specialist') : null;
        $office = $request->filled('office') ? $request->get('office') : null;
        $user = $request->filled('user') ? $request->get('user') : null;
        $cv_sent_option = $request->filled('cv_sent_option') ? $request->get('cv_sent_option') : null;

        $auth_user = Auth::user();
        $result = Office::with('user')
            ->join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->join('users', 'users.id', '=', 'sales.user_id');
        if ($cv_sent_option) {
            if ($cv_sent_option == 'max') {
                $result = $result->where('sales.send_cv_limit', '=', function ($query) {
                    $query->select(DB::raw('count(cv_notes.sale_id) AS sent_cv_count FROM cv_notes WHERE cv_notes.sale_id=sales.id AND cv_notes.status="active"'));
                });
            } elseif ($cv_sent_option == 'not_max') {
                $result = $result->where('sales.send_cv_limit', '>', function ($query) {
                    $query->select(DB::raw('count(cv_notes.sale_id) AS sent_cv_count FROM cv_notes WHERE cv_notes.sale_id=sales.id AND cv_notes.status="active" HAVING sent_cv_count > 0 AND sent_cv_count <> sales.send_cv_limit'));
                });
            } elseif ($cv_sent_option == 'zero') {
                $result = $result->where('sales.send_cv_limit', '>', function ($query) {
                    $query->select(DB::raw('count(cv_notes.sale_id) AS sent_cv_count FROM cv_notes WHERE cv_notes.sale_id=sales.id AND cv_notes.status="active" HAVING sent_cv_count = 0'));
                });
            }

        }
        $result = $result->select('sales.*', 'offices.office_name','units.contact_name','users.name',
                'units.contact_email','units.unit_name','units.contact_phone_number')
            ->where('sales.status','active')->where('sales.is_on_hold','0');

        if ($office) {
            $result = $result->where('sales.head_office', '=', $office);
        }
        if ($job_category) {
            $result = $result->where('sales.job_category', '=', $job_category);
        }
		if($specialist_title=="nurse specialist" || $specialist_title=="nonnurse specialist" )
        {
            $result = $result->where('sales.job_title', '=', $specialist_title);
        }
        $result = $result->selectRaw(DB::raw("(SELECT COUNT(*) FROM cv_notes WHERE cv_notes.sale_id = sales.id AND cv_notes.status = 'active') as no_of_sent_cv"));
        $aColumns = ['sale_added_date','updated_at','job_category','job_title',
        'office_name','unit_name','postcode','job_type','experience','qualification','salary'];
        $iStart = $request->get('iDisplayStart');
        $iPageSize = $request->get('iDisplayLength');
        // $iPageSize = 9;
        $order = 'id';
        $sort = ' DESC';

        if ($request->get('iSortCol_0')!='') { //iSortingCols
      
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

        } else {
            $result = $result->orderBy('sales.updated_at', 'DESC');
        }

        $sKeywords = $request->get('sSearch');
        if ($sKeywords != "") {

            $result->Where(function($query) use ($sKeywords) {
                $query->orWhere('job_title', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('office_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('unit_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('postcode', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('job_type', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('experience', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('qualification', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('salary', 'LIKE', "%{$sKeywords}%");
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
        $row_data = array();
        $output = array(
             "sEcho" => intval($request->get('sEcho')),
             "iTotalRecords" => $iTotal,
             "iTotalDisplayRecords" => $iFilteredTotal,
             "aaData" => array()
        );
        
        $i = 0;
        foreach ($saleData as $sRow) 
        {

            $phoneArray = $sRow->contact_phone_number;
            $landlineArray = $sRow->contact_landline;
            $emailArray = $sRow->contact_email;
            $nameArray = $sRow->contact_name;
        
            $emails = array_filter(explode(',', $emailArray));
            $phones = array_filter(explode(',', $phoneArray));
            $landlines = array_filter(explode(',', $landlineArray));
            $names = array_filter(explode(',', $nameArray));

            $mergedArray = [];
        
            $maxLength = max(count($emails), count($phones), count($landlines), count($names));
        
            for ($i = 0; $i < $maxLength; $i++) {
                $email = $emails[$i] ?? '';
                $phone = $phones[$i] ?? '';
                $landline = $landlines[$i] ?? '';
                $name = $names[$i] ?? '';
        
                if ($email || $phone || $landline || $name) {
                    $mergedArray[] = [
                        'email' => $email,
                        'phone' => $phone,
                        'landline' => $landline,
                        'name' => $name
                    ];
                }
            }

            $checkbox = "<label class=\"mt-checkbox mt-checkbox-single mt-checkbox-outline\">
                             <input type=\"checkbox\" class=\"checkbox-index\" value=\"{$sRow->id}\">
                             <span></span>
                          </label>";
            //            if($sRow->status == 'active'){
            //                $status = '<h5><span class="badge badge-success">Active</span></h5>';
            //            }else{
            //                $status = '<h5><span class="badge badge-danger">Disable</span></h5>';
            //            }
                                    $status = $sRow->no_of_sent_cv==$sRow->send_cv_limit?'<span class="badge w-100 badge-danger" style="font-size:90%" >'.$sRow->no_of_sent_cv.'/'.$sRow->send_cv_limit.' Limit Reached</span>':"<span class='badge w-100 badge-success' style='font-size:90%'>".((int)$sRow->send_cv_limit - (int)$sRow->no_of_sent_cv.'/'.(int)$sRow->send_cv_limit)." Cv's limit remaining</span>";



            $url = '/close-sale';
			$url_on_hold = '/on-hold-sale';
            $url_note = route('module_note.store');
            $csrf = csrf_token();

            $action = "<div class=\"list-icons\">
                        <div class=\"dropdown\">
                            <a href=\"#\" class=\"list-icons-item\" data-toggle=\"dropdown\">
                                <i class=\"icon-menu9\"></i>
                            </a>
                            <div class=\"dropdown-menu dropdown-menu-right\">";

            if ($auth_user->hasPermissionTo('sale_edit')) {
                $action .=      "<a href=\"/sales/{$sRow->id}/edit\" class=\"dropdown-item\"> Edit</a>";
            }
            if ($auth_user->hasPermissionTo('sale_view')) {
                $action .=      "<a href=\"/sales/{$sRow->id}\" class=\"dropdown-item\"> View </a>";
            }
            if ($auth_user->hasPermissionTo('sale_close')) {
                $action .=
                                "<a href=\"#\" class=\"dropdown-item\"
                                               data-controls-modal=\"#close_sale{$sRow->id}\"
                                               data-backdrop=\"static\"
                                               data-keyboard=\"false\" data-toggle=\"modal\"
                                               data-target=\"#close_sale{$sRow->id}\"
                                            > Close </a>";
            }
            $action .=
                                "<a href=\"#\" class=\"dropdown-item\"
                                               data-controls-modal=\"#manager_details{$sRow->id}\"
                                               data-backdrop=\"static\"
                                               data-keyboard=\"false\" data-toggle=\"modal\"
                                               data-target=\"#manager_details{$sRow->id}\"
                                            > Manager Details </a>";
            if ($auth_user->hasPermissionTo('sale_history')) {
                $action .=      "<a href=\"/sale-history/{$sRow->id}\" class=\"dropdown-item\"> History</a>";
            }
            if ($auth_user->hasPermissionTo('sale_notes')) {
                $action .=      "<a href=\"/all-open-sales-notes/{$sRow->id}\" class=\"dropdown-item\">Notes</a>";
            }
			if ($auth_user->hasPermissionTo('sale_on-hold')) {
                $action .=
                                "<a href=\"#\" class=\"dropdown-item\"
                                               data-controls-modal=\"#add_on_hold{$sRow->id}\"
                                               data-backdrop=\"static\"
                                               data-keyboard=\"false\" data-toggle=\"modal\"
                                               data-target=\"#add_on_hold{$sRow->id}\">
                                               On Hold
                                </a>";
            }

            if ($auth_user->hasPermissionTo('sale_note-create')) {
                $action .=
                                "<a href=\"#\" class=\"dropdown-item\"
                                               data-controls-modal=\"#add_sale_note{$sRow->id}\"
                                               data-backdrop=\"static\"
                                               data-keyboard=\"false\" data-toggle=\"modal\"
                                               data-target=\"#add_sale_note{$sRow->id}\">
                                               Add Note
                                </a>";
            }
            if ($auth_user->hasPermissionTo('sale_note-history')) {
                $action .=
                                "<a href=\"#\" class=\"dropdown-item notes_history\" data-sale=\"{$sRow->id}\" data-controls-modal=\"#notes_history{$sRow->id}\"
                                               data-backdrop=\"static\"
                                               data-keyboard=\"false\" data-toggle=\"modal\"
                                               data-target=\"#notes_history{$sRow->id}\"> 
                                               Notes History
                                </a>";
            }
            $action .=
                            "</div>
                        </div>
                      </div>";
                    if ($auth_user->hasPermissionTo('sale_close')) {
                        $action .=
                                "<div id=\"close_sale{$sRow->id}\" class=\"modal fade\" tabindex=\"-1\">
                                    <div class=\"modal-dialog modal-sm\">
                                        <div class=\"modal-content\">
                                            <div class=\"modal-header\">
                                                <h5 class=\"modal-title\">Close Sale Notes</h5>
                                                <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                            </div>
                                            <form action=\"{$url}\"
                                                method=\"POST\" class=\"form-horizontal\">
                                                <input type=\"hidden\" name=\"_token\" value=\"{$csrf}\">
                                                <div class=\"modal-body\">
                                                    <div class=\"form-group row\">
                                                        <label class=\"col-form-label col-sm-3\">Details</label>
                                                        <div class=\"col-sm-9\">
                                                            <input type=\"hidden\" name=\"sale_id\" value=\"{$sRow->id}\">
                                                            <textarea name=\"details\" class=\"form-control\" cols=\"30\" rows=\"4\"
                                                                    placeholder=\"TYPE HERE..\" required></textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class=\"modal-footer\">
                                                    <button type=\"button\" class=\"btn btn-link legitRipple\" data-dismiss=\"modal\">
                                                        Close
                                                    </button>
                                                    <button type=\"submit\" class=\"btn bg-teal legitRipple\">Save</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>";
                    }
                    $action .=
                    "<div id=\"manager_details{$sRow->id}\" class=\"modal fade\" tabindex=\"-1\">
                        <div class=\"modal-dialog modal-sm\">
                            <div class=\"modal-content\">
                                <div class=\"modal-header\">
                                    <h5 class=\"modal-title\">Manager Details</h5>
                                    <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                </div>
                                <div class=\"modal-body\">";
                                    foreach($mergedArray as $index => $value) {
                                        $index = $index + 1;
                                        $action .=
                                            "<div>
                                                <ul class=\"list-group pt-0 mt-2\">
                                                    <li class=\"list-group-item active\"><b><em>Person - {$index}</em></b></li>
                                                    <li class=\"list-group-item\"><b>Name: </b> {$value['name']}</li>
                                                    <li class=\"list-group-item\"><b>Phone: </b> {$value['phone']}</li>
                                                    <li class=\"list-group-item\"><b>Email: </b> {$value['email']}</li>
                                                </ul>
                                            </div>";
                                    }
                                    $action .=
                                "</div>
                                <div class=\"modal-footer\">
                                    <button type=\"button\" class=\"btn bg-teal legitRipple\" data-dismiss=\"modal\">CLOSE</button>
                                </div>
                            </div>
                        </div>
                    </div>";
        
			if ($auth_user->hasPermissionTo('sale_on-hold')) {
                            $action .=
                                    "<div id=\"add_on_hold{$sRow->id}\" class=\"modal fade\" tabindex=\"-1\">
                                        <div class=\"modal-dialog modal-lg\">
                                            <div class=\"modal-content\">
                                                <div class=\"modal-header\">
                                                    <h5 class=\"modal-title\">Add Sale On Hold</h5>
                                                    <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                                </div>
                                                <form action=\"{$url_on_hold}\" method=\"POST\" class=\"form-horizontal\" id=\"onhold_form{$sRow->id}\">
                                                    <input type=\"hidden\" name=\"_token\" value=\"{$csrf}\">
                                                    <input type=\"hidden\" name=\"onhold_module\" value=\"Sale\">
                                                    <div class=\"modal-body\">
                                                        <div id=\"onhold_note_alert{$sRow->id}\"></div>
                                                        <div class=\"form-group row\">
                                                            <label class=\"col-form-label col-sm-3\">Details</label>
                                                            <div class=\"col-sm-9\">
                                                                <input type=\"hidden\" name=\"onhold_module_key\" value=\"{$sRow->id}\">
                                                                <textarea name=\"onhold_details\" id=\"onhold_note_details{$sRow->id}\" class=\"form-control\" cols=\"30\" rows=\"4\"
                                                                          placeholder=\"TYPE HERE ..\" required></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
            
                                                    <div class=\"modal-footer\">
                                                        <button type=\"button\" class=\"btn btn-link legitRipple\" data-dismiss=\"modal\">
                                                            Close
                                                        </button>
                                                        <button type=\"submit\" data-note_key=\"{$sRow->id}\" class=\"btn bg-teal legitRipple\">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>";
                        }
            if ($auth_user->hasPermissionTo('sale_note-create')) {
                $action .=
                        "<div id=\"add_sale_note{$sRow->id}\" class=\"modal fade\" tabindex=\"-1\">
                            <div class=\"modal-dialog modal-lg\">
                                <div class=\"modal-content\">
                                    <div class=\"modal-header\">
                                        <h5 class=\"modal-title\">Add Sale Note</h5>
                                        <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                    </div>
                                    <form action=\"{$url_note}\" method=\"POST\" class=\"form-horizontal\" id=\"note_form{$sRow->id}\">
                                        <input type=\"hidden\" name=\"_token\" value=\"{$csrf}\">
                                        <input type=\"hidden\" name=\"module\" value=\"Sale\">
                                        <div class=\"modal-body\">
                                            <div id=\"note_alert{$sRow->id}\"></div>
                                            <div class=\"form-group row\">
                                                <label class=\"col-form-label col-sm-3\">Details</label>
                                                <div class=\"col-sm-9\">
                                                    <input type=\"hidden\" name=\"module_key\" value=\"{$sRow->id}\">
                                                    <textarea name=\"details\" id=\"note_details{$sRow->id}\" class=\"form-control\" cols=\"30\" rows=\"4\"
                                                              placeholder=\"TYPE HERE ..\" required></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class=\"modal-footer\">
                                            <button type=\"button\" class=\"btn btn-link legitRipple\" data-dismiss=\"modal\">
                                                Close
                                            </button>
                                            <button type=\"submit\" data-note_key=\"{$sRow->id}\" class=\"btn bg-teal legitRipple note_form_submit\">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>";
            }
            if ($auth_user->hasPermissionTo('sale_note-history')) {
                $action .=
                        "<div id=\"notes_history{$sRow->id}\" class=\"modal fade\" tabindex=\"-1\">
                            <div class=\"modal-dialog modal-lg\">
                                <div class=\"modal-content\">
                                    <div class=\"modal-header\">
                                        <h5 class=\"modal-title\">Sales Notes History - 
                                        <span class=\"font-weight-semibold\">{$sRow->job_title}</span></h5>
                                        <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                    </div>
                                    <div class=\"modal-body\" id=\"sales_notes_history{$sRow->id}\" style=\"max-height: 500px; overflow-y: auto;\">
                                    </div>
                                    <div class=\"modal-footer\">
                                        <button type=\"button\" class=\"btn bg-teal legitRipple\" data-dismiss=\"modal\">CLOSE
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                      ";
            }

			//to get agent name
            $updated_by = Audit::join('users', 'users.id', '=', 'audits.user_id')
                ->where(['audits.auditable_id' => $sRow->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                ->where('audits.message', 'like', '%has been updated%')
                ->select('users.name')
                ->orderBy('audits.created_at', 'desc')
				->first();

            $updated_by = $updated_by ? ucwords($updated_by->name) : ucwords($sRow->name);
			
			//get sale clearance date into audit
            $audit_clear_date= Audit::join('users', 'users.id', '=', 'audits.user_id')
                ->where(['audits.auditable_id' => $sRow->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                ->where('audits.message', 'like', '%sale-opened%')
                ->select('audits.updated_at')
                ->orderBy('audits.updated_at', 'desc')
                ->first();

            $clearance_date = @Carbon::parse($audit_clear_date->updated_at)->toFormattedDateString();
			
			$job_title_desc='';
            if($sRow->job_title_prof!=null)
                {
        			$job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where("id", $sRow->job_title_prof)->first();
                    $job_title_desc = strtoupper($sRow->job_title.' ('.$job_prof_res->specialist_prof.')');
                }
                else
                {
                    $job_title_desc = strtoupper($sRow->job_title);
                }
			 $row_class='';
               if ($sRow->is_re_open=='1'){
                   $row_class = 'class_success';
               }else{
                   
               }
            $output['aaData'][] = array(
                "DT_RowId" => "row_{$sRow->id}",
            //    @$checkbox,
				 "row_class" => $row_class,
                '<span data-popup="tooltip" title="'.$sRow->name.'">'.@Carbon::parse($sRow->sale_added_date)->toFormattedDateString().'</span>',
                '<span data-popup="tooltip" title="'.$updated_by.'">'.@Carbon::parse($sRow->updated_at)->toFormattedDateString().'</span>',
				@$clearance_date,
				@$updated_by,
                @strtoupper($sRow->job_category),
                $job_title_desc,
                '<span data-popup="tooltip" title="'.$sRow->user->name.'">'.@$sRow->office_name.'</span>',
                @$sRow->unit_name,
                @strtoupper($sRow->postcode),
                @ucwords($sRow->job_type),
                @$sRow->experience,
                @$sRow->qualification,
                @$sRow->salary,
                @$status,
                @$action
            );    
            $i++;

        }


       //  print_r($output);
         echo json_encode($output);
    }

    public function pendingApprovalOnHoldSales()
    {
        $head_offices = Office::where("status","active")->get();
        $items = array();
        foreach($head_offices as $office){
            $items[$office->id] = $office->office_name;
        }
        $head_office_users = Office::join('users', 'users.id', '=', 'offices.user_id')
            ->select('users.id', 'users.name')
            ->distinct('user_id')
            ->get()->toArray();
            
        return view('administrator.sale.onhold.pending_onhold_sales', compact('head_offices', 'head_office_users'));
    }
    
    public function getPendingApprovalOnHoldSales(Request $request)
    {
        $job_category = $request->filled('job_category') ? $request->get('job_category') : null;
		$specialist_title = $request->filled('job_specialist') ? $request->get('job_specialist') : null;
        $office = $request->filled('office') ? $request->get('office') : null;
        $user = $request->filled('user') ? $request->get('user') : null;
        $cv_sent_option = $request->filled('cv_sent_option') ? $request->get('cv_sent_option') : null;

        $auth_user = Auth::user();

        $result = Office::with('user')
            ->join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->leftJoin('sales_notes', function ($join) {
                $join->on('sales_notes.sale_id', '=', 'sales.id')
                     ->where('sales_notes.id', '=', function($query) {
                         $query->select('id')
                               ->from('sales_notes')
                               ->whereColumn('sale_id', 'sales.id')
                               ->orderBy('created_at', 'desc') // Adjust based on your timestamp field
                               ->limit(1);
                     });
            });

        if ($cv_sent_option) {
            if ($cv_sent_option == 'max') {
                $result = $result->where('sales.send_cv_limit', '=', function ($query) {
                    $query->select(DB::raw('count(cv_notes.sale_id) AS sent_cv_count FROM cv_notes WHERE cv_notes.sale_id=sales.id AND cv_notes.status="active"'));
                });
            } elseif ($cv_sent_option == 'not_max') {
                $result = $result->where('sales.send_cv_limit', '>', function ($query) {
                    $query->select(DB::raw('count(cv_notes.sale_id) AS sent_cv_count FROM cv_notes WHERE cv_notes.sale_id=sales.id AND cv_notes.status="active" HAVING sent_cv_count > 0 AND sent_cv_count <> sales.send_cv_limit'));
                });
            } elseif ($cv_sent_option == 'zero') {
                $result = $result->where('sales.send_cv_limit', '>', function ($query) {
                    $query->select(DB::raw('count(cv_notes.sale_id) AS sent_cv_count FROM cv_notes WHERE cv_notes.sale_id=sales.id AND cv_notes.status="active" HAVING sent_cv_count = 0'));
                });
            }
        }

        $result = $result->select('sales.*', 'offices.office_name','units.contact_name','users.name',
                'units.contact_email','units.unit_name','units.contact_phone_number','sales_notes.sale_note')
            ->where('sales.status','active')->where('sales.is_on_hold','2');

        if ($office) {
            $result = $result->where('sales.head_office', '=', $office);
        }
        if ($job_category) {
            $result = $result->where('sales.job_category', '=', $job_category);
        }
		if($specialist_title=="nurse specialist" || $specialist_title=="nonnurse specialist" )
        {
            $result = $result->where('sales.job_title', '=', $specialist_title);
        }
        $result = $result->selectRaw(DB::raw("(SELECT COUNT(*) FROM cv_notes WHERE cv_notes.sale_id = sales.id AND cv_notes.status = 'active') as no_of_sent_cv"));
        $aColumns = ['sale_added_date','updated_at','job_category','job_title',
        'office_name','unit_name','postcode','job_type','experience','qualification','salary'];
        $iStart = $request->get('iDisplayStart');
        $iPageSize = $request->get('iDisplayLength');
        // $iPageSize = 9;
        $order = 'id';
        $sort = ' DESC';

        if ($request->get('iSortCol_0')!='') { //iSortingCols
      
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

        } else {
            $result = $result->orderBy('sales.updated_at', 'DESC');
        }

        $sKeywords = $request->get('sSearch');
        if ($sKeywords != "") {

            $result->Where(function($query) use ($sKeywords) {
                $query->orWhere('job_title', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('office_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('unit_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('postcode', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('job_type', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('experience', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('qualification', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('salary', 'LIKE', "%{$sKeywords}%");
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
        $row_data = array();
        $output = array(
             "sEcho" => intval($request->get('sEcho')),
             "iTotalRecords" => $iTotal,
             "iTotalDisplayRecords" => $iFilteredTotal,
             "aaData" => array()
        );
        
        $i = 0;
        foreach ($saleData as $sRow) 
        {

            $phoneArray = $sRow->contact_phone_number;
            $landlineArray = $sRow->contact_landline;
            $emailArray = $sRow->contact_email;
            $nameArray = $sRow->contact_name;
        
            $emails = array_filter(explode(',', $emailArray));
            $phones = array_filter(explode(',', $phoneArray));
            $landlines = array_filter(explode(',', $landlineArray));
            $names = array_filter(explode(',', $nameArray));

            $mergedArray = [];
        
            $maxLength = max(count($emails), count($phones), count($landlines), count($names));
        
            for ($i = 0; $i < $maxLength; $i++) {
                $email = $emails[$i] ?? '';
                $phone = $phones[$i] ?? '';
                $landline = $landlines[$i] ?? '';
                $name = $names[$i] ?? '';
        
                if ($email || $phone || $landline || $name) {
                    $mergedArray[] = [
                        'email' => $email,
                        'phone' => $phone,
                        'landline' => $landline,
                        'name' => $name
                    ];
                }
            }

            $checkbox = "<label class=\"mt-checkbox mt-checkbox-single mt-checkbox-outline\">
                             <input type=\"checkbox\" class=\"checkbox-index\" value=\"{$sRow->id}\">
                             <span></span>
                          </label>";
            //            if($sRow->status == 'active'){
            //                $status = '<h5><span class="badge badge-success">Active</span></h5>';
            //            }else{
            //                $status = '<h5><span class="badge badge-danger">Disable</span></h5>';
            //            }
            $status = $sRow->no_of_sent_cv==$sRow->send_cv_limit?'<span class="badge w-100 badge-danger" style="font-size:90%" >'.$sRow->no_of_sent_cv.'/'.$sRow->send_cv_limit.' Limit Reached</span>':"<span class='badge w-100 badge-success' style='font-size:90%'>".((int)$sRow->send_cv_limit - (int)$sRow->no_of_sent_cv.'/'.(int)$sRow->send_cv_limit)." Cv's limit remaining</span>";



            // $url = '/close-sale';
			// $url_on_hold = '/on-hold-sale';
            // $url_note = route('module_note.store');
            // $csrf = csrf_token();

            $action = "<div class=\"list-icons\">
                        <div class=\"dropdown\">
                            <a href=\"#\" class=\"list-icons-item\" data-toggle=\"dropdown\">
                                <i class=\"icon-menu9\"></i>
                            </a>
                            <div class=\"dropdown-menu dropdown-menu-right\">";
                    $action .=
                            "<a href=\"approve-on-hold-sale/{$sRow->id}/1\" class=\"dropdown-item\">
                                            Approve
                            </a>";
                    $action .=
                            "<a href=\"approve-on-hold-sale/{$sRow->id}/0\" class=\"dropdown-item\">
                                            Disapprove
                            </a>";


			//to get agent name
            $updated_by = Audit::join('users', 'users.id', '=', 'audits.user_id')
                ->where(['audits.auditable_id' => $sRow->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                ->where('audits.message', 'like', '%has been updated%')
                ->select('users.name')
                ->orderBy('audits.created_at', 'desc')
				->first();

            $updated_by = $updated_by ? ucwords($updated_by->name) : ucwords($sRow->name);
			
			//get sale clearance date into audit
            $audit_clear_date= Audit::join('users', 'users.id', '=', 'audits.user_id')
                ->where(['audits.auditable_id' => $sRow->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                ->where('audits.message', 'like', '%sale-opened%')
                ->select('audits.updated_at')
                ->orderBy('audits.updated_at', 'desc')
                ->first();

            $clearance_date = @Carbon::parse($audit_clear_date->updated_at)->toFormattedDateString();
			
			$job_title_desc='';
            if($sRow->job_title_prof!=null)
                {
        			$job_prof_res = Specialist_job_titles::select('id','specialist_prof')->where("id", $sRow->job_title_prof)->first();
                    $job_title_desc = strtoupper($sRow->job_title.' ('.$job_prof_res->specialist_prof.')');
                }
                else
                {
                    $job_title_desc = strtoupper($sRow->job_title);
                }
			 $row_class='';
               if ($sRow->is_re_open=='1'){
                   $row_class = 'class_success';
               }else{
                   
               }
            $output['aaData'][] = array(
                "DT_RowId" => "row_{$sRow->id}",
            //    @$checkbox,
				 "row_class" => $row_class,
                '<span data-popup="tooltip" title="'.$sRow->name.'">'.@Carbon::parse($sRow->sale_added_date)->toFormattedDateString().'</span>',
                '<span data-popup="tooltip" title="'.$updated_by.'">'.@Carbon::parse($sRow->updated_at)->toFormattedDateString().'</span>',
				@$clearance_date,
				@$updated_by,
                @strtoupper($sRow->job_category),
                $job_title_desc,
                '<span data-popup="tooltip" title="'.$sRow->user->name.'">'.@$sRow->office_name.'</span>',
                @$sRow->unit_name,
                @strtoupper($sRow->postcode),
                @ucwords($sRow->job_type),
                @$sRow->experience,
                @$sRow->qualification,
                @$sRow->salary,
                @$sRow->sale_note,
                @$status,
                @$action
            );    
            $i++;

        }
         echo json_encode($output);
    }

    public function setSessionUnitID (Request $request) {
        Session::put('unit_id', $request->unit_list_id);
        Session::save();
        return "true";
    }

    public function getSessionUnitID () {
        $unit_id = Session::get('unit_id');
        return $unit_id;
    }

    public function setSessionTitleID (Request $request) {
        Session::put('title_id', $request->job_title_id);
        Session::save();
        return "true";
    }

    public function getSessionTitleID () {
        $title_id = Session::get('title_id');

        return $title_id;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $units = Office::join('units', 'offices.id', '=', 'units.head_office')
            ->select('units.*', 'offices.office_name')->where('units.status', 'active')->get();
        $head_offices = Office::where("status", "active")->get();
        return view('administrator.sale.open.create', compact('head_offices', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        date_default_timezone_set('Europe/London');
        $latitude = 00.000000;
        $longitude = 00.000000;
		$job_title_category = $request->Input('job_title');
        $job_title_prof_validate= '';
        if($job_title_category=='nonnurse specialist' || $job_title_category=='nurse specialist')
        {
            $job_title_prof_validate='required';
        }
        else
        {
            $job_title_prof_validate='';
        }
        $auth_user = Auth::user()->id;
        $validator = Validator::make($request->all(), [
            'job_category' => 'required|in:nurse,nonnurse,chef',
            'job_title' => 'required',
			'job_title_prof' => $job_title_prof_validate,
            'postcode' => [
                'required',
                Rule::unique('sales')->where( function ($query) use ($request) {
                    return $query->where('sales.job_title', $request->input('job_title'))
                        ->where('sales.postcode', $request->input('postcode'))
                        ->where('sales.job_category', $request->input('job_category'))
                        ->where('sales.head_office', $request->input('head_office'))
                        ->where('sales.head_office_unit', $request->input('head_office_unit'))
                        ->whereIn('sales.status', ['active','pending']);
                })
            ],
            'send_cv_limit' => 'required|integer|between:1,10',
            'job_type' => 'required',
            'timing' => 'required',
            'salary' => 'required',
            'experience' => 'required',
            'qualification'  => 'required',
            'benefits'  => 'required',
            'head_office'  => 'required',
            'sale_note'  => 'required|string',
            'head_office_unit' => 'required',
			 'job_description'=>'nullable',
        ], ['postcode.unique' => 'The combination: category, job title, postcode, head office and unit has already been taken.'])->validate();

        $postcode = $request->input('postcode');
        $data_arr = $this->geocode($postcode);
        if ($data_arr) {
            $latitude = $data_arr[0];
            $longitude = $data_arr[1];
        }
        $auth_user = Auth::user()->id;
        $sale_add_note = $request->input('sale_note').' --- By: '.auth()->user()->name.' Date: '.Carbon::now()->format('d-m-Y');
        $sale = new Sale();
        $sale->user_id = $auth_user;
        $sale->job_category = $request->input('job_category');
        $jobTitle = $request->input('job_title');
        $sale->job_title = $jobTitle;
        if($jobTitle === 'nurse specialist' || $jobTitle === 'nonnurse specialist'){
        $sale->job_title_prof = $request->input('job_title_prof');
        }
		//$sale->job_title_prof = $request->input('job_title_prof');
        $sale->postcode = $request->input('postcode');
        $sale->send_cv_limit = $request->input('send_cv_limit');
        $sale->job_type = $request->input('job_type');
        $sale->timing = $request->input('timing');
        $sale->salary = $request->input('salary');
        $sale->experience = $request->input('experience');
        $sale->qualification = $request->input('qualification');
        $sale->benefits = $request->input('benefits');
        $sale->head_office = $request->input('head_office');
        $sale->head_office_unit = $request->input('head_office_unit');
        $sale->posted_date = date("Y-m-d");
        $sale->sale_added_date = date("jS F Y");
        $sale->sale_added_time = date("h:i A");
        $sale->lat = $latitude;
        $sale->lng = $longitude;
		$sale->job_description = $request->input('job_description');
        $sale->save();
        $last_inserted_sale = $sale->id;
        if ($last_inserted_sale > 0) {
            $sale_uid = md5($last_inserted_sale);
            Sale::where('id', $last_inserted_sale)->update(['sale_uid' => $sale_uid]);
            $sale_note = new Sales_notes();
            $sale_note->sale_id = $last_inserted_sale;
            $sale_note->user_id = $auth_user;
            $sale_note->sales_note_added_date = date("jS F Y");
            $sale_note->sales_note_added_time = date("h:i A");
            $sale_note->sale_note = $sale_add_note;
            $sale_note->save();
            $last_inserted_sale_note_id = $sale_note->id;
            if($last_inserted_sale_note_id > 0){
                $sale_note_uid = md5($last_inserted_sale_note_id);
                Sales_notes::where('id',$last_inserted_sale_note_id)->update(['sales_notes_uid' => $sale_note_uid]);
                return redirect('sales')->with('success', 'Job ('.$sale->postcode.') created Successfully');
            }

        } else {
            return redirect('sales.create')->with('error', 'Something went wrong!!');
        }
    }

    public function show($id)
    {
        $sale = Sale::with(['office', 'unit'])->find($id);
		$sec_job_data = Specialist_job_titles::select("*")->where("id",$sale->job_title_prof)->first();
        return view('administrator.sale.open.show', compact('sale','sec_job_data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $sale = Sale::find($id);
        $sec_job_data='';
        $spec_all_jobs_data='';
        if($sale->job_title=='nonnurse specialist' || $sale->job_title=='nurse specialist')
        {
            $sec_job_data = Specialist_job_titles::select("*")->where("id",$sale->job_title_prof)->first();
            $spec_all_jobs_data = Specialist_job_titles::select("*")->where("specialist_title",$sale->job_title)->get();
        }
        // print_r($sale);exit();

        // echo $sale->id;exit();
       
        // print_r($spec_all_jobs_data);exit();

        // echo $sec_job_data->specialist_prof;exit();
        $sent_cv_count = Cv_note::where(['sale_id' => $id, 'status' => 'active'])->count();
        //$office_types = Office::where("status","active")->get();
        $office_types = Office::all();
        //        $latest_module_note = $sale->latest_module_note();
        //        if (!$latest_module_note) {
        //            $latest_module_note = $sale->latest_sale_note();
        //            $sale_note = ['key' => $latest_module_note->id.'-sales_notes', 'sale_note' => $latest_module_note->sale_note];
        //        } else {
        //            $sale_note = ['key' => $latest_module_note->id.'-module_notes', 'sale_note' => $latest_module_note->details];
                    
        //        }
        if($sec_job_data!='' || $spec_all_jobs_data!='')
        {
            return view('administrator.sale.open.edit', compact('sale', 'office_types', 'sent_cv_count','sec_job_data','spec_all_jobs_data'));
        }
        else
        {
            return view('administrator.sale.open.edit', compact('sale', 'office_types', 'sent_cv_count'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $back_url = explode('/', $request->input('previous_url'));
        $latitude = 00.000000;
        $longitude = 00.000000;

        $auth_user = Auth::user()->id;
        $inputs = $request->all();
        $job_title_category = explode('-', $request->Input('job_title'));
         $job_title_prof_validate= '';
        if($job_title_category[0]=='nonnurse specialist' || $job_title_category[0]=='nurse specialist')
        {
            $job_title_prof_validate='required';
        }
        else
        {
            $job_title_prof_validate='';
        }
        $inputs['job_category'] = $job_title_category[1];
        $request->replace($inputs);
        $sent_cv_count = Cv_note::where(['sale_id' => $id, 'status' => 'active'])->count();

        $validator = Validator::make($request->all(), [
            //            'sale_note_key' => 'required',
            'job_category' => 'required|in:nurse,nonnurse,chef',
            'job_title' => 'required',
			'job_title_prof' => $job_title_prof_validate,
            'postcode' => [
                'required',
                Rule::unique('sales')->where( function ($query) use ($request) {
                    return $query->where('sales.job_title', $request->input('job_title'))
                        ->where('sales.postcode', $request->input('postcode'))
                        ->where('sales.job_category', $request->input('job_category'))
                        ->where('sales.head_office', $request->input('head_office'))
                        ->where('sales.head_office_unit', $request->input('head_office_unit'))
                        ->whereIn('sales.status', ['active','pending']);
                })->ignore($id)
            ],
            'send_cv_limit' => 'required|integer|between:'.$sent_cv_count.',10',
            'job_type' => 'required',
            'timing' => 'required',
            'salary' => 'required',
            'experience' => 'required',
            'qualification'  => 'required',
            'benefits'  => 'required',
            'head_office'  => 'required',
			 'sale_note'  => 'required|string',
            'head_office_unit' => 'required',
            //            'sale_note'  => 'required|string',
			  'job_description'=>'nullable',

        ], [
            'postcode.unique' => 'The combination: category, job title, postcode, head office and unit has already been taken.',
            //            'sale_note_key.required' => 'Missing sale note info.'
        ])->validate();

        $postcode = $request->input('postcode');
        $data_arr = $this->geocode($postcode);
        if ($data_arr) {
            $latitude = $data_arr[0];
            $longitude = $data_arr[1];
        }

        $sale = Sale::find($id);
            //        $sale->user_id = $auth_user;
        $jobTitle = $request->input('job_title');
        $sale->job_title = $job_title_category[0];
        if($job_title_category[0] === 'nurse specialist' || $job_title_category[0] === 'nonnurse specialist'){
            $sale->job_title_prof = $request->input('job_title_prof');
            }
		 else
            {
                $sale->job_title_prof = null;
            }
		//$sale->job_title_prof = $request->Input('job_title_prof');
        $sale->job_category = $request->Input('job_category');
        $sale->postcode = $request->Input('postcode');
        $sale->send_cv_limit = $request->Input('send_cv_limit');
        $sale->job_type = $request->Input('job_type');
        $sale->timing = $request->Input('timing');
        $sale->salary = $request->Input('salary');
        $sale->experience = $request->Input('experience');
        $sale->qualification = $request->Input('qualification');
        $sale->benefits = $request->Input('benefits');
        $sale->head_office = $request->Input('head_office');
        $sale->head_office_unit = $request->Input('head_office_unit');
        //        $sale->posted_date = date('Y-m-d');
        $sale->lat = $latitude;
        $sale->lng = $longitude;
		$sale->job_description = $request->input('job_description');
        $updated = $sale->update();

		if ($request->Input('sale_note')!='') {
            $sale_note = new Sales_notes();
            $sale_note->sale_id = $id;
            $sale_note->user_id = $auth_user;
            $sale_note->sales_note_added_date = date("jS F Y");
            $sale_note->sales_note_added_time = date("h:i A");
            $sale_note->sale_note = 'Date: '.Carbon::now()->format('d-m-Y').' '.$request->input('sale_note').' ---By: '.auth()->user()->name;
            $sale_note->save();
            $last_inserted_sale_note_id = $sale_note->id;
            if($last_inserted_sale_note_id > 0){
                $sale_note_uid = md5($last_inserted_sale_note_id);
                Sales_notes::where('id',$last_inserted_sale_note_id)->update(['sales_notes_uid' => $sale_note_uid]);
                // return redirect('sales')->with('success', 'Job ('.$sale->postcode.') created Successfully');
            }

        } 
        //        $sale_note_key = explode('-', $request->input('sale_note_key'));
        //        $column = ($sale_note_key[1] == 'sales_notes') ? 'sale_note' : 'details';
        //        $sale_note_updated = \Illuminate\Support\Facades\DB::table($sale_note_key[1])->where('id', $sale_note_key[0])->update([$column => $request->input('sale_note')]);

                //add a new note entry in ModuleNote table for sales
        //        $module_note = new ModuleNote();
        //        $module_note->user_id = $auth_user;
        //        $module_note->module_noteable_id = $id;
        //        $module_note->module_noteable_type = 'Horsefly\Sale';
        //        $module_note->module_note_added_date = date('jS F Y');
        //        $module_note->module_note_added_time = date("h:i A");
        //        $module_note->details = $request->input('sale_note');
        //        $module_note->status = 'active';
        //        $module_note->save();
        //        $last_inserted_module_note = $module_note->id;

        if ($updated) {
            //            $module_note_uid = md5($last_inserted_module_note);
            //            DB::table('module_notes')->where('id', $last_inserted_module_note)->update(['module_note_uid' => $module_note_uid]);
            //            return redirect('sales')->with('updateSuccessMsg', 'Job has been updated.');
            return redirect($back_url[count($back_url) - 1])->with('updateSuccessMsg', 'Job has been updated.');
        } else {
            return redirect('sales.edit')->with('sale_edit_error', 'WHOOPS! Job could not be updated.');
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notes = request()->details;
        $sale = Sale::find($id);
        $status = $sale->status;
        if ($status == 'active') {
            if (Sale::where('id', $id)->update(['status' => 'disable'])) {
                Sale::where('id', $id)->update(['sale_notes' => $notes]);
                return redirect('sales')->with('saleDeleteSuccessMsg', 'Job has been disabled Successfully');
            } else {
                return redirect('sales')->with('saleDeleteErrMsg', 'WHOOPS! Something Went Wrong!!');
            }

        } else if ($status == 'disable') {
            if (DB::table('sales')->where('id', $id)->update(['status' => 'active'])) {
                return redirect('sales')->with('saleDeleteSuccessMsg', 'Job has been enabled Successfully');
            } else {
                return redirect('sales')->with('saleDeleteErrMsg', 'WHOOPS! Something Went Wrong!!');
            }

        }
    }

    public function getCloseOrOpenSale(Request $request){
        //        $sale_observer = new SaleObserver();

        date_default_timezone_set('Europe/London');
        $notes = $request->input('details');
        $id = $request->input('sale_id');
        $auth_user = Auth::user()->id;
        $sale = Sale::find($id);
        $status = $sale->status;
        $audit = new ActionObserver();
        if ($status == 'active') {
            //            Sale::where('id', $id)->update(['status' => 'disable']);
            $sale->update(['status' => 'disable']);
            $audit->changeSaleStatus($sale, ['status' => $sale->status]);
            $sale_note = new Sales_notes();
            $sale_note->sale_id = $id;
            $sale_note->user_id = $auth_user;
            $sale_note->sales_note_added_date = date("jS F Y");
            $sale_note->sales_note_added_time = date("h:i A");
            $sale_note->sale_note = $notes;
            $sale_note->save();
            //            $sale_observer->updated($sale, "Status: Disable updated for {$sale->job_title} successfully", ["status" => "disable"]);

            $last_inserted_sale_note_id = $sale_note->id;
            if($last_inserted_sale_note_id > 0){
                $sale_note_uid = md5($last_inserted_sale_note_id);
                Sales_notes::where('id',$last_inserted_sale_note_id)->update(['sales_notes_uid' => $sale_note_uid,'status' => 'disable']);
                return redirect('sales')->with('saleDeleteSuccessMsg', 'Job has been disabled Successfully');
            }
        } else if ($status == 'disable') {
            //            Sale::where('id', $id)->update(['status' => 'active']);
            $sale->update(['status' => 'active']);
            $audit->changeSaleStatus($sale, ['status' => $sale->status]);
            Sales_notes::where('sale_id',$id)->update(['status' => 'disable']);
            $sale_note = new Sales_notes();
            $sale_note->sale_id = $id;
            $sale_note->user_id = $auth_user;
            $sale_note->sales_note_added_date = date("jS F Y");
            $sale_note->sales_note_added_time = date("h:i A");
            $sale_note->sale_note = $notes;
            $sale_note->save();
            //            $sale_observer->updated($sale, "Status: Active updated for {$sale->job_title} successfully", ["status" => "active"]);

            $last_inserted_sale_note_id = $sale_note->id;
            if($last_inserted_sale_note_id > 0){
                $sale_note_uid = md5($last_inserted_sale_note_id);
                Sales_notes::where('id',$last_inserted_sale_note_id)->
                update(['sales_notes_uid' => $sale_note_uid,'status' => 'active']);
                return redirect('sales')->with('saleDeleteSuccessMsg', 'Job has been enabled Successfully');
            }
        }
    }

    public function getCloseSale(Request $request)
    {
        //        $sale_observer = new SaleObserver();

        date_default_timezone_set('Europe/London');
        $notes = $request->input('details');
        $id = $request->input('sale_id');
        $auth_user = Auth::user()->id;
        $sale = Sale::find($id);
        $status = $sale->status;
        $audit = new ActionObserver();
        if ($status == 'active') {
            //            Sale::where('id', $id)->update(['status' => 'disable']);
            $sale->update(['status' => 'disable']);
            $audit->changeSaleStatus($sale, ['status' => $sale->status]);
            $sale_note = new Sales_notes();
            $sale_note->sale_id = $id;
            $sale_note->user_id = $auth_user;
            $sale_note->sales_note_added_date = date("jS F Y");
            $sale_note->sales_note_added_time = date("h:i A");
            $sale_note->sale_note = $notes;
            $sale_note->save();
            //            $sale_observer->updated($sale, "Status: Disable updated for {$sale->job_title} successfully", ["status" => "disable"]);

            $last_inserted_sale_note_id = $sale_note->id;
            if($last_inserted_sale_note_id > 0){
                $sale_note_uid = md5($last_inserted_sale_note_id);
                Sales_notes::where('id',$last_inserted_sale_note_id)->update(['sales_notes_uid' => $sale_note_uid,'status' => 'disable']);
                return redirect('sales')->with('saleDeleteSuccessMsg', 'Job has been disabled Successfully');
            }
        }
        return redirect('sales')->with('saleDeleteSuccessMsg', 'Job already disabled.');
    }
	
	public function onHoldSale(Request $request)
    {
        date_default_timezone_set('Europe/London');

        $input = $request->all();
        $input['onhold_module'] = filter_var($request->input('onhold_module'), FILTER_SANITIZE_STRING);
        $input['onhold_details'] = filter_var($request->input('onhold_details'), FILTER_SANITIZE_STRING);

        $request->replace($input);

        $validator = Validator::make($request->all(), [
            'onhold_module' => "required|in:Office,Sale,Unit,Applicant",
            'onhold_module_key' => "required",
            'onhold_details' => "required|string",
        ])->validate();

        $html = '<div class="alert alert-danger border-0 alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    <span class="font-weight-semibold">'.$request->input('onhold_module').'</span> On Hold Sale Could Not Be Added
                </div>';

        $model_class = 'Horsefly\\' . $request->input('onhold_module');
        $model = $model_class::find($request->input('onhold_module_key'));
        if ($model) {
            $module_note = $model->module_notes()->create([
                'user_id' => Auth::id(),
                'module_note_added_date' => date('jS F Y'),
                'module_note_added_time' => date("h:i A"),
                'details' => $request->input('onhold_details'),
                'status' => 'active'
            ]);
            Sale::where('id', $model->id)->update(['is_on_hold' => '2']);
            $last_inserted_module_note = $module_note->id;
            if($last_inserted_module_note){
                $module_note_uid = md5($last_inserted_module_note);
                DB::table('module_notes')->where('id', $last_inserted_module_note)->update(['module_note_uid' => $module_note_uid]);
                $html = '<div class="alert alert-success border-0 alert-dismissible" id="alert_note'.$model->id.'">
							<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
							<span class="font-weight-semibold">'.$request->input('onhold_module').'</span> Note Added Successfully
						</div>';
                return redirect('sales')->with('success', 'Your request has been successfully submitted and is awaiting approval.');
            }
            else {
                return redirect('sales')->with('error', 'Something went wrong! Sale is not added in on hold sales!!!');

            }
        } else {
            return redirect('sales')->with('error', 'Something went wrong! Sale is not added in on hold sales!!!');

        }
    }
	
	public function approveOnHoldSale(Request $request,$id,$status)
    {

        $sale = Sale::find($id);

        if($sale){
            if($status == '1'){
                $sale->is_on_hold =  $status;
                $sale->update();
                $string = "Approved";
            }else{
                $sale->is_on_hold =  $status;
                $sale->update();
                $string = "Disapproved";

                // Find the latest record matching the conditions
                $latestNote = DB::table('module_notes')
                    ->where('module_noteable_type', 'Horsefly\Sale')
                    ->where('status', 'active')
                    ->where('module_noteable_id', $sale->id)
                    ->latest('id')
                    ->first(); // Get the latest record

                // Check if a record was found
                if ($latestNote) {
                // Delete the latest record
                DB::table('module_notes')->where('id', $latestNote->id)->delete();
                }
            }

            return redirect('pending-onhold-sales')->with('success', 'Sale is '.$string.' for on hold sale');
        }
        else {
            return redirect('pending-onhold-sales')->with('error', 'Something went wrong! Sale is not '.$string.' for on hold sales!!!');

        }
       
    }
	
	public function unHoldSale(Request $request)
    {
        date_default_timezone_set('Europe/London');

        $input = $request->all();
        $input['module'] = filter_var($request->input('module'), FILTER_SANITIZE_STRING);
        $input['details'] = filter_var($request->input('details'), FILTER_SANITIZE_STRING);

        $request->replace($input);

        $validator = Validator::make($request->all(), [
            'module' => "required|in:Office,Sale,Unit,Applicant",
            'module_key' => "required",
            'details' => "required|string",
        ])->validate();

        $html = '<div class="alert alert-danger border-0 alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    <span class="font-weight-semibold">'.$request->input('module').'</span> On Hold Sale Could Not Be Added
                </div>';

        $model_class = 'Horsefly\\' . $request->input('module');
        $model = $model_class::find($request->input('module_key'));
        if ($model) {
            $module_note = $model->module_notes()->create([
                'user_id' => Auth::id(),
                'module_note_added_date' => date('jS F Y'),
                'module_note_added_time' => date("h:i A"),
                'details' => $request->input('details'),
                'status' => 'active'
            ]);
            Sale::where('id', $model->id)->update(['is_on_hold' => '0']);
            $last_inserted_module_note = $module_note->id;
            if($last_inserted_module_note){
                $module_note_uid = md5($last_inserted_module_note);
                DB::table('module_notes')->where('id', $last_inserted_module_note)->update(['module_note_uid' => $module_note_uid]);
                $html = '<div class="alert alert-success border-0 alert-dismissible" id="alert_note'.$model->id.'">
							<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
							<span class="font-weight-semibold">'.$request->input('module').'</span> Note Added Successfully
						</div>';
                return redirect('all-on-hold-sales')->with('success', 'Sale is added active sales');
            }
            else {
                return redirect('all-on-hold-sales')->with('error', 'Something went wrong! Sale is not added in active sales!!!');

            }
        } else {
            return redirect('all-on-hold-sales')->with('error', 'Something went wrong! Sale is not added in active sales!!!');

        }
    }

    public function getOpenSale(Request $request)
    {
        //        $sale_observer = new SaleObserver();

        date_default_timezone_set('Europe/London');
        $notes = $request->input('details');
        $id = $request->input('sale_id');
        $auth_user = Auth::user()->id;
        $sale = Sale::find($id);
        $existing_open_sales = Sale::where([
            'job_title' => $sale->job_title,
            'postcode' => $sale->postcode,
            'job_category' => $sale->job_category,
            'status' => 'active'
        ])->count();
        if ($existing_open_sales) {
            return redirect('all-closed-sales')->with('error', 'Sale with same combination (Job Category: '.ucwords($sale->job_category).' | Job Title: '.ucwords($sale->job_title).' | Postcode: '.$sale->postcode.') already open');
        } else {
            $status = $sale->status;
            $audit = new ActionObserver();
            if ($status == 'disable') {
                //            Sale::where('id', $id)->update(['status' => 'active']);
                //$sale->update(['status' => 'active']);
				     $sale->update(['status' => 're_open']);
                $audit->changeSaleStatus($sale, ['status' => $sale->status]);
                Sales_notes::where('sale_id', $id)->update(['status' => 'disable']);
                $sale_note = new Sales_notes();
                $sale_note->sale_id = $id;
                $sale_note->user_id = $auth_user;
                $sale_note->sales_note_added_date = date("jS F Y");
                $sale_note->sales_note_added_time = date("h:i A");
                $sale_note->sale_note = $notes;
                $sale_note->save();
                //            $sale_observer->updated($sale, "Status: Active updated for {$sale->job_title} successfully", ["status" => "active"]);

                $last_inserted_sale_note_id = $sale_note->id;
                if ($last_inserted_sale_note_id > 0) {
                    $sale_note_uid = md5($last_inserted_sale_note_id);
                    Sales_notes::where('id', $last_inserted_sale_note_id)->
                    update(['sales_notes_uid' => $sale_note_uid, 'status' => 'active']);
                    return redirect('all-closed-sales')->with('saleDeleteSuccessMsg', 'Job has been enabled Successfully');
                }
            }
        }
        return redirect('all-closed-sales')->with('saleDeleteSuccessMsg', 'Job already enabled.');
    }

    public function getAllPslClientSale()
    {
        $psl_office = Office::where("office_type", "psl")->where(["status" => "active"])->get();
        return view('administrator.sale.psl.index', compact('psl_office'));
    }

    public function getAllPslUnitDetails($id)
    {
        $units = Unit::where(["head_office" => $id, "status" => "active"])->get();
        return view('administrator.sale.psl.show', compact('units'));

    }

    public function getAllNonPslClientSale()
    {
        $non_psl_office = Office::where(["office_type" => "non psl", "status" => "active"])->get();
        return view('administrator.sale.non_psl.index', compact('non_psl_office'));
    }

    public function getAllNonPslUnitDetails($id)
    {
        $units = Unit::where(["head_office" => $id, "status" => "active"])->get();
        return view('administrator.sale.non_psl.show', compact('units'));

    }

    public function getAllClosedSales()
    {
		$offices = Office::where('status','active')->select('id','office_name')->orderBy('office_name','asc')->get();
        return view('administrator.sale.close.index',compact('offices'));
    }

	public function getOnHoldSales()
    {
        $head_offices = Office::where("status","active")->get();
        $items = array();
        foreach($head_offices as $office){
            $items[$office->id] = $office->office_name;
        }
        $head_office_users = Office::join('users', 'users.id', '=', 'offices.user_id')
            ->select('users.id', 'users.name')
            ->distinct('user_id')
            ->get()->toArray();
        return view('administrator.sale.onhold.on_hold', compact('head_offices', 'head_office_users'));
    }
	
	public function getAllOnHoldSales(Request $request)
    {
        $job_category = $request->filled('job_category') ? $request->get('job_category') : null;
        $office = $request->filled('office') ? $request->get('office') : null;
        $user = $request->filled('user') ? $request->get('user') : null;
        $cv_sent_option = $request->filled('cv_sent_option') ? $request->get('cv_sent_option') : null;

        $auth_user = Auth::user();
        $result = Office::with('user','sales.latest_sale_note')
            ->join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->join('users', 'users.id', '=', 'sales.user_id');
        if ($cv_sent_option) {
            if ($cv_sent_option == 'max') {
                $result = $result->where('sales.send_cv_limit', '=', function ($query) {
                    $query->select(DB::raw('count(cv_notes.sale_id) AS sent_cv_count FROM cv_notes WHERE cv_notes.sale_id=sales.id AND cv_notes.status="active"'));
                });
            } elseif ($cv_sent_option == 'not_max') {
                $result = $result->where('sales.send_cv_limit', '>', function ($query) {
                    $query->select(DB::raw('count(cv_notes.sale_id) AS sent_cv_count FROM cv_notes WHERE cv_notes.sale_id=sales.id AND cv_notes.status="active" HAVING sent_cv_count > 0 AND sent_cv_count <> sales.send_cv_limit'));
                });
            } elseif ($cv_sent_option == 'zero') {
                $result = $result->where('sales.send_cv_limit', '>', function ($query) {
                    $query->select(DB::raw('count(cv_notes.sale_id) AS sent_cv_count FROM cv_notes WHERE cv_notes.sale_id=sales.id AND cv_notes.status="active" HAVING sent_cv_count = 0'));
                });
            }

        }
        $result = $result->select('sales.*', 'offices.office_name','units.contact_name','users.name',
                'units.contact_email','units.unit_name','units.contact_phone_number')
            ->where('sales.status','active')->where('sales.is_on_hold','1');

        if ($office) {
            $result = $result->where('sales.head_office', '=', $office);
        }
        if ($job_category) {
            $result = $result->where('sales.job_category', '=', $job_category);
        }
        $result = $result->selectRaw(DB::raw("(SELECT COUNT(*) FROM cv_notes WHERE cv_notes.sale_id = sales.id AND cv_notes.status = 'active') as no_of_sent_cv"));
        $aColumns = ['sale_added_date','updated_at','job_category','job_title',
        'office_name','unit_name','postcode','job_type','experience','qualification','salary'];
        $iStart = $request->get('iDisplayStart');
        $iPageSize = $request->get('iDisplayLength');
        // $iPageSize = 9;

        $order = 'id';
        $sort = ' DESC';

        if ($request->get('iSortCol_0')!='') { //iSortingCols
      
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

        } else {
            $result = $result->orderBy('sales.updated_at', 'DESC');
        }

        $sKeywords = $request->get('sSearch');
        if ($sKeywords != "") {

            $result->Where(function($query) use ($sKeywords) {
                $query->orWhere('job_title', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('office_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('unit_name', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('postcode', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('job_type', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('experience', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('qualification', 'LIKE', "%{$sKeywords}%");
                $query->orWhere('salary', 'LIKE', "%{$sKeywords}%");
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
        $row_data = array();
        $output = array(
             "sEcho" => intval($request->get('sEcho')),
             "iTotalRecords" => $iTotal,
             "iTotalDisplayRecords" => $iFilteredTotal,
             "aaData" => array()
        );
        
        $i = 0;
        foreach ($saleData as $sRow) 
        {

            $checkbox = "<label class=\"mt-checkbox mt-checkbox-single mt-checkbox-outline\">
                             <input type=\"checkbox\" class=\"checkbox-index\" value=\"{$sRow->id}\">
                             <span></span>
                          </label>";
            //            if($sRow->status == 'active'){
            //                $status = '<h5><span class="badge badge-success">Active</span></h5>';
            //            }else{
            //                $status = '<h5><span class="badge badge-danger">Disable</span></h5>';
            //            }
            $status = $sRow->no_of_sent_cv.' / '.$sRow->send_cv_limit;

            $url = '/close-sale';
            $url_on_hold = '/un-hold-sale';
            $url_note = route('module_note.store');
            $csrf = csrf_token();

            $action = "<div class=\"list-icons\">
                        <div class=\"dropdown\">
                            <a href=\"#\" class=\"list-icons-item\" data-toggle=\"dropdown\">
                                <i class=\"icon-menu9\"></i>
                            </a>
                            <div class=\"dropdown-menu dropdown-menu-right\">";
            if ($auth_user->hasPermissionTo('sale_on-hold')) {
                $action .=
                                "<a href=\"#\" class=\"dropdown-item\"
                                               data-controls-modal=\"#un_hold_sale{$sRow->id}\"
                                               data-backdrop=\"static\"
                                               data-keyboard=\"false\" data-toggle=\"modal\"
                                               data-target=\"#un_hold_sale{$sRow->id}\">
                                               Unhold Sale
                                </a>";
            }
            if ($auth_user->hasPermissionTo('sale_note-history')) {
                $action .=
                                "<a href=\"#\" class=\"dropdown-item notes_history\" data-sale=\"{$sRow->id}\" data-controls-modal=\"#notes_history{$sRow->id}\"
                                               data-backdrop=\"static\"
                                               data-keyboard=\"false\" data-toggle=\"modal\"
                                               data-target=\"#notes_history{$sRow->id}\"> 
                                               Onhold Sale Note
                                </a>";
            }
			 $action .=      "<a href=\"/onhold-sale-history/{$sRow->id}\" class=\"dropdown-item\">History</a>";
            $action .=
                            "</div>
                        </div>
                      </div>";
            
           
                        if ($auth_user->hasPermissionTo('sale_on-hold')) {
                            $action .=
                                    "<div id=\"un_hold_sale{$sRow->id}\" class=\"modal fade\" tabindex=\"-1\">
                                        <div class=\"modal-dialog modal-lg\">
                                            <div class=\"modal-content\">
                                                <div class=\"modal-header\">
                                                    <h5 class=\"modal-title\">Unhold Sale</h5>
                                                    <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                                </div>
                                                <form action=\"{$url_on_hold}\" method=\"POST\" class=\"form-horizontal\" id=\"onhold_form{$sRow->id}\">
                                                    <input type=\"hidden\" name=\"_token\" value=\"{$csrf}\">
                                                    <input type=\"hidden\" name=\"module\" value=\"Sale\">
                                                    <div class=\"modal-body\">
                                                        <div id=\"note_alert{$sRow->id}\"></div>
                                                        <div class=\"form-group row\">
                                                            <label class=\"col-form-label col-sm-3\">Details</label>
                                                            <div class=\"col-sm-9\">
                                                                <input type=\"hidden\" name=\"module_key\" value=\"{$sRow->id}\">
                                                                <textarea name=\"details\" id=\"note_details{$sRow->id}\" class=\"form-control\" cols=\"30\" rows=\"4\"
                                                                          placeholder=\"TYPE HERE ..\" required></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
            
                                                    <div class=\"modal-footer\">
                                                        <button type=\"button\" class=\"btn btn-link legitRipple\" data-dismiss=\"modal\">
                                                            Close
                                                        </button>
                                                        <button type=\"submit\" data-note_key=\"{$sRow->id}\" class=\"btn bg-teal legitRipple\">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>";
                        }
             
            if ($auth_user->hasPermissionTo('sale_note-history')) {
                $action .=
                        "<div id=\"notes_history{$sRow->id}\" class=\"modal fade\" tabindex=\"-1\">
                            <div class=\"modal-dialog modal-lg\">
                                <div class=\"modal-content\">
                                    <div class=\"modal-header\">
                                        <h5 class=\"modal-title\">Onhold Sales Note - 
                                        <span class=\"font-weight-semibold\">{$sRow->job_title}</span></h5>
                                        <button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>
                                    </div>
                                    <div class=\"modal-body\" id=\"onhold_sales_notes_history{$sRow->id}\" style=\"max-height: 500px; overflow-y: auto;\">
                                    </div>
                                    <div class=\"modal-footer\">
                                        <button type=\"button\" class=\"btn bg-teal legitRipple\" data-dismiss=\"modal\">CLOSE
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                      ";
            }

            $updated_by = Audit::join('users', 'users.id', '=', 'audits.user_id')
                ->where(['audits.auditable_id' => $sRow->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                ->where('audits.message', 'like', '%has been updated%')
                ->select('users.name')
                ->orderBy('audits.created_at', 'desc')->first();


            $updated_by = $updated_by ? $updated_by->name : $sRow->name;
			$job_title_desc='';
            if(@$sRow->job_title_prof!='')
                {
                    $job_title_desc = @$sRow->job_title.' ('.@$sRow->job_title_prof.')';
                }
                else
                {
                    $job_title_desc = @$sRow->job_title;
                }
            $output['aaData'][] = array(
                "DT_RowId" => "row_{$sRow->id}",
            //    @$checkbox,
                '<span data-popup="tooltip" title="'.$sRow->name.'">'.@Carbon::parse($sRow->sale_added_date)->toFormattedDateString().'</span>',
                '<span data-popup="tooltip" title="'.$updated_by.'">'.@Carbon::parse($sRow->updated_at)->toFormattedDateString().'</span>',
                @strtoupper($sRow->job_category),
                $job_title_desc,
                '<span data-popup="tooltip" title="'.$sRow->user->name.'">'.@$sRow->office_name.'</span>',
                @$sRow->unit_name,
                @strtoupper($sRow->postcode),
                @ucwords($sRow->job_type),
                @$sRow->experience,
                @$sRow->qualification,
                @$sRow->salary,
                @$status,
                @$action
            );    
            $i++;

        }


       //  print_r($output);
         echo json_encode($output);
    }

    public function test()
    {
        echo 'test';exit();
        // $close_sales = Office::join('sales', 'offices.id', '=', 'sales.head_office')
        // ->join('units','sales.head_office_unit', '=', 'units.id')
        // ->join('users','sales.user_id', '=', 'users.id')
        // ->select('sales.*','offices.office_name','units.contact_name','users.name',
        //     'units.contact_email','units.unit_name','units.contact_phone_number')
        // ->where('sales.status','disable')
        // ->orderBy('sales.created_at', 'desc')
        // ->limit(5)
        // ->get();

        $resortData = DB::table('sales')
        ->Join('units','sales.head_office_unit', '=', 'units.id')
        ->Join('users','sales.user_id', '=', 'users.id')
        ->Join('offices','offices.user_id', '=', 'users.id')
        ->select('sales.*','units.contact_name','users.name',
        'units.contact_email','offices.office_name','units.unit_name','units.contact_phone_number')
        ->where('sales.status','disable')
        ->orderBy('sales.created_at', 'desc')
        ->limit(5)
       ->get();
        // $res = DB::table('sales')
        //     ->orderBy('updated_at','desc')
        //     ->limit(10)
        //     ->get()->unique('user_id');;

      
        echo '<pre>';print_r($resortData);echo '<pre>';exit();
    }

    public function allClosedSales()
    {
        $close_sales = Office::join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->select('sales.*','offices.office_name','units.contact_name','users.name',
                'units.contact_email','units.unit_name','units.contact_phone_number')
            ->where(['sales.status' => 'disable']);

        $auth_user = Auth::user();
        $raw_columns = ['job_title','close_date','agent_by','created_at','updated_at','job_type','status'];
        $datatable = datatables()->of($close_sales)
			->editColumn('job_title', function ($close_sales) {
            $job_title_desc = ($close_sales->job_title_prof!='')?$close_sales->job_title.' ('.$close_sales->job_title_prof.')':$close_sales->job_title;
            return $job_title_desc;
     })
			 ->addColumn("close_date", function ($closed_sale){
                $close_date_query = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%sale-closed%')
                    ->select('audits.updated_at')
                    ->orderBy('audits.updated_at','desc')
                    ->first();
                $close_date = @Carbon::parse($close_date_query->updated_at)->toFormattedDateString();
                return $close_date;
            })
			 ->addColumn("agent_by",function($closed_sale){
                $updated_by = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been updated%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')->first();
                $updated_by = $updated_by ? $updated_by->name : $closed_sale->name;
                return ucwords($updated_by);
            })
            ->addColumn("created_at", function ($closed_sale) {
                return '<span data-popup="tooltip" title="'.$closed_sale->name.'">'.Carbon::parse($closed_sale->sale_added_date)->toFormattedDateString().'</span>';
            })->addColumn("updated_at", function ($closed_sale) {
                $updated_by = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been updated%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')->first();
                $updated_by = $updated_by ? $updated_by->name : $closed_sale->name;
                return '<span data-popup="tooltip" title="'.$updated_by.'">'.Carbon::parse($closed_sale->updated_at)->toFormattedDateString().'</span>';
            })->addColumn("job_type", function ($closed_sale) {
                return ucwords($closed_sale->job_type);
            })->addColumn("status", function ($closed_sale) {
                return "<h5><span class=\"badge badge-danger\">".ucfirst($closed_sale->status)."</span></h5>";
            });
        if ($auth_user->hasAnyPermission(['sale_open','sale_closed-sale-notes'])) {
            $datatable = $datatable->addColumn("action", function ($closed_sale) use ($auth_user) {
                $action =
                    '<div class="list-icons">
                        <div class="dropdown">
                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                <i class="icon-menu9"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    $action .=
                                '<a href="#" class="dropdown-item"
                                   data-controls-modal="#open_sale'.$closed_sale->id.'"
                                   data-backdrop="static"
                                   data-keyboard="false" data-toggle="modal"
                                   data-target="#open_sale'.$closed_sale->id.'"
                                > Open </a>';
                }
                if ($auth_user->hasPermissionTo('sale_closed-sale-notes')) {
                    $action .=
                                '<a href="' . route('viewAllCloseNotes', $closed_sale->id) . '" class="dropdown-item">Notes</a>';
                }
                $action .=
                            '</div>
                        </div>
                    </div>';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    /*** Open Sale Modal */
                    $action .=
                        '<div id="open_sale'.$closed_sale->id.'" class="modal fade" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Close Sale Notes</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="' . route('openSale') . '"
                                          method="POST" class="form-horizontal">'
                                        . csrf_field() .
                                        '<div class="modal-body">
                                            <div class="form-group row">
                                                <label class="col-form-label col-sm-3">Details</label>
                                                <div class="col-sm-9">
                                                    <input type="hidden" name="sale_id" value="' . $closed_sale->id . '">
                                                    <textarea name="details" class="form-control" cols="30" rows="4"
                                                              placeholder="TYPE HERE.." required></textarea>
                                                </div>
                                            </div>
                                        </div>
                    
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn bg-teal legitRipple">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>';
                    /*** /open sale modal */
                }
                return $action;
            });
            $raw_columns = ['job_title','created_at','updated_at','job_type','status','action'];
        }
        $datatable = $datatable->rawColumns($raw_columns)->make(true);
        return $datatable;
    }

    public function export_email(Request $request){
        $job_category =  $request->job_category;

        return Excel::download(new ClosedSalesEmailExport($job_category), 'close_sales_'. $job_category .'.csv');
    }
	
	public function allClosedSalesNurse()
    {
        $close_sales = Office::join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->select('sales.*','offices.office_name','units.contact_name','users.name',
                'units.contact_email','units.unit_name','units.contact_phone_number',
                DB::raw("MAX(CASE WHEN sales.job_title = 'nonnurse specialist' THEN sales.job_title END) AS job_title_prof_calc"),
                DB::raw("MAX(sales.created_at) AS most_recent_created_at"))
            ->where(['sales.status' => 'disable', 'sales.is_on_hold' => '0'])
            ->where('sales.job_category','nurse')
            ->orderBy('sales.updated_at','desc')
            ->groupBy('sales.id');

        $auth_user = Auth::user();
        $raw_columns = ['job_title','close_date','agent_by','created_at','updated_at','job_type','status'];
        $datatable = datatables()->of($close_sales)
            ->editColumn('job_title', function ($close_sales) {
                $job_title_desc = ($close_sales->job_title_prof!='')?$close_sales->job_title.' ('.$close_sales->job_title_prof.')':$close_sales->job_title;
                return $job_title_desc;
            })
            ->addColumn("close_date", function ($closed_sale){
                $close_date_query = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%sale-closed%')
                    ->select('audits.updated_at')
                    ->orderBy('audits.updated_at','desc')
                    ->first();
                $close_date = @Carbon::parse($close_date_query->updated_at)->toFormattedDateString();
                return $close_date;
            })
            ->addColumn("created_at", function ($closed_sale) {
                return '<span data-popup="tooltip" title="'.$closed_sale->name.'">'.Carbon::parse($closed_sale->sale_added_date)->toFormattedDateString().'</span>';
            })
            ->addColumn("updated_at", function ($closed_sale) {
                $updated_by = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been updated%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')->first();
                $updated_by = $updated_by ? $updated_by->name : $closed_sale->name;
                return '<span data-popup="tooltip" title="'.$updated_by.'">'.Carbon::parse($closed_sale->updated_at)->toFormattedDateString().'</span>';
            })
            ->addColumn("agent_by",function($closed_sale){
                $opened_sale_record= Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%sale-closed%')
                    ->select('users.name')
                    ->orderBy('audits.updated_at', 'desc')
                    ->first();

                $created_by_record = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been created successfully%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')
                    ->first();

                $created_by='';
                if($opened_sale_record){
                    $created_by = $opened_sale_record->name;
                }elseif($created_by_record){
                    $created_by = $created_by_record->name;
                }

                return ucwords($created_by);
            })
            ->addColumn("job_type", function ($closed_sale) {
                return ucwords($closed_sale->job_type);
            })
            ->addColumn("status", function ($closed_sale) {
                return "<h5><span class=\"badge badge-danger\">".ucfirst($closed_sale->status)."</span></h5>";
            });
        if ($auth_user->hasAnyPermission(['sale_open','sale_closed-sale-notes'])) {
            $datatable = $datatable->addColumn("action", function ($closed_sale) use ($auth_user) {
                $action =
                    '<div class="list-icons">
                        <div class="dropdown">
                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                <i class="icon-menu9"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    $action .=
                        '<a href="#" class="dropdown-item"
                                   data-controls-modal="#open_sale'.$closed_sale->id.'"
                                   data-backdrop="static"
                                   data-keyboard="false" data-toggle="modal"
                                   data-target="#open_sale'.$closed_sale->id.'"
                                > Open </a>';
                }
                if ($auth_user->hasPermissionTo('sale_closed-sale-notes')) {
                    $action .=
                        '<a href="' . route('viewAllCloseNotes', $closed_sale->id) . '" class="dropdown-item">Notes</a>';
                }
                $action .=
                    '</div>
                        </div>
                    </div>';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    /*** Open Sale Modal */
                    $action .=
                        '<div id="open_sale'.$closed_sale->id.'" class="modal fade" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Close Sale Notes</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="' . route('openSale') . '"
                                          method="POST" class="form-horizontal">'
                        . csrf_field() .
                        '<div class="modal-body">
                                            <div class="form-group row">
                                                <label class="col-form-label col-sm-3">Details</label>
                                                <div class="col-sm-9">
                                                    <input type="hidden" name="sale_id" value="' . $closed_sale->id . '">
                                                    <textarea name="details" class="form-control" cols="30" rows="4"
                                                              placeholder="TYPE HERE.." required></textarea>
                                                </div>
                                            </div>
                                        </div>
                    
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn bg-teal legitRipple">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>';
                    /*** /open sale modal */
                }
                return $action;
            });
            $raw_columns = ['job_title','created_at','updated_at','job_type','status','action'];
        }
        $datatable = $datatable->rawColumns($raw_columns)->make(true);
        return $datatable;
    }

    public function allClosedSalesNonNurse()
    {
        $close_sales = Office::join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->select('sales.*','offices.office_name','units.contact_name','users.name',
                'units.contact_email','units.unit_name','units.contact_phone_number',
                DB::raw("MAX(CASE WHEN sales.job_title = 'nonnurse specialist' THEN sales.job_title END) AS job_title_prof_calc"),
                DB::raw("MAX(sales.created_at) AS most_recent_created_at"))
            ->where(['sales.status' => 'disable', 'sales.is_on_hold' => '0'])
            ->where('sales.job_category','nonnurse')
            ->orderBy('sales.updated_at','desc')
            ->groupBy('sales.id');

        $auth_user = Auth::user();
        $raw_columns = ['job_title','close_date','agent_by','created_at','updated_at','job_type','status'];
        $datatable = datatables()->of($close_sales)
            ->editColumn('job_title', function ($close_sales) {
                $job_title_desc = ($close_sales->job_title_prof!='')?$close_sales->job_title.' ('.$close_sales->job_title_prof.')':$close_sales->job_title;
                return $job_title_desc;
            })
            ->addColumn("close_date", function ($closed_sale){
                $close_date_query = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%sale-closed%')
                    ->select('audits.updated_at')
                    ->orderBy('audits.updated_at','desc')
                    ->first();
                $close_date = @Carbon::parse($close_date_query->updated_at)->toFormattedDateString();
                return $close_date;
            })
            ->addColumn("created_at", function ($closed_sale) {
                return '<span data-popup="tooltip" title="'.$closed_sale->name.'">'.Carbon::parse($closed_sale->sale_added_date)->toFormattedDateString().'</span>';
            })
            ->addColumn("updated_at", function ($closed_sale) {
                $updated_by = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been updated%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')->first();
                $updated_by = $updated_by ? $updated_by->name : $closed_sale->name;
                return '<span data-popup="tooltip" title="'.$updated_by.'">'.Carbon::parse($closed_sale->updated_at)->toFormattedDateString().'</span>';
            })
            ->addColumn("agent_by",function($closed_sale){
                $opened_sale_record= Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%sale-closed%')
                    ->select('users.name')
                    ->orderBy('audits.updated_at', 'desc')
                    ->first();

                $created_by_record = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been created successfully%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')
                    ->first();

                $created_by='';
                if($opened_sale_record){
                    $created_by = $opened_sale_record->name;
                }elseif($created_by_record){
                    $created_by = $created_by_record->name;
                }

                return ucwords($created_by);
            })
            ->addColumn("job_type", function ($closed_sale) {
                return ucwords($closed_sale->job_type);
            })
            ->addColumn("status", function ($closed_sale) {
                return "<h5><span class=\"badge badge-danger\">".ucfirst($closed_sale->status)."</span></h5>";
            });
        if ($auth_user->hasAnyPermission(['sale_open','sale_closed-sale-notes'])) {
            $datatable = $datatable->addColumn("action", function ($closed_sale) use ($auth_user) {
                $action =
                    '<div class="list-icons">
                        <div class="dropdown">
                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                <i class="icon-menu9"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    $action .=
                        '<a href="#" class="dropdown-item"
                                   data-controls-modal="#open_sale'.$closed_sale->id.'"
                                   data-backdrop="static"
                                   data-keyboard="false" data-toggle="modal"
                                   data-target="#open_sale'.$closed_sale->id.'"
                                > Open </a>';
                }
                if ($auth_user->hasPermissionTo('sale_closed-sale-notes')) {
                    $action .=
                        '<a href="' . route('viewAllCloseNotes', $closed_sale->id) . '" class="dropdown-item">Notes</a>';
                }
                $action .=
                    '</div>
                        </div>
                    </div>';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    /*** Open Sale Modal */
                    $action .=
                        '<div id="open_sale'.$closed_sale->id.'" class="modal fade" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Close Sale Notes</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="' . route('openSale') . '"
                                          method="POST" class="form-horizontal">'
                        . csrf_field() .
                        '<div class="modal-body">
                                            <div class="form-group row">
                                                <label class="col-form-label col-sm-3">Details</label>
                                                <div class="col-sm-9">
                                                    <input type="hidden" name="sale_id" value="' . $closed_sale->id . '">
                                                    <textarea name="details" class="form-control" cols="30" rows="4"
                                                              placeholder="TYPE HERE.." required></textarea>
                                                </div>
                                            </div>
                                        </div>
                    
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn bg-teal legitRipple">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>';
                    /*** /open sale modal */
                }
                return $action;
            });
            $raw_columns = ['job_title','created_at','updated_at','job_type','status','action'];
        }
        $datatable = $datatable->rawColumns($raw_columns)->make(true);
        return $datatable;
    }

    public function allClosedSalesSpecialist()
    {
        $close_sales = Office::join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->select('sales.*','offices.office_name','units.contact_name','users.name',
                'units.contact_email','units.unit_name','units.contact_phone_number',
                DB::raw("MAX(CASE WHEN sales.job_title = 'nonnurse specialist' THEN sales.job_title END) AS job_title_prof_calc"),
                DB::raw("MAX(sales.created_at) AS most_recent_created_at"))
            ->where(['sales.status' => 'disable', 'sales.is_on_hold' => '0'])
            ->whereNotIn('sales.job_category', ['nurse', 'nonnurse'])
            ->orderBy('sales.updated_at','desc')
            ->groupBy('sales.id');

        $auth_user = Auth::user();
        $raw_columns = ['job_title','close_date','agent_by','created_at','updated_at','job_type','status'];
        $datatable = datatables()->of($close_sales)
            ->editColumn('job_title', function ($close_sales) {
                $job_title_desc = ($close_sales->job_title_prof!='')?$close_sales->job_title.' ('.$close_sales->job_title_prof.')':$close_sales->job_title;
                return $job_title_desc;
            })
            ->addColumn("close_date", function ($closed_sale){
                $close_date_query = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%sale-closed%')
                    ->select('audits.updated_at')
                    ->orderBy('audits.updated_at','desc')
                    ->first();
                $close_date = @Carbon::parse($close_date_query->updated_at)->toFormattedDateString();
                return $close_date;
            })
            ->addColumn("created_at", function ($closed_sale) {
                return '<span data-popup="tooltip" title="'.$closed_sale->name.'">'.Carbon::parse($closed_sale->sale_added_date)->toFormattedDateString().'</span>';
            })
            ->addColumn("updated_at", function ($closed_sale) {
                $updated_by = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been updated%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')->first();
                $updated_by = $updated_by ? $updated_by->name : $closed_sale->name;
                return '<span data-popup="tooltip" title="'.$updated_by.'">'.Carbon::parse($closed_sale->updated_at)->toFormattedDateString().'</span>';
            })
            ->addColumn("agent_by",function($closed_sale){
                $opened_sale_record= Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%sale-closed%')
                    ->select('users.name')
                    ->orderBy('audits.updated_at', 'desc')
                    ->first();

                $created_by_record = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been created successfully%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')
                    ->first();

                $created_by='';
                if($opened_sale_record){
                    $created_by = $opened_sale_record->name;
                }elseif($created_by_record){
                    $created_by = $created_by_record->name;
                }

                return ucwords($created_by);
            })
            ->addColumn("job_type", function ($closed_sale) {
                return ucwords($closed_sale->job_type);
            })
            ->addColumn("status", function ($closed_sale) {
                return "<h5><span class=\"badge badge-danger\">".ucfirst($closed_sale->status)."</span></h5>";
            });
        if ($auth_user->hasAnyPermission(['sale_open','sale_closed-sale-notes'])) {
            $datatable = $datatable->addColumn("action", function ($closed_sale) use ($auth_user) {
                $action =
                    '<div class="list-icons">
                        <div class="dropdown">
                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                <i class="icon-menu9"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    $action .=
                        '<a href="#" class="dropdown-item"
                                   data-controls-modal="#open_sale'.$closed_sale->id.'"
                                   data-backdrop="static"
                                   data-keyboard="false" data-toggle="modal"
                                   data-target="#open_sale'.$closed_sale->id.'"
                                > Open </a>';
                }
                if ($auth_user->hasPermissionTo('sale_closed-sale-notes')) {
                    $action .=
                        '<a href="' . route('viewAllCloseNotes', $closed_sale->id) . '" class="dropdown-item">Notes</a>';
                }
                $action .=
                    '</div>
                        </div>
                    </div>';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    /*** Open Sale Modal */
                    $action .=
                        '<div id="open_sale'.$closed_sale->id.'" class="modal fade" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Close Sale Notes</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="' . route('openSale') . '"
                                          method="POST" class="form-horizontal">'
                        . csrf_field() .
                        '<div class="modal-body">
                                            <div class="form-group row">
                                                <label class="col-form-label col-sm-3">Details</label>
                                                <div class="col-sm-9">
                                                    <input type="hidden" name="sale_id" value="' . $closed_sale->id . '">
                                                    <textarea name="details" class="form-control" cols="30" rows="4"
                                                              placeholder="TYPE HERE.." required></textarea>
                                                </div>
                                            </div>
                                        </div>
                    
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn bg-teal legitRipple">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>';
                    /*** /open sale modal */
                }
                return $action;
            });
            $raw_columns = ['job_title','created_at','updated_at','job_type','status','action'];
        }
        $datatable = $datatable->rawColumns($raw_columns)->make(true);
        return $datatable;
    }

    public function allClosedSalesNurseFilter(Request $request)
    {
        $close_sales = Office::join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->select('sales.*','offices.office_name','units.contact_name','users.name',
                'units.contact_email','units.unit_name','units.contact_phone_number',
                DB::raw("MAX(CASE WHEN sales.job_title = 'nonnurse specialist' THEN sales.job_title END) AS job_title_prof_calc"),
                DB::raw("MAX(sales.created_at) AS most_recent_created_at"))
            ->where(['sales.status' => 'disable', 'sales.is_on_hold' => '0'])
            ->where('sales.job_category','nurse')
            ->orderBy('sales.updated_at','desc')
            ->where('sales.head_office',$request->office_id)
            ->groupBy('sales.id');

        $auth_user = Auth::user();
        $raw_columns = ['job_title','close_date','agent_by','created_at','updated_at','job_type','status'];
        $datatable = datatables()->of($close_sales)
            ->editColumn('job_title', function ($close_sales) {
                $job_title_desc = ($close_sales->job_title_prof!='')?$close_sales->job_title.' ('.$close_sales->job_title_prof.')':$close_sales->job_title;
                return $job_title_desc;
            })
            ->addColumn("close_date", function ($closed_sale){
                $close_date_query = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%sale-closed%')
                    ->select('audits.updated_at')
                    ->orderBy('audits.updated_at','desc')
                    ->first();
                $close_date = @Carbon::parse($close_date_query->updated_at)->toFormattedDateString();
                return $close_date;
            })
            ->addColumn("created_at", function ($closed_sale) {
                return '<span data-popup="tooltip" title="'.$closed_sale->name.'">'.Carbon::parse($closed_sale->sale_added_date)->toFormattedDateString().'</span>';
            })
            ->addColumn("updated_at", function ($closed_sale) {
                $updated_by = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been updated%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')->first();
                $updated_by = $updated_by ? $updated_by->name : $closed_sale->name;
                return '<span data-popup="tooltip" title="'.$updated_by.'">'.Carbon::parse($closed_sale->updated_at)->toFormattedDateString().'</span>';
            })
            ->addColumn("agent_by",function($closed_sale){
                $opened_sale_record= Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%sale-closed%')
                    ->select('users.name')
                    ->orderBy('audits.updated_at', 'desc')
                    ->first();

                $created_by_record = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been created successfully%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')
                    ->first();

                $created_by='';
                if($opened_sale_record){
                    $created_by = $opened_sale_record->name;
                }elseif($created_by_record){
                    $created_by = $created_by_record->name;
                }

                return ucwords($created_by);
            })
            ->addColumn("job_type", function ($closed_sale) {
                return ucwords($closed_sale->job_type);
            })
            ->addColumn("status", function ($closed_sale) {
                return "<h5><span class=\"badge badge-danger\">".ucfirst($closed_sale->status)."</span></h5>";
            });
        if ($auth_user->hasAnyPermission(['sale_open','sale_closed-sale-notes'])) {
            $datatable = $datatable->addColumn("action", function ($closed_sale) use ($auth_user) {
                $action =
                    '<div class="list-icons">
                        <div class="dropdown">
                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                <i class="icon-menu9"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    $action .=
                        '<a href="#" class="dropdown-item"
                                   data-controls-modal="#open_sale'.$closed_sale->id.'"
                                   data-backdrop="static"
                                   data-keyboard="false" data-toggle="modal"
                                   data-target="#open_sale'.$closed_sale->id.'"
                                > Open </a>';
                }
                if ($auth_user->hasPermissionTo('sale_closed-sale-notes')) {
                    $action .=
                        '<a href="' . route('viewAllCloseNotes', $closed_sale->id) . '" class="dropdown-item">Notes</a>';
                }
                $action .=
                    '</div>
                        </div>
                    </div>';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    /*** Open Sale Modal */
                    $action .=
                        '<div id="open_sale'.$closed_sale->id.'" class="modal fade" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Close Sale Notes</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="' . route('openSale') . '"
                                          method="POST" class="form-horizontal">'
                        . csrf_field() .
                        '<div class="modal-body">
                                            <div class="form-group row">
                                                <label class="col-form-label col-sm-3">Details</label>
                                                <div class="col-sm-9">
                                                    <input type="hidden" name="sale_id" value="' . $closed_sale->id . '">
                                                    <textarea name="details" class="form-control" cols="30" rows="4"
                                                              placeholder="TYPE HERE.." required></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn bg-teal legitRipple">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>';
                    /*** /open sale modal */
                }
                return $action;
            });
            $raw_columns = ['job_title','created_at','updated_at','job_type','status','action'];
        }
        $datatable = $datatable->rawColumns($raw_columns)->make(true);
        return $datatable;
    }

    public function allClosedSalesNonNurseFilter(Request $request)
    {
        $close_sales = Office::join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->select('sales.*','offices.office_name','units.contact_name','users.name',
                'units.contact_email','units.unit_name','units.contact_phone_number',
                DB::raw("MAX(CASE WHEN sales.job_title = 'nonnurse specialist' THEN sales.job_title END) AS job_title_prof_calc"),
                DB::raw("MAX(sales.created_at) AS most_recent_created_at"))
            ->where(['sales.status' => 'disable', 'sales.is_on_hold' => '0'])
            ->where('sales.job_category','nonnurse')
            ->orderBy('sales.updated_at','desc')
            ->where('sales.head_office',$request->office_id)
            ->groupBy('sales.id');

        $auth_user = Auth::user();
        $raw_columns = ['job_title','close_date','agent_by','created_at','updated_at','job_type','status'];
        $datatable = datatables()->of($close_sales)
            ->editColumn('job_title', function ($close_sales) {
                $job_title_desc = ($close_sales->job_title_prof!='')?$close_sales->job_title.' ('.$close_sales->job_title_prof.')':$close_sales->job_title;
                return $job_title_desc;
            })
            ->addColumn("close_date", function ($closed_sale){
                $close_date_query = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%sale-closed%')
                    ->select('audits.updated_at')
                    ->orderBy('audits.updated_at','desc')
                    ->first();
                $close_date = @Carbon::parse($close_date_query->updated_at)->toFormattedDateString();
                return $close_date;
            })
            ->addColumn("created_at", function ($closed_sale) {
                return '<span data-popup="tooltip" title="'.$closed_sale->name.'">'.Carbon::parse($closed_sale->sale_added_date)->toFormattedDateString().'</span>';
            })
            ->addColumn("updated_at", function ($closed_sale) {
                $updated_by = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been updated%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')->first();
                $updated_by = $updated_by ? $updated_by->name : $closed_sale->name;
                return '<span data-popup="tooltip" title="'.$updated_by.'">'.Carbon::parse($closed_sale->updated_at)->toFormattedDateString().'</span>';
            })
            ->addColumn("agent_by",function($closed_sale){
                $opened_sale_record= Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%sale-closed%')
                    ->select('users.name')
                    ->orderBy('audits.updated_at', 'desc')
                    ->first();

                $created_by_record = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been created successfully%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')
                    ->first();

                $created_by='';
                if($opened_sale_record){
                    $created_by = $opened_sale_record->name;
                }elseif($created_by_record){
                    $created_by = $created_by_record->name;
                }

                return ucwords($created_by);
            })
            ->addColumn("job_type", function ($closed_sale) {
                return ucwords($closed_sale->job_type);
            })
            ->addColumn("status", function ($closed_sale) {
                return "<h5><span class=\"badge badge-danger\">".ucfirst($closed_sale->status)."</span></h5>";
            });
        if ($auth_user->hasAnyPermission(['sale_open','sale_closed-sale-notes'])) {
            $datatable = $datatable->addColumn("action", function ($closed_sale) use ($auth_user) {
                $action =
                    '<div class="list-icons">
                        <div class="dropdown">
                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                <i class="icon-menu9"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    $action .=
                        '<a href="#" class="dropdown-item"
                                   data-controls-modal="#open_sale'.$closed_sale->id.'"
                                   data-backdrop="static"
                                   data-keyboard="false" data-toggle="modal"
                                   data-target="#open_sale'.$closed_sale->id.'"
                                > Open </a>';
                }
                if ($auth_user->hasPermissionTo('sale_closed-sale-notes')) {
                    $action .=
                        '<a href="' . route('viewAllCloseNotes', $closed_sale->id) . '" class="dropdown-item">Notes</a>';
                }
                $action .=
                    '</div>
                        </div>
                    </div>';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    /*** Open Sale Modal */
                    $action .=
                        '<div id="open_sale'.$closed_sale->id.'" class="modal fade" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Close Sale Notes</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="' . route('openSale') . '"
                                          method="POST" class="form-horizontal">'
                        . csrf_field() .
                        '<div class="modal-body">
                                            <div class="form-group row">
                                                <label class="col-form-label col-sm-3">Details</label>
                                                <div class="col-sm-9">
                                                    <input type="hidden" name="sale_id" value="' . $closed_sale->id . '">
                                                    <textarea name="details" class="form-control" cols="30" rows="4"
                                                              placeholder="TYPE HERE.." required></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn bg-teal legitRipple">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>';
                    /*** /open sale modal */
                }
                return $action;
            });
            $raw_columns = ['job_title','created_at','updated_at','job_type','status','action'];
        }
        $datatable = $datatable->rawColumns($raw_columns)->make(true);
        return $datatable;
    }

    public function allClosedSalesSpecialistFilter(Request $request)
    {
        $close_sales = Office::join('sales', 'offices.id', '=', 'sales.head_office')
            ->join('units', 'units.id', '=', 'sales.head_office_unit')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->select('sales.*','offices.office_name','units.contact_name','users.name',
                'units.contact_email','units.unit_name','units.contact_phone_number',
                DB::raw("MAX(CASE WHEN sales.job_title = 'nonnurse specialist' THEN sales.job_title END) AS job_title_prof_calc"),
                DB::raw("MAX(sales.created_at) AS most_recent_created_at"))
            ->where(['sales.status' => 'disable', 'sales.is_on_hold' => '0'])
            ->whereNotIn('sales.job_category', ['nurse', 'nonnurse'])
            ->orderBy('sales.updated_at','desc')
            ->where('sales.head_office',$request->office_id)
            ->groupBy('sales.id');

        $auth_user = Auth::user();
        $raw_columns = ['job_title','close_date','agent_by','created_at','updated_at','job_type','status'];
        $datatable = datatables()->of($close_sales)
            ->editColumn('job_title', function ($close_sales) {
                $job_title_desc = ($close_sales->job_title_prof!='')?$close_sales->job_title.' ('.$close_sales->job_title_prof.')':$close_sales->job_title;
                return $job_title_desc;
            })
            ->addColumn("close_date", function ($closed_sale){
                $close_date_query = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%sale-closed%')
                    ->select('audits.updated_at')
                    ->orderBy('audits.updated_at','desc')
                    ->first();
                $close_date = @Carbon::parse($close_date_query->updated_at)->toFormattedDateString();
                return $close_date;
            })
            ->addColumn("created_at", function ($closed_sale) {
                return '<span data-popup="tooltip" title="'.$closed_sale->name.'">'.Carbon::parse($closed_sale->sale_added_date)->toFormattedDateString().'</span>';
            })
            ->addColumn("updated_at", function ($closed_sale) {
                $updated_by = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been updated%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')->first();
                $updated_by = $updated_by ? $updated_by->name : $closed_sale->name;
                return '<span data-popup="tooltip" title="'.$updated_by.'">'.Carbon::parse($closed_sale->updated_at)->toFormattedDateString().'</span>';
            })
            ->addColumn("agent_by",function($closed_sale){
                $opened_sale_record= Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%sale-closed%')
                    ->select('users.name')
                    ->orderBy('audits.updated_at', 'desc')
                    ->first();

                $created_by_record = Audit::join('users', 'users.id', '=', 'audits.user_id')
                    ->where(['audits.auditable_id' => $closed_sale->id, 'audits.auditable_type' => 'Horsefly\Sale'])
                    ->where('audits.message', 'like', '%has been created successfully%')
                    ->select('users.name')
                    ->orderBy('audits.created_at', 'desc')
                    ->first();

                $created_by='';
                if($opened_sale_record){
                    $created_by = $opened_sale_record->name;
                }elseif($created_by_record){
                    $created_by = $created_by_record->name;
                }

                return ucwords($created_by);
            })
            ->addColumn("job_type", function ($closed_sale) {
                return ucwords($closed_sale->job_type);
            })
            ->addColumn("status", function ($closed_sale) {
                return "<h5><span class=\"badge badge-danger\">".ucfirst($closed_sale->status)."</span></h5>";
            });
        if ($auth_user->hasAnyPermission(['sale_open','sale_closed-sale-notes'])) {
            $datatable = $datatable->addColumn("action", function ($closed_sale) use ($auth_user) {
                $action =
                    '<div class="list-icons">
                        <div class="dropdown">
                            <a href="#" class="list-icons-item" data-toggle="dropdown">
                                <i class="icon-menu9"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    $action .=
                        '<a href="#" class="dropdown-item"
                                   data-controls-modal="#open_sale'.$closed_sale->id.'"
                                   data-backdrop="static"
                                   data-keyboard="false" data-toggle="modal"
                                   data-target="#open_sale'.$closed_sale->id.'"
                                > Open </a>';
                }
                if ($auth_user->hasPermissionTo('sale_closed-sale-notes')) {
                    $action .=
                        '<a href="' . route('viewAllCloseNotes', $closed_sale->id) . '" class="dropdown-item">Notes</a>';
                }
                $action .=
                    '</div>
                        </div>
                    </div>';
                if ($auth_user->hasPermissionTo('sale_open')) {
                    /*** Open Sale Modal */
                    $action .=
                        '<div id="open_sale'.$closed_sale->id.'" class="modal fade" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Close Sale Notes</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="' . route('openSale') . '"
                                          method="POST" class="form-horizontal">'
                        . csrf_field() .
                        '<div class="modal-body">
                                            <div class="form-group row">
                                                <label class="col-form-label col-sm-3">Details</label>
                                                <div class="col-sm-9">
                                                    <input type="hidden" name="sale_id" value="' . $closed_sale->id . '">
                                                    <textarea name="details" class="form-control" cols="30" rows="4"
                                                              placeholder="TYPE HERE.." required></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-link legitRipple" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn bg-teal legitRipple">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>';
                    /*** /open sale modal */
                }
                return $action;
            });
            $raw_columns = ['job_title','created_at','updated_at','job_type','status','action'];
        }
        $datatable = $datatable->rawColumns($raw_columns)->make(true);
        return $datatable;
    }

    public function getSaleHistory($sale_history_id)
    {
        $auth_user = Auth::user()->id;
        $sale = Sale::with('office','unit')->withCount('active_cvs')->find($sale_history_id);
$sec_job_data = Specialist_job_titles::select("*")->where("id",$sale->job_title_prof)->first();
		if(is_null($sale->job_title_prof))
		{
			$sale->job_title_prof='';
		}
		if($sale->job_title_prof!='')
        {
         $sec_job_data = Specialist_job_titles::select("*")->where("id",$sale->job_title_prof)->first();
        }
        else
        {
            $sec_job_data = new \stdClass();
            $sec_job_data->specialist_prof = '';
        }
				//print_r($sec_job_data);exit();

        /***
        //APPLICANT Against This Sale
        $cv_send_in_quality_notes = Cv_note::where(array('sale_id' => $sale_history_id, 'user_id' => $auth_user
        , 'status' => 'active'))->get();
        $applicants = array();
        foreach($cv_send_in_quality_notes as $note){

            $applicants[] = Applicant::where(["id" => $note->applicant_id, "status" => "active"])->first();
        }
        // ./APPLICANT SEND AGAINST THIS JOB IN QUALITY FROM SEARCH RESULTS
        return view('administrator.sale.history.index',compact('applicants','sale_history_id', 'sale'));
        */

        $applicants_in_crm = Applicant::join('crm_notes', function($join) use ($sale_history_id) {
                $join->on('crm_notes.applicant_id', '=', 'applicants.id');
                $join->where('crm_notes.sales_id', '=', $sale_history_id);
            })
            ->join('history', function($join) use ($sale_history_id) {
                $join->on('crm_notes.applicant_id', '=', 'history.applicant_id');
                $join->where('history.sale_id', '=', $sale_history_id);
            })
            ->select("applicants.id as app_id","applicants.applicant_name","applicants.applicant_job_title","applicants.job_category","applicants.applicant_postcode","applicants.applicant_phone","applicants.applicant_homePhone",
                "crm_notes.id as note_id","crm_notes.user_id","crm_notes.applicant_id","crm_notes.sales_id as sale_id","crm_notes.details","crm_notes.moved_tab_to","crm_notes.crm_added_date as note_added_date",
                "crm_notes.crm_added_time as note_added_time","crm_notes.status","crm_notes.created_at","crm_notes.updated_at",
                "history.history_added_date", "history.sub_stage"
            )->where(array(
                'history.status' => 'active'))
            ->whereIn('crm_notes.id', function($query) use ($sale_history_id) {
                $query->select(DB::raw('MAX(id) FROM crm_notes WHERE sales_id='.$sale_history_id.' and applicants.id=applicant_id'));
            })
            ->get();

        $applicants_in_quality_reject = Applicant::join('history', function($join) use ($sale_history_id) {
                $join->on('applicants.id', '=', 'history.applicant_id');
                $join->where('history.sale_id', '=', $sale_history_id);
                $join->where('history.sub_stage', '=', 'quality_reject');
            })
            ->join('quality_notes', function($join) use ($sale_history_id) {
                $join->on('quality_notes.applicant_id', '=', 'applicants.id');
                $join->where('quality_notes.sale_id', '=', $sale_history_id);
            })
            ->select("applicants.id as app_id","applicants.applicant_name","applicants.applicant_job_title","applicants.job_category","applicants.applicant_postcode","applicants.applicant_phone","applicants.applicant_homePhone",
                "quality_notes.id as note_id","quality_notes.user_id","quality_notes.applicant_id","quality_notes.sale_id","quality_notes.details","quality_notes.moved_tab_to",
                "quality_notes.quality_added_date as note_added_date","quality_notes.quality_added_time as note_added_time","quality_notes.status","quality_notes.created_at","quality_notes.updated_at",
                "history.history_added_date", "history.sub_stage"
            )->where(array(
                'history.status' => 'active'))
            ->whereIn('quality_notes.id', function($query) use ($sale_history_id) {
                $query->select(DB::raw('MAX(id) FROM quality_notes WHERE sale_id='.$sale_history_id.' and applicants.id=applicant_id and moved_tab_to="rejected"'));
            })
            ->get();

        $applicants_in_quality = Applicant::join('history', function($join) use ($sale_history_id) {
            $join->on('applicants.id', '=', 'history.applicant_id');
            $join->where('history.sale_id', '=', $sale_history_id);
            $join->where('history.sub_stage', '=', 'quality_cvs');
        })
            ->join('cv_notes', function($join) use ($sale_history_id) {
                $join->on('cv_notes.applicant_id', '=', 'applicants.id');
                $join->where('cv_notes.sale_id', '=', $sale_history_id);
            })
            ->select("applicants.id as app_id","applicants.applicant_name","applicants.applicant_job_title","applicants.job_category","applicants.applicant_postcode","applicants.applicant_phone","applicants.applicant_homePhone",
                "cv_notes.id as note_id","cv_notes.user_id","cv_notes.applicant_id","cv_notes.sale_id","cv_notes.details",
                "cv_notes.send_added_date as note_added_date","cv_notes.send_added_time as note_added_time","cv_notes.status","cv_notes.created_at","cv_notes.updated_at",
                "history.history_added_date", "history.sub_stage"
            )->where(['history.status' => 'active', 'cv_notes.status' => 'active'])->get();

        $applicant_crm_notes = Applicant::join('crm_notes', 'crm_notes.applicant_id', '=', 'applicants.id')
            ->select("crm_notes.*", "crm_notes.sales_id as sale_id", "applicants.id as app_id")
            ->where(['crm_notes.sales_id' => $sale_history_id])
            ->orderBy("crm_notes.created_at", "desc")
            ->get();

        $history_stages = config('constants.history_stages');
        $crm_stages = config('constants.crm_stages');
        // ./APPLICANT SEND AGAINST THIS JOB IN QUALITY FROM SEARCH RESULTS
        return view('administrator.sale.history.index',compact('applicants_in_crm', 'applicants_in_quality_reject', 'applicants_in_quality', 'applicant_crm_notes', 'history_stages', 'crm_stages', 'sale','sec_job_data'));
    }

    public function getSaleFullHistory($applicant_id,$sale_id){

        $sale = $sale_id;
        $applicant = $applicant_id;
        $auth_user = Auth::user()->id;
        $applicant_name = Applicant::select("applicant_name")->where("id",$applicant)->first();
        // Applicants Activities in Quality
        $applicant_in_quality = Quality_notes::where(array('applicant_id' => $applicant, 'user_id' => $auth_user
        ,'sale_id' => $sale, 'status' => 'active'))->first();
        // ./ Applicants Activities in Quality

        // CRM Actvity
        $applicant_in_crm = Crm_note::join('applicants', 'crm_notes.applicant_id', '=', 'applicants.id')
            ->select("applicants.applicant_job_title","applicants.applicant_name","applicants.applicant_postcode","crm_notes.*")
            ->where(array('crm_notes.applicant_id' => $applicant, 'crm_notes.sales_id' => $sale, 'crm_notes.user_id' => $auth_user
            , 'crm_notes.status' => 'active'))->get();
        // ./CRM Actvity

        // Tract Applicant in CRM
        $track_applicant_in_crm = History::join('applicants', 'history.applicant_id', '=', 'applicants.id')
            ->select("applicants.applicant_name","applicants.applicant_job_title","applicants.applicant_postcode","history.*")->
            where(array('history.applicant_id' => $applicant, 'history.user_id' => $auth_user,'history.sale_id' => $sale , 'history.status' => 'active'))->first();
        // ./Tract Applicant in CRM
            // echo '<pre>';print_r($track_applicant_in_crm->toArray());exit;
        return view('administrator.applicant.history.full_history',compact('applicant_in_quality',
            'applicant_in_crm','track_applicant_in_crm','applicant_name'));
    }
    public function getAllOpenedSalesNotes($sale_note_id){
        $open_sale_notes = Sales_notes::where(['sale_id' => $sale_note_id])->get();
        return view('administrator.sale.open.notes.index',compact('open_sale_notes'));
    }

    public function getAllClosedSalesNotes($sale_close_note_id){
        $open_sale_notes = Sales_notes::where(['status' => 'disable', 'sale_id' => $sale_close_note_id])->get();
        return view('administrator.sale.open.notes.index',compact('open_sale_notes'));
    }
    public function getUploadSaleCsv(Request $request){
        date_default_timezone_set('Europe/London');
        if ($request->file('sale_csv') != null ){

            $file = $request->file('sale_csv');

            // File Details
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();

            // Valid File Extensions
            $valid_extension = array("csv");

            // 2MB in Bytes
            $maxFileSize = 2097152;

            // Check file extension
            if(in_array(strtolower($extension),$valid_extension)){

                // Check file size
                if($fileSize <= $maxFileSize){

                    // File upload location
                    $location = 'uploads';

                    // Upload file
                    $file->move($location,$filename);

                    // Import CSV to Database
                    $filepath = public_path($location."/".$filename);

                    // Reading file
                    $file = fopen($filepath,"r");

                    $importData_arr = array();
                    $i = 0;

                    while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
                        $num = count($filedata );

                        // Skip first row (Remove below comment if you want to skip the first row)
                        if($i == 0){
                            $i++;
                            continue;
                        }
                        for ($c=0; $c < $num; $c++) {
                            $importData_arr[$i][] = $filedata [$c];
                        }
                        $i++;
                    }
                    fclose($file);
//                    echo '<pre>';print_r($importData_arr);exit;
                    foreach($importData_arr as $importData){

                        $postcode = $importData[2];
                        $data_arr = $this->geocode($postcode);
                        $latitude = 00.000000;
                        $longitude = 00.000000;
                        if ($data_arr) {
                            $latitude = $data_arr[0];
                            $longitude = $data_arr[1];
                        }
                        $auth_user = Auth::user()->id;
                        $sale = new Sale();
                        $sale->user_id = $auth_user;
                        $sale->head_office = $request->input('head_office');
                        $sale->head_office_unit = $request->input('unit_list');
                        $sale->job_category = $importData[0];
                        $sale->job_title = $importData[1];
                        $sale->postcode = $postcode;
                        $sale->job_type = $importData[3];
                        $sale->timing = $importData[4];
                        $sale->salary = $importData[5];
                        $sale->experience = $importData[6];
                        $sale->qualification = $importData[7];
                        $sale->benefits = $importData[8];
                        $sale->sale_added_date = date("jS F Y");
                        $sale->sale_added_time = date("h:i A");
                        $sale->lat = $latitude;
                        $sale->lng = $longitude;
                        $sale->save();
                        $last_inserted_sale = $sale->id;
                        if ($last_inserted_sale > 0) {
                            $sale_uid = md5($last_inserted_sale);
                            Sale::where('id', $last_inserted_sale)->update(['sale_uid' => $sale_uid]);
                            $sale_note = new Sales_notes();
                            $sale_note->sale_id = $last_inserted_sale;
                            $sale_note->user_id = $auth_user;
                            $sale_note->sales_note_added_date = date("jS F Y");
                            $sale_note->sales_note_added_time = date("h:i A");
                            $sale_note->sale_note = $importData[9];
                            $sale_note->save();
                            $last_inserted_sale_note_id = $sale_note->id;
                            if($last_inserted_sale_note_id > 0){
                                $sale_note_uid = md5($last_inserted_sale_note_id);
                                Sales_notes::where('id',$last_inserted_sale_note_id)->update(['sales_notes_uid' => $sale_note_uid]);
                            }
                        }
                    }
                    Session::flash('message','Import Successful.');
                }else{
                    Session::flash('message','File too large. File must be less than 2MB.');
                }

            }else{
                Session::flash('message','Invalid File Extension.');
            }
        }
        return redirect('sales')->with('applicant_success_msg', 'Applicant Added Successfully');
    }
    function geocode($address)
    {

        $address = urlencode($address);

//        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key=AIzaSyBPx06p1VPBhS_qz-dw7t0rYkoMbKeoNBM";
        $postcode_api = config('app.postcode_api');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$postcode_api}";

        $resp_json = file_get_contents($url);

        $resp = json_decode($resp_json, true);

        // response status will be 'OK', if able to geocode given address
        if ($resp['status'] == 'OK') {

            // get the important data
            $lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
            $longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";


            // verify if data is complete
            if ($lati && $longi) {

                // put the data in the array
                $data_arr = array();

                array_push(
                    $data_arr,
                    $lati,
                    $longi
                );

                return $data_arr;

            } else {
                return false;
            }

        } else {
            echo "<strong>ERROR: {$resp['status']}</strong>";
            return false;
        }
    }

    public function updateHistory(Request $request)
    {
        $input = $request->all();
        $input['module'] = filter_var($request->input('module'), FILTER_SANITIZE_STRING);
        $request->replace($input);

        $validator = Validator::make($request->all(), [
            'module' => "required|in:Sale",
            'module_key' => "required"
        ])->validate();

        $model_class = 'Horsefly\\' . $request->input('module');
        $model = $model_class::with('unit','office','updated_by_audits')->find($request->input('module_key'));
        if ($model) {

//            $created_model = Sale::with()

            $audit_data = $changes_made_arr = [];
            $index = 0;
            foreach ($model->updated_by_audits as $audit) {
                if (!empty($audit->data['changes_made'])) {
                    $changes_made = Arr::except($audit->data['changes_made'], ['user_id','posted_date','sale_uid','lat','lng','sale_added_date','sale_added_time']);
                    if (count($changes_made) == 1) continue;
                    if (isset($changes_made['head_office'])) {
                        $changes_made['head_office'] = @Office::find($changes_made['head_office'])->office_name;

                    }
                    if (isset($changes_made['head_office_unit'])) {
                        $changes_made['head_office_unit'] = @Unit::find($changes_made['head_office_unit'])->unit_name;

                    }
                    $audit_data[$index]['changes_made'] = $changes_made;
                    $audit_data[$index++]['changes_made_by'] = $audit->user->name;
                }
            }
            $audit_data = array_reverse($audit_data);
            $original_sale = $model->created_by_audit;

            $update_modal_body = view('administrator.sale.history.sale_update_history', compact('audit_data','original_sale'))->render();
            return $update_modal_body;
        } else {
            return 'WHOOPS!! Sale not found!!';
        }
    }
    public function getJobDescription($id){
        $sale=Sale::find($id);
        if ($sale){
            return response()->json(['status'=>true,'data'=>$sale]);
        }
    }
}
