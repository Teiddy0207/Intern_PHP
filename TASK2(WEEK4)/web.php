<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Admin\ExportExcelController;
use App\Http\Controllers\ContestController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UploadImagesController;

// Route::post('/admin/api/sync-data', 'Admin\SyncDataController@syncData');

Route::get('/', 'Admin\LoginController@index')->name('home');
Route::get('unauthenticated', function () {
    return view('pages.unauthenticated');
})->name('unauthenticated');
Route::get('admin/login', 'Admin\LoginController@index')->name('admin.login.index1');
Route::post('admin/login', 'Admin\LoginController@login')->name('admin.login');
Route::get('admin/logout', 'Admin\LoginController@logout')->name('admin.logout');
Route::get('upload-multiple-image-preview', [UploadImagesController::class, 'index']);

Route::post('upload-multiple-image-preview', [UploadImagesController::class, 'store']);

// Route::get('/quiz-intro', 'Admin\QuizContestController@intro')->name('quizIntro');
Route::get('/library-stat-api', 'Admin\LibraryController@statApi')->name('admin.library.statApi');

Route::middleware('auth.customapi2')->group(function () {
    Route::get('/quiz-list', [ContestController::class, 'listcontest'])->name('contest.list');
    Route::get('/quiz-intro', [ContestController::class, 'index'])->name('contest.countdown');
    Route::get('/quiz-intro-mock', [ContestController::class, 'index'])->name('contest.countdownMock');
    Route::get('/lessonPlan-api/{numWeek}', 'Admin\LessonPlanController@getDataByWeek')->name('lessonPlan.getDataByWeek');
    Route::post('/lessonPlan-usage-time', 'Admin\LessonPlanController@storeUsageTimes')->name('lessonPlan.storeUsageTime');
    Route::get('/lessonPlan-akari-api/{numWeek}', 'AdminAkari\LessonPlanController@getDataByWeek')->name('lessonPlan.akari.getDataByWeek');
    Route::post('/lessonPlan-akari-usage-time', 'AdminAkari\LessonPlanController@storeUsageTimes')->name('lessonPlan.akari.storeUsageTime');
    Route::get('/admin/lessonPlanViewOld', 'Admin\LessonPlanController@view')->name('admin.lessonPlan.viewOld');
    Route::get('/admin/lessonPlanView', 'Admin\LessonPlanController@view2')->name('admin.lessonPlan.view');
    Route::get('/adminAkari/lessonPlanViewOld', 'AdminAkari\LessonPlanController@view')->name('adminAkari.lessonPlan.viewOld');
    Route::get('/adminAkari/lessonPlanView', 'AdminAkari\LessonPlanController@view2')->name('adminAkari.lessonPlan.view');
});


Route::get('/ranking', [ContestController::class, 'ranking'])->name('contest.ranking');
Route::get('/phase', [ContestController::class, 'result'])->name('contest.phase');
Route::domain(env('DOMAIN_CONTEST'))->group(function () {
    Route::get('/xem-bang-xep-hang-sieu-nhi-tieng-anh', [ContestController::class, 'ranking'])->name('contest.ranking2');
    Route::get('/danh-sach-bang-xep-hang-sieu-nhi-tieng-anh', [ContestController::class, 'result'])->name('contest.phase2');
});

Route::group(
    ['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth', 'namespace' => 'Admin'],
    function () {
        Route::get('/', 'LoginController@index')->name('login.index');
        Route::get('/dashboard', 'DashboardController@index')->name('dashboard.index');
        Route::get('/dashboard2', 'DashboardController@empty')->name('dashboard.empty');
        Route::get('/realtime', 'DashboardController@realtime')->name('dashboard.realtime');

        Route::resource('/account', 'AccountController')->middleware('can:managerAccount');
        Route::get('/account/changePassword/{id}', 'AccountController@changePass')->name('account.changePass')->middleware('can:managerAccount');
        Route::post('/account/changePassword/{id}', 'AccountController@postChangePass')->name('account.postChangePass')->middleware('can:managerAccount');

        Route::resource('/role', 'RoleController')->middleware('can:managerRole');

        Route::resource('/batch', 'BatchController')->middleware('can:managerBatch');
        Route::get('/search', 'SearchController@index')->name('search.index')->middleware('can:managerKey');
        Route::post('/key/export', 'SearchController@export')->name('key.export')->middleware('can:managerKey');
        Route::post('/key/recall', 'SearchController@recall')->name('key.recall')->middleware('can:managerKey');

        Route::resource('/product', 'ProductController')->middleware('can:managerProduct');
        Route::resource('/agentproduct', 'AgentProductController')->middleware('can:agentProduct');

        Route::resource('/student', 'StudentController')->middleware('can:managerStudent');
        Route::get('/student-homework', 'StudentController@homework')->name('student.homework')->middleware('can:managerStudent');
        Route::get('/student-api-homework', 'StudentController@homeworkApi')->name('student.homeworkApi')->middleware('can:managerStudent');
        Route::get('/student_all', 'StudentController@studentAll')->name('student.student_all')->middleware('can:managerStudent');
        Route::get('/student_odd', 'StudentController@student_odd')->name('student.student_odd')->middleware('can:managerStudentOdd');
        Route::get('/student_import', 'StudentController@import')->name('student.import')->middleware('can:managerStudent');
        Route::post('/student_import', 'StudentController@doImport')->name('student.doImport')->middleware('can:managerStudent');
        Route::post('/permission_upd', 'StudentController@updatePermission')->name('student.permission_upd');
        Route::get('/student_register', 'StudentController@getStudentRegister')->name('student.register');
        Route::get('/api/districts', 'StudentController@getDistricts');
        Route::get('/api/schools', 'StudentController@getSchools');
        Route::get('/api/classes', 'StudentController@getClasses');
        Route::get('/api/students', 'StudentController@getStudents');

        Route::get('/student/api/key-batches', 'StudentController@getKeyBatches');
        Route::get('/student/api/keys', 'StudentController@getKeys')->middleware('can:activeKey');
        Route::put('/student/api/activate', 'StudentController@activateKey')->middleware('can:activeKey');
        Route::put('/student/api/resetpw', 'StudentController@resetPassword');
        Route::put('/student/api/hiddenaccount', 'StudentController@hiddenAccount');
        Route::post('/api/sync-data', 'SyncDataController@syncData')->middleware('can:syncData');
        Route::get('/api/quiz-ranking-class', 'QuizController@getClassesWithFullParticipation');
        //marketing 
        Route::get('/api/feedbacks', 'MarketingController@getFeedbacks')->middleware('can:saleMarketing');
        Route::get('/api/sale-employees', 'MarketingController@getSaleEmployees')->middleware('can:saleMarketing');
        Route::get('/marketing', 'MarketingController@index')->name('marketing.index')->middleware('can:saleMarketing');
        Route::get('/api/marketing', 'MarketingController@getData')->middleware('can:saleMarketing');
        Route::get('/api/consultations/{studentId}', 'MarketingController@getConsulations')->middleware('can:saleMarketing');
        Route::post('/api/consultations', 'MarketingController@saveConsultations')->middleware('can:saleMarketing');
        Route::put('/api/consultations/{id}', 'MarketingController@updateConsultations')->middleware('can:saleMarketing');
        Route::delete('/api/consultations/{id}', 'MarketingController@deleteConsultations')->middleware('can:saleMarketing');
        Route::get('/marketing/statistical', 'MarketingController@statistical')->name('marketing.statistical')->middleware('can:saleMarketing');
        Route::get('/api/marketing/statistical', 'MarketingController@statisticalAPI')->middleware('can:saleMarketing');
        Route::get('/api/marketing/feedback-data', 'MarketingController@getFeedbackData')->middleware('can:saleMarketing');
         
        //codeQuangAnh: đếm số lượng học sinh có thông tin liên lạc trong hộp trường và hộp lớp 
        Route::get(uri: '/api/getCountSchool', action: 'MarketingController@getCountSchool');
        Route::get(uri: '/api/getCountClass', action: 'MarketingController@getCountClass');
        //store feedback
        Route::post('/api/feedback/store',  'MarketingController@storeFeedback');



        Route::resource('/user', 'UsersController')->middleware('can:infoUser');
        Route::get('/user/changePassword/{id}', 'UsersController@changePass')->name('user.changePass')->middleware('can:infoUser');
        Route::post('/user/changePassword/{id}', 'UsersController@postChangePass')->name('user.postChangePass')->middleware('can:infoUser');

        Route::resource('/class', 'ClassController')->middleware('can:managerClass');
        Route::get('/class/activated/{id}', 'ClassController@activated')->name('class.activated')->middleware('can:managerClass');

        Route::get('/school/activation-statistics', 'SchoolController@getActivationStatistics')->name('school.activation_statistics')
            ->middleware('can:managerSchool');
        Route::resource('/school', 'SchoolController')->middleware('can:managerSchool');

        // Thống kê ảnh của trường và lớp
        Route::get('/stat/library', 'LibraryController@detailStat')->name('school.library_stat')->middleware('can:managerLibrary');
        // Thống kê quyền của người dùng
        Route::get('/stat/permission', 'StudentController@statPermission')->name('student.permission_stat')->middleware('can:managerAccount');

        Route::post('/select/province', 'SelectController@province')->name('select.province');
        Route::post('/select/district', 'SelectController@district')->name('select.district');
        Route::post('/select/ward', 'SelectController@ward')->name('select.ward');
        Route::post('/select/school', 'SelectController@school')->name('select.school');
        Route::post('/select/class', 'SelectController@class')->name('select.class');
        Route::post('/select/week', 'SelectController@week')->name('select.week');

        Route::get('/select/schools/contest/{contestId}', 'SelectController@getSchoolsByContestId')->name('select.schools_by_contestId');

        // Filter image in library
        Route::post('/select/provinceFilterLibrary', 'SelectController@provinceFilterLibrary')->name('select.provinceFilterLibrary');
        Route::post('/select/districtFilterLibrary', 'SelectController@districtFilterLibrary')->name('select.districtFilterLibrary');
        Route::post('/select/wardFilterLibrary', 'SelectController@wardFilterLibrary')->name('select.wardFilterLibrary');
        Route::post('/select/schoolInDistrict', 'SelectController@schoolInDistrict')->name('select.schoolInDistrict');
        Route::post('/select/schoolHaveImg', 'SelectController@schoolHaveImg')->name('select.schoolHaveImg');
        Route::post('/select/classHaveImg', 'SelectController@classHaveImg')->name('select.classHaveImg');

        Route::get('/role', 'RoleController@index')->name('role.index')->middleware('can:managerRole');

        Route::get('/activated', 'ActivatedController@index')->name('activated.index')->middleware('can:managerActive');

        // Export
        Route::get('/export/student/{search}/{chooseTime}/{province_id}/{district_id}/{ward_id}/{school_id}/{ks}', 'ExportExcelController@student')->name('export.student')->middleware('can:exportExcel');
        Route::get('/export/image-library', 'ExportExcelController@exportImageLibrary')->name('export.imageLibrary');
        Route::get('/export/image-library-all', 'ExportExcelController@exportImageLibraryAll')->name('export.imageLibraryAll');
        Route::get('/export/student-register', 'ExportExcelController@exportStudentRegister')->name('export.studentRegister');

        Route::resource('/image', 'ImageController')->middleware('can:managerImage');
        Route::resource('/library', 'LibraryController')->middleware('can:managerLibrary');
        Route::get('/library-stat', 'LibraryController@stat')->name('library.stat')->middleware('can:managerLibrary');
        Route::get('/library-api-check-upload', 'LibraryController@apiCheckUpload')->name('library.apiCheckUpload')->middleware('can:managerLibrary');


        Route::get('/library/image/all', 'LibraryController@allimage')->name('library.allimage')->middleware('can:managerLibrary');
        Route::get('/library/image/default', 'LibraryController@default')->name('library.default')->middleware('can:acceptImage');
        Route::get('/library/image/create_default', 'LibraryController@create_default')->name('library.create_default')->middleware('can:acceptImage');
        Route::post('/library/image/store_default', 'LibraryController@store_default')->name('library.store_default')->middleware('can:acceptImage');
        Route::resource('/acceptImage', 'LibraryAcceptController')->middleware('can:acceptImage');
        Route::post('/acceptImage/image/tick/', 'LibraryAcceptController@tick_image')->name('acceptImage.tick_image')->middleware('can:acceptImage');
        Route::resource('/calendar', 'CalendarController')->middleware('can:managerLibrary');
        Route::get('/calendar/creweek/{id}', 'CalendarController@creweek')->name('calendar.creweek')->middleware('can:managerLibrary');
        Route::post('/calendar/stoweek/{id}', 'CalendarController@stoweek')->name('calendar.stoweek')->middleware('can:managerLibrary');
        // Route::get('image-upload', 'Admin\ImageUploadController@imageUpload')->name('image.upload');
        // Route::post('image-upload', 'Admin\ImageUploadController@imageUploadPost')->name('image.upload.post');

        Route::post('/calendar/image/preview', 'CalendarController@save_image')->name('calendar.preview_image');
        Route::post('/calendar/image/upload', 'CalendarController@store_image')->name('calendar.upload_image');
        Route::post('/calendar/image/delete', 'CalendarController@delete_image')->name('calendar.delete_image');

        Route::resource('/quizQuestion', 'QuizQuestionController')->middleware('can:managerQuiz');
        Route::resource('/quizContest', 'QuizContestController')->middleware('can:managerQuiz');
        Route::get('/quiz-stat', 'QuizContestController@statistic')->name('quizContest.stat')->middleware('can:managerQuiz');

        Route::resource('/firebaseNoti', 'FirebaseNotiController')->middleware('can:managerNotification');
        Route::get('/firebaseSearch', 'FirebaseNotiController@search')->name('firebase.search')->middleware('can:managerNotification');
        Route::resource('/lessonPlan', 'LessonPlanController')->middleware('can:managerLessonPlan');
        Route::get('/lessonPlan-usageTime', 'LessonPlanController@usagetime')->name('lesson.usagetime')->middleware('can:managerLessonPlan');
        Route::get('/export/teacherlecture/{searchString}/{fromdate}/{todate}', 'ExportExcelController@teacherlecture')->name('export.teacherlecture');
        Route::get('/export/user1', 'ExportExcelController@exportUser1')->name('export.user1');
        Route::resource('/blackList', 'BlackListController')->middleware('can:managerBlackList');
    }
);
Route::group(
    ['prefix' => 'adminAkari', 'as' => 'adminAkari.', 'middleware' => 'auth', 'namespace' => 'AdminAkari'],
    function () {
        Route::resource('/lessonPlan', 'LessonPlanController')->middleware('can:managerLessonPlanAkari');
        Route::get('/lessonPlan-usageTime-akari', 'LessonPlanController@usagetime')->name('lesson.usagetime')->middleware('can:managerLessonPlanAkari');
    }
);
