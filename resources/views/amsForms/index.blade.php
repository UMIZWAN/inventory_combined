@extends('layouts.app')

@section('content')
    <div class="px-8 py-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">
                AMS Forms & Documents
            </h2>

            <button onclick="openUploadModal()" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Upload Document
            </button>
        </div>

        {{-- Success/Error Messages --}}
        {{-- @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
        @endif --}}

        {{-- Search Bar --}}
        <form method="GET" class="mb-4 bg-white p-4 rounded shadow">
            <div class="flex gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search by document name or description..."
                        class="w-full border rounded px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('amsForms.index') }}"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded text-sm hover:bg-gray-400">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        {{-- Documents Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($forms as $form)
                <div class="bg-white rounded shadow hover:shadow-lg transition-shadow p-4 border">
                    {{-- File Icon --}}
                    <div class="flex items-center mb-3">
                        <div class="w-12 h-12 bg-blue-100 rounded flex items-center justify-center mr-3">
                            @php
                                $ext = strtolower($form->file_extension);
                                $icon = match ($ext) {
                                    'pdf' => '📄',
                                    'doc', 'docx' => '📝',
                                    'xls', 'xlsx' => '📊',
                                    'zip', 'rar' => '🗜️',
                                    default => '📁'
                                };
                            @endphp
                            <span class="text-2xl">{{ $icon }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-800 truncate">{{ $form->name }}</h3>
                            <p class="text-xs text-gray-500">{{ strtoupper($form->file_extension) }} • {{ $form->file_size }}
                            </p>
                        </div>
                    </div>

                    {{-- Description --}}
                    @if($form->descriptions)
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $form->descriptions }}</p>
                    @else
                        <p class="text-sm text-gray-400 italic mb-3">No description</p>
                    @endif

                    {{-- Metadata --}}
                    <div class="text-xs text-gray-500 mb-3">
                        <div>Uploaded: {{ $form->created_at->format('d M Y') }}</div>
                        @if($form->created_at != $form->updated_at)
                            <div>Updated: {{ $form->updated_at->format('d M Y') }}</div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        <a href="{{ route('amsForms.download', $form->id) }}"
                            class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 text-center">
                            <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download
                        </a>
                        <button onclick='openEditModal(@json($form))'
                            class="px-3 py-2 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </button>
                        <form action="{{ route('amsForms.destroy', $form->id) }}" method="POST" class="inline"
                            onsubmit="return confirm('Are you sure you want to delete this document?')">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded shadow p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <p class="text-gray-500 mb-2">No documents found</p>
                    @if(request('search'))
                        <p class="text-sm text-gray-400 mb-4">Try adjusting your search terms</p>
                    @else
                        <p class="text-sm text-gray-400 mb-4">Upload your first document to get started</p>
                    @endif
                    <button onclick="openUploadModal()"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                        Upload Document
                    </button>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($forms->hasPages())
            <div class="mt-6">
                {{ $forms->links() }}
            </div>
        @endif
    </div>

    {{-- Upload Modal --}}
    <div id="uploadModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white w-full max-w-lg rounded shadow p-6 mx-4">
            <h3 class="text-lg font-semibold mb-4">Upload New Document</h3>

            <form action="{{ route('amsForms.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block font-medium mb-1">Document Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Description</label>
                    <textarea name="descriptions" rows="3"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">File <span class="text-red-500">*</span></label>
                    <input type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip,.rar"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Accepted: PDF, DOC, DOCX, XLS, XLSX, CSV, TXT, ZIP, RAR (Max:
                        10MB)</p>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeUploadModal()"
                        class="px-4 py-2 border rounded text-sm hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white w-full max-w-lg rounded shadow p-6 mx-4">
            <h3 class="text-lg font-semibold mb-4">Edit Document</h3>

            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block font-medium mb-1">Document Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="editName" required
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Description</label>
                    <textarea name="descriptions" id="editDescription" rows="3"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Replace File (Optional)</label>
                    <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip,.rar"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep existing file</p>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 border rounded text-sm hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUploadModal() {
            document.getElementById('uploadModal').classList.remove('hidden');
            document.getElementById('uploadModal').classList.add('flex');
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.add('hidden');
            document.getElementById('uploadModal').classList.remove('flex');
        }

        function openEditModal(form) {
            const modal = document.getElementById('editModal');
            const editForm = document.getElementById('editForm');

            editForm.action = `/ams-forms/${form.id}`;
            document.getElementById('editName').value = form.name;
            document.getElementById('editDescription').value = form.descriptions || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
        }

        // Close modals on outside click
        ['uploadModal', 'editModal'].forEach(id => {
            document.getElementById(id)?.addEventListener('click', function (e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                    this.classList.remove('flex');
                }
            });
        });
    </script>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
@endsection