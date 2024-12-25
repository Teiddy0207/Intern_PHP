$(document).ready(function () {
    $('.select2').select2({
        placeholder: "Select an option",
    });
});

function getFeedback() {
    $.ajax({
        url: 'api/feedbacks', // URL API của bạn
        method: 'GET',
        success: function (response) {
            var options = '<option value="0">-Chọn phản hồi PH-</option>';
            var feedbacks = response.feedbacks;

            var parentOptions = feedbacks.filter(feedback => feedback.parent_key === 0);
            var childOptions = feedbacks.filter(feedback => feedback.parent_key !== 0);

            // Lặp qua các phản hồi cha và thêm vào dropdown
            parentOptions.forEach(function (parent) {
                // Kiểm tra nếu phản hồi cha có ít nhất một phản hồi con
                var hasChild = childOptions.some(child => child.parent_key === parent.id);

                options += `<option class="parent" value="${parent.id}" ${hasChild ? 'disabled' : ''}>
            ${parent.description}
        </option>`;

                // Lọc và thêm các phản hồi con tương ứng
                childOptions.forEach(function (child) {
                    if (child.parent_key === parent.id) {
                        options += `<option class="child" value="${child.id}">--${child.description}</option>`;
                    }
                });
            });

            // Cập nhật dropdown với các lựa chọn cha và con
            $('#feedbackSelect').html(options);

            // // Khởi tạo lại Select2 sau khi cập nhật HTML
            // $('#feedbackSelect').select2();
        }

    });
}

function getSaleEmployee() {
    $.ajax({
        url: 'api/sale-employees',
        method: 'GET',
        success: function (response) {
            var options = '<option value="0">-Nhân viên sale-</option>';
            response.sales.forEach(function (sale) {
                options += `<option value="${sale.id}">${sale.name}</option>`;
            });
            $('#saleEmployeeSelect').html(options);
        }
    });
}
function getDistrict(provinceId) {
    // Gửi AJAX để lấy danh sách quận/huyện
    $.ajax({
        url: 'api/districts',
        method: 'GET',
        data: {
            province_id: provinceId
        },
        success: function (response) {
            var options = '<option value="0">-Chọn quận huyện-</option>';
            response.districts.forEach(function (district) {
                options += `<option value="${district.id}">${district.name}</option>`;
            });
            $('#districtSelect').html(options);
            $('#schoolSelect').html('<option value="0">-Chọn trường-</option>'); // Reset trường
        }
    });
}

// async function getSchool(districtId) {
//     // Gửi AJAX để lấy danh sách trường
//     let data;
//     try {
//         const response = await fetch('/admin/api/getCountSchool');
//         if (!response.ok) {
//             throw new Error(`HTTP error! status: ${response.status}`);
//         }
//         data = await response.json();
//         console.log(data)
//     } catch (error) {
//         console.error('Error:', error);
//     }

//     $.ajax({
//         url: 'api/schools',
//         method: 'GET',
//         data: {
//             district_id: districtId
//         },

//         success: function (response) {
//             var options = '<option value="0">-Chọn trường-</option>';
            
            
            
//             response.schools.forEach(function (school) {
//                 var studentCount = school.contact_students_count;
//                 options += `<option value="${school.id}">${school.name} (${data})</option>`;
//             });
//             $('#schoolSelect').html(options);

//         }
//     });
// }

//code của quang anh 
async function getSchool(districtId) {
    let schoolData;
    try {
        const response = await fetch('/admin/api/getCountSchool');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        schoolData = await response.json(); 
        console.log('School Data:', schoolData); 
    } catch (error) {
        console.error('Error:', error);
        return; 
    }

    // Gửi yêu cầu AJAX để lấy danh sách trường theo quận
    $.ajax({
        url: 'api/schools',
        method: 'GET',
        data: {
            district_id: districtId
        },
        success: function (response) {
            console.log('Response from schools API:', response); // Kiểm tra phản hồi
            var options = '<option value="0">-Chọn trường-</option>';
            
          
            let studentCountMap = {};
            schoolData.schools.forEach(function (school) {
                // Ở đây, bạn cần ánh xạ ID của trường với số lượng học sinh
                studentCountMap[school.school] = school.contact_students_count; // school.school là ID
            });

            // Duyệt qua các trường trong phản hồi từ AJAX
            response.schools.forEach(function (school) {
                // Lấy số học sinh tương ứng với ID trường
                var studentCount = studentCountMap[school.id] || 0; // Sử dụng school.id để ánh xạ
                options += `<option value="${school.id}">${school.name} (${studentCount})</option>`;
            });
            $('#schoolSelect').html(options); 
        }
    });
}








function getClass(schoolId) {
    // Gửi AJAX để lấy danh sách lớp
    $.ajax({
        url: 'api/classes',
        method: 'GET',
        data: {
            school_id: schoolId
        },
        success: function (response) {
            // Xóa các option cũ và thêm option mới
            var classOptions = '<option value="0">-Chọn lớp-</option>';
            response.classes.forEach(function (classItem) {
                classOptions += '<option value="' + classItem.id + '">' + classItem.class_code +
                    '</option>';
            });
            $('#classSelect').html(classOptions).trigger('change');
        },
        error: function () {
            alert('Không thể tải danh sách lớp, vui lòng thử lại!');
        }
    });
}
$(document).ready(function () {
    getSchool(0);
    getFeedback();
    getSaleEmployee();
    // Khi thay đổi tỉnh/thành phố
    $('#provinceSelect').change(function () {
        var provinceId = $(this).val();
        if (provinceId == 0) {
            $('#dSelect').html('<option value="0">-Chọn quận huyện-</option>');
            $('#classSelect').html('<option value="0">-Chọn lớp-</option>');
            return;
        }
        getDistrict(provinceId);
        getSchool(0);
    });

    // Khi thay đổi quận/huyện
    $('#districtSelect').change(function () {
        var districtId = $(this).val();
        getSchool(districtId);
    });

    $('#typeAccountSelect').change(function () {
        var accountType = $(this).val();
        if (accountType === "0") { // Tài khoản ngoài
            $('.schoolClass').hide(); // Ẩn hàng
        } else if (accountType === "1") { // Tài khoản cấp
            $('.schoolClass').show(); // Hiện hàng
        }
    });

    // Khi thay đổi trường
    $('#schoolSelect').change(function () {
        var schoolId = $(this).val();
        getClass(schoolId);
    });
    // Cấu hình DataTables
    var table = $('#marketingTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        pageLength: 25,
        ajax: {
            url: "api/marketing",
            data: function (d) {
                d.start_time = $('#startTime').val();
                d.end_time = $('#endTime').val();
                d.province_id = $('#provinceSelect').val();
                d.district_id = $('#districtSelect').val();
                d.school_id = $('#schoolSelect').val();
                d.class_id = $('#classSelect').val();
                d.sale_id = $('#saleEmployeeSelect').val();
                d.account_type = $('#typeAccountSelect').val();
                d.search_input = $('#searchInput').val();
                d.num_consultation = $('#numConsultationSelect').val();
                d.feedback = $('#feedbackSelect').val();
            }
        },
        columns: [{
            data: 'student_name',
            name: 'student_name',
            orderable: false,
            render: function (data, type, row) {
                return `<a href="/admin/student/${row.student_id}">${data}</a>`;
            }
        },
        {
            data: 'age',
            name: 'age',
            orderable: false
        },
        {
            data: 'district_name',
            name: 'district_name',
            orderable: false
        },
        {
            data: 'school_name',
            name: 'school_name',
            orderable: false
        },
        {
            data: 'class_code',
            name: 'class_code',
            orderable: false
        },
        {
            data: 'phone',
            name: 'phone',
            orderable: false
        },
        {
            data: 'email',
            name: 'email',
            orderable: false
        },
        {
            data: 'consultations_count',
            name: 'consultations_count',
            orderable: false,
            render: function (data, type, row) {
                return `<div class="consultations-link" data-student-id="${row.student_id}" style="cursor: pointer; color: blue; padding: 5px;">
                    ${data}
                </div>`;
            }
        },
        {
            data: 'feedback_description',
            name: 'feedback_description',
            orderable: false
        },
        ]
    });

    var debounceTimeout;

    // Khi nhấn nút tìm kiếm
    $('#searchButton').click(function () {
        var searchValue = $('#searchInput').val(); // Lấy giá trị từ ô tìm kiếm
        table.search(searchValue).draw(); // Thực hiện tìm kiếm và vẽ lại bảng
    });

    // Khi nhấn nút lọc
    $('#filterButton').click(function () {
        table.ajax.reload(); // Tải lại bảng với các điều kiện đã chọn
    });

    var student_id;

    $(document).on('click', '.consultations-link', function () {
        var studentId = $(this).data('student-id');
        $('#consultationsModal').modal('show');
        $('#addConsultationRow').show();
        student_id = studentId;

        // Gọi hàm để tải dữ liệu
        loadConsultationsModal(studentId);
    });


    $(document).on('click', '#addConsultationRow', function () {
        var currentDate = new Date();
        currentDate.setHours(currentDate.getHours() + 7); // Cộng thêm 7 giờ

        var formattedDate = currentDate.toISOString().slice(0, 19).replace('T', ' ');

        $('#addConsultationRow').hide();
        // Gửi AJAX để lấy danh sách feedback
        $.ajax({
            url: 'api/feedbacks',
            method: 'GET',
            success: function (response) {
                var parentOptions = response.feedbacks.filter(feedback => feedback.parent_key === 0);
                var childOptions = response.feedbacks.filter(feedback => feedback.parent_key !== 0);

                var options = '';

                // Thêm các phản hồi cha và con
                parentOptions.forEach(function (parent) {
                    // Kiểm tra nếu phản hồi cha có ít nhất một phản hồi con
                    var hasChild = childOptions.some(child => child.parent_key === parent.id);

                    options += `<option style="font-weight: bold;" value="${parent.id}" ${hasChild ? 'disabled' : ''}>
                ${parent.description}
            </option>`;

                    childOptions.forEach(function (child) {
                        if (child.parent_key === parent.id) {
                            options += `<option style="padding-left: 20px;" value="${child.id}">
                        -- ${child.description}
                    </option>`;
                        }
                    });
                });

                // Thêm dòng mới vào bảng
                $('#consultationsTableBody').append(`
            <tr>
                <td>${formattedDate}</td>
                <td>${currentUserName}</td>
                <td>
                    <select class="feedback-select">
                        ${options}
                    </select>
                </td>
                <td>
                    <div style="display: flex; align-items: center;">
                        <input type="text" id="note-input" class="form-control" placeholder="Nhập ghi chú" style="margin-right: 10px;" />
                        <button class="btn btn-success btn-save-note" style="margin-right: 5px;">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-danger btn-cancel-note">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);
            },
            error: function () {
                alert("Không thể tải danh sách phản hồi.");
            }
        });


    });

    // Xử lý nút Lưu
    $(document).on('click', '.btn-save-note', function () {
        var row = $(this).closest('tr'); // Lấy dòng hiện tại
        var feedbackId = row.find('.feedback-select').val(); // Lấy giá trị phản hồi PH
        var note = $('#note-input').val(); // Lấy giá trị ghi chú
        $('#addConsultationRow').show();
        $.ajax({
            url: 'api/consultations',
            method: 'POST',
            data: JSON.stringify({
                student_id: student_id,
                feedback_id: feedbackId,
                note: note,
                _token: window.csrfToken
            }),
            contentType: 'application/json',
            success: function (response) {
                alert("Lưu thành công!");
                row.remove();
                loadConsultationsModal(student_id);
                table.ajax.reload(null, false); // Giữ nguyên trạng thái phân trang, sắp xếp
                $('#addConsultationRow').show();
            },
            error: function () {
                alert("Đã xảy ra lỗi khi lưu dữ liệu!");
            }
        });
    });

    // Xử lý nút Hủy
    $(document).on('click', '.btn-cancel-note', function () {
        var row = $(this).closest('tr'); // Lấy dòng hiện tại
        // Hiển thị lại nút "Thêm dòng mới"
        $('#addConsultationRow').show();
        row.remove();
    });

    function loadConsultationsModal(studentId) {
        $.ajax({
            url: `api/consultations/${studentId}`,
            method: 'GET',
            success: function (response) {
                // Xóa dữ liệu cũ
                $('#consultationsTableBody').empty();
                // Thêm dữ liệu mới vào bảng
                response.data.forEach(function (consultation) {
                    var editAndDeleteButtons = ''; // Biến chứa các nút sửa và xóa
                    console.log(consultation);
                    // Kiểm tra nếu sale_id của bản ghi trùng với tài khoản đăng nhập
                    if (consultation.sale_id == currentUserId) {
                        editAndDeleteButtons = `
                            <button class="btn btn-warning btn-edit-consultation" data-id="${consultation.id}" style="margin-right: 5px;">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-delete-consultation" data-id="${consultation.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                    }

                    $('#consultationsTableBody').append(`
                        <tr>
                            <td>${consultation.created_at}</td>
                            <td>${consultation.sale_name}</td>
                            <td>${consultation.feedback}</td>
                            <td style="word-wrap: break-word; white-space: pre-wrap;">${consultation.note}</td>
                            <td>
                                ${editAndDeleteButtons}
                            </td>
                        </tr>
                    `);
                });

                // Hiển thị thông tin học sinh trong modal (nếu cần)
                var studentInfo = response.student; // Giả sử API trả về thông tin học sinh
                $('#studentName').text(studentInfo.name);
                $('#studentDistrict').text(studentInfo.district);
                $('#studentSchool').text(studentInfo.school);
                $('#studentClass').text(studentInfo.class);
                $('#studentAge').text(studentInfo.age);
                $('#studentPhone').text(studentInfo.phone);
                $('#studentEmail').text(studentInfo.email);
            },
            error: function () {
                console.log('Không thể lấy dữ liệu các lần tư vấn!');
            }
        });
    }

    $(document).on('click', '.btn-edit-consultation', function () {
        var row = $(this).closest('tr'); // Lấy dòng hiện tại
        var consultationId = $(this).data('id'); // Lấy ID của consultation
        // Lấy dữ liệu từ hàng hiện tại
        var currentFeedback = row.find('td:nth-child(3)').text().trim(); // Lấy phản hồi hiện tại
        var currentNote = row.find('td:nth-child(4)').text().trim(); // Lấy ghi chú hiện tại

        // Ẩn tất cả các nút sửa và xóa
        $('.btn-edit-consultation, .btn-delete-consultation').hide();

        // Gửi AJAX để lấy danh sách phản hồi
        $.ajax({
            url: 'api/feedbacks',
            method: 'GET',
            success: function (response) {
                var parentOptions = response.feedbacks.filter(feedback => feedback.parent_key === 0);
                var childOptions = response.feedbacks.filter(feedback => feedback.parent_key !== 0);

                var options = '';

                // Tạo danh sách cha và con
                parentOptions.forEach(function (parent) {
                    var hasChild = childOptions.some(child => child.parent_key === parent.id);

                    options += `<option value="${parent.id}" style="font-weight: bold;" ${hasChild ? 'disabled' : ''} ${parent.description === currentFeedback ? 'selected' : ''}>
                ${parent.description}
            </option>`;

                    childOptions.forEach(function (child) {
                        if (child.parent_key === parent.id) {
                            options += `<option value="${child.id}" style="padding-left: 20px;" ${child.description === currentFeedback ? 'selected' : ''}>
                        -- ${child.description}
                    </option>`;
                        }
                    });
                });

                // Thay đổi nội dung hàng hiện tại
                row.html(`
            <td>${row.find('td:first').text()}</td> <!-- Giữ nguyên ngày tạo -->
            <td>${row.find('td:nth-child(2)').text()}</td> <!-- Giữ nguyên tên nhân viên -->
            <td>
                <select class="feedback-select">
                    ${options}
                </select>
            </td>
            <td>
                <div style="display: flex; align-items: center;">
                    <input type="text" id="note-input-edit" class="form-control" value="${currentNote}" placeholder="Nhập ghi chú" style="margin-right: 10px;" />
                    <button class="btn btn-success btn-save-edit" data-id="${consultationId}" style="margin-right: 5px;">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn btn-danger btn-cancel-edit" data-id="${consultationId}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </td>
        `);
            },
            error: function () {
                alert("Không thể tải danh sách phản hồi.");
            }
        });
    });

    $(document).on('click', '.btn-save-edit', function () {
        var row = $(this).closest('tr'); // Lấy dòng hiện tại
        var consultationId = $(this).data('id'); // Lấy ID của consultation
        var feedbackId = row.find('.feedback-select').val(); // Lấy ID phản hồi PH
        var note = row.find('#note-input-edit').val(); // Lấy ghi chú

        // Gửi AJAX để lưu thay đổi
        $.ajax({
            url: `api/consultations/${consultationId}`,
            method: 'PUT',
            data: {
                feedback_id: feedbackId,
                note: note,
                _token: window.csrfToken
            },
            success: function (response) {
                alert('Lưu thành công!');
                // Gọi lại hàm load modal để cập nhật dữ liệu
                loadConsultationsModal(student_id);
                table.ajax.reload(null, false); // Giữ nguyên trạng thái phân trang, sắp xếp
            },
            error: function () {
                alert('Đã xảy ra lỗi khi lưu dữ liệu!');
            }
        });
    });

    $(document).on('click', '.btn-cancel-edit', function () {
        loadConsultationsModal(student_id); // Tải lại modal để khôi phục dữ liệu gốc
    });

    $(document).on('click', '.btn-delete-consultation', function () {
        var consultationId = $(this).data('id'); // Lấy ID của consultation
        // Gửi AJAX để lưu thay đổi
        $.ajax({
            url: `api/consultations/${consultationId}`,
            method: 'DELETE',
            data: {
                _token: window.csrfToken
            },
            success: function (response) {
                alert('Xóa thành công!');
                // Gọi lại hàm load modal để cập nhật dữ liệu
                loadConsultationsModal(student_id);
                table.ajax.reload(null, false); // Giữ nguyên trạng thái phân trang, sắp xếp
            },
            error: function () {
                alert('Đã xảy ra lỗi khi xóa!');
            }
        });
    });
});
