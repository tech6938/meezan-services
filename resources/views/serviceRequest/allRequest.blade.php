     @extends('layout.dashboard-layout')

     @section('css')
         <link rel="stylesheet" href="assets/bundles/datatables/datatables.min.css">
         <link rel="stylesheet" href="assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">

         <style>
             /* Modal background */
             .modal {
                 display: none;
                 /* hidden by default */
                 position: fixed;
                 z-index: 1000;
                 left: 0;
                 top: 0;
                 width: 100%;
                 height: 100%;
                 overflow: auto;
                 background-color: rgba(0, 0, 0, 0.5);
                 /* semi-transparent background */
             }

             /* Modal content box */
             .modal-content {
                 background-color: #fff;
                 margin: 15% auto;
                 padding: 20px;
                 border-radius: 5px;
                 width: 300px;
                 text-align: center;
                 position: relative;
             }

             /* Close button */
             .close {
                 position: absolute;
                 top: 10px;
                 right: 15px;
                 font-size: 20px;
                 cursor: pointer;
             }

             /* Dropdown styling */
             select {
                 padding: 5px;
                 font-size: 16px;
                 margin-top: 15px;
             }

             button {
                 padding: 10px 20px;
                 font-size: 16px;
                 cursor: pointer;
             }

             /* View Modal Styling */
             .view-modal {
                 width: 420px;
                 /* was 380px */
                 padding: 25px;
                 animation: scaleIn 0.3s ease;
             }


             @keyframes scaleIn {
                 from {
                     transform: scale(0.8);
                     opacity: 0;
                 }

                 to {
                     transform: scale(1);
                     opacity: 1;
                 }
             }

             .view-header {
                 text-align: center;
                 margin-bottom: 20px;
             }

             .avatar {
                 width: 60px;
                 height: 60px;
                 background: #6777ef;
                 border-radius: 50%;
                 display: flex;
                 align-items: center;
                 justify-content: center;
                 margin: 0 auto 10px;
                 color: #fff;
             }

             .view-header h3 {
                 margin: 5px 0;
                 font-size: 20px;
             }

             .status-badge {
                 display: inline-block;
                 padding: 5px 12px;
                 font-size: 13px;
                 border-radius: 20px;
                 background: #eee;
                 margin-top: 5px;
             }

             .status-badge.approved {
                 background: #28a745;
                 color: #fff;
             }

             .status-badge.blocked {
                 background: #dc3545;
                 color: #fff;
             }

             .status-badge.suspend {
                 background: #ffc107;
                 color: #ffffffff;
             }

             .status-badge.pending {
                 background: rgba(21, 239, 255, 1);
                 color: #ffffffff;
             }

             .view-body {
                 margin: 20px 0;
             }

             .info-row {
                 display: flex;
                 align-items: center;
                 gap: 10px;
                 padding: 10px 0;
                 border-bottom: 1px solid #f1f1f1;
             }

             .info-row i {
                 color: #6777ef;
             }

             .viewCloseBtn {
                 width: 100%;
                 margin-top: 10px;
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
                                <div class="card-header"
                                style="display: flex; justify-content: space-between; align-items: center;">
                                <h4>All Orders</h4>
                                @include('components.export-button', [
                                         'apiUrl' => route('requests.export'),
                                         'fileName' => 'all_requests',
                                         'queryParams' => request()->all(),
                                         'buttonLabel' => 'Export',
                                     ])
                                 </div>

                                 <div class="card-body">
                                     @include('components.date-range-filter')
                                     <div class="table-responsive">
                                         <table class="table table-striped" id="table-1">
                                             <thead>
                                                 <tr>
                                                     <th class="text-center">#</th>
                                                     <th>Name</th>
                                                     <th>Status</th>
                                                     <th>Action</th>
                                                 </tr>
                                             </thead>
                                             <tbody>
                                                 @if (!empty($result) && count($result))
                                                     {{-- @dd($result) --}}
                                                     @foreach ($result as $provider)
                                                         <tr>
                                                             <td class="text-center">{{ $loop->iteration }}</td>

                                                             {{-- User name --}}
                                                             <td>{{ $provider['user_name'] }}</td>

                                                             {{-- Status badge --}}
                                                             <td>
                                                                 <span
                                                                     class="badge
                    {{ $provider['status'] == 'approved' ? 'badge-success' : '' }}
                    {{ $provider['status'] == 'blocked' ? 'badge-danger' : '' }}
                    {{ $provider['status'] == 'suspend' ? 'badge-warning' : '' }}
                    {{ $provider['status'] == 'pending' ? 'badge-info' : '' }}
                ">
                                                                     {{ ucfirst($provider['status']) }}
                                                                 </span>
                                                             </td>
                                                             <td>
                                                                 {{-- Action buttons --}}
                                                                 <!-- In your table row, pass files as JSON -->
                                                                 <button class="btn btn-dark viewBtn"
                                                                     data-name="{{ $provider['user_name'] }}"
                                                                     data-status="{{ $provider['status'] }}"
                                                                     data-lat="{{ $provider['lat'] ?? 'N/A' }}"
                                                                     data-lng="{{ $provider['lang'] ?? 'N/A' }}"
                                                                     data-desc="{{ $provider['desc'] ?? 'N/A' }}"
                                                                     data-file="{{ json_encode($provider['file_urls'] ?? []) }}">
                                                                     <i data-feather="eye"></i>
                                                                 </button>
                                                                 <a href="{{ route('service-request.accepted-providers', $provider['id']) }}"
                                                                     class="btn btn-info" target="_blank">
                                                                     <i data-feather="users"></i> View Providers
                                                                 </a>

                                                             </td>
                                                         </tr>
                                                     @endforeach
                                                 @else
                                                     <tr>
                                                         <td colspan="6" class="text-center">No Providers Found</td>
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
             <!-- View Provider Modal -->
             <!-- View Provider Modal -->
             <!-- In your blade file, update the modal HTML -->
             <div id="viewModal" class="modal">
                 <div class="modal-content view-modal" style="width: 500px; max-height: 80vh; overflow-y: auto;">
                     <span class="close viewClose">&times;</span>

                     <div class="view-header">
                         <div class="avatar">
                             <i data-feather="user"></i>
                         </div>
                         <h3 id="viewName"></h3>
                         <span class="status-badge" id="viewStatus"></span>
                     </div>

                     <div class="view-body">
                         <div class="info-row">
                             <i data-feather="map-pin"></i>
                             <span id="viewLat">Latitude</span>, <span id="viewLng">Longitude</span>
                         </div>

                         <div class="info-row">
                             <i data-feather="file-text"></i>
                             <span id="viewDesc"></span>
                         </div>

                         <div class="info-row">
                             <i data-feather="file"></i>
                             <div id="viewFiles">
                                 <!-- Files will be displayed here -->
                             </div>
                         </div>
                     </div>

                     <button class="btn btn-secondary viewCloseBtn">Close</button>
                 </div>
             </div>

             <!-- Add CSS for file display -->
             <style>
                 .file-preview-container {
                     display: flex;
                     flex-wrap: wrap;
                     gap: 10px;
                     margin-top: 10px;
                 }

                 .file-item {
                     flex: 0 0 calc(50% - 10px);
                     max-width: calc(50% - 10px);
                     border: 1px solid #ddd;
                     border-radius: 5px;
                     padding: 10px;
                     text-align: center;
                 }

                 .file-item img {
                     max-width: 100%;
                     max-height: 150px;
                     object-fit: contain;
                 }

                 .file-item video,
                 .file-item audio {
                     width: 100%;
                 }

                 .file-name {
                     margin-top: 5px;
                     font-size: 12px;
                     word-break: break-all;
                 }

                 .file-download-btn {
                     margin-top: 5px;
                     font-size: 12px;
                     padding: 3px 8px;
                 }
             </style>
         @endsection

         @section('js')
             <script>
                 const viewModal = document.getElementById("viewModal");

                 document.querySelectorAll(".viewBtn").forEach(btn => {
                     btn.addEventListener("click", function() {
                         document.getElementById("viewName").textContent = this.dataset.name;

                         const statusEl = document.getElementById("viewStatus");
                         statusEl.textContent = this.dataset.status;
                         statusEl.className = "status-badge " + this.dataset.status;

                         document.getElementById("viewLat").textContent = this.dataset.lat;
                         document.getElementById("viewLng").textContent = this.dataset.lng;
                         document.getElementById("viewDesc").textContent = this.dataset.desc;

                         // Handle multiple files
                         const filesContainer = document.getElementById("viewFiles");

                         try {
                             // Parse file data (could be array or single URL)
                             let fileUrls = [];
                             if (this.dataset.file) {
                                 try {
                                     // Try to parse as JSON first
                                     fileUrls = JSON.parse(this.dataset.file);
                                 } catch (e) {
                                     // If not JSON, treat as single string
                                     fileUrls = [this.dataset.file];
                                 }
                             }

                             if (fileUrls.length > 0 && fileUrls[0]) {
                                 let htmlContent = '<div class="file-preview-container">';

                                 fileUrls.forEach((fileUrl, index) => {
                                     if (!fileUrl || fileUrl === 'null' || fileUrl === 'N/A') return;

                                     // Extract filename
                                     const fileName = fileUrl.split('/').pop();
                                     const fileExt = fileName.split('.').pop().toLowerCase();

                                     // Determine file type
                                     const audioExts = ['mp3', 'wav', 'ogg', 'm4a'];
                                     const videoExts = ['mp4', 'webm', 'avi', 'mov', 'mkv'];
                                     const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

                                     let fileContent = '';

                                     if (audioExts.includes(fileExt)) {
                                         fileContent = `
                                <div class="file-item">
                                    <audio controls style="width: 100%;">
                                        <source src="${fileUrl}" type="audio/${fileExt}">
                                        Your browser does not support audio.
                                    </audio>


                                </div>
                            `;
                                     } else if (videoExts.includes(fileExt)) {
                                         fileContent = `
                                <div class="file-item">
                                    <video controls style="width: 100%; max-height: 150px;">
                                        <source src="${fileUrl}" type="video/${fileExt}">
                                        Your browser does not support video.
                                    </video>
                                </div>
                            `;
                                     } else if (imageExts.includes(fileExt)) {
                                         fileContent = `
                                <div class="file-item">
                                    <img src="${fileUrl}" alt="${fileName}" style="max-width: 100%; max-height: 150px;">


                                </div>
                            `;
                                     } else {
                                         fileContent = `
                                <div class="file-item">
                                    <i data-feather="file" style="width: 50px; height: 50px;"></i>

                                </div>
                            `;
                                     }

                                     htmlContent += fileContent;
                                 });

                                 htmlContent += '</div>';
                                 filesContainer.innerHTML = htmlContent;
                             } else {
                                 filesContainer.innerHTML = '<span>No files attached</span>';
                             }

                         } catch (error) {
                             console.error('Error processing files:', error);
                             filesContainer.innerHTML = '<span>Error loading files</span>';
                         }

                         viewModal.style.display = "block";
                         feather.replace();
                     });
                 });

                 // Close modal buttons
                 document.querySelector("#viewModal .close").onclick = () => viewModal.style.display = "none";
                 document.querySelector(".viewCloseBtn").onclick = () => viewModal.style.display = "none";

                 // Close modal when clicking outside
                 window.onclick = function(event) {
                     if (event.target === viewModal) viewModal.style.display = "none";
                 };
             </script>
             <script src="assets/bundles/jquery/jquery.min.js"></script>
             <script src="assets/bundles/bootstrap/js/bootstrap.bundle.min.js"></script>
             <script src="assets/bundles/datatables/datatables.min.js"></script>
             <script src="assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
             <script src="assets/js/page/datatables.js"></script>
         @endsection
