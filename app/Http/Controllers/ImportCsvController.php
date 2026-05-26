<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetGroup;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportCsvController extends Controller
{
    /**
     * Show the import form
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Check access level
        if (!Auth::user()->accessLevel?->import_csv) {
            return redirect()->back()->with('error', 'You do not have permission to import CSV');
        }

        return view('import.index', [
            'groups' => AssetGroup::where('is_deleted', false)->get(),
            'branches' => Branch::where('is_active', true)->get(),
            'suppliers' => Supplier::where('is_deleted', false)->get(),
            'users' => User::where('is_active', true)->get(),
            'departments' => \App\Models\Department::where('is_active', true)->get(),
        ]);
    }

    /**
     * Download sample CSV template
     */
    public function downloadTemplate()
    {
        $headers = [
            'asset_no',
            'old_asset_no',
            'asset_name',
            'asset_uom',
            'asset_purchase_date',
            'asset_cost',
            'group_name',
            'asset_lifespan',
            'asset_service_interval',
            'venue',
            'branch_code',
            'user_name',
            'department_name',
            'supplier_name',
            'inv_no',
            'has_warranty',
            'warranty_period',
        ];

        $sampleData = [
            'AST-001',
            'OLD-AST-001',
            'Office Chair',
            'UNIT',
            '15.01.2024',
            '500.00',
            'A1',
            '5-6 Years',
            '6 Months',
            'Main Office',
            'UTW',
            'John Doe',
            'IT Department',
            'Mega Parts Sdn Bhd',
            'INV-2024-001',
            '1',
            '6 Months',
        ];

        $callback = function () use ($headers, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, $sampleData);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="asset_import_template.csv"',
        ]);
    }

    /**
     * Preview CSV data before import
     */
    public function preview(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $data = [];
        $validationErrors = [];
        $headers = [];

        // First pass: collect all rows
        if (($handle = fopen($path, 'r')) !== false) {
            $row = 0;
            while (($line = fgetcsv($handle, 1000, ',')) !== false) {
                if ($row === 0) {
                    $headers = array_map('trim', $line);
                } else {
                    $rowData = [];
                    foreach ($headers as $index => $header) {
                        $rowData[$header] = isset($line[$index]) ? trim($line[$index]) : '';
                    }
                    $rowData['_row'] = $row;
                    $data[] = $rowData;
                }
                $row++;
            }
            fclose($handle);
        }

        // Detect asset_nos that appear more than once within the CSV
        $csvAssetNos = array_filter(array_column($data, 'asset_no'));
        $csvDuplicateNos = array_keys(array_filter(array_count_values($csvAssetNos), fn($count) => $count > 1));

        // Second pass: validate each row
        foreach ($data as $rowData) {
            $rowErrors = $this->validateRow($rowData, $rowData['_row'], $csvDuplicateNos);
            if (!empty($rowErrors)) {
                $validationErrors[$rowData['_row']] = $rowErrors;
            }
        }

        // Store data in session for import
        session(['csv_import_data' => $data]);
        session(['csv_import_headers' => $headers]);

        return view('import.preview', [
            'data' => $data,
            'headers' => $headers,
            'validationErrors' => $validationErrors,
            'totalRows' => count($data),
            'errorCount' => count($validationErrors),
        ]);
    }

    /**
     * Validate a single row
     */
    private function validateRow(array $row, int $rowNumber, array $csvDuplicateNos = []): array
    {
        $errors = [];

        // Required field: asset_no
        if (empty($row['asset_no'] ?? '')) {
            $errors[] = 'Asset No is required';
        } else {
            // Check for duplicate within the CSV itself
            if (in_array($row['asset_no'], $csvDuplicateNos)) {
                $errors[] = "Asset No '{$row['asset_no']}' is duplicated within the CSV";
            }
            // Check for duplicate in database
            $exists = Asset::where('asset_no', $row['asset_no'])
                ->where('is_deleted', false)
                ->exists();
            if ($exists) {
                $errors[] = "Asset No '{$row['asset_no']}' already exists in the system";
            }
        }

        // Validate date format (supports YYYY-MM-DD, DD.MM.YYYY, DD/MM/YYYY)
        if (!empty($row['asset_purchase_date'] ?? '')) {
            $parsedDate = $this->parseDate($row['asset_purchase_date']);
            if (!$parsedDate) {
                $errors[] = 'Invalid date format (use YYYY-MM-DD, DD.MM.YYYY, or DD/MM/YYYY)';
            }
        }

        // Validate numeric fields (clean currency symbols and formatting first)
        if (!empty($row['asset_cost'] ?? '')) {
            $cleanedCost = $this->parseCurrency($row['asset_cost']);
            if ($cleanedCost === null) {
                $errors[] = 'Asset cost must be a valid number';
            }
        }



        // Validate branch exists
        if (!empty($row['branch_code'] ?? '')) {
            $branch = Branch::where('code', $row['branch_code'])->where('is_active', true)->first();
            if (!$branch) {
                $errors[] = "Branch code '{$row['branch_code']}' not found";
            }
        }

        // Validate group exists
        if (!empty($row['group_name'] ?? '')) {
            $group = AssetGroup::where('name', $row['group_name'])->where('is_deleted', false)->first();
            if (!$group) {
                $errors[] = "Group '{$row['group_name']}' not found";
            }
        }

        // Validate supplier exists
        if (!empty($row['supplier_name'] ?? '')) {
            $supplier = Supplier::where('name', $row['supplier_name'])->where('is_deleted', false)->first();
            if (!$supplier) {
                $errors[] = "Supplier '{$row['supplier_name']}' not found";
            }
        }

        // Validate user exists
        if (!empty($row['user_name'] ?? '')) {
            $user = User::where('name', $row['user_name'])->where('is_active', true)->first();
            if (!$user) {
                $errors[] = "User '{$row['user_name']}' not found — user must be registered first";
            }
        }

        return $errors;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate(string $dateString): ?string
    {
        $dateString = trim($dateString);

        // Try YYYY-MM-DD format
        $date = \DateTime::createFromFormat('Y-m-d', $dateString);
        if ($date && $date->format('Y-m-d') === $dateString) {
            return $dateString;
        }

        // Try DD.MM.YYYY format
        $date = \DateTime::createFromFormat('d.m.Y', $dateString);
        if ($date && $date->format('d.m.Y') === $dateString) {
            return $date->format('Y-m-d');
        }

        // Try DD/MM/YYYY format
        $date = \DateTime::createFromFormat('d/m/Y', $dateString);
        if ($date && $date->format('d/m/Y') === $dateString) {
            return $date->format('Y-m-d');
        }

        return null;
    }

    /**
     * Parse currency string to float
     */
    private function parseCurrency(string $value): ?float
    {
        // Remove currency symbols, letters, spaces, and thousand separators
        $cleaned = preg_replace('/[^\d.,\-]/', '', trim($value));

        // Handle comma as thousand separator (e.g., 1,200.00)
        if (preg_match('/^\d{1,3}(,\d{3})*(\.\d+)?$/', $cleaned)) {
            $cleaned = str_replace(',', '', $cleaned);
        }
        // Handle comma as decimal separator (e.g., 1200,00)
        elseif (preg_match('/^\d+,\d{2}$/', $cleaned)) {
            $cleaned = str_replace(',', '.', $cleaned);
        }

        if ($cleaned === '' || !is_numeric($cleaned)) {
            return null;
        }

        return (float) $cleaned;
    }

    /**
     * Import the CSV data
     */
    public function import(Request $request)
    {
        if (!Auth::user()->accessLevel?->import_csv) {
            return redirect()->back()->with('error', 'You do not have permission to import CSV');
        }

        $data = session('csv_import_data');

        if (empty($data)) {
            return redirect()->route('import.index')->with('error', 'No data to import. Please upload a CSV file first.');
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        // Detect within-CSV duplicates
        $csvAssetNos = array_filter(array_column($data, 'asset_no'));
        $csvDuplicateNos = array_keys(array_filter(array_count_values($csvAssetNos), fn($count) => $count > 1));

        DB::beginTransaction();

        try {
            foreach ($data as $row) {
                $rowNumber = $row['_row'] ?? 0;

                // Skip rows with validation errors
                $rowErrors = $this->validateRow($row, $rowNumber, $csvDuplicateNos);
                if (!empty($rowErrors)) {
                    $skipped++;
                    $errors[$rowNumber] = $rowErrors;
                    continue;
                }

                // Get related IDs
                $branchId = null;
                if (!empty($row['branch_code'])) {
                    $branch = Branch::where('code', $row['branch_code'])->where('is_active', true)->first();
                    $branchId = $branch?->id;
                }

                $groupId = null;
                if (!empty($row['group_name'])) {
                    $group = AssetGroup::where('name', $row['group_name'])->where('is_deleted', false)->first();
                    $groupId = $group?->id;
                }

                $supplierId = null;
                if (!empty($row['supplier_name'])) {
                    $supplier = Supplier::where('name', $row['supplier_name'])->where('is_deleted', false)->first();
                    $supplierId = $supplier?->id;
                }

                // Resolve user by name
                $userId = null;
                if (!empty($row['user_name'])) {
                    $user = User::where('name', $row['user_name'])->where('is_active', true)->first();
                    $userId = $user?->id;
                }

                // Resolve department by name
                $departmentId = null;
                if (!empty($row['department_name'])) {
                    $dept = \App\Models\Department::where('name', $row['department_name'])->where('is_active', true)->first();
                    $departmentId = $dept?->id;
                }

                // Parse date and cost
                $purchaseDate = !empty($row['asset_purchase_date'])
                    ? $this->parseDate($row['asset_purchase_date'])
                    : now()->toDateString();
                $assetCost = !empty($row['asset_cost'])
                    ? $this->parseCurrency($row['asset_cost']) ?? 0
                    : 0;

                // Create the asset
                Asset::create([
                    'asset_no' => $row['asset_no'],
                    'old_asset_no' => $row['old_asset_no'] ?? null,
                    'asset_name' => $row['asset_name'] ?? null,
                    'asset_uom' => $row['asset_uom'] ?? null,
                    'asset_purchase_date' => $purchaseDate,
                    'asset_cost' => $assetCost,
                    'group_id' => $groupId,
                    'asset_lifespan' => !empty($row['asset_lifespan']) ? $row['asset_lifespan'] : null,
                    'asset_service_interval' => !empty($row['asset_service_interval']) ? $row['asset_service_interval'] : null,
                    'venue' => $row['venue'] ?? null,
                    'branch_id' => $branchId ?? 1,
                    'user_id' => $userId,
                    'department_id' => $departmentId,
                    'supplier_id' => $supplierId,
                    'inv_no' => $row['inv_no'] ?? null,
                    'has_warranty' => !empty($row['has_warranty']) && $row['has_warranty'] == '1',
                    'warranty_period' => !empty($row['warranty_period']) ? $row['warranty_period'] : null,
                    'is_disposed' => false,
                    'is_deleted' => false,
                    'created_by' => Auth::id(),
                    'asset_log' => 'Imported via CSV by ' . (Auth::user()->name ?? 'Unknown') . ' at ' . now()->format('Y-m-d H:i:s'),
                ]);

                $imported++;
            }

            DB::commit();

            // Clear session data
            session()->forget(['csv_import_data', 'csv_import_headers']);

            return redirect()->route('import.index')->with('success', "Successfully imported {$imported} assets. Skipped {$skipped} rows with errors.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CSV Import Error: ' . $e->getMessage());
            return redirect()->route('import.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
