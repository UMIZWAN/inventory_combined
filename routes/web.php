<?php

use App\Http\Controllers\AssetAccessLevelController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DepartmentController;

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

use App\Http\Controllers\AssetGroupController;
use App\Http\Controllers\AssetTransferController;

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


Route::get('/', [AssetController::class, 'index']);
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


use App\Http\Controllers\AmsFormController;
use App\Http\Controllers\ReportController;

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

// Access Levels Routes
Route::get('/access-levels', [AssetAccessLevelController::class, 'index'])->name('accessLevels.index');
Route::post('/access-levels', [AssetAccessLevelController::class, 'store'])->name('accessLevels.store');
Route::put('/access-levels/{id}', [AssetAccessLevelController::class, 'update'])->name('accessLevels.update');
Route::delete('/access-levels/{id}', [AssetAccessLevelController::class, 'destroy'])->name('accessLevels.destroy');
Route::post('/access-levels/{id}/duplicate', [AssetAccessLevelController::class, 'duplicate'])->name('accessLevels.duplicate');
Route::post('/access-levels/reorder', [AssetAccessLevelController::class, 'reorder'])->name('accessLevels.reorder');


// Import CSV Routes
use App\Http\Controllers\ImportCsvController;

Route::prefix('import')->group(function () {
    Route::get('/', [ImportCsvController::class, 'index'])->name('import.index');
    Route::get('/template', [ImportCsvController::class, 'downloadTemplate'])->name('import.template');
    Route::post('/preview', [ImportCsvController::class, 'preview'])->name('import.preview');
    Route::post('/process', [ImportCsvController::class, 'import'])->name('import.process');
});
