@extends('layouts.app')

@if($sent_cv_count < $job['send_cv_limit'])
@section('style')

    <script>
      $(document).ready(function() {
        $.fn.dataTable.ext.errMode = 'none';
        var job = $("#hidden_job_value").val();
        var radius = $("#hidden_radius_value").val();
          $('#applicants_15km_sample').DataTable({
               "processing": false,
               "serverSide": false,
               "ajax":"{!! url('get15kmApplicantsAjax') !!}/"+job+"/"+radius,
               "order": [],
               "columns": [
               { "data":"updated_at", "name": "updated_at"},
               { "data":"applicant_added_time", "name": "applicant_added_time", "orderable": false },
               { "data":"applicant_name", "name": "applicants.applicant_name" },
				{ "data":"applicant_email", "name": "applicants.applicant_email" },
               { "data":"applicant_job_title", "name": "applicants.applicant_job_title" },
               { "data":"job_category", "name": "applicants.job_category" },
               { "data":"applicant_postcode", "name": "applicants.applicant_postcode", "orderable": true },
               { "data":"applicant_phone", "name": "applicants.applicant_phone" },
			   { "data":"download", "name": "applicants.download", "orderable": false  },
               { "data":"updated_cv", "name": "applicants.updated_cv", "orderable": false  },
               { "data":"applicant_homePhone", "name": "applicants.applicant_homePhone" },
               { "data":"applicant_source", "name": "applicants.applicant_source" },
               { "data":"applicant_notes", "name": "applicant_notes" },
               { "data":"status", "name": "applicants.status" },
               { "data":"action", "name": "action", "orderable": false}
               ],
               "rowCallback": function( row, data ) {
                  var dateCell = data.updated_at;
                  var sortedDate = dateSorting (dateCell);
                  $('td:eq(0)', row).html(sortedDate);
              }
          });

      });

    </script>

@endsection
@endif

@section('content')
    <!-- Main content -->
    <div class="content-wrapper">

        <!-- Page header -->
{{--        <div class="page-header page-header-dark has-cover" style="border: 1px solid #ddd; border-bottom: 0;">--}}
        <div class="page-header page-header-dark has-cover">
            <div class="page-header-content header-elements-inline">

                <div class="page-title">
                    <h5>
                        <i class="icon-arrow-left52 mr-2"></i>
                        <span class="font-weight-semibold">Applicants Within</span> - 10KMs / 5Miles
                    </h5>
                </div>
            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="#" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Resources</a>
                        <a href="#" class="breadcrumb-item">Current</a>
                        <span class="breadcrumb-item active">Direct</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page header -->


        <!-- Content area -->
        <div class="content">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Job Details</h5>
                <input type="hidden" id="hidden_job_value" value="{{ $id}}">
				<input type="hidden" id="hidden_radius_value" value="{{ $radius}}">
            </div>
            @if($job)
            <div class="row">
                <div class="col-lg-6">
                    <div class="card border-left-3 border-left-slate rounded-left-0">
                        <div class="card-body">
                            <div class="d-sm-flex align-item-sm-center flex-sm-nowrap">
                                <div>
                                    Title:<span class="font-weight-semibold">{{ $job['job_title'] }}</span>
									 @if($cv_limit == $job['send_cv_limit'])
                                    <span class="badge badge-danger" style="font-size:90%">Limit Reached</span>
                                    @else
                                    <span class='badge badge-success' style='font-size:90%'>{{$job['send_cv_limit'] - $cv_limit." Cv's limit remaining  "}}</span>

                                    @endif
                                    <ul class="list list-unstyled mb-0">
                                        <li>Postcode: <span class="font-weight-semibold">{{ $job['postcode'] }}</span>
                                        </li>
                                        <li>Type: <span class="font-weight-semibold">{{ $job['job_type'] }}</span></li>
                                        <li>Head Office: <span
                                                    class="font-weight-semibold">{{ $job['office_name'] }}</span></li>
                                        <li>Qualification: <span
                                                    class="font-weight-semibold">{{ $job['qualification'] }}</span></li>
                                    </ul>
                                </div>

                                <div class="text-sm-right mb-0 mt-3 mt-sm-0 ml-auto">
                                    Salary:<span class="font-weight-semibold">{{ $job['salary'] }}</span>
                                    <ul class="list list-unstyled mb-0">
                                        <li>Categroy: <span class="font-weight-semibold">{{ $job['job_category'] }}</span>
                                        </li>
                                        <li>Experience: <span class="font-weight-semibold">{{ $job['experience'] }}</span>
                                        </li>
                                        <li>Unit: <span class="font-weight-semibold">{{ $job['unit_name'] }}</span>
                                        </li>
                                        <li class="dropdown">
                                            Status: &nbsp;
                                            <a href="#" class="badge bg-teal align-top">{{ $job['status'] }}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-sm-flex justify-content-sm-between align-items-sm-center">
                                        <span>
                                            Sent CV: <span class="font-weight-semibold">{{ $sent_cv_count }} out of {{ $job['send_cv_limit'] }}</span>
                                        </span>

                            <ul class="list-inline list-inline-condensed mb-0 mt-2 mt-sm-0">
                                <li class="list-inline-item">
                                    Posted On:<span class="font-weight-semibold">{{ $job['sale_added_date'] }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Default ordering -->
            <div class="col-md-12">
                        @if ($message = Session::get('error'))
                            <div class="alert alert-danger border-0 alert-dismissible mb-0 p-2">
                                <button type="button" class="close p-2" data-dismiss="alert"><span>×</span></button>
                                <span class="font-weight-semibold">Error!</span> {{ $message }}
                            </div>
                        @endif
                        @if ($message = Session::get('success'))
                            <div class="alert alert-success border-0 alert-dismissible mb-0 p-2">
                                <button type="button" class="close p-2" data-dismiss="alert"><span>×</span></button>
                                <span class="font-weight-semibold">Success!</span> {{ $message }}
                            </div>
                        @endif
                    </div>
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Active Applicants Within 10KMs / 5Miles</h5>
				 <p></p>
                    
                    @can('applicant_export')
                    <a href="{{ route('export_15km_applicants',['id' => $sale_export_id]) }}" class="btn bg-slate-800 legitRipple float-right" style="margin-right:20px;">
                        <i class="icon-cloud-upload"></i>
                        &nbsp;Export</a>
                    @endcan
            </div>
			<div id="import_applicant_cv" class="modal fade">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Import CV</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('import_applicantCv') }}" method="post" enctype="multipart/form-data">
                                    @csrf()
                                    <div class="form-group row">
                                        <div class="col-lg-12">
                                            <input type="file" name="applicant_cv" class="file-input-advanced" data-fouc>
                                        </div>
                                    </div>
                                   
                                   <div class="modal-body-id">
                                        <input type="hidden" name="page_url" id="page_url" value="{{url()->current()}}"/>
                                    </div>
                                    <div class="modal-body-id">
                                        <input type="hidden" name="applicant_id" id="applicant_id" value=""/>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <div class="card">
                @if($sent_cv_count < $job['send_cv_limit'])
                <table class="table table-hover table-striped" id="applicants_15km_sample">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Name</th>
						<th>Email</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Postcode</th>
                        <th>Phone#</th>
						<th>Applicant CV</th>
                        <th>Updated CV</th>
                      {{-- <th>Upload CV</th> --}}
                        <th>Landline#</th>
                        <th>Source</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    
                    </tbody>
                </table>
                @else
                    <h4 class="font-weight-semibold text-center mt-3">Send CV Limit for this Sale has reached maximum. Kindly increase Send CV Limit to send any CV on this Sale. Thank You</h4>
                    @if (!empty($active_applicants))
                        <table class="table table-hover table-striped datatable-sorting">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Name</th>
                                <th>Postcode</th>
                                <th>Stage</th>
                                <th>Sub Stage</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php($history_stages = config('constants.history_all_positive_stages'))
                            @foreach($active_applicants as $applicant)
                                <tr>
                                    <td>{{ $applicant['history_added_date'] }}</td>
                                    <td>{{ $applicant['history_added_time'] }}</td>
                                    <td>{{ $applicant['applicant_name'] }}</td>
                                    <td>{{ $applicant['applicant_postcode'] }}</td>
                                    <td>{{ strtoupper($applicant['stage']) }}</td>
                                    <td>{{ $history_stages[$applicant['sub_stage']] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                @endif
            </div>
            <!-- /default ordering -->
            @else
                <div class="card">
                    <h4 class="text-center mt-2">Following job is either <span class="font-weight-semibold">pending</span> or <span class="font-weight-semibold">rejected</span>. Kindly contact your supervisor to activate this job. Thank You.</h4>
                </div>
            @endif
        </div>
        <!-- /content area -->

@endsection

@if($sent_cv_count < $job['send_cv_limit'])
@section('script')
<script>

    // Update checkbox values dynamically
		$(document).on('change', '#no_job_checkbox', function() {
			$(this).val(this.checked ? '1' : '0');
		});
		
		$(document).on('change', '#interview_availability_checkbox', function() {
			$(this).val(this.checked ? '1' : '0');
		});
		
		$(document).on('change', '#alternate_weekend_checkbox', function() {
			$(this).val(this.checked ? '1' : '0');
		});
		
		$(document).on('change', '#nursing_home_checkbox', function() {
			$(this).val(this.checked ? '1' : '0');
		});
		
		$(document).on('change', '#hangup_call', function() {
			$(this).val(this.checked ? '1' : '0');
		});

    function dateSorting(date_timestamp) {
        var a = new Date(date_timestamp * 1000);
		console.log(date_timestamp);
        var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var days = ['1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th', '9th', '10th', '11th', '12th', '13th', '14th', '15th', '16th', '17th', '18th', '19th', '20th', '21st', '22nd', '23rd', '24th', '25th', '26th', '27th', '28th', '29th', '30th', '31st'];
        var year = a.getFullYear();
        var month = months[a.getMonth()];
        var date = days[a.getDate()-1];
        var date_time = date + ' ' + month + ' ' + year;

        return date_time;
    }
		$(document).on('click', '.app_notes_form_submit', function (event) {
        // event.preventDefault();
        // alert('sdfafas');
        var note_key = $(this).data('note_key');
        var detail = $('textarea#sent_cv_details'+note_key).val();

        // var reason =$(#reason option:selected).val();
        var reason = $("#reason"+note_key).val();

        var $notes_form = $('#app_notes_form'+note_key);
        var $notes_alert = $('#app_notes_alert' +note_key);
        // var note_details = $.trim($("#sent_cv_details"+note_key).val());
        // alert(reason);
        if (detail=='' || reason==0) {
            $notes_alert.html('<p class="text-danger">Please Fill Out All The Fields!</p>');
            $notes_form.trigger('reset');
        setTimeout(function () {
            $notes_alert.html('');
        }, 2000);
        return false;
        } 
        return true;
       
    });
		$(document).on("click", ".import_cv", function () {
     var app_id = $(this).data('id');
    //  alert(app_id);
     $(".modal-body #applicant_id").val(app_id);
});
</script>
@endsection
@endif
