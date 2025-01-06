<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\getMarketingRequest;
use App\Http\Requests\getStudentRequest;
use App\Models\SaleFeedback;
use App\Models\SaleMarketing;
use App\Models\Student;
use App\Models\RootSchool;
use App\Models\Login;
use App\Models\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


//Code Quang Anh
use App\Models\School;

class MarketingController extends Controller
{
    //code lay so lu

    public function getCountSchool()
    {

        $hotlinePhone = '1800599989';

        // Lấy danh sách các trường và đếm số học sinh
        $schools = Login::select('school', DB::raw('COUNT(*) as contact_students_count'))

            ->where('phone', '!=', '')
            ->where('isGrantedAccount', 1)
            ->where('deletePending', '!=', 1)
            ->where('isTeacherAccount', 0)
            ->where('isTesterAccount', 0)
            ->where('phone', '!=', $hotlinePhone)
            ->groupBy('school')
            ->get();

        return response()->json(['schools' => $schools]);


       

    }

    public function getCountClass(Request $request)
    {

        $hotlinePhone = '1800599989';
     //   $classId = $request->input('class_id'); 
        $classes = Login::select(
            'class',
            'root_schools.name as school_name',
            'logins.age as age_student',
           DB::raw('COUNT(*) as contact_students_count_in_classes')
        )
            ->join('root_schools', 'logins.school', '=', 'root_schools.id') // Kết nối với bảng root_schools
            ->where('phone', '!=', '')
            ->where('isGrantedAccount', 1)
            ->where('deletePending', '!=', 1)
            ->where('isTeacherAccount', 0)
            ->where('isTesterAccount', 0)
            ->where('phone', '!=', $hotlinePhone)
        //    ->where('class', $classId) 
            ->groupBy('class')
            ->get();
          

        return response()->json(['classes' => $classes]);

 
    }


//     public function storeFeedback(Request $request)
//     {
//         $request -> validate ([
//             'description' => 'required|string|max:1000',
//         ]);
//        // return $request -> all();
//        $feedback = SaleFeedback::create([
//         'description' => $request->input('description'),
   
//     ]);
//     return response()->json(['success' => true, 'feedback' => $feedback]);

// //         return redirect() -> route('admin.marketing.index');

//     }


public function storeFeedback(Request $request)
{
    $validator = Validator::make($request->all(), [
        'feedbackContent' => 'required|string|max:1000',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Tìm giá trị lớn nhất hiện tại của key
    $maxKey = SaleFeedback::max('key');

    // Nếu không có bản ghi nào, bắt đầu từ 17
    $newKey = $maxKey ? $maxKey + 1 : 17;

    // Tạo một bản ghi mới cho phản hồi
    $feedback = new SaleFeedback();
    $feedback->description = $request->input('feedbackContent'); // Sử dụng description
    $feedback->parent_key = 0; // Đặt parent_key là 0
    $feedback->key = $newKey; // Gán giá trị key mới
    $feedback->save();

    return response()->json(['message' => 'Phản hồi đã được lưu thành công!'], 200);
}








    public function index()
    {

        return view("admin.marketing.index");
    }

    public function getFeedbacks()
    {
        $feedbacks = SaleFeedback::select('id', 'description', 'parent_key')->get();
        return response()->json(['feedbacks' => $feedbacks]);
    }

    public function getSaleEmployees()
    {
        $salesRole = config('role.sale');
        $sales = User::select('id', 'name')
            ->where('deletePending', '!=', 1)->where('role', $salesRole)->get();
        return response()->json(['sales' => $sales]);
    }

    public function getData(getMarketingRequest $request)
    {
        // Lấy dữ liệu đã được xác thực
        $validated = $request->validated();
        $hotlinePhone = '1800599989';
        $studentsQuery = Student::select(
            'logins.uID as student_id',
            'logins.fullname as student_name',
            'root_schools.name as school_name',
            'root_schools.id as school_id',
            'root_classes.class_code as class_code',
            'logins.class as class_id',
            'logins.age as age',
            'logins.phone as phone',
            'logins.email as email',
            'root_districts.name as district_name',
            'logins.username as username',
            DB::raw('COUNT(sale_marketing.id) as consultations_count'), // Đếm số lần được tư vấn
            DB::raw('(SELECT sale_feedback.description
                  FROM sale_feedback
                  JOIN sale_marketing ON sale_marketing.feedback = sale_feedback.id
                  WHERE sale_marketing.student_id = logins.uID
                  AND sale_marketing.deleted_at is null
                  ORDER BY sale_marketing.created_at DESC
                  LIMIT 1) as feedback_description') // Lấy description từ bảng sale_feedback
        )
            ->leftJoin('root_students', 'root_students.id', '=', 'logins.objID')
            ->leftJoin('root_schools', 'root_schools.id', '=', 'logins.school')
            ->leftJoin('root_classes', 'root_classes.id', '=', 'logins.class')
            ->leftJoin('root_districts', 'root_districts.id', '=', 'root_schools.district')
            ->leftJoin('sale_marketing', 'sale_marketing.student_id', '=', 'logins.uID')
            ->where('isGrantedAccount', $validated['account_type'])
            ->where('deletePending', '!=', 1)
            ->where('isTeacherAccount', 0)
            ->where('isTesterAccount', 0)
            ->where('logins.phone', '!=', '')
            ->where('logins.phone', '!=', $hotlinePhone)
            ->whereNull('sale_marketing.deleted_at')
            ->groupBy(
                'logins.uID',
                'logins.fullname',
                'root_schools.name',
                'root_schools.id',
                'root_classes.class_code',
                'logins.class',
                'root_districts.name',
                'logins.username'
            );

        // Thêm điều kiện tìm kiếm nếu có
        $search = $validated['search_input'];
        if ($search != null) {
            $studentsQuery->where(function ($query) use ($search) {
                $query->where('logins.fullname', 'like', "%{$search}%")
                    ->orWhere('logins.username', 'like', "%{$search}%")
                    ->orWhere('logins.email', 'like', "%{$search}%")
                    ->orWhere('logins.phone', 'like', "%{$search}%");
            });
        }

        if ($validated['account_type'] == 0) {
            $validated['province_id'] = 0;
            $validated['district_id'] = 0;
            $validated['school_id'] = 0;
            $validated['class_id'] = 0;
        }

        if ($validated['start_time'] != null) {
            $studentsQuery->whereDate('sale_marketing.created_at', '>=', $validated['start_time']);
        }

        if ($validated['end_time'] != null) {
            $studentsQuery->whereDate('sale_marketing.created_at', '<=', $validated['end_time']);
        }

        if ($validated['sale_id'] != 0) {
            $studentsQuery->where('sale_marketing.sale_id', $validated['sale_id']);
        }

        if ($validated['feedback'] != 0) {
            $studentsQuery->where('sale_marketing.feedback', $validated['feedback']);
        }

        if (isset($validated['num_consultation'])) {
            switch ($validated['num_consultation']) {
                case 'notyet':
                    $studentsQuery->having('consultations_count', '=', 0);
                    break;
                case 'once':
                    $studentsQuery->having('consultations_count', '=', 1);
                    break;
                case 'many':
                    $studentsQuery->having('consultations_count', '>', 1);
                    break;
            }
        }

        if ($validated['province_id'] != 0) {
            $studentsQuery->where('province_id', $validated['province_id']);
        }

        if ($validated['district_id'] != 0) {
            $studentsQuery->where('root_schools.district', $validated['district_id']);
        }

        if ($validated['school_id'] != 0) {
            $studentsQuery->where('root_students.school_id', $validated['school_id']);
        }

        if ($validated['class_id'] != 0) {
            $studentsQuery->where('class_id', $validated['class_id']);
        }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $total = $studentsQuery->get()->count();

        $students = $studentsQuery->offset($start)->limit($length)->get();
        if ($validated['account_type'] == 0) {
            foreach ($students as $student) {
                $student->school_name = '--';
                $student->class_code = '--';
                $student->district_name = '--';
            }
        }

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $students
        ]);
    }

    public function getConsulations($studentId)
    {
        $student = Student::leftJoin('root_students', 'root_students.id', '=', 'logins.objID')
            ->leftJoin('root_schools', 'root_schools.id', '=', 'logins.school')
            ->leftJoin('root_classes', 'root_classes.id', '=', 'logins.class')
            ->leftJoin('root_districts', 'root_districts.id', '=', 'root_schools.district')
            ->where('uID', $studentId)
            ->where('deletePending', '!=', 1)
            ->where('isTeacherAccount', 0)
            ->where('isTesterAccount', 0)
            ->select('logins.fullname as name', 'root_districts.name as district', 'root_schools.name as school', 'root_classes.class_code as class', 'logins.age as age', 'logins.phone as phone', 'logins.email as email')
            ->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $consultations = SaleMarketing::leftJoin('backend_users', 'sale_marketing.sale_id', '=', 'backend_users.id')
            ->leftJoin('sale_feedback', 'sale_marketing.feedback', '=', 'sale_feedback.id')
            ->where('student_id', $studentId)
            ->whereNull('sale_marketing.deleted_at')
            ->select('sale_marketing.created_at', 'sale_marketing.id as id', 'sale_marketing.sale_id as sale_id', 'backend_users.name as sale_name', 'sale_feedback.description as feedback', 'note')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'student' => [
                'name' => $student->name,
                'district' => $student->district,
                'school' => $student->school,
                'class' => $student->class,
                'age' => $student->age,
                'phone' => $student->phone,
                'email' => $student->email,
            ],
            'data' => $consultations,
        ]);
    }

    public function saveConsultations(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer|exists:logins,uID',  // Kiểm tra sự tồn tại của học sinh trong bảng logins
            'feedback_id' => 'required|integer|exists:sale_feedback,id',  // Kiểm tra sự tồn tại của phản hồi trong bảng sale_feedback
            'note' => 'nullable|string',  // Ghi chú có thể rỗng
        ]);

        // Lưu thông tin tư vấn mới vào bảng sale_marketing
        $consultation = new SaleMarketing();
        $consultation->student_id = $request->student_id;
        $consultation->feedback = $request->feedback_id;
        $consultation->note = $request->note;
        $consultation->created_at = now();
        $consultation->sale_id = Auth::user()->id;
        $consultation->created_by = Auth::user()->id;
        $consultation->save();

        // Trả về phản hồi thành công
        return response()->json(['message' => 'Lưu thành công.'], 200);
    }

    public function updateConsultations(Request $request, $id)
    {
        $currentUserId = Auth::user()->id;
        // Xác thực dữ liệu từ client
        $request->validate([
            'feedback_id' => 'required|integer|exists:sale_feedback,id', // Kiểm tra phản hồi tồn tại
            'note' => 'nullable|string|max:255', // Note có thể rỗng
        ]);

        // Tìm bản ghi consultation
        $consultation = SaleMarketing::find($id);

        // Nếu không tìm thấy, trả lỗi 404
        if (!$consultation) {
            return response()->json(['message' => 'Không tìm thấy!'], 404);
        }

        if ($consultation->sale_id != $currentUserId) {
            return response()->json(['message' => 'Không có quyền chỉnh sửa!'], 403);
        }

        // Cập nhật dữ liệu
        $consultation->feedback = $request->feedback_id;
        $consultation->note = $request->note;
        $consultation->updated_at = now();
        $consultation->updated_by = $currentUserId;
        $consultation->save();

        // Trả về phản hồi JSON sau khi cập nhật
        return response()->json([
            'message' => 'Cập nhật thành công!',
            'data' => $consultation,
        ], 200);
    }

    public function deleteConsultations(Request $request, $id)
    {
        $currentUserId = Auth::user()->id;
        // Tìm bản ghi consultation
        $consultation = SaleMarketing::find($id);

        // Nếu không tìm thấy, trả lỗi 404
        if (!$consultation) {
            return response()->json(['message' => 'Không tìm thấy!'], 404);
        }

        if ($consultation->sale_id != $currentUserId) {
            return response()->json(['message' => 'Không có quyền xóa!'], 403);
        }

        // Cập nhật dữ liệu
        $consultation->deleted_at = now();
        $consultation->deleted_by = $currentUserId;
        $consultation->save();

        // Trả về phản hồi JSON sau khi cập nhật
        return response()->json([
            'message' => 'Xóa thành công!',
            'data' => $consultation,
        ], 200);
    }
    public function statistical()
    {
        return view("admin.marketing.statistical");
    }

    public function statisticalAPI()
    {
        $hotlinePhone = '1800599989';

        $queryStudents = Student::
            where('deletePending', '!=', 1)
            ->where('isTeacherAccount', 0)
            ->where('isTesterAccount', 0)
            ->where('logins.phone', '!=', '')
            ->where('logins.phone', '!=', $hotlinePhone)->get();

        $totalGrantedStudents = $queryStudents->where('isGrantedAccount', 1)->count();
        $totalNotGrantedStudents = $queryStudents->where('isGrantedAccount', 0)->count();

        $querySaleMarketings = SaleMarketing::get();
        $totalCalls = $querySaleMarketings->count();

        $answeredCalls = $querySaleMarketings->where('feedback', '!=', 1)->count();
        $unansweredCalls = $querySaleMarketings->where('feedback', 1)->count();

        $queryStudentCalls = SaleMarketing::leftJoin('logins', 'logins.uID', '=', 'sale_marketing.student_id')->distinct('sale_marketing.student_id');
        ;
        $grantedStudentCalls = $queryStudentCalls->where('isGrantedAccount', 1)->count();
        $notGrantedStudentCalls = $queryStudentCalls->where('isGrantedAccount', 0)->count();

        $data = [
            [
                'text' => 'Tài khoản cấp đã có dữ liệu liên hệ',
                'value' => $totalGrantedStudents
            ],
            [
                'text' => 'Tài khoản ngoài đã có dữ liệu liên hệ',
                'value' => $totalNotGrantedStudents
            ],
            [
                'text' => 'Số cuộc gọi đã thực hiện',
                'value' => $totalCalls
            ],
            [
                'text' => 'Số lượng tài khoản cấp đã gọi',
                'value' => $grantedStudentCalls
            ],
            [
                'text' => 'Số lượng tài khoản ngoài đã gọi',
                'value' => $notGrantedStudentCalls
            ],
            [
                'text' => 'Số lượng cuộc gọi được phản hồi',
                'value' => $answeredCalls
            ],
            [
                'text' => 'Số lượng cuộc gọi không được phản hồi',
                'value' => $unansweredCalls
            ]
        ];

        // Trả về dữ liệu dưới dạng JSON
        return response()->json($data);
    }

    public function getFeedbackData()
    {
        // Lấy tất cả các feedback có con
        $feedbacksWithChildren = SaleFeedback::where('parent_key', '!=', 0)->pluck('parent_key')->unique();

        $answeredCalls = SaleMarketing::where('feedback', '!=', 1)->count();

        // Lấy dữ liệu từ bảng sale_feedback, kết hợp với bảng sale_marketing
        $feedbacks = DB::table('sale_feedback')
            ->leftJoin('sale_marketing', 'sale_feedback.id', '=', 'sale_marketing.feedback')
            ->select('sale_feedback.description', 'sale_feedback.id', DB::raw('COUNT(sale_marketing.id) as count'))
            ->where('sale_feedback.id', '!=', 1)
            ->whereNull('sale_marketing.deleted_at')
            ->whereNotIn('sale_feedback.id', $feedbacksWithChildren) // Loại bỏ feedback có con
            ->groupBy('sale_feedback.id', 'sale_feedback.description')
            ->get();

        // Thêm tỷ lệ phần trăm
        $feedbacks = $feedbacks->map(function ($feedback) use ($answeredCalls) {
            $feedback->percentage = $answeredCalls ? round(($feedback->count / $answeredCalls) * 100, 2) : 0;
            return $feedback;
        });

        return response()->json($feedbacks);
    }


}
