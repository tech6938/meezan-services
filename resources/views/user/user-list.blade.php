@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
    <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
    <!-- Font Awesome for WhatsApp icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Modal background */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
        }

        /* Modal content box */
        .modal-content {
            background-color: #fff;
            margin: 12% auto;
            padding: 25px 30px;
            border-radius: 15px;
            width: 350px;
            max-width: 90%;
            text-align: center;
            position: relative;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.4s ease;
        }

        /* Animation */
        @keyframes fadeInUp {
            0% {
                transform: translateY(-30px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Close button */
        .close {
            position: absolute;
            top: 12px;
            right: 18px;
            font-size: 22px;
            font-weight: bold;
            color: #888;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #e74c3c;
        }

        /* Modal header */
        .modal-content h3 {
            font-size: 22px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }

        /* Dropdown styling */
        select {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            border-radius: 10px;
            border: 1.5px solid #ccc;
            background: #f9f9f9;
            color: #333;
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg width='16' height='16' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5 7L10 12L15 7' stroke='%23333' stroke-width='2'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 12px;
        }

        select:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 6px rgba(76, 175, 80, 0.3);
        }

        /* Save button */
        button#saveBtn {
            width: 100%;
            padding: 12px 0;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(90deg, #4CAF50, #43a047);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        button#saveBtn:hover {
            background: linear-gradient(90deg, #43a047, #388e3c);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        /* WhatsApp Icon Specific Styling */
        .btn-whatsapp {
            background-color: #25D366 !important;
        }

        .btn-whatsapp .fab.fa-whatsapp {
            font-size: 18px;
            color: white;
        }

        /* If you want to ensure the icon is visible */
        .btn-whatsapp i {
            width: auto;
            height: auto;
            font-size: 18px;
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <section class="section">

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            {{-- <div class="card-header">
                            </div> --}}

                            <div class="card-header"
                                style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                <h4>Users List</h4>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    {{-- <a href="{{ route('users.preview') }}" class="btn btn-sm btn-primary p-0 m-0">Preview</a> --}}
                                    @include('components.preview-button', [
                                        'apiUrl' => route('users.preview'),
                                        'previewTitle' => 'Users Data Preview',
                                        'queryParams' => request()->all(),
                                        'buttonLabel' => 'Preview',
                                    ])
                                    @include('components.export-button', [
                                        'apiUrl' => route('users.exportMulti'),
                                        'fileName' => 'users',
                                        'queryParams' => request()->all(),
                                        'buttonLabel' => 'Export',
                                    ])
                                </div>
                                {{-- @include('components.export-button', [
                                    'apiUrl' => route('users.export'),
                                    'fileName' => 'users',
                                    'queryParams' => request()->all(),
                                    'buttonLabel' => 'Export',
                                ]) --}}
                            </div>
                            <div class="card-body">
                                @include('components.date-range-filter')
                                <div class="table-responsive">
                                    <table class="table table-striped" id="table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>User Name</th>
                                                <th>Phone</th>
                                                <th>Referral Code</th>
                                                <th>Direct Referrals</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @if ($data->isNotEmpty())
                                                @foreach ($data as $user)
                                                    <tr>

                                                        <td class="text-center">

                                                            {{ $loop->iteration }}
                                                        </td>
                                                        <td>
                                                            <img src="{{ $user->image_url ?? asset('public\assets\img\users\user-8.png') }}" style="width:80px; height:70px;"
                                                                alt="user image">
                                                            {{ $user->name }}
                                                        </td>
                                                        <td>
                                                            {{ $user->phone ?? 'N/A' }}
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-light">
                                                                {{ $user->referral_code ?? 'N/A' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <strong>{{ $user->referrals_count ?? 0 }}</strong>
                                                        </td>
                                                        <td>
                                                            <div class="badge badge-primary ">{{ $user->status }}</div>
                                                        </td>
                                                        <td class="d-flex align-items-center gap-2">
                                                            <a href="{{ route('viewUserDetail', ['id' => $user->id]) }}"
                                                                class="btn btn-dark"><i data-feather="eye"></i>
                                                            </a>

                                                            <button class="btn btn-primary openModalBtn"
                                                                data-user-id="{{ $user->id }}">
                                                                <i data-feather="refresh-cw"></i>
                                                            </button>

                                                            <form action="{{ route('user.destroy', ['id' => $user->id]) }}"
                                                                method="POST" class="p-1">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">
                                                                    <i data-feather="trash-2"></i>
                                                                </button>
                                                            </form>

                                                            <!-- WhatsApp Button with proper icon -->
                                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->phone) }}"
                                                                class="btn btn-whatsapp" target="_blank"
                                                                title="Chat on WhatsApp">
                                                                <i class="fab fa-whatsapp"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="7" class="text-center">
                                                        No Users Found
                                                    </td>
                                                </tr>
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
    </div>
    <!-- Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Update User Status</h3>
            <form action="{{ route('updateUserStatus') }}" method="post">
                @csrf
                <input type="hidden" id="userIdInput" name="user_id" value="">
                <select id="statusDropdown" name="status" required>
                    <option value="" selected disabled>Select Status</option>
                    <option value="blocked">Blocked</option>
                    <option value="unblocked">Unblocked</option>
                </select>
                <button id="saveBtn" type="submit">Save</button>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#table').DataTable({
                "pageLength": 100,
                "lengthMenu": [
                    [100, 300, 500, 1000],
                    [100, 300, 500, 1000]
                ]
            });
            const modal = document.getElementById("userModal");
            const closeBtn = document.querySelector(".close");
            const userIdInput = document.getElementById("userIdInput");

            // Open modal and set user ID
            document.querySelectorAll(".openModalBtn").forEach(btn => {
                btn.addEventListener("click", function() {
                    const userId = this.getAttribute("data-user-id");
                    userIdInput.value = userId;
                    modal.style.display = "block";
                });
            });

            // Close modal
            closeBtn.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        });
    </script>
    <script src="assets/bundles/jquery/jquery.min.js"></script>
    <script src="assets/bundles/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/bundles/datatables/datatables.min.js"></script>
    <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/page/datatables.js"></script>
@endsection
