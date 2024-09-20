@extends('layouts.app')

@section('content')
    <!-- Main content -->
    <div class="content-wrapper">

        <!-- Page header -->
{{--        <div class="page-header page-header-dark has-cover" style="border: 1px solid #ddd; border-bottom: 0;">--}}
        <div class="page-header page-header-dark has-cover">
            <div class="page-header-content header-elements-inline">
                <div class="page-title">
                    <h5>
                        <a href="{{ route('applicants.index') }}"><i class="icon-arrow-left52 mr-2" style="color: white;"></i></a>
                        <span class="font-weight-semibold">Applicants</span> - Update
                    </h5>
                </div>
            </div>

            <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                <div class="d-flex">
                    <div class="breadcrumb">
                        <a href="#" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
						<a href="#" class="breadcrumb-item">Applicants</a>
                        <a href="#" class="breadcrumb-item">Current</a>
                        <span class="breadcrumb-item active">Update</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page header -->


        <!-- Content area -->
        <div class="content">
            <!-- Centered forms -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-10 offset-md-1">
                                    <div class="header-elements-inline">
                                        <h5 class="card-title">Edit Applicant</h5>
                                        <a href="{{ route('applicants.index') }}" class="btn bg-slate-800 legitRipple">
                                            <i class="icon-cross"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-10 offset-md-1">
                                    {{ Form::open(array('route'=>['applicants.update.applicant',$applicant->id],'method'=>'PATCH','files'=>true,'id'=>'update_applicant_with_notes')) }}
                                    <div class="form-group">

                                        {{ Form::label('job_title','Job Title',array('class'=>'col-form-label')) }}
                                        <select name="applicant_job_title" id="applicant_job_title_id" class="form-control form-control-select2">
                                            <optgroup label="NURSES">
                                                <option value="rgn" @if($applicant->applicant_job_title === 'rgn') selected='selected' @endif>RGN</option>
                                                <option value="rmn" @if($applicant->applicant_job_title === 'rmn') selected='selected' @endif>RMN</option>
                                                <option value="rnld" @if($applicant->applicant_job_title === 'rnld') selected='selected' @endif>RNLD</option>
                                                <option value="nurse deputy manager" @if($applicant->applicant_job_title === 'nurse deputy manager') selected='selected' @endif>NURSE DEPUTY MANAGER</option>
                                                <option value="nurse manager" @if($applicant->applicant_job_title === 'nurse manager') selected='selected' @endif>NURSE MANAGER</option>
                                                <option value="senior nurse" @if($applicant->applicant_job_title === 'senior nurse') selected='selected' @endif>SENIOR NURSE</option>
                                                <option value="rgn/rmn" @if($applicant->applicant_job_title === 'rgn/rmn') selected='selected' @endif>RGN/RMN</option>
                                                <option value="rmn/rnld" @if($applicant->applicant_job_title === 'rmn/rnld') selected='selected' @endif>RMN/RNLD</option>
                                                <option value="rgn/rmn/rnld" @if($applicant->applicant_job_title === 'rgn/rmn/rnld') selected='selected' @endif>RGN/RMN/RNLD</option>
                                                <option value="clinical lead" @if($applicant->applicant_job_title === 'clinical lead') selected='selected' @endif>CLINICAL LEAD</option>
                                                <option value="rcn" @if($applicant->applicant_job_title === 'rcn') selected='selected' @endif>RCN</option>
                                                <option value="peripatetic nurse" @if($applicant->applicant_job_title === 'peripatetic nurse') selected='selected' @endif>PERIPATETIC NURSE</option>
                                                <option value="unit manager" @if($applicant->applicant_job_title === 'unit manager') selected='selected' @endif>UNIT MANAGER</option>
                                                <option value="nurse specialist" @if($applicant->applicant_job_title === 'nurse specialist') selected='selected' @endif>NURSE SPECIALIST</option>
                                            </optgroup>
                                            <optgroup label="NON NURSES">
                                                <option value="care assistant" @if($applicant->applicant_job_title === 'care assistant') selected='selected' @endif>CARE ASSISTANT</option>
                                                <option value="senior care assistant" @if($applicant->applicant_job_title === 'senior care assistant') selected='selected' @endif>SENIOR CARE ASSISTANT</option>
                                                <option value="team lead" @if($applicant->applicant_job_title === 'team lead') selected='selected' @endif>TEAM LEAD</option>
                                                <option value="deputy manager" @if($applicant->applicant_job_title === 'deputy manager') selected='selected' @endif>DEPUTY MANAGER</option>
                                                <option value="registered manager" @if($applicant->applicant_job_title === 'registered manager') selected='selected' @endif>REGISTERED MANAGER</option>
                                                <option value="support worker" @if($applicant->applicant_job_title === 'support worker') selected='selected' @endif>SUPPORT WORKER</option>
                                                <option value="senior support worker" @if($applicant->applicant_job_title === 'senior support worker') selected='selected' @endif>SENIOR SUPPORT WORKER</option>
                                                <option value="activity coordinator" @if($applicant->applicant_job_title === 'activity coordinator') selected='selected' @endif>ACTIVITY COORDINATOR</option>
                                                <option value="nonnurse specialist" @if($applicant->applicant_job_title === 'nonnurse specialist') selected='selected' @endif>NON-NURSE SPECIALIST</option>
                                            </optgroup>
											   <optgroup label="Chef">
                                                <option value="chef" @if($applicant->applicant_job_title === 'chef') selected='selected' @endif>Chef</option>
                                                <option value="head chef" @if($applicant->applicant_job_title === 'head chef') selected='selected' @endif>Head Chef</option>
                                                <option value="chef de partie" @if($applicant->applicant_job_title === 'chef de partie') selected='selected' @endif>Chef De Partie</option>
                                                <option value="sous chef" @if($applicant->applicant_job_title === 'sous chef') selected='selected' @endif>Sous Chef</option>
                                                <option value="commis chef" @if($applicant->applicant_job_title === 'commis chef') selected='selected' @endif>commis chef</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <?php if($applicant->applicant_job_title =='nonnurse specialist' || $applicant->applicant_job_title =='nurse specialist'){?>
                                    <div class="form-group" id="app_specialist_edit">
                                    <label>Select Job Profession</label>
                                    <select name='job_title_prof' class='form-control form-control-select2' id='job_title_prof_id' required>
                                        <option value=''>Select Profession</option>
                                        
                                         @foreach($spec_all_jobs_data as $item) 
                                        <option value="{{$item['id']}}" @if($sec_job_data && $sec_job_data->id == $item['id']) selected='selected' @endif()> {{ $item['specialist_prof'] }}</option>
                                        @endforeach()
                                   </select>
                                        
                                    </div>
                                     <!-- <div class="form-group" id="specialist_edit_new">

                                    </div> -->
                                    <?php }?>
                                    <div class="form-group" id="app_specialist_edit_special_only">
                                   </div>
                                    <input type="hidden" name="applicant_id" id="applicant_id" value="{{$applicant->id}}">


                                    <div class="form-group">
                                   @if(\Illuminate\Support\Facades\Auth::user()->is_admin==1 || \Illuminate\Support\Facades\Auth::id()==66)
                                        {{ Form::label('name','Name') }}
                                        {{ Form::text('applicant_name',$applicant->applicant_name,array('class'=>'form-control','id'=>'name')) }}
										 @else
										    {{ Form::label('name','Name') }}
                                        {{ Form::text('applicant_name',$applicant->applicant_name,array('class'=>'form-control disabled','disabled','id'=>'name')) }}
										
										    @endif
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('email_address','Email Address',array('class'=>'col-form-label')) }}
                                        {{ Form::email('applicant_email',$applicant->applicant_email,array('id'=>'email_address_id','class'=>'form-control')) }}
                                    </div>
                                    <div class="form-group">
                                    {{ Form::hidden('notes_details', 'notes_details', array('id' => 'notes_details')) }}
                                    {{ Form::hidden('notes_type', 'asdfasfasdfasdf', array('id' => 'notes_type')) }}
                                        
                                    </div>
                                    <div class="form-group">
                                    {{ Form::label('source', 'Source') }}
                                    {!! Form::select('applicant_source', $applicant_source ,$selectedID, array('id'=>'source','class'=>'form-control','required',
                                        'placeholder' => 'SELECT APPLICANT SOURCE')) !!}
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('postcode','Postcode') }}
                                        {{ Form::text('applicant_postcode',$applicant->applicant_postcode,array('id'=>'postcode_id','class'=>'form-control')) }}
                                    </div>
									
                                   
                                    @if(\Illuminate\Support\Facades\Auth::user()->is_admin==1|| \Illuminate\Support\Facades\Auth::id()==66)
                                    <div class="form-group">
                                        {{ Form::label('phnumber','Phone Number') }}
                                        {{ Form::text('applicant_phone',$applicant->applicant_phone,array('id'=>'phone_number_id','class'=>'form-control')) }}
                                    </div>
									 <div class="form-group">
                                            {{ Form::label('mobile','Landline Number') }}
                                            {{ Form::text('applicant_homePhone',$applicant->applicant_homePhone,array('id'=>'mobile_number_id','class'=>'form-control')) }}
                                        </div>
                                       
                                    @else

                                    <div class="form-group">
                                        {{ Form::label('phnumber','Phone Number') }}
                                        {{ Form::text('applicant_phone',$applicant->applicant_phone,array('id'=>'phone_number_id','class'=>'form-control disabled','disabled')) }}
                                    </div>
        @if(\Illuminate\Support\Facades\Auth::id()==66)
                                            <div class="form-group">
                                                {{ Form::label('mobile','Landline Number') }}
                                                {{ Form::text('applicant_homePhone',$applicant->applicant_homePhone,array('id'=>'mobile_number_id','class'=>'form-control' )) }}
                                            </div>

                                        @else
                                            <div class="form-group">
                                                {{ Form::label('mobile','Landline Number') }}
                                                {{ Form::text('applicant_homePhone',$applicant->applicant_homePhone,array('id'=>'mobile_number_id','class'=>'form-control disabled','disabled' )) }}
                                            </div>
                                        @endif
                                      
                                    @endif
									
                                    <div class="form-group">
                                        {{ Form::hidden('old_image',$applicant->applicant_cv) }}
                                        {{ Form::label('attachment', 'Attach CV:') }}
                                        {{ $applicant->applicant_cv }}
                                        {{ Form::file('applicant_cv',array('class'=>'form-input-styled')) }}
                                    </div>
{{--                                    <div class="form-group">--}}
{{--                                        {{ Form::label('applicant_notes', 'Add Notes:') }}--}}
{{--                                        {{ Form::textarea('applicant_notes',$applicant->applicant_notes,--}}
{{--                                        array('class'=>'form-control form-input-styled','rows' => '7','cols' => '20')) }}--}}
{{--                                    </div>--}}

                                    <div class="text-right">
                                        <!-- {{ Form::button('Save <i class="icon-paperplane ml-2"></i>',['type'=>'submit','class'=>'btn bg-teal legitRipple']) }} -->
                                        <a href="#" class="reject_history icon-paperplane ml-2 btn bg-teal legitRipple" data-applicant="'.$applicant->id.'"
                                 data-controls-modal="#check_notes"
                                 data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                 data-target="#check_notes">Save</a>
                                        <!-- <button type="submit" class="btn bg-teal legitRipple">Save <i class="icon-paperplane ml-2"></i></button> -->
                                    </div>
                                    {{ Form::close() }}
                                    
                                    <div id="check_notes" class="modal fade" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                <h5 class="modal-title">Notes</h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                
                                                    <div class="modal-body">
                                                        <div id="sent_cv_alert' . $applicant->id . '"></div>
                                                        <div class="form-group row">
                                                            <label class="col-form-label col-sm-3">Details</label>
                                                            <div class="col-sm-9">
                                                                <input type="hidden" name="applicant_hidden_id" value="">
                                                                <textarea name="details" id="sent_cv_details" class="form-control" cols="30" rows="4" placeholder="TYPE HERE.." required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="form-group row">
                                                            <label class="col-form-label col-sm-3">Choose type:</label>
                                                            <div class="col-sm-9">
                                                                <select name="reject_reason" class="form-control crm_select_reason" id="reason">
                                                                    <option value="0" >Select Reason</option>
                                                                    <option value="1">Casual Notes</option>
                                                                    <option value="2">Block Applicant Notes</option>
                                                                    <option value="3">Temporary Not Interested Applicants Notes</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <div class="modal-footer">
                                    
                                                    <button type="button" class="btn bg-dark legitRipple sent_cv_submit" data-dismiss="modal">Close</button>
                                        
                                                    <button type="submit" value="cv_sent_save" class="btn bg-teal legitRipple update_applicant">Update</button>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /form centered -->
        </div>
        <!-- /content area -->

@endsection()
@section('script')

<script>
    $(document).ready(function(){
    // create new note
    $(document).on('click', '.update_applicant', function (event) {
        event.preventDefault();
        $('#notes_details').val($('#sent_cv_details').val());
        var notes_details=$('#notes_details').val();
       $('#notes_type').val($('#reason option:selected').val());
        var notes_type=$('#notes_type').val();
        $("#update_applicant_with_notes").submit()
        
    });
});
	
	$(document).ready(function(){
   $('.reject_history').on('click',function(){
       var source =$("select#source option").filter(":selected").text();
       if(source=='SELECT APPLICANT SOURCE')
       {
        alert('Please Select Applicant Source');
        return false;
       }

       return true;
  
   }); 
});

</script>
@endsection