@extends('layout.dashboard-layout')

@section('content')
<div class="container" style="min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <div class="row w-100">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4>Tax Settings</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('tax.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="type">Tax Type</label>
                            <select name="type" id="type" class="form-control">
                                <option value="fixed" {{ (isset($tax) && $tax->type == 'fixed') ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="percentage" {{ (isset($tax) && $tax->type == 'percentage') ? 'selected' : '' }}>Percentage</option>
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <label for="amount">Tax Amount</label>
                            <input type="number" name="amount" id="amount" class="form-control"
                                value="{{ $tax->amount ?? '' }}" step="0.01" min="0" required>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3 w-100">
                            {{ isset($tax) ? 'Update Tax' : 'Add Tax' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection