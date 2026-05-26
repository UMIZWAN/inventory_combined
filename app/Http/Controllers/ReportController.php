<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetTransfer;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $branchId = $request->input('branch_id');

        // Default to current month if no date range provided
        if (!$fromDate && !$toDate) {
            $fromDate = Carbon::now()->startOfMonth()->toDateString();
            $toDate = Carbon::now()->endOfMonth()->toDateString();
        }

        // Assets added (created_at within date range)
        $assetsAddedQuery = Asset::where('is_deleted', false);
        if ($fromDate) {
            $assetsAddedQuery->where('created_at', '>=', $fromDate . ' 00:00:00');
        }
        if ($toDate) {
            $assetsAddedQuery->where('created_at', '<=', $toDate . ' 23:59:59');
        }
        if ($branchId) {
            $assetsAddedQuery->where('branch_id', $branchId);
        }
        $assetsAdded = $assetsAddedQuery->count();
        $assetsAddedList = $assetsAddedQuery->with(['branch', 'group'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Total asset value added
        $totalAssetValue = $assetsAddedList->sum('asset_cost');

        // Transfers created within date range
        $transfersQuery = AssetTransfer::query();
        if ($fromDate) {
            $transfersQuery->where('created_at', '>=', $fromDate . ' 00:00:00');
        }
        if ($toDate) {
            $transfersQuery->where('created_at', '<=', $toDate . ' 23:59:59');
        }
        if ($branchId) {
            $transfersQuery->where(function ($q) use ($branchId) {
                $q->where('transfer_from', $branchId)
                  ->orWhere('transfer_to', $branchId);
            });
        }
        $transfersCount = $transfersQuery->count();
        $transfersList = $transfersQuery->with(['fromBranch', 'toBranch', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Disposed assets within date range
        $disposedQuery = Asset::where('is_disposed', true)->where('is_deleted', false);
        if ($fromDate) {
            $disposedQuery->where('updated_at', '>=', $fromDate . ' 00:00:00');
        }
        if ($toDate) {
            $disposedQuery->where('updated_at', '<=', $toDate . ' 23:59:59');
        }
        if ($branchId) {
            $disposedQuery->where('branch_id', $branchId);
        }
        $disposedCount = $disposedQuery->count();
        $disposedList = $disposedQuery->with(['branch', 'group'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $branches = Branch::where('is_active', true)->get();

        return view('report.index', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'branchId' => $branchId,
            'branches' => $branches,
            'assetsAdded' => $assetsAdded,
            'assetsAddedList' => $assetsAddedList,
            'totalAssetValue' => $totalAssetValue,
            'transfersCount' => $transfersCount,
            'transfersList' => $transfersList,
            'disposedCount' => $disposedCount,
            'disposedList' => $disposedList,
        ]);
    }

    public function export(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $branchId = $request->input('branch_id');

        if (!$fromDate && !$toDate) {
            $fromDate = Carbon::now()->startOfMonth()->toDateString();
            $toDate = Carbon::now()->endOfMonth()->toDateString();
        }

        $branchName = 'All Branches';
        if ($branchId) {
            $branch = Branch::find($branchId);
            $branchName = $branch->branch_name ?? 'Unknown';
        }

        // Assets added
        $assetsAddedQuery = Asset::where('is_deleted', false);
        if ($fromDate) {
            $assetsAddedQuery->where('created_at', '>=', $fromDate . ' 00:00:00');
        }
        if ($toDate) {
            $assetsAddedQuery->where('created_at', '<=', $toDate . ' 23:59:59');
        }
        if ($branchId) {
            $assetsAddedQuery->where('branch_id', $branchId);
        }
        $assetsAddedList = $assetsAddedQuery->with(['branch', 'group'])->orderBy('created_at', 'desc')->get();

        // Transfers
        $transfersQuery = AssetTransfer::query();
        if ($fromDate) {
            $transfersQuery->where('created_at', '>=', $fromDate . ' 00:00:00');
        }
        if ($toDate) {
            $transfersQuery->where('created_at', '<=', $toDate . ' 23:59:59');
        }
        if ($branchId) {
            $transfersQuery->where(function ($q) use ($branchId) {
                $q->where('transfer_from', $branchId)
                  ->orWhere('transfer_to', $branchId);
            });
        }
        $transfersList = $transfersQuery->with(['fromBranch', 'toBranch', 'creator'])->orderBy('created_at', 'desc')->get();

        // Disposed
        $disposedQuery = Asset::where('is_disposed', true)->where('is_deleted', false);
        if ($fromDate) {
            $disposedQuery->where('updated_at', '>=', $fromDate . ' 00:00:00');
        }
        if ($toDate) {
            $disposedQuery->where('updated_at', '<=', $toDate . ' 23:59:59');
        }
        if ($branchId) {
            $disposedQuery->where('branch_id', $branchId);
        }
        $disposedList = $disposedQuery->with(['branch', 'group'])->orderBy('updated_at', 'desc')->get();

        $filename = 'report_' . ($fromDate ?? 'all') . '_to_' . ($toDate ?? 'all') . '.csv';

        $callback = function () use ($assetsAddedList, $transfersList, $disposedList, $fromDate, $toDate, $branchName) {
            $file = fopen('php://output', 'w');

            // Report header
            fputcsv($file, ['Asset Management Report']);
            fputcsv($file, ['Date Range: ' . ($fromDate ?? '-') . ' to ' . ($toDate ?? '-')]);
            fputcsv($file, ['Branch: ' . $branchName]);
            fputcsv($file, []);

            // Summary
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Assets Added', $assetsAddedList->count()]);
            fputcsv($file, ['Total Asset Value (RM)', number_format($assetsAddedList->sum('asset_cost'), 2)]);
            fputcsv($file, ['Transfers', $transfersList->count()]);
            fputcsv($file, ['Assets Disposed', $disposedList->count()]);
            fputcsv($file, []);

            // Assets Added section
            fputcsv($file, ['ASSETS ADDED']);
            fputcsv($file, ['Asset No', 'Asset Name', 'Branch', 'Group', 'Cost (RM)', 'Date Added']);
            foreach ($assetsAddedList as $asset) {
                fputcsv($file, [
                    $asset->asset_no,
                    $asset->asset_name ?? '',
                    $asset->branch->branch_name ?? '',
                    $asset->group->name ?? '',
                    number_format($asset->asset_cost ?? 0, 2),
                    $asset->created_at->format('d/m/Y'),
                ]);
            }
            fputcsv($file, []);

            // Transfers section
            fputcsv($file, ['TRANSFERS']);
            fputcsv($file, ['Reference', 'From Branch', 'To Branch', 'Purpose', 'Status', 'Cost (RM)', 'Created By', 'Date']);
            foreach ($transfersList as $transfer) {
                fputcsv($file, [
                    $transfer->transfer_running_no,
                    $transfer->fromBranch->branch_name ?? '',
                    $transfer->toBranch->branch_name ?? '',
                    $transfer->transfer_purpose ?? '',
                    ucfirst(str_replace('_', ' ', $transfer->transfer_status)),
                    number_format($transfer->transfer_cost ?? 0, 2),
                    $transfer->creator->name ?? '',
                    $transfer->created_at->format('d/m/Y'),
                ]);
            }
            fputcsv($file, []);

            // Disposed section
            fputcsv($file, ['ASSETS DISPOSED']);
            fputcsv($file, ['Asset No', 'Asset Name', 'Branch', 'Group', 'Cost (RM)', 'Date Disposed']);
            foreach ($disposedList as $asset) {
                fputcsv($file, [
                    $asset->asset_no,
                    $asset->asset_name ?? '',
                    $asset->branch->branch_name ?? '',
                    $asset->group->name ?? '',
                    number_format($asset->asset_cost ?? 0, 2),
                    $asset->updated_at->format('d/m/Y'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
