@extends('layouts.app')

@section('style')
<style>
/*body{margin-top:20px;}*/

.chat-online {
    color: #34ce57
}

.chat-offline {
    color: #e4606d
}

.chat-messages {
    display: flex;
    flex-direction: column;
    max-height: 75vh;
    overflow-y: scroll
}

.chat-message-left,
.chat-message-right {
    display: flex;
    flex-shrink: 0;
    width:70%;
	max-width:70%;
}

.chat-message-left {
    margin-right: auto
}

.chat-message-right {
    flex-direction: row-reverse;
    margin-left: auto
}
.py-3 {
    padding-top: 1rem!important;
    padding-bottom: 1rem!important;
}
.px-4 {
    padding-right: 1.5rem!important;
    padding-left: 1.5rem!important;
}
.flex-grow-0 {
    flex-grow: 0!important;
}
.border-top {
    border-top: 1px solid #dee2e6!important;
}
.navbar-light{
    display: none !important;
}
</style>
@endsection
@section('content')

<main class="content-wrapper">
	<div class="container p-0" style="margin-top:90px !important;">
		<div class="row">
			<div class="col-md-6">
				<h5 class="mb-3">Messages</h5>
			</div>
			<div  class="col-md-6">
				<div class="form-group" style="text-align:right;">
					<label>Search:</label>
					<input type="text" class="form-controller" id="search" name="search">
				</div>
			</div>
		</div>
		<div class="row inbox_search_result">
			@include('administrator.chat.search_partial')
		</div>
	</div>
</main>

@endsection

@section('script')
<script>

$(document).ready(function(){
    $("#show_chat").click(function(){
        $("#exampleModal").show();
        
    });

    $("#btnClose").click(function() {
        $("#exampleModal").hide();
    });
   
});


     





$(document).ready(function(){


function fetch_data(page='',query='')
{
var data_url='';
if(query!='')
{
     data_url = "/pagination/fetch_data?page="+page+"&query="+query;
}
else
{
    data_url = "/pagination/fetch_data?page="+page;
}
 $.ajax({
    url:data_url,
  success:function(response)
  {
   console.log(response);

   $(".inbox_search_result").html(response);
  }
  
 });
}

$(document).on('keyup', '#search', function(){
 var query = $(this).val();
 var page = $('#hidden_page').val();
 fetch_data(page,query);
});


$(document).on('click', '.pagination a', function(event){
  event.preventDefault();
  var page = $(this).attr('href').split('page=')[1];
  $('#hidden_page').val(page);
  var query = $('#search').val();
  $('li').removeClass('active');
        $(this).parent().addClass('active');
  fetch_data(page, query);
 });



});








</script>
@endsection

