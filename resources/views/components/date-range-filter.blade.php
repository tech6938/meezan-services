@php
    $actionUrl = $actionUrl ?? url()->current();
    $startDate = request('start_date');
    $endDate = request('end_date');
@endphp

<form method="GET" action="{{ $actionUrl }}" class="d-flex flex-wrap align-items-end gap-2 mb-3" style="margin-bottom: 0;">
    @foreach(request()->except(['start_date', 'end_date', 'page']) as $key => $value)
        @if(is_array($value))
            @foreach($value as $item)
                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="form-group mb-0" style="min-width: 160px;">
        <label class="d-block mb-1" for="export-start-date">Start Date</label>
        <input id="export-start-date" type="date" name="start_date" value="{{ old('start_date', $startDate) }}" class="form-control">
    </div>

    <div class="form-group mb-0" style="min-width: 160px;">
        <label class="d-block mb-1" for="export-end-date">End Date</label>
        <input id="export-end-date" type="date" name="end_date" value="{{ old('end_date', $endDate) }}" class="form-control">
    </div>

    <div class="d-flex gap-2 mb-0 align-items-center">
        <button type="submit" class="btn btn-primary">Apply</button>
        @if($startDate || $endDate)
            <a href="{{ $actionUrl }}" class="btn btn-secondary">Reset</a>
        @endif
    </div>
</form>
