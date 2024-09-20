@php($index=1)
@foreach($module_notes_history as $key => $value)
    <div class="p-2">
        <p>
            <span class="font-weight-semibold">{{ $index++ }}. Created by: </span>{{ $value->name }}
            <span class="font-weight-semibold"> - Created at: </span>{{ $value->updated_at }}
        </p>
        <p class="pl-3">
            <span class="font-weight-semibold">Note Details: </span><br>{!! $value->details !!}
        </p>
        <hr class="w-25 center">
    </div>
@endforeach

@foreach($model_notes_history as $key => $value)

    <p>
        <span class="font-weight-semibold">{{ $index++ }}. Created by: </span>{{ $value['username'] }}
		<span class="font-weight-semibold"> - Created at: </span>{{ $value['updated_at'] }}

    </p>
    <p class="pl-3">
        <span class="font-weight-semibold">Note Details: </span><br>{{ $value['note'] }}
    </p>
    <hr class="w-25 center">
@endforeach
