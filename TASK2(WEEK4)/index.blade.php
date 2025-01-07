@extends('layout.master')

@push('plugin-styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="{{ asset('css/marketing/style.css') }}" rel="stylesheet" />



@endpush

@section('content')


<div class="container">
    <div class="row mb-3">
        <div class="col-md-3">
            <input type="date" id="startTime" class="form-control" style="width: 100%;">
        </div>
        <div class="col-md-3">
            <input type="date" id="endTime" class="form-control" style="width: 100%;">
        </div>

    </div>
    <div class="row mb-3">
        <div class="col-md-3">
            <select id="saleEmployeeSelect" class="form-control select2" style="width: 100%;">
                <option value="0" selected>-Nhân viên sale-</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="typeAccountSelect" class="form-control select2" style="width: 100%;">
                <option value="0">Tài khoản ngoài</option>
                <option value="1" selected>Tài khoản cấp</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="feedbackSelect" class="" style="width: 100%;height: 100%">
                <option value="0">-Chọn phản hồi PH-</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="numConsultationSelect" class="form-control select2" style="width: 100%;">
                <option value="0" selected>-Số lần tư vấn-</option>
                <option value="notyet">Chưa tư vấn</option>
                <option value="once">Đã tư vấn 1 lần</option>
                <option value="many">Đã tư vấn nhiều lần</option>
            </select>
        </div>
    </div>
    <div class="row mb-3 schoolClass">
        <!-- Thành phố -->
        <div class="col-md-3">
            <select id="provinceSelect" class="form-control select2" style="width: 100%;">
                <option value="0" selected>-Chọn thành phố-</option>
                <option value="01">Hà Nội</option>
                <option value="31">Hải Phòng</option>
            </select>
        </div>

        <!-- Quận huyện -->
        <div class="col-md-3">
            <select id="districtSelect" class="form-control select2" style="width: 100%;">
                <option value="0">-Chọn quận huyện-</option>
            </select>
        </div>
        <!-- Trường -->
        <div class="col-md-3">
            <select id="schoolSelect" class="form-control select2" style="width: 100%;">
                <option value="0">-Chọn trường-</option>
            </select>
        </div>
        <!-- Lớp -->
        <div class="col-md-3">
            <select id="classSelect" class="form-control select2" style="width: 100%;">
                <option value="0">-Chọn lớp-</option>
            </select>
        </div>
    </div>
    <div class="row mb-3 justify-content-center">
        <div class="col-md-2 d-flex ">
            <button id="filterButton" class="btn btn-primary mb-2" style="flex: 1;">Lọc</button>
            <button id="feedback" class="btn btn-secondary" style="flex: 1;" onclick="openFeedbackForm()">Phản
                hồi</button>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div style="text-align: left">
            <input type="text" id="searchInput" placeholder="Tìm kiếm">
            <button id="searchButton">Tìm kiếm</button>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <br>
        <table id="marketingTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Họ tên</th>
                    <th>Tuổi</th>
                    <th>Quận</th>
                    <th>Trường</th>
                    <th>Lớp</th>
                    <th>SDT</th>
                    <th>Email</th>
                    <th>Số lần tư vấn</th>
                    <th>Phản hồi PH</th>
                </tr>
            </thead>
            <tbody>
                <!-- Dữ liệu học sinh sẽ được load từ DataTables -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Feedback -->
<div id="feedbackModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <h4 class="mb-3">Phản hồi</h4>
        <form>
            <div class="mb-3">
                @CSRF
                <label for="feedbackContent" class="form-label">Nội dung phản hồi</label>
                <textarea class="form-control" id="feedbackContent" name="feedbackContent" rows="4" required></textarea>

                <!-- chọn parent key  -->
                <label for="parentKeySelect">Chọn loại phản hồi</label>
                <select id="feedbackSelectParent">
                <option value="0">-Chọn phản hồi PH-</option>
                </select>

            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary me-2" onclick="closeFeedbackForm()">Hủy</button>
                <button id="btn_save_fb" class="btn btn-success">Luu</button>
            </div>
        </form>
    </div>
</div>

<div id="successPopup"
    style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background-color:white; border:1px solid #ccc; padding:20px; z-index:1000;">
    <h4>Thêm thành công!</h4>
    <p>Phản hồi của bạn đã được lưu!</p>
    <button id="closePopup">Đóng</button>
</div>

</div>

@include('admin.marketing.consultation-modal')
@endsection
@push('plugin-scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Thêm Bootstrap JS -->
@endpush

@push('custom-scripts')
    <script>
        var currentUserName = "{{ Auth::user()->name }}";
        var currentUserId = "{{ Auth::user()->id }}";
        window.csrfToken = '{{ csrf_token() }}';


    </script>
    <script src="{{ asset('js/marketing/index.js') }}"></script>
    <script>
        // Gọi API khi trang được load
        $(document).ready(function () {
            getSchool();
        });



    </script>


@endpush







<style>
    #successPopup {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    #successPopup button {
        margin-top: 10px;
    }

    /* Hiển thị nền tối khi form xuất hiện */
    .modal-overlay {
        position: fixed;
        width: 700px;
        height: 200px;
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        top: 30%;
        left: 30%;
    }

    .modal-overlay.show {
        display: flex;
        animation: fadeIn 0.3s ease-in-out;
    }

    .modal-content {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0);
        width: 400px;
        max-width: 90%;
        transform: scale(0.9);
        opacity: 0;
        animation: scaleUp 0.3s ease-in-out forwards;
        background: rgba(0, 0, 0, 0.7);
    }

    /* Hiệu ứng fade-in cho nền */
    @keyframes fadeIn {
        from {
            background: rgba(0, 0, 0, 0);
        }

        to {
            background: rgba(0, 0, 0, 0.5);
        }
    }

    /* Hiệu ứng zoom-in cho form */
    @keyframes scaleUp {
        from {
            transform: scale(0.9);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* Đóng cuộn khi form mở */
    body.modal-open {
        overflow: hidden;
    }
</style>