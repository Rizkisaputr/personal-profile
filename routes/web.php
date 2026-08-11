<?php

use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\EducationController;
use App\Http\Controllers\Admin\ExperienceController;
use App\Http\Controllers\Admin\FinanceAccountController;
use App\Http\Controllers\Admin\FinanceBillController;
use App\Http\Controllers\Admin\FinanceCategoryController;
use App\Http\Controllers\Admin\FinanceDebtController;
use App\Http\Controllers\Admin\FinanceOverviewController;
use App\Http\Controllers\Admin\FinanceSavingsController;
use App\Http\Controllers\Admin\FinanceTransactionController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TechnologyController;
use App\Http\Controllers\Admin\TrainingController;
use App\Http\Controllers\Admin\WebsiteSettingController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\ProjectController as SiteProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/projects', [SiteProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project:slug}', [SiteProjectController::class, 'show'])->name('projects.show');

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::post('language', LanguageController::class)->name('admin.language.update');
    Route::get('/', DashboardController::class)->name('admin.index');
    Route::get('profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::match(['put', 'patch'], 'profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
    Route::resource('skills', SkillController::class)->except('show')->names('admin.skills');
    Route::resource('experiences', ExperienceController::class)->except('show')->names('admin.experiences');
    Route::resource('educations', EducationController::class)->except('show')->names('admin.educations');
    Route::resource('trainings', TrainingController::class)->except('show')->names('admin.trainings');
    Route::delete('projects/{project}/images/{projectImage}', [ProjectController::class, 'destroyImage'])->name('admin.projects.images.destroy');
    Route::resource('projects', ProjectController::class)->except('show')->names('admin.projects');
    Route::resource('technologies', TechnologyController::class)->except('show')->names('admin.technologies');
    Route::resource('certificates', CertificateController::class)->except('show')->names('admin.certificates');
    Route::resource('social-links', SocialLinkController::class)->except('show')->names('admin.social-links');
    Route::get('finance', FinanceOverviewController::class)->name('admin.finance.overview');
    Route::get('finance/accounts/{account}/adjust', [FinanceAccountController::class, 'adjust'])->name('admin.finance.accounts.adjust');
    Route::post('finance/accounts/{account}/adjust', [FinanceAccountController::class, 'storeAdjustment'])->name('admin.finance.accounts.adjust.store');
    Route::resource('finance/accounts', FinanceAccountController::class)->except('show')->names('admin.finance.accounts');
    Route::resource('finance/categories', FinanceCategoryController::class)->except('show')->names('admin.finance.categories');
    Route::resource('finance/transactions', FinanceTransactionController::class)->except('show')->names('admin.finance.transactions');
    Route::post('finance/debts/{debt}/schedules', [FinanceDebtController::class, 'schedule'])->name('admin.finance.debts.schedules.store');
    Route::post('finance/debts/{debt}/payments', [FinanceDebtController::class, 'pay'])->name('admin.finance.debts.pay');
    Route::resource('finance/debts', FinanceDebtController::class)->names('admin.finance.debts');
    Route::post('finance/savings/{saving}/targets', [FinanceSavingsController::class, 'target'])->name('admin.finance.savings.targets.store');
    Route::post('finance/savings/{saving}/entries', [FinanceSavingsController::class, 'entry'])->name('admin.finance.savings.entries.store');
    Route::resource('finance/savings', FinanceSavingsController::class)->names('admin.finance.savings');
    Route::post('finance/bills/{bill}/payments', [FinanceBillController::class, 'pay'])->name('admin.finance.bills.pay');
    Route::resource('finance/bills', FinanceBillController::class)->names('admin.finance.bills');
    Route::get('documents/{document}/view', [DocumentController::class, 'view'])->name('admin.documents.view');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('admin.documents.download');
    Route::resource('documents', DocumentController::class)->except('show')->names('admin.documents');
    Route::post('subscriptions/{subscription}/payments', [SubscriptionController::class, 'pay'])->name('admin.subscriptions.pay');
    Route::resource('subscriptions', SubscriptionController::class)->names('admin.subscriptions');
    Route::resource('assets', AssetController::class)->except('show')->names('admin.assets');
    Route::get('settings', [WebsiteSettingController::class, 'edit'])->name('admin.settings.edit');
    Route::match(['put', 'patch'], 'settings', [WebsiteSettingController::class, 'update'])->name('admin.settings.update');
});

Route::redirect('/dashboard', '/admin')
    ->middleware('auth')
    ->name('dashboard');

require __DIR__.'/settings.php';
