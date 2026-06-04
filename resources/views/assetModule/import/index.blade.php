@extends('layouts.app')

@section('content')
    <div class="px-4">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="ml-2 text-xl font-semibold text-gray-800">
                    Import Assets from CSV
                </h3>
                <p class="ml-2 mt-1 text-sm text-gray-500">Upload a CSV file to bulk import assets.</p>
            </div>

            <a href="{{ route('import.template') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download Template
            </a>
        </div>

        {{-- Success/Error Messages --}}
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        {{-- Upload Form --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Upload CSV File</h4>

            <form action="{{ route('import.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select CSV File *</label>
                    <input type="file" name="csv_file" accept=".csv,.txt"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        required>
                    <p class="text-xs text-gray-500 mt-1">Maximum file size: 10MB. Supported formats: CSV, TXT</p>
                    @error('csv_file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                        </path>
                    </svg>
                    Upload & Preview
                </button>
            </form>
        </div>

        {{-- Instructions --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">CSV Format Instructions</h4>

            <div class="space-y-4 text-sm text-gray-600">
                <p>Your CSV file should have the following columns (first row as headers):</p>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left border">Column Name</th>
                                <th class="px-4 py-2 text-left border">Required</th>
                                <th class="px-4 py-2 text-left border">Description</th>
                                <th class="px-4 py-2 text-left border">Example</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-4 py-2 border font-mono text-xs">asset_no</td>
                                <td class="px-4 py-2 border"><span class="text-red-500">Yes</span></td>
                                <td class="px-4 py-2 border">Unique asset number</td>
                                <td class="px-4 py-2 border">AST-001</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2 border font-mono text-xs">old_asset_no</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Previous/old asset number</td>
                                <td class="px-4 py-2 border">OLD-AST-001</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 border font-mono text-xs">asset_name</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Name of the asset</td>
                                <td class="px-4 py-2 border">Office Chair</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 border font-mono text-xs">asset_uom</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Unit of measure</td>
                                <td class="px-4 py-2 border">UNIT</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2 border font-mono text-xs">asset_purchase_date</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Purchase date (YYYY-MM-DD, DD.MM.YYYY, or DD/MM/YYYY)</td>
                                <td class="px-4 py-2 border">2024-01-15 or 15.01.2024 or 15/01/2024</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 border font-mono text-xs">asset_cost</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Cost amount (currency symbols auto-removed)</td>
                                <td class="px-4 py-2 border">500.00 or RM1,200.00</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2 border font-mono text-xs">group_name</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Asset group name (must exist)</td>
                                <td class="px-4 py-2 border">A1</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 border font-mono text-xs">asset_lifespan</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Lifespan (text format)</td>
                                <td class="px-4 py-2 border">5-6 Years</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2 border font-mono text-xs">asset_service_interval</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Service interval (text format)</td>
                                <td class="px-4 py-2 border">6 Months</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 border font-mono text-xs">venue</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Location/venue</td>
                                <td class="px-4 py-2 border">Main Office</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2 border font-mono text-xs">branch_code</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Branch code (must exist)</td>
                                <td class="px-4 py-2 border">UTW</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 border font-mono text-xs">user_name</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">User name (must exist)</td>
                                <td class="px-4 py-2 border">John Doe</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2 border font-mono text-xs">department_name</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Department name (must exist)</td>
                                <td class="px-4 py-2 border">IT Department</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2 border font-mono text-xs">supplier_name</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Supplier name (must exist)</td>
                                <td class="px-4 py-2 border">Mega Parts Sdn Bhd</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 border font-mono text-xs">inv_no</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Invoice number</td>
                                <td class="px-4 py-2 border">INV-2024-001</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2 border font-mono text-xs">has_warranty</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Has warranty (1 = Yes, 0 = No)</td>
                                <td class="px-4 py-2 border">1</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 border font-mono text-xs">warranty_period</td>
                                <td class="px-4 py-2 border">No</td>
                                <td class="px-4 py-2 border">Warranty period (text format)</td>
                                <td class="px-4 py-2 border">6 Months</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <h5 class="font-semibold text-yellow-800 mb-2">Important Notes:</h5>
                    <ul class="list-disc list-inside text-yellow-700 space-y-1">
                        <li>The first row must contain column headers</li>
                        <li>Asset numbers must be unique</li>
                        <li>Dates can be in YYYY-MM-DD, DD.MM.YYYY, or DD/MM/YYYY format</li>
                        <li>Cost values can include currency symbols (e.g., RM1,200.00) - they will be auto-cleaned</li>
                        <li>Branch codes, group names, supplier names, and PIC names must match existing records</li>
                        <li>Rows with errors will be skipped during import</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Available References --}}
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Branches --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <h5 class="font-semibold text-gray-800 mb-2">Available Branch Codes</h5>
                <div class="max-h-40 overflow-y-auto text-sm">
                    @foreach ($branches as $branch)
                        <div class="py-1 border-b border-gray-100 last:border-0">
                            <span class="font-mono text-xs bg-gray-100 px-1 rounded">{{ $branch->code }}</span>
                            <span class="text-gray-600 text-xs ml-1">{{ $branch->branch_name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Groups --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <h5 class="font-semibold text-gray-800 mb-2">Available Groups</h5>
                <div class="max-h-40 overflow-y-auto text-sm">
                    @foreach ($groups as $group)
                        <div class="py-1 border-b border-gray-100 last:border-0">
                            <span class="font-mono text-xs bg-gray-100 px-1 rounded">{{ $group->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Suppliers --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <h5 class="font-semibold text-gray-800 mb-2">Available Suppliers</h5>
                <div class="max-h-40 overflow-y-auto text-sm">
                    @foreach ($suppliers as $supplier)
                        <div class="py-1 border-b border-gray-100 last:border-0">
                            <span class="text-gray-600 text-xs">{{ $supplier->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Users --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <h5 class="font-semibold text-gray-800 mb-2">Available Users</h5>
                <div class="max-h-40 overflow-y-auto text-sm">
                    @foreach ($users as $user)
                        <div class="py-1 border-b border-gray-100 last:border-0">
                            <span class="text-gray-600 text-xs">{{ $user->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Departments --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <h5 class="font-semibold text-gray-800 mb-2">Available Departments</h5>
                <div class="max-h-40 overflow-y-auto text-sm">
                    @foreach ($departments as $department)
                        <div class="py-1 border-b border-gray-100 last:border-0">
                            <span class="text-gray-600 text-xs">{{ $department->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
