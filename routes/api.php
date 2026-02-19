<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| API Routes - Training Management System
|--------------------------------------------------------------------------
| Base URL: /api/v1
| All routes (except auth) require Bearer token authentication
*/

// Authentication Routes (No auth required)
Route::prefix('auth')->group(function () {
    Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/pin-login', [App\Http\Controllers\Api\AuthController::class, 'pinLogin']);
    Route::post('/forgot-password', [App\Http\Controllers\Api\AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [App\Http\Controllers\Api\AuthController::class, 'resetPassword']);
});

// All authenticated routes
Route::middleware(['auth:sanctum', 'client.scope'])->group(function () {

    // Users & Roles
    Route::apiResource('users', App\Http\Controllers\Api\UserController::class);
    Route::put('/users/{user}/roles', [App\Http\Controllers\Api\UserController::class, 'updateRoles']);
    Route::apiResource('user-roles', App\Http\Controllers\Api\UserRoleController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('roles', App\Http\Controllers\Api\RoleController::class);

    // Training Events
    Route::apiResource('events', App\Http\Controllers\Api\EventController::class);
    Route::patch('/events/{event}/reschedule', [App\Http\Controllers\Api\EventController::class, 'reschedule']);
    Route::patch('/events/bulk-status', [App\Http\Controllers\Api\EventController::class, 'bulkStatus']);
    Route::delete('/events/bulk', [App\Http\Controllers\Api\EventController::class, 'bulkDelete']);

    // Event Sessions
    Route::get('/events/{event}/sessions', [App\Http\Controllers\Api\EventSessionController::class, 'index']);
    Route::post('/events/{event}/sessions', [App\Http\Controllers\Api\EventSessionController::class, 'store']);
    Route::get('/sessions/{session}', [App\Http\Controllers\Api\EventSessionController::class, 'show']);
    Route::put('/sessions/{session}', [App\Http\Controllers\Api\EventSessionController::class, 'update']);
    Route::patch('/sessions/{session}/approve', [App\Http\Controllers\Api\EventSessionController::class, 'approve']);
    Route::patch('/sessions/{session}/archive', [App\Http\Controllers\Api\EventSessionController::class, 'archive']);

    // Session Grading
    Route::get('/sessions/{session}/grades', [App\Http\Controllers\Api\SessionGradeController::class, 'index']);
    Route::post('/sessions/{session}/grades', [App\Http\Controllers\Api\SessionGradeController::class, 'store']);
    Route::get('/sessions/{session}/sectors', [App\Http\Controllers\Api\SessionSectorController::class, 'index']);
    Route::post('/sessions/{session}/sectors', [App\Http\Controllers\Api\SessionSectorController::class, 'store']);
    Route::get('/sessions/{session}/exposures', [App\Http\Controllers\Api\SessionExposureController::class, 'index']);
    Route::post('/sessions/{session}/exposures', [App\Http\Controllers\Api\SessionExposureController::class, 'store']);

    // Training Forms & Blocks
    Route::apiResource('forms', App\Http\Controllers\Api\FormController::class);
    Route::put('/forms/{form}/endorsements', [App\Http\Controllers\Api\FormController::class, 'setEndorsements']);
    Route::apiResource('blocks', App\Http\Controllers\Api\BlockController::class);
    Route::apiResource('block-elements', App\Http\Controllers\Api\BlockElementController::class);

    // Endorsements & Currencies
    Route::apiResource('endorsements', App\Http\Controllers\Api\EndorsementController::class);
    Route::apiResource('currencies', App\Http\Controllers\Api\CurrencyController::class);

    // Reference Data
    Route::apiResource('block-types', App\Http\Controllers\Api\BlockTypeController::class);
    Route::apiResource('gradings', App\Http\Controllers\Api\GradingController::class);
    Route::apiResource('endorsement-types', App\Http\Controllers\Api\EndorsementTypeController::class);
    Route::apiResource('endorsement-schedules', App\Http\Controllers\Api\EndorsementScheduleController::class);
    Route::apiResource('endorsement-forms', App\Http\Controllers\Api\EndorsementFormController::class);
    Route::apiResource('training-facilities', App\Http\Controllers\Api\TrainingFacilityController::class);
    Route::apiResource('facility-types', App\Http\Controllers\Api\FacilityTypeController::class);
    Route::apiResource('aircraft-types', App\Http\Controllers\Api\AircraftTypeController::class);
    Route::apiResource('exposure-types', App\Http\Controllers\Api\ExposureTypeController::class);

    // Teams & Groups
    Route::apiResource('teams', App\Http\Controllers\Api\TeamController::class);
    Route::get('/teams/{team}/users', [App\Http\Controllers\Api\TeamUserController::class, 'index']);
    Route::post('/teams/{team}/users', [App\Http\Controllers\Api\TeamUserController::class, 'store']);
    Route::delete('/teams/{team}/users/{user}', [App\Http\Controllers\Api\TeamUserController::class, 'destroy']);
    Route::apiResource('ems-teams', App\Http\Controllers\Api\EmsTeamController::class)->only(['index', 'update']);

    // Training Programs
    Route::apiResource('training', App\Http\Controllers\Api\TrainingController::class);
    Route::apiResource('training-enrollments', App\Http\Controllers\Api\TrainingEnrollmentController::class);
    Route::apiResource('training-sessions', App\Http\Controllers\Api\TrainingSessionController::class);
    Route::apiResource('trainee-profiles', App\Http\Controllers\Api\TraineeProfileController::class);

    // Notifications & Reports
    Route::apiResource('notifications', App\Http\Controllers\Api\NotificationController::class);
    Route::apiResource('notification-rules', App\Http\Controllers\Api\NotificationRuleController::class);
    Route::apiResource('scheduled-reports', App\Http\Controllers\Api\ScheduledReportController::class);
    Route::patch('/scheduled-reports/{report}/toggle', [App\Http\Controllers\Api\ScheduledReportController::class, 'toggle']);
    Route::apiResource('email-templates', App\Http\Controllers\Api\EmailTemplateController::class);

    // Administration
    Route::get('/config', [App\Http\Controllers\Api\ConfigController::class, 'index']);
    Route::put('/config', [App\Http\Controllers\Api\ConfigController::class, 'update']);
    Route::apiResource('audit-log', App\Http\Controllers\Api\AuditLogController::class)->only(['index', 'store']);
    
    // Master Admin Only
    Route::middleware('master.admin')->group(function () {
        Route::apiResource('clients', App\Http\Controllers\Api\ClientController::class);
    });

    // Document Management System (DMS)
    Route::apiResource('dms/documents', App\Http\Controllers\Api\DmsDocumentController::class);
    Route::apiResource('dms/categories', App\Http\Controllers\Api\DmsCategoryController::class);
    Route::get('/dms/documents/{document}/files', [App\Http\Controllers\Api\DmsDocumentFileController::class, 'index']);
    Route::post('/dms/documents/{document}/files', [App\Http\Controllers\Api\DmsDocumentFileController::class, 'store']);
    Route::delete('/dms/files/{file}', [App\Http\Controllers\Api\DmsDocumentFileController::class, 'destroy']);
    Route::apiResource('dms/library-folders', App\Http\Controllers\Api\DmsLibraryFolderController::class);
    Route::get('/dms/library-folders/{folder}/files', [App\Http\Controllers\Api\DmsLibraryFileController::class, 'index']);
    Route::post('/dms/library-folders/{folder}/files', [App\Http\Controllers\Api\DmsLibraryFileController::class, 'store']);
    Route::delete('/dms/library-files/{file}', [App\Http\Controllers\Api\DmsLibraryFileController::class, 'destroy']);
    Route::post('/dms/documents/{document}/distribute', [App\Http\Controllers\Api\DmsDistributionController::class, 'store']);
    Route::post('/dms/documents/{document}/acknowledge', [App\Http\Controllers\Api\DmsAcknowledgementController::class, 'store']);

    // Features & RBAC
    Route::apiResource('features', App\Http\Controllers\Api\FeatureController::class);
    Route::apiResource('role-features', App\Http\Controllers\Api\RoleFeatureController::class);

    // Reports
    Route::get('/reports/compliance', [App\Http\Controllers\Api\ReportController::class, 'compliance']);
    Route::get('/reports/ltr-summary', [App\Http\Controllers\Api\ReportController::class, 'ltrSummary']);
    Route::get('/reports/training-analytics', [App\Http\Controllers\Api\ReportController::class, 'trainingAnalytics']);

    // Divisions
    Route::apiResource('divisions', App\Http\Controllers\Api\DivisionController::class);

    // Exercises & Questions
    Route::apiResource('exercises', App\Http\Controllers\Api\ExerciseController::class);
    Route::get('/exercises/{exercise}/questions', [App\Http\Controllers\Api\ExerciseQuestionController::class, 'index']);
    Route::post('/exercises/{exercise}/questions', [App\Http\Controllers\Api\ExerciseQuestionController::class, 'store']);
    Route::apiResource('exercise-questions', App\Http\Controllers\Api\ExerciseQuestionController::class)->only(['update', 'destroy']);
    Route::apiResource('exercise-sessions', App\Http\Controllers\Api\ExerciseSessionController::class);

    // Surveys & Questions
    Route::apiResource('surveys', App\Http\Controllers\Api\SurveyController::class);
    Route::get('/surveys/{survey}/questions', [App\Http\Controllers\Api\SurveyQuestionController::class, 'index']);
    Route::post('/surveys/{survey}/questions', [App\Http\Controllers\Api\SurveyQuestionController::class, 'store']);
    Route::put('/surveys/{survey}/questions/reorder', [App\Http\Controllers\Api\SurveyQuestionController::class, 'reorder']);
    Route::apiResource('survey-questions', App\Http\Controllers\Api\SurveyQuestionController::class)->only(['update', 'destroy']);
    Route::apiResource('survey-distributions', App\Http\Controllers\Api\SurveyDistributionController::class);
    Route::post('/survey-distributions/{distribution}/respond', [App\Http\Controllers\Api\SurveyResponseController::class, 'store']);

    // Certificate Templates & Assets
    Route::apiResource('certificate-templates', App\Http\Controllers\Api\CertificateController::class);
    Route::post('/certificate-templates/{certificateTemplate}/generate', [App\Http\Controllers\Api\CertificateController::class, 'generate']);
    Route::apiResource('certificate-assets', App\Http\Controllers\Api\CertificateAssetController::class);
});

/*
|--------------------------------------------------------------------------
| API Routes - Billing Module
|--------------------------------------------------------------------------
| Base URL: /api/v1/billing
| All routes require Bearer token authentication
*/

Route::prefix('billing')->middleware(['auth:sanctum'])->group(function () {

    // Clients
    Route::get('/clients', [App\Http\Controllers\Api\BillingClientController::class, 'index']);
    Route::get('/clients/{client}', [App\Http\Controllers\Api\BillingClientController::class, 'show']);
    Route::post('/clients', [App\Http\Controllers\Api\BillingClientController::class, 'store']);
    Route::put('/clients/{client}', [App\Http\Controllers\Api\BillingClientController::class, 'update']);
    Route::delete('/clients/{client}', [App\Http\Controllers\Api\BillingClientController::class, 'destroy']);
    Route::patch('/clients/{client}', [App\Http\Controllers\Api\BillingClientController::class, 'updateExclusion']);

    // Client Users
    Route::get('/client-users', [App\Http\Controllers\Api\ClientUserController::class, 'index']);
    Route::post('/client-users', [App\Http\Controllers\Api\ClientUserController::class, 'store']);
    Route::put('/client-users/{clientUser}', [App\Http\Controllers\Api\ClientUserController::class, 'update']);
    Route::delete('/client-users/{clientUser}', [App\Http\Controllers\Api\ClientUserController::class, 'destroy']);

    // Products
    Route::get('/products', [App\Http\Controllers\Api\ProductController::class, 'index']);
    Route::post('/products', [App\Http\Controllers\Api\ProductController::class, 'store']);
    Route::put('/products/{product}', [App\Http\Controllers\Api\ProductController::class, 'update']);
    Route::delete('/products/{product}', [App\Http\Controllers\Api\ProductController::class, 'destroy']);

    // Tiers
    Route::get('/tiers', [App\Http\Controllers\Api\TierController::class, 'index']);
    Route::post('/tiers', [App\Http\Controllers\Api\TierController::class, 'store']);
    Route::put('/tiers/{tier}', [App\Http\Controllers\Api\TierController::class, 'update']);
    Route::delete('/tiers/{tier}', [App\Http\Controllers\Api\TierController::class, 'destroy']);

    // Tier Brackets
    Route::get('/tier-brackets', [App\Http\Controllers\Api\TierBracketController::class, 'index']);
    Route::post('/tier-brackets', [App\Http\Controllers\Api\TierBracketController::class, 'store']);
    Route::put('/tier-brackets/{tierBracket}', [App\Http\Controllers\Api\TierBracketController::class, 'update']);
    Route::delete('/tier-brackets/{tierBracket}', [App\Http\Controllers\Api\TierBracketController::class, 'destroy']);

    // Client Products
    Route::get('/client-products', [App\Http\Controllers\Api\ClientProductController::class, 'index']);
    Route::post('/client-products', [App\Http\Controllers\Api\ClientProductController::class, 'store']);
    Route::put('/client-products/{clientProduct}', [App\Http\Controllers\Api\ClientProductController::class, 'update']);
    Route::delete('/client-products/{clientProduct}', [App\Http\Controllers\Api\ClientProductController::class, 'destroy']);

    // Courses
    Route::get('/courses', [App\Http\Controllers\Api\CourseController::class, 'index']);
    Route::post('/courses', [App\Http\Controllers\Api\CourseController::class, 'store']);
    Route::put('/courses/{course}', [App\Http\Controllers\Api\CourseController::class, 'update']);
    Route::delete('/courses/{course}', [App\Http\Controllers\Api\CourseController::class, 'destroy']);

    // Billing Records
    Route::get('/billing-records', [App\Http\Controllers\Api\BillingRecordController::class, 'index']);
    Route::post('/billing-records', [App\Http\Controllers\Api\BillingRecordController::class, 'store']);
    Route::patch('/billing-records/{billingRecord}', [App\Http\Controllers\Api\BillingRecordController::class, 'updatePrice']);
    Route::delete('/billing-records/{billingRecord}', [App\Http\Controllers\Api\BillingRecordController::class, 'destroy']);
    Route::patch('/billing-records/{billingRecord}/soft-delete', [App\Http\Controllers\Api\BillingRecordController::class, 'softDelete']);
    Route::post('/billing-records/recalculate', [App\Http\Controllers\Api\BillingRecordController::class, 'recalculate']);

    // Invoices
    Route::get('/invoices', [App\Http\Controllers\Api\InvoiceController::class, 'index']);
    Route::post('/invoices', [App\Http\Controllers\Api\InvoiceController::class, 'generate']);
    Route::post('/invoices/manual', [App\Http\Controllers\Api\InvoiceController::class, 'createManual']);
    Route::post('/invoices/send', [App\Http\Controllers\Api\InvoiceController::class, 'sendEmails']);
    Route::get('/invoices/{invoice}/line-items', [App\Http\Controllers\Api\InvoiceController::class, 'lineItems']);
    Route::patch('/invoices/{invoice}', [App\Http\Controllers\Api\InvoiceController::class, 'updateStatus']);
    Route::patch('/invoices/batch', [App\Http\Controllers\Api\InvoiceController::class, 'batchStatus']);
    Route::patch('/invoices/{invoice}/rounding', [App\Http\Controllers\Api\InvoiceController::class, 'updateRounding']);
    Route::delete('/invoices/{invoice}', [App\Http\Controllers\Api\InvoiceController::class, 'destroy']);
    Route::delete('/invoices/batch', [App\Http\Controllers\Api\InvoiceController::class, 'batchDelete']);
    Route::post('/invoices/{invoice}/pdf', [App\Http\Controllers\Api\InvoiceController::class, 'storePdf']);

    // Settings
    Route::get('/settings', [App\Http\Controllers\Api\SettingsController::class, 'getSettings']);
    Route::put('/settings', [App\Http\Controllers\Api\SettingsController::class, 'updateSettings']);

    // Email Templates
    Route::get('/settings/email-templates/{type}', [App\Http\Controllers\Api\SettingsController::class, 'getEmailTemplate']);
    Route::put('/settings/email-templates/{type}', [App\Http\Controllers\Api\SettingsController::class, 'updateEmailTemplate']);

    // Email Tracking
    Route::get('/settings/email-tracking', [App\Http\Controllers\Api\SettingsController::class, 'listTracking']);
    Route::get('/settings/email-tracking/stats', [App\Http\Controllers\Api\SettingsController::class, 'trackingStats']);

    // Cron Jobs
    Route::get('/settings/cron-jobs', [App\Http\Controllers\Api\SettingsController::class, 'listCronJobs']);
    Route::patch('/settings/cron-jobs/{name}', [App\Http\Controllers\Api\SettingsController::class, 'toggleCron']);
    Route::put('/settings/cron-jobs/{name}', [App\Http\Controllers\Api\SettingsController::class, 'updateCronSchedule']);
});
