<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    /**
     * List suppliers
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $suppliers = Supplier::active()
            ->orderBy('name')
            ->where('is_deleted', false)
            ->get();

        return view('supplier.list', compact('suppliers'));
    }

    /**
     * Store new supplier
     */
    public function addSupplier(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'nullable|email|max:255',
            'phone_no'  => 'nullable|string|max:50',
            'address'   => 'nullable|string',
        ]);

        Supplier::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone_no'   => $request->phone_no,
            'address'    => $request->address,
            'is_deleted' => false,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Update supplier
     */
    public function updateSupplier(Request $request, $id)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'nullable|email|max:255',
            'phone_no'  => 'nullable|string|max:50',
            'address'   => 'nullable|string',
        ]);

        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone_no' => $request->phone_no,
            'address'  => $request->address,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Soft delete supplier
     */
    public function deleteSupplier($id)
    {
        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'is_deleted' => true,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Export Suppliers to CSV
     */
    public function exportCSV()
    {
        $suppliers = Supplier::where('is_deleted', false)
            ->orderBy('name')
            ->get();

        $filename = 'suppliers_export_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($suppliers) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($file, ['Name', 'Email', 'Phone', 'Address']);

            // Data rows
            foreach ($suppliers as $supplier) {
                fputcsv($file, [
                    $supplier->name,
                    $supplier->email ?? '',
                    $supplier->phone_no ?? '',
                    $supplier->address ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import Suppliers from CSV
     */
    public function importCSV(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $csvData = array_map('str_getcsv', file($path));

        // Remove header row
        $header = array_shift($csvData);

        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2; // +2 because we removed header and array is 0-indexed

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Ensure row has at least 1 column (name)
            if (count($row) < 1) {
                $errors[] = "Row {$rowNumber}: Invalid format (missing columns)";
                $skippedCount++;
                continue;
            }

            $name = trim($row[0] ?? '');
            $email = trim($row[1] ?? '');
            $phone = trim($row[2] ?? '');
            $address = trim($row[3] ?? '');

            // Validate name
            if (empty($name)) {
                $errors[] = "Row {$rowNumber}: Name is required";
                $skippedCount++;
                continue;
            }

            // Validate email format if provided
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row {$rowNumber}: Invalid email format '{$email}'";
                $skippedCount++;
                continue;
            }

            try {
                // Create supplier
                Supplier::create([
                    'name' => $name,
                    'email' => !empty($email) ? $email : null,
                    'phone_no' => !empty($phone) ? $phone : null,
                    'address' => !empty($address) ? $address : null,
                    'is_deleted' => false,
                ]);

                $importedCount++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: {$e->getMessage()}";
                $skippedCount++;
            }
        }

        $message = "Import completed. {$importedCount} supplier(s) imported";
        if ($skippedCount > 0) {
            $message .= ", {$skippedCount} skipped";
        }

        if (!empty($errors)) {
            return back()->with('warning', $message)
                ->with('import_errors', $errors);
        }

        return back()->with('success', $message);
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $filename = 'suppliers_import_template.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($file, ['Name', 'Email', 'Phone', 'Address']);

            // Example rows
            fputcsv($file, ['ABC Supplies Inc', 'contact@abcsupplies.com', '012-3456789', '123 Main St, City']);
            fputcsv($file, ['XYZ Trading Co', 'info@xyztrading.com', '019-8765432', '456 Commerce Ave']);
            fputcsv($file, ['Tech Solutions Ltd', 'sales@techsolutions.com', '011-2468135', '789 Industrial Park']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
