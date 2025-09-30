<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\FeeStructureController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\DataTransferController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentInstallmentController;
use App\Http\Controllers\Admin\InstallmentFineController;
use App\Http\Controllers\Admin\PaymentDiscountController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentGuardianController;
use App\Http\Controllers\Admin\StudentDocumentController as AdminStudentDocumentController;
use App\Http\Controllers\Management\StaffController;
use App\Http\Controllers\Management\TeacherController as ManagementTeacherController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Parents\DashboardController as ParentDashboardController;
use App\Http\Controllers\Parents\FeeReceiptController as ParentFeeReceiptController;
use App\Http\Controllers\Parents\FeedbackController as ParentFeedbackController;
use App\Http\Controllers\Parents\StudentDocumentController as ParentStudentDocumentController;
use App\Http\Controllers\Reception\AttendanceController as ReceptionAttendanceController;
use App\Http\Controllers\Reception\DashboardController as ReceptionDashboardController;
use App\Http\Controllers\Reception\EnquiryController as ReceptionEnquiryController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\TestPerformanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::middleware(['auth','verified'])->group(function () {

    // Role-aware dashboard redirect (replace any existing /dashboard route with this)
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole('admin'))     return to_route('admin.dashboard');
        if ($user->hasRole('teacher'))   return to_route('teacher.dashboard');
        if ($user->hasRole('reception')) return to_route('reception.dashboard');
        if ($user->hasRole('manager'))   return to_route('manager.dashboard');
        if ($user->hasRole('parent'))    return to_route('parent.dashboard');
        if ($user->hasRole('student'))   return to_route('student.dashboard');

        // fallback if a user has no role yet
        return view('dashboard');
    })->name('dashboard');

    // role-guarded areas
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::resource('fee-structures', FeeStructureController::class)->except(['show']);
        Route::put('settings/tenant', [ProfileController::class, 'updateTenantSettings'])->name('settings.tenant.update');
        Route::get('data-transfer', [DataTransferController::class, 'index'])->name('data-transfer.index');
        Route::post('data-transfer/export', [DataTransferController::class, 'export'])->name('data-transfer.export');
        Route::post('data-transfer/import', [DataTransferController::class, 'import'])->name('data-transfer.import');
    });

    Route::middleware('role:admin|manager')->prefix('admin')->name('admin.')->group(function () {
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

        // START: Add these new routes for the admission wizard
            Route::get('students/{student}/add-guardian', [StudentController::class, 'addGuardianForm'])->name('students.add-guardian');
            Route::post('students/{student}/add-guardian', [StudentController::class, 'storeGuardian'])->name('students.store-guardian');

            Route::get('students/{student}/add-fee-plan', [StudentController::class, 'addFeePlanForm'])->name('students.add-fee-plan');
            Route::post('students/{student}/add-fee-plan', [StudentController::class, 'storeFeePlan'])->name('students.store-fee-plan');
        // END: Add new routes


        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
            Route::get('/fees', [ReportController::class, 'fees'])->name('fees');
            Route::get('/finance', [ReportController::class, 'finance'])->name('finance');
            Route::get('/enquiries', [ReportController::class, 'enquiries'])->name('enquiries');
        });

        Route::get('attendances/entry', [AttendanceController::class, 'entry'])->name('attendances.entry');
        Route::post('attendances/entry', [AttendanceController::class, 'entryStore'])->name('attendances.entry.store');
        Route::resource('attendances', AttendanceController::class)->except(['show']);
        
        

        Route::resource('students', StudentController::class);
        Route::get('students/{student}/documents', [AdminStudentDocumentController::class, 'create'])->name('students.documents');
        Route::post('students/{student}/documents', [AdminStudentDocumentController::class, 'store'])->name('students.documents.store');
        Route::delete('students/{student}/documents/{media}', [AdminStudentDocumentController::class, 'destroy'])->name('students.documents.destroy');
        Route::get('students/{student}/documents/{media}/download', [AdminStudentDocumentController::class, 'download'])->name('students.documents.download');
        Route::get('students/{student}/payments', [StudentController::class, 'payments'])->name('students.payments');
        Route::prefix('students/{student}')->name('students.')->group(function () {
            Route::get('guardians/{guardian}/edit', [StudentGuardianController::class, 'edit'])->name('guardians.edit');
            Route::put('guardians/{guardian}', [StudentGuardianController::class, 'update'])->name('guardians.update');
        });
        
        Route::resource('expenses', ExpenseController::class)->except(['show']);
        Route::get('expenses/{expense}/attachments/{media}', [ExpenseController::class, 'download'])->name('expenses.attachments.download');
        Route::resource('payrolls', PayrollController::class)->except(['show']);
        Route::resource('announcements', AnnouncementController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::resource('feedback', AdminFeedbackController::class)->only(['index', 'update', 'destroy']);
        Route::resource('enquiries', EnquiryController::class)->except(['show']);
        Route::resource('payments', PaymentController::class);

        Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::get('payments/{payment}/installments/{installment}/receipt', [PaymentController::class, 'installmentReceipt'])->name('payments.installments.receipt');

        Route::post('payments/{payment}/installments', [PaymentInstallmentController::class, 'store'])->name('payments.installments.store');
        Route::put('payments/{payment}/installments/{installment}', [PaymentInstallmentController::class, 'update'])->name('payments.installments.update');
        Route::delete('payments/{payment}/installments/{installment}', [PaymentInstallmentController::class, 'destroy'])->name('payments.installments.destroy');
        Route::post('payments/{payment}/installments/{installment}/mark-paid', [PaymentInstallmentController::class, 'markPaid'])->name('payments.installments.mark-paid');
        Route::post('payments/{payment}/installments/{installment}/fines', [InstallmentFineController::class, 'store'])->name('payments.installments.fines.store');
        Route::put('payments/{payment}/installments/{installment}/fines/{fine}', [InstallmentFineController::class, 'update'])->name('payments.installments.fines.update');
        Route::delete('payments/{payment}/installments/{installment}/fines/{fine}', [InstallmentFineController::class, 'destroy'])->name('payments.installments.fines.destroy');
        Route::post('payments/{payment}/installments/{installment}/fines/{fine}/mark-paid', [InstallmentFineController::class, 'markPaid'])->name('payments.installments.fines.mark-paid');
        Route::post('payments/{payment}/discounts', [PaymentDiscountController::class, 'store'])->name('payments.discounts.store');
        Route::delete('payments/{payment}/discounts/{discount}', [PaymentDiscountController::class, 'destroy'])->name('payments.discounts.destroy');
    });

    Route::middleware('role:admin|manager|reception')->prefix('admin/academics')->name('academics.')->group(function () {
        Route::resource('test-performances', TestPerformanceController::class)->except(['show']);
        Route::get('test-performances/{test_performance}/attachments/{media}', [TestPerformanceController::class, 'download'])->name('test-performances.attachments.download');
    });
    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', TeacherDashboardController::class)->name('dashboard');
        Route::post('/attendance', [TeacherAttendanceController::class, 'store'])->name('attendance.store');

        Route::resource('test-performances', TestPerformanceController::class)->except(['show']);
        Route::get('test-performances/{test_performance}/attachments/{media}', [TestPerformanceController::class, 'download'])->name('test-performances.attachments.download');
    });

    Route::middleware('role:reception')->prefix('reception')->name('reception.')->group(function () {
        Route::get('/dashboard', ReceptionDashboardController::class)->name('dashboard');
        Route::post('/attendance', [ReceptionAttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/enquiries', [ReceptionEnquiryController::class, 'store'])->name('enquiries.store');
    });

    Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', ManagerDashboardController::class)->name('dashboard');
    });

    Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', ParentDashboardController::class)->name('dashboard');
        Route::get('/installments/{installment}/receipt', [ParentFeeReceiptController::class, 'show'])->name('installments.receipt');
        Route::post('/feedback', [ParentFeedbackController::class, 'store'])->name('feedback.store');
        Route::post('/students/{student}/documents', [ParentStudentDocumentController::class, 'store'])->name('students.documents.store');
        Route::get('/students/{student}/documents/{media}/download', [ParentStudentDocumentController::class, 'download'])->name('students.documents.download');

        Route::resource('test-performances', TestPerformanceController::class)->only(['index']);
        Route::get('test-performances/{test_performance}/attachments/{media}', [TestPerformanceController::class, 'download'])->name('test-performances.attachments.download');
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');

        Route::resource('test-performances', TestPerformanceController::class)->only(['index']);
        Route::get('test-performances/{test_performance}/attachments/{media}', [TestPerformanceController::class, 'download'])->name('test-performances.attachments.download');
    });

    Route::middleware('role:admin|manager')->prefix('management')->name('management.')->group(function () {
        Route::resource('teachers', ManagementTeacherController::class)->except(['show']);
        Route::resource('staff', StaffController::class)->except(['show']);
    });

    // Breeze nav often links to 'profile'
    Route::get('/profile', fn () => view('profile'))->name('profile');
});

require __DIR__.'/auth.php';











