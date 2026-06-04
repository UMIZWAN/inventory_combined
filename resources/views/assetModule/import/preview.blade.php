@extends('layouts.app')

@section('content')
    <div class="px-4">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="ml-2 text-xl font-semibold text-gray-800">
                    Preview Import Data
                </h3>
                <p class="ml-2 mt-1 text-sm text-gray-500">
                    Review the data before importing. Rows with errors will be skipped.
                </p>
            </div>

            <a href="{{ route('import.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back to Upload
            </a>
        </div>

        {{-- Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Total Rows</p>
                        <p class="text-2xl font-bold text-blue-700">{{ $totalRows }}</p>
                    </div>
                    <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-green-600 font-medium">Valid Rows</p>
                        <p class="text-2xl font-bold text-green-700">{{ $totalRows - $errorCount }}</p>
                    </div>
                    <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-600 font-medium">Rows with Errors</p>
                        <p class="text-2xl font-bold text-red-700">{{ $errorCount }}</p>
                    </div>
                    <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        @if ($errorCount > 0)
            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h4 class="font-semibold text-yellow-800">Warning: Some rows have errors</h4>
                        <p class="text-sm text-yellow-700 mt-1">Rows with errors will be skipped during import. Only valid rows
                            will be imported.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Preview Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800">Data Preview</h4>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left border-b font-semibold text-gray-700">Row</th>
                            <th class="px-4 py-3 text-left border-b font-semibold text-gray-700">Status</th>
                            @foreach ($headers as $header)
                                <th class="px-4 py-3 text-left border-b font-semibold text-gray-700">
                                    {{ ucwords(str_replace('_', ' ', $header)) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $index => $row)
                            @php
                                $rowNumber = $row['_row'] ?? $index + 1;
                                $hasError = isset($validationErrors[$rowNumber]);
                            @endphp
                            <tr class="{{ $hasError ? 'bg-red-50' : 'hover:bg-gray-50' }}">
                                <td class="px-4 py-2 border-b text-gray-600">{{ $rowNumber }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if ($hasError)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-700 text-xs font-medium rounded">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                            Error
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                            Valid
                                        </span>
                                    @endif
                                </td>
                                @foreach ($headers as $header)
                                    <td class="px-4 py-2 border-b text-gray-700">
                                        {{ $row[$header] ?? '-' }}
                                    </td>
                                @endforeach
                            </tr>
                            @if ($hasError)
                                <tr class="bg-red-50">
                                    <td colspan="{{ count($headers) + 2 }}" class="px-4 py-2 border-b">
                                        <div class="flex items-start gap-2 text-sm">
                                            <svg class="w-4 h-4 text-red-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                            <div>
                                                <p class="font-medium text-red-800 mb-1">Validation Errors:</p>
                                                <ul class="list-disc list-inside text-red-700 space-y-0.5">
                                                    @foreach ($validationErrors[$rowNumber] as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Import Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('import.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel
            </a>

            <form action="{{ route('import.process') }}" method="POST">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
                    {{ $totalRows === 0 ? 'disabled' : '' }}>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Import {{ $totalRows - $errorCount }} Valid
                    {{ Str::plural('Asset', $totalRows - $errorCount) }}
                </button>
            </form>
        </div>
    </div>
@endsection
