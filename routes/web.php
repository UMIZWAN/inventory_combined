<?php

use App\Http\Controllers\Asset\AssetAccessLevelController;
use App\Http\Controllers\Asset\AssetController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Marketing\MarketingItemController;
use App\Http\Controllers\Marketing\MarketingAccessLevelController;
use App\Http\Controllers\Marketing\MarketingStaffController;
use App\Http\Controllers\Marketing\MarketingBranchController;
use App\Http\Controllers\Marketing\MarketingCategoryController;
use App\Http\Controllers\Marketing\BranchSelectorController;
use App\Http\Controllers\Marketing\MarketingTransactionPurposeController;
use App\Http\Controllers\Marketing\MarketingShippingOptionController;
use App\Http\Controllers\Marketing\MarketingTransactionController;
use App\Http\Controllers\Marketing\MarketingReportController;

/**
 *  AUTH CONTROLLER
 */
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::patch('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

/**
 *  STAFF CONTROLLER
 */
Route::get('/staff', [StaffController::class, 'showStaffList'])->name('staff');
Route::post('/staff/register', [StaffController::class, 'register'])->name('staff.register');
Route::post('/staff/register-no-login', [StaffController::class, 'registerNoLogin'])->name('staff.registerNoLogin');
Route::post('/staff/{id}/update-no-login', [StaffController::class, 'updateNoLogin'])->name('staff.updateNoLogin');
Route::post('/staff/{id}/update', [StaffController::class, 'update'])->name('staff.update');
Route::post('/staff/{id}/toggle-status', [StaffController::class, 'toggleStatus'])
    ->name('staff.toggleStatus');

/**
 *  BRANCH CONTROLLER
 */
Route::get('/branches', [BranchController::class, 'index']);
Route::post('/branches', [BranchController::class, 'addBranch']);
Route::put('/branches/{id}', [BranchController::class, 'updateBranch']);
Route::delete('/branches/{id}', [BranchController::class, 'deleteBranch']);

Route::get('/departments', [DepartmentController::class, 'index']);
Route::post('/departments', [DepartmentController::class, 'store']);
Route::put('/departments/{id}', [DepartmentController::class, 'update']);
Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);

use App\Http\Controllers\Asset\AssetGroupController;
use App\Http\Controllers\Asset\AssetTransferController;

Route::prefix('assetgroup')->group(function () {

    // List
    Route::get('/', [AssetGroupController::class, 'index'])
        ->name('assetgroup.list');

    // Create
    Route::post('/add', [AssetGroupController::class, 'addAssetGroup'])
        ->name('assetgroup.add');

    // Update
    Route::put('/update/{id}', [AssetGroupController::class, 'updateAssetGroup'])
        ->name('assetgroup.update');

    // Soft delete
    Route::delete('/delete/{id}', [AssetGroupController::class, 'deleteAssetGroup'])
        ->name('assetgroup.delete');
});

use App\Http\Controllers\SupplierController;

Route::prefix('supplier')->group(function () {

    // List
    Route::get('/', [SupplierController::class, 'index'])
        ->name('supplier.list');

    // Create
    Route::post('/add', [SupplierController::class, 'addSupplier'])
        ->name('supplier.add');

    // Update
    Route::put('/update/{id}', [SupplierController::class, 'updateSupplier'])
        ->name('supplier.update');

    // Soft delete
    Route::delete('/delete/{id}', [SupplierController::class, 'deleteSupplier'])
        ->name('supplier.delete');

    // CSV Import/Export
    Route::get('/export', [SupplierController::class, 'exportCSV'])
        ->name('supplier.export');
    Route::post('/import', [SupplierController::class, 'importCSV'])
        ->name('supplier.import');
    Route::get('/template', [SupplierController::class, 'downloadTemplate'])
        ->name('supplier.template');
});


Route::get('/', [HomeController::class, 'index'])->name('home');
// Marketing topbar branch switcher
Route::post('/marketing/set-branch', [BranchSelectorController::class, 'set'])->name('marketing.setBranch');

// Marketing Items — landing page for MIS
Route::get('/marketing', [MarketingItemController::class, 'index'])->name('marketing.items.index');
Route::post('/marketing/items', [MarketingItemController::class, 'store'])->name('marketing.items.store');
Route::put('/marketing/items/{id}', [MarketingItemController::class, 'update'])->name('marketing.items.update');
Route::delete('/marketing/items/{id}', [MarketingItemController::class, 'destroy'])->name('marketing.items.destroy');

Route::get('/asset', [AssetController::class, 'index'])->name('master.list');
Route::get('/asset/export-csv', [AssetController::class, 'exportCsv'])->name('asset.exportCsv');
Route::get('/asset/{id}/view', [AssetController::class, 'show'])->name('asset.view');
Route::post('/asset/store', [AssetController::class, 'store'])->name('asset.store');
Route::put('/asset/{id}', [AssetController::class, 'update'])->name('asset.update');
// Route::delete('/asset/delete/{id}', [AssetController::class, 'destroy'])->name('asset.destroy');
Route::put('/asset/{id}/disposal', [AssetController::class, 'disposalStatus'])
    ->name('asset.disposalStatus');

Route::delete('/asset/{id}', [AssetController::class, 'destroy'])
    ->name('asset.destroy');

// Asset search and details routes
Route::get('/asset/search', [AssetController::class, 'search'])->name('asset.search');
Route::get('/asset/{id}/details', [AssetController::class, 'getDetails'])->name('asset.details');
Route::delete('/asset/{id}/delete-image', [AssetController::class, 'deleteImage'])->name('asset.deleteImage');

// Disposal routes
Route::get('/disposal', [AssetController::class, 'disposalList'])->name('asset.disposalList');
Route::post('/asset/{id}/request-disposal', [AssetController::class, 'requestDisposal'])->name('asset.requestDisposal');
Route::post('/asset/{id}/approve-disposal', [AssetController::class, 'approveDisposal'])->name('asset.approveDisposal');
Route::post('/asset/{id}/reject-disposal', [AssetController::class, 'rejectDisposal'])->name('asset.rejectDisposal');
Route::post('/asset/{id}/recycle', [AssetController::class, 'recycleAsset'])->name('asset.recycle');
Route::post('/asset/bulk-change-color', [AssetController::class, 'bulkChangeColor'])->name('asset.bulkChangeColor');
Route::post('/asset/bulk-change-user-dept', [AssetController::class, 'bulkChangeUserDept'])->name('asset.bulkChangeUserDept');


// In routes/web.php
Route::get('/asset-transfer', [AssetTransferController::class, 'index'])->name('assetTransfer.index');
Route::post('/asset-transfer', [AssetTransferController::class, 'store'])->name('assetTransfer.store');
Route::get('/asset-transfer/{id}', [AssetTransferController::class, 'show'])->name('assetTransfer.show');
Route::put('/asset-transfer/{id}/status', [AssetTransferController::class, 'updateStatus'])->name('assetTransfer.updateStatus');
Route::get('/asset-transfer/{id}/pdf', [AssetTransferController::class, 'downloadPdf'])->name('assetTransfer.pdf');
Route::post('/asset-transfer/{id}/approve-items', [AssetTransferController::class, 'approveItems'])->name('assetTransfer.approveItems');
Route::post('/asset-transfer/{id}/receive-selected', [AssetTransferController::class, 'receiveSelected'])->name('assetTransfer.receiveSelected');
// // List transfers
// Route::get('/asset-transfer', [AssetTransferController::class, 'index'])->name('asset-transfer.index');

// // Store new transfer
// Route::post('/asset-transfer', [AssetTransferController::class, 'store'])->name('assetTransfer.store');

// // Update transfer status (approve / send / receive / return / reject)
// Route::post('/asset-transfer/{id}/update-status', [AssetTransferController::class, 'updateStatus'])
//     ->name('asset-transfer.updateStatus');


use App\Http\Controllers\Asset\AmsFormController;
use App\Http\Controllers\Asset\ReportController;

// Report routes
Route::get('/report', [ReportController::class, 'index'])->name('report.index');
Route::get('/report/export', [ReportController::class, 'export'])->name('report.export');

Route::resource('ams-forms', AmsFormController::class)
    ->names('amsForms')
    ->except(['create', 'edit', 'show']);

Route::get(
    'ams-forms/{amsForm}/download',
    [AmsFormController::class, 'download']
)->name('amsForms.download');

// Access Levels Routes — Asset
Route::get('/access-levels', [AssetAccessLevelController::class, 'index'])->name('accessLevels.index');
Route::post('/access-levels', [AssetAccessLevelController::class, 'store'])->name('accessLevels.store');
Route::put('/access-levels/{id}', [AssetAccessLevelController::class, 'update'])->name('accessLevels.update');
Route::delete('/access-levels/{id}', [AssetAccessLevelController::class, 'destroy'])->name('accessLevels.destroy');
Route::post('/access-levels/{id}/duplicate', [AssetAccessLevelController::class, 'duplicate'])->name('accessLevels.duplicate');
Route::post('/access-levels/reorder', [AssetAccessLevelController::class, 'reorder'])->name('accessLevels.reorder');

// Access Levels Routes — Marketing
Route::prefix('marketing/access-levels')->name('marketingAccessLevels.')->group(function () {
    Route::get('/', [MarketingAccessLevelController::class, 'index'])->name('index');
    Route::post('/', [MarketingAccessLevelController::class, 'store'])->name('store');
    Route::post('/reorder', [MarketingAccessLevelController::class, 'reorder'])->name('reorder');
    Route::put('/{id}', [MarketingAccessLevelController::class, 'update'])->name('update');
    Route::delete('/{id}', [MarketingAccessLevelController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/duplicate', [MarketingAccessLevelController::class, 'duplicate'])->name('duplicate');
});

// Category Routes — Marketing
Route::prefix('marketing/categories')->name('marketingCategories.')->group(function () {
    Route::get('/', [MarketingCategoryController::class, 'index'])->name('index');
    Route::post('/', [MarketingCategoryController::class, 'store'])->name('store');
    Route::put('/{id}', [MarketingCategoryController::class, 'update'])->name('update');
    Route::delete('/{id}', [MarketingCategoryController::class, 'destroy'])->name('destroy');
});

// Transaction Purpose Routes — Marketing
Route::prefix('marketing/transaction-purposes')->name('marketingTransactionPurposes.')->group(function () {
    Route::get('/', [MarketingTransactionPurposeController::class, 'index'])->name('index');
    Route::post('/', [MarketingTransactionPurposeController::class, 'store'])->name('store');
    Route::put('/{id}', [MarketingTransactionPurposeController::class, 'update'])->name('update');
    Route::delete('/{id}', [MarketingTransactionPurposeController::class, 'destroy'])->name('destroy');
});

// Shipping Option Routes — Marketing
Route::prefix('marketing/shipping-options')->name('marketingShippingOptions.')->group(function () {
    Route::get('/', [MarketingShippingOptionController::class, 'index'])->name('index');
    Route::post('/', [MarketingShippingOptionController::class, 'store'])->name('store');
    Route::put('/{id}', [MarketingShippingOptionController::class, 'update'])->name('update');
    Route::delete('/{id}', [MarketingShippingOptionController::class, 'destroy'])->name('destroy');
});

// Transactions — Marketing (tabbed page)
Route::post('/marketing/transactions/request', [MarketingTransactionController::class, 'storeRequest'])
    ->name('marketingTransactions.storeRequest');
Route::post('/marketing/transactions/transfer', [MarketingTransactionController::class, 'storeTransfer'])
    ->name('marketingTransactions.storeTransfer');
Route::post('/marketing/transactions/invoice', [MarketingTransactionController::class, 'storeInvoice'])
    ->name('marketingTransactions.storeInvoice');
Route::get('/marketing/transactions/{tab?}', [MarketingTransactionController::class, 'index'])
    ->name('marketingTransactions.index')
    ->where('tab', 'marketing-in|transfer-list|request|transfer|invoice');

// Reports — Marketing
Route::get('/marketing/reports/in-out-history', [MarketingReportController::class, 'inOutHistory'])
    ->name('marketingReports.inOutHistory');
Route::get('/marketing/reports/invoice', [MarketingReportController::class, 'invoice'])
    ->name('marketingReports.invoice');

// Branch Routes — Marketing
Route::prefix('marketing/branches')->name('marketingBranches.')->group(function () {
    Route::get('/', [MarketingBranchController::class, 'index'])->name('index');
    Route::post('/', [MarketingBranchController::class, 'addBranch'])->name('store');
    Route::put('/{id}', [MarketingBranchController::class, 'updateBranch'])->name('update');
    Route::delete('/{id}', [MarketingBranchController::class, 'deleteBranch'])->name('destroy');
});

// Staff Routes — Marketing
Route::prefix('marketing/staff')->name('marketingStaff.')->group(function () {
    Route::get('/', [MarketingStaffController::class, 'showStaffList'])->name('list');
    Route::post('/register', [MarketingStaffController::class, 'register'])->name('register');
    Route::post('/register-no-login', [MarketingStaffController::class, 'registerNoLogin'])->name('registerNoLogin');
    Route::post('/{id}/update', [MarketingStaffController::class, 'update'])->name('update');
    Route::post('/{id}/update-no-login', [MarketingStaffController::class, 'updateNoLogin'])->name('updateNoLogin');
    Route::post('/{id}/toggle-status', [MarketingStaffController::class, 'toggleStatus'])->name('toggleStatus');
});


// Import CSV Routes
use App\Http\Controllers\Asset\ImportCsvController;

Route::prefix('import')->group(function () {
    Route::get('/', [ImportCsvController::class, 'index'])->name('import.index');
    Route::get('/template', [ImportCsvController::class, 'downloadTemplate'])->name('import.template');
    Route::post('/preview', [ImportCsvController::class, 'preview'])->name('import.preview');
    Route::post('/process', [ImportCsvController::class, 'import'])->name('import.process');
});
