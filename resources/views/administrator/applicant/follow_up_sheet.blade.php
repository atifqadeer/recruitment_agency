@extends('layouts.app')

@section('style')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
<style>
    .custom-input {
    padding-left: 10px;
    border: #303140 solid 1px;
    width: 120px;
    /* border-radius: 5px; */
    box-shadow: 0 0 5px bisque;
    margin: 7px;
}

</style>
    <script>
        var columns = [
            { "data": null, "sortable": false, "render": function (data, type, row, meta) {
                        return meta.row + 1; // Serial number
                    }
            },
            { "data": "applicant_name", "name": "applicants.applicant_name" },
            { "data": "applicant_email", "name": "applicants.applicant_email" },
            { "data": "applicant_job_title", "name": "applicants.applicant_job_title" },
            {
                "data": "job_category",
                "name": "applicants.job_category",
                "render": function(data, type, row) {
                    return data.toUpperCase();
                }
            },
            { "data": "applicant_postcode", "name": "applicants.applicant_postcode", "orderable": true },
            { "data": "applicant_phone", "name": "applicants.applicant_phone" },
            { "data": "download", "name": "download", "orderable": false },
            { "data": "updated_cv", "name": "updated_cv", "orderable": false },
            { "data": "upload", "name": "upload", "orderable": false },
            { "data": "applicant_homePhone", "name": "applicants.applicant_homePhone" },
            { "data": "applicant_source", "name": "applicants.applicant_source" },
            { "data": "applicant_notes", "name": "applicants.applicant_notes" },
            { "data": "status", "name": "applicants.status" }
        ];

        var blockedColumns = columns.concat([
            { "data": "checkbox", orderable:false, searchable:false},
        ]);
        
        $(document).ready(function() {
            $.fn.dataTable.ext.errMode = 'none';
    
            // Initialize DataTables for each tab but only load data for the first tab initially
            var all_nurse = $('#all_nurse_resources_table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": "{!! url('getFollowUpApplicants') !!}/44",
                "order": [[0, 'desc']],
                "columns": columns,
            });
    
            var all_nonnurse = $('#all_nonnurse_resources_table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": "{!! url('getFollowUpApplicants') !!}/45",
                "order": [[0, 'desc']],
                "columns": columns,
                "deferRender": true  // Defer rendering until the tab is shown
            });
    
            // Add a click event listener to the tab links
            $('.nav-tabs a').on('shown.bs.tab', function(e) {
                var targetTab = $(e.target).attr("href");
                // Check which tab is active and reload DataTable accordingly
                if (targetTab === "#all_nurse_resources") {
                    all_nurse.ajax.reload(); // Reload data for the active tab
                } else if (targetTab === "#all_nonnurse_resources") {
                    all_nonnurse.ajax.reload();
                }
            });
        });
    </script>
@endsection 

@section('content')
    <!-- Main content -->
    <div class="content-wrapper">

        <!-- Page header -->
        <div class="page-header page-header-dark has-cover">
            <div class="page-header-content header-elements-inline">
                <div class="page-title">
                    <h5>
                        <i class="icon-arrow-left52 mr-2"></i>
                        <span class="font-weight-semibold">Follow-UP - All
                    </h5>
                </div>
            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="#" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                        <a href="#" class="breadcrumb-item">Current</a>
                        <a href="#" class="breadcrumb-item">Follow-UP</a>
                    </div>
                </div>

            </div>

        </div>
        <!-- /page header -->


        <!-- Content area -->
        <div class="content">

            <!-- Default ordering -->
            <div class="card">
                <div class="card-header header-elements-inline">
                    <ul class="nav nav-tabs nav-tabs-highlight">
                          <li class="nav-item">
                              <a href="#all_nurse_resources" class="nav-link active legitRipple" data-toggle="tab" data-datatable_name="all_nurse_resources_table">Nurse Applicants</a>
                          </li>
                          <li class="nav-item">
                              <a href="#all_nonnurse_resources" class="nav-link legitRipple" data-toggle="tab" data-datatable_name="all_nonnurse_resources_table">Non-Nurse Applicants</a>
                          </li>
                      </ul>
                </div>
                
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
                <div class="card-body">
                </div>
                <div class="tab-content">
                    <div class="tab-pane active" id="all_nurse_resources">
                        <table class="table table-hover table-striped" id="all_nurse_resources_table">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Job Title</th>
                                    <th>Category</th>
                                    <th>Postcode</th>
                                    <th>Phone</th>
                                    <th>Applicant CV</th>
                                    <th>Updated CV</th>
                                    <th>Upload CV</th>
                                    <th>Landline</th>
                                    <th>Source</th>
                                    <th>Notes</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane" id="all_nonnurse_resources">
                        <table class="table table-hover table-striped" id="all_nonnurse_resources_table">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Job Title</th>
                                    <th>Category</th>
                                    <th>Postcode</th>
                                    <th>Phone</th>
                                    <th>Applicant CV</th>
                                    <th>Updated CV</th>
                                    <th>Upload CV</th>
                                    <th>Landline</th>
                                    <th>Source</th>
                                    <th>Notes</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /default ordering -->

        </div>
        <!-- /content area -->

@endsection
@section('js_file')
    <script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection
		
@section('script')
<script>
    $(document).on('click', '.reject_history', function () {
        var applicant = $(this).data('applicant');
        $.ajax({
            url: "{{ route('rejectedHistory') }}",
            type: "post",
            data: {
                _token: "{{ csrf_token() }}",
                applicant: applicant
            },
            success: function(response){
                $('#applicant_rejected_history'+applicant).html(response);
            },
            error: function(response){
                var raw_html = '<p>WHOOPS! Something Went Wrong!!</p>';
                $('#applicant_rejected_history'+applicant).html(raw_html);
            }
        });
    });
	
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

    $('#submitSelectedButton').on('click', function() {

        var selectedIds = [];

        // Get selected IDs
        $('.applicant_checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            toastr.error('Please select at least one applicant to unblock.');
            return; // Exit the function if no checkboxes are selected
        }


        // Submit AJAX request
        $.ajax({
            url: '/blocked-applicant-revert-all', // Update the URL to match your route
            type: 'POST',
            data: { ids: selectedIds,_token: $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (response.success) {
                    // Reload the DataTable
                    $('#last_2_months_blocked_sample').DataTable().ajax.reload();

                    // Display a success message
                    $('#success-message').text(response.message).show();

                    // Hide the success message after 5 seconds
                    setTimeout(function() {
                        $('#success-message').hide();
                    }, 5000);

                    // Hide the error message if it was previously shown
                    $('#error-message').hide();
                } else {
                    // Display an error message
                    $('#error-message').text(response.message).show();

                    // Hide the error message after 5 seconds
                    setTimeout(function() {
                        $('#error-message').hide();
                    }, 5000);

                    // Hide the success message if it was previously shown
                    $('#success-message').hide();
                }
            },
            error: function(error) {
                // Handle other errors (e.g., network issues)
                $('#error-message').text('Error: ' + error.statusText).show();

                // Hide the error message after 5 seconds
                setTimeout(function() {
                    $('#error-message').hide();
                }, 5000);

                // Hide the success message if it was previously shown
                $('#success-message').hide();
            }
        });
    });
	
    $(document).on("click", ".import_cv", function () {
        var app_id = $(this).data('id');

        $(".modal-body #applicant_id").val(app_id);
    });
    $('#master-checkbox').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.applicant_checkbox').prop('checked', isChecked);

        // Manually toggle the DataTables selected class
        $('.applicant_checkbox').each(function() {
            var $row = $(this).closest('tr');
            if (isChecked) {
                $row.addClass('selected');
            } else {
                $row.removeClass('selected');
            }
        });
    });

    // Add a listener to individual checkboxes to update the master checkbox state
    $(document).on('change', '.applicant_checkbox', function() {
        var allCheckboxesChecked = $('.applicant_checkbox:checked').length === $('.applicant_checkbox').length;
        $('#master-checkbox').prop('checked', allCheckboxesChecked);

        // Manually toggle the DataTables selected class
        var $row = $(this).closest('tr');
        if ($(this).prop('checked')) {
            $row.addClass('selected');
        } else {
            $row.removeClass('selected');
        }
    });
</script>
@endsection