@extends('layout.dashboard-layout')

@section('css')
<link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
<link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
@endsection

@section('content')
<div class="main-content">
    <section class="section">
        <div class="d-flex justify-content-end pb-3">
            <!-- Add New Button -->
            <button type="button" class="btn btn-primary text-white" data-toggle="modal" data-target="#addSubCategoryModal">
                + Add New
            </button>
        </div>

        <div class="section-body">
            <!-- Success/Error Alerts -->
            <!-- SubCategories Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Sub Categories Table</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-1">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Sub Category</th>
                                            <th>Main Category</th>
                                            <th>Image</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($categories as $key => $cat)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                           <td>{{ $cat->name }} <br> {{ $cat->urdu_name }}</td>

                                            <td>{{ $cat->mainCategory ? $cat->mainCategory->name : 'N/A' }}</td>
                                            <td>
                                                @if($cat->image)
                                                <img src="{{ asset('storage/' . $cat->image) }}" width="50" alt="">
                                                @endif
                                            </td>
                                            <td>
                                                <!-- Edit Button -->
                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editSubCategoryModal{{ $cat->id }}"> <i data-feather="edit-3"></i> </button>

                                                <!-- Delete Button -->
                                                <form action="{{ route('sub-categories.destroy', $cat->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"> <i data-feather="trash-2"></i> </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Add Sub Category Modal -->
    <div class="modal fade" id="addSubCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addSubCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('sub-categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Sub Category</h5>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Main Category</label>
                            <select name="cat_id" class="form-control" required>
                                <option value="">-- Select Main Category --</option>
                                @foreach($mainCategories as $main)
                                <option value="{{ $main->id }}">{{ $main->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
    <label>Sub Category Name (Urdu)</label>
    <input type="text" name="urdu_name" class="form-control">
</div>

                        <div class="form-group">
                            <label>Image</label>
                            <input type="file" name="image" class="form-control-file" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Sub Category Modals (one per category) -->
    @foreach($categories as $cat)
    <div class="modal fade" id="editSubCategoryModal{{ $cat->id }}" tabindex="-1" role="dialog" aria-labelledby="editSubCategoryModalLabel{{ $cat->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('sub-categories.update', $cat->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Sub Category</h5>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Main Category</label>
                            <select name="cat_id" class="form-control" required>
                                <option value="">-- Select Main Category --</option>
                                @foreach($mainCategories as $main)
                                <option value="{{ $main->id }}" {{ $cat->cat_id == $main->id ? 'selected' : '' }}>
                                    {{ $main->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                                    <div class="form-group">
    <label>Sub Category Name (Urdu)</label>
    <input type="text" name="urdu_name" class="form-control" value="{{ $cat->urdu_name }}">
</div>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $cat->name }}" required>
                        </div>
                        <div class="form-group">
                            <label>Image</label>
                            <input type="file" name="image" class="form-control-file edit-image-input">
                            @if($cat->image)
                            <small>
                                <img src="{{ asset('storage/' . $cat->image) }}" width="60" class="mt-2 edit-image-preview" alt="">
                            </small>
                            @else
                            <small>
                                <img src="" width="50" class=" mt-2 edit-image-preview d-none" alt="">
                            </small>
                            @endif
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endforeach

</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('.edit-image-input').on('change', function() {
            const input = this;
            const preview = $(this).siblings('small').find('.edit-image-preview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.attr('src', e.target.result);
                    preview.removeClass('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.attr('src', '');
                preview.addClass('d-none');
            }
        });
    });
</script>

<script src="assets/bundles/jquery/jquery.min.js"></script>
<script src="assets/bundles/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/bundles/datatables/datatables.min.js"></script>
<script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/bundles/jquery-ui/jquery-ui.min.js"></script>
<script src="assets/js/page/datatables.js"></script>
@endsection
