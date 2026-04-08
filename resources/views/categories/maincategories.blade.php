@extends('layout.dashboard-layout')

@section('css')
<link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
<link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
@endsection

@section('content')
<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="d-flex justify-content-end pb-3">
            <a href="" class="btn btn-primary text-white" data-toggle="modal" data-target="#addMainCategoryModal">+ Add New</a>
        </div>

        <div class="section-body">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Main Categories Table</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-1">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Main Category Title</th>
                                            <th>Image</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($data->isNotEmpty())
                                        @foreach($data as $item)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $item->name }}
                                                <br>
                                                {{ $item->urdu_name }}
                                            </td>

                                            <td>
                                                <img src="{{ asset('storage/' . $item->image) }}" style="width:100px; height:100px;" alt="image">
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <form action="{{ route('main-categories.destroy', ['main_category' => $item->id]) }}" method="POST" class="p-1">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger"><i data-feather="trash-2"></i></button>
                                                    </form>
                                                    <div class="p-1">
                                                        <button type="button" class=" btn btn-info" data-toggle="modal" data-target="#editMainCategoryModal{{ $item->id }}">
                                                            <i data-feather="edit-3"></i>
                                                        </button>
                                                    </div>
                                                    <div class="p-1">
                                                        <a href="{{ route('main-categories.show',['main_category' => $item->id]) }}" type="button" class=" btn btn-primary">
                                                            <i data-feather="eye"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- Sidebar (unchanged) -->
    <div class="settingSidebar">
        <a href="javascript:void(0)" class="settingPanelToggle"> <i class="fa fa-spin fa-cog"></i> </a>
        <div class="settingSidebar-body ps-container ps-theme-default">
            <div class="fade show active">
                <div class="setting-panel-header">Setting Panel</div>
                <div class="p-15 border-bottom">
                    <h6 class="font-medium m-b-10">Select Layout</h6>
                    <div class="selectgroup layout-color w-50">
                        <label class="selectgroup-item">
                            <input type="radio" name="value" value="1" class="selectgroup-input-radio select-layout" checked>
                            <span class="selectgroup-button">Light</span>
                        </label>
                        <label class="selectgroup-item">
                            <input type="radio" name="value" value="2" class="selectgroup-input-radio select-layout">
                            <span class="selectgroup-button">Dark</span>
                        </label>
                    </div>
                </div>
                <!-- Sidebar color, theme color, mini sidebar, sticky header unchanged -->
                <div class="mt-4 mb-4 p-3 align-center rt-sidebar-last-ele">
                    <a href="#" class="btn btn-icon icon-left btn-primary btn-restore-theme">
                        <i class="fas fa-undo"></i> Restore Default
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Main Category Modal -->
<div class="modal fade" id="addMainCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Main Category</h5>
            </div>
            <form action="{{ route('main-categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>کیٹیگری کا نام</label>
                        <input type="text" class="form-control" name="urdu_name">
                    </div>
                    <div class="form-group">
                        <label>Category Image</label>
                        <input type="file" class="form-control" name="image" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals (placed after table to avoid tbody issues) -->
@foreach($data as $item)
<div class="modal fade" id="editMainCategoryModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Main Category</h5>
            </div>
            <form action="{{ route('main-categories.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" class="form-control" name="name" value="{{ $item->name }}" required>
                    </div>
                    <div class="form-group">
                        <label>کیٹیگری کا نام</label>
                        <input type="text" class="form-control" value="{{ $item->urdu_name }}" name="urdu_name" required>
                    </div>
                    <div class="form-group">
                        <label>Category Image</label>
                        <input type="file" class="form-control edit-image-input" name="image">
                        <div class="mt-2">
                            <img
                                src="{{ $item->image ? asset('storage/' . $item->image) : '' }}"
                                style="width:100px; height:100px;"
                                alt="image preview"
                                class="edit-image-preview {{ $item->image ? '' : 'd-none' }}">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Listen to change events on all edit image inputs
        $('.edit-image-input').on('change', function() {
            const input = this;
            const preview = $(this).siblings('div').find('.edit-image-preview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.attr('src', e.target.result); // Set the new image
                    preview.removeClass('d-none'); // Show it if hidden
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.attr('src', '');
                preview.addClass('d-none'); // Hide if no file selected
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection