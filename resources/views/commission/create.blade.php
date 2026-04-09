@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <a href="{{ route('commission.index') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left"></i>
                </a> &nbsp;
                <h1>Create Commission</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Commission Details</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('commission.store') }}" method="POST">
                                    @csrf

                                    <div class="form-group">
                                        <label>Commission Type</label>
                                        <select name="type" class="form-control">
                                            <option value="fixed">Fixed</option>
                                            <option value="percentage">Percentage</option>
                                        </select>
                                    </div>

                                    <div class="form-group mt-3">
                                        <label>Amount</label>
                                        <input type="number" name="amount" class="form-control" required>
                                    </div>

                                    <div class="form-group mt-3">
                                        <label>Main Category</label>
                                        <select name="main_category_id" id="main_category_id" class="form-control">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group mt-3">
                                        <label>Sub Category</label>
                                        <select name="sub_category_id" id="sub_category_id" class="form-control">
                                            <option value="">Select Sub Category</option>
                                        </select>
                                    </div>

                                    <button class="btn btn-primary mt-3">Save</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.getElementById('main_category_id').addEventListener('change', function() {
            fetch('/get-subcategories/' + this.value)
                .then(res => res.json())
                .then(data => {
                    let sub = document.getElementById('sub_category_id');
                    sub.innerHTML = '<option value="">Select Sub Category</option>';
                    data.forEach(s => sub.innerHTML += `<option value="${s.id}">${s.name}</option>`);
                });
        });
    </script>
@endsection
