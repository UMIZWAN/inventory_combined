@extends('layouts.app')

@section('content')
    {{-- Asset Log Tooltip --}}
    <div id="assetLogTooltip" style="display:none; position:fixed; z-index:99998; width:300px;">
        <div style="background:#fff; border-radius:12px; box-shadow:0 8px 28px rgba(0,0,0,0.16); border:1px solid #e5e7eb; overflow:hidden; font-family:inherit;">
            {{-- Header --}}
            <div style="padding:9px 14px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:12px; font-weight:700; color:#374151; letter-spacing:0.02em;">Edit history</span>
                <div style="display:flex; align-items:center; gap:2px;">
                    <button id="assetLogPrev" onclick="logNav(-1)"
                        style="border:none; background:none; cursor:pointer; color:#9ca3af; font-size:20px; line-height:1; padding:0 5px; display:flex; align-items:center;">&#8249;</button>
                    <span id="assetLogCounter" style="font-size:11px; color:#9ca3af; min-width:30px; text-align:center;"></span>
                    <button id="assetLogNext" onclick="logNav(1)"
                        style="border:none; background:none; cursor:pointer; color:#9ca3af; font-size:20px; line-height:1; padding:0 5px; display:flex; align-items:center;">&#8250;</button>
                </div>
            </div>
            {{-- Entry --}}
            <div style="padding:12px 14px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                    <div id="assetLogAvatar"
                        style="width:34px; height:34px; border-radius:50%; background:#6366f1; color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; flex-shrink:0;">
                        ?
                    </div>
                    <div>
                        <div id="assetLogUser" style="font-size:13px; font-weight:600; color:#111827;"></div>
                        <div id="assetLogDate" style="font-size:11px; color:#6b7280; margin-top:1px;"></div>
                    </div>
                </div>
                <p id="assetLogMessage" style="font-size:12px; color:#374151; line-height:1.5; margin:0; word-break:break-word; white-space:pre-wrap;"></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltip  = document.getElementById('assetLogTooltip');
            var _entries = [], _idx = 0, _hideTimer = null;

            var avatarPalette = ['#6366f1','#ec4899','#14b8a6','#f97316','#8b5cf6','#0ea5e9','#10b981','#f59e0b'];
            function avatarColor(name) {
                var h = 0;
                for (var i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) >>> 0;
                return avatarPalette[h % avatarPalette.length];
            }

            function fmtDate(str) {
                if (!str) return '';
                var d = new Date(str);
                if (isNaN(d)) return str;
                return d.toLocaleDateString('en-US', {year:'numeric', month:'long', day:'numeric'}) +
                       ', ' + d.toLocaleTimeString('en-US', {hour:'numeric', minute:'2-digit'});
            }

            function parseLine(line) {
                line = line.trim();
                if (!line || line === 'No log available') return null;
                var m;
                // "YYYY-MM-DD HH:MM | User | Message"
                m = line.match(/^(\d{4}-\d{2}-\d{2}[\sT]\d{2}:\d{2}(?::\d{2})?)\s*\|\s*(.+?)\s*\|\s*(.+)$/);
                if (m) return { date: fmtDate(m[1]), user: m[2].trim(), message: m[3].trim() };
                // "[Date] User: Message"
                m = line.match(/^\[([^\]]+)\]\s+(.+?):\s+(.+)$/);
                if (m) return { date: fmtDate(m[1]), user: m[2].trim(), message: m[3].trim() };
                // "Date - User: Message"
                m = line.match(/^(\d[\d\/\-\s:apmAPM]+?)\s*[-–]\s*(.+?):\s*(.+)$/);
                if (m) return { date: fmtDate(m[1].trim()), user: m[2].trim(), message: m[3].trim() };
                // "Action by USER at YYYY-MM-DD HH:MM:SS[. optional extra]"
                m = line.match(/^(.+?)\s+by\s+(.+?)\s+at\s+(\d{4}-\d{2}-\d{2}[\sT]\d{2}:\d{2}(?::\d{2})?)(.*)$/i);
                if (m) {
                    var action = m[1].trim();
                    var extra  = m[4].trim().replace(/^[.\s]+/, '');
                    return { date: fmtDate(m[3].trim()), user: m[2].trim(), message: extra ? action + '. ' + extra : action };
                }
                return { date: '', user: '', message: line };
            }

            function render() {
                var e = _entries[_idx];
                if (!e) return;
                var av = document.getElementById('assetLogAvatar');
                av.textContent  = e.user ? e.user.charAt(0).toUpperCase() : '?';
                av.style.backgroundColor = e.user ? avatarColor(e.user) : '#9ca3af';
                document.getElementById('assetLogUser').textContent    = e.user    || 'System';
                document.getElementById('assetLogDate').textContent    = e.date    || '';
                document.getElementById('assetLogMessage').textContent = e.message || '';
                var total = _entries.length;
                document.getElementById('assetLogCounter').textContent = total > 1 ? (_idx + 1) + '/' + total : '';
                document.getElementById('assetLogPrev').style.opacity  = _idx > 0           ? '1' : '0.25';
                document.getElementById('assetLogNext').style.opacity  = _idx < total - 1   ? '1' : '0.25';
            }

            window.logNav = function(dir) {
                var n = _idx + dir;
                if (n >= 0 && n < _entries.length) { _idx = n; render(); }
            };

            function place(x, y) {
                var tw = tooltip.offsetWidth, th = tooltip.offsetHeight;
                var left = x + 16;
                var top  = y + 16;
                if (left + tw > window.innerWidth  - 8) left = x - tw - 8;
                if (top  + th > window.innerHeight - 8) top  = y - th - 8;
                tooltip.style.left = left + 'px';
                tooltip.style.top  = top  + 'px';
            }

            var _activeCell = null;

            document.addEventListener('click', function(e) {
                var cell = e.target.closest('.sc-name');

                // Click on an asset name cell
                if (cell) {
                    var row = cell.closest('.asset-row-hover');
                    if (!row || !row.dataset.assetLog) return;

                    // Toggle off if clicking the same cell
                    if (_activeCell === cell && tooltip.style.display === 'block') {
                        tooltip.style.display = 'none';
                        _activeCell = null;
                        return;
                    }

                    _activeCell = cell;
                    _entries = row.dataset.assetLog.trim().split('\n').map(parseLine).filter(Boolean);
                    if (!_entries.length) return;
                    _idx = _entries.length - 1;
                    render();
                    tooltip.style.display = 'block';
                    place(e.clientX, e.clientY);
                    return;
                }

                // Click outside tooltip and asset cell → close
                if (!e.target.closest('#assetLogTooltip')) {
                    tooltip.style.display = 'none';
                    _activeCell = null;
                }
            });
        });
    </script>

    {{-- IMAGE PREVIEW MODAL - Using inline styles for guaranteed visibility --}}
    <div id="imagePreviewModal" onclick="closeImagePreview()"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 99999; justify-content: center; align-items: center; cursor: pointer;">
        <div onclick="event.stopPropagation()" style="position: relative; cursor: default;">
            <button onclick="closeImagePreview()"
                style="
        position: absolute;
        top: -45px;
        right: 0;
        width: 40px;
        height: 40px;
        background: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    ">
                <span style="
        position: relative;
        width: 16px;
        height: 16px;
    ">
                    <span
                        style="
            position: absolute;
            width: 100%;
            height: 2px;
            background: black;
            top: 50%;
            left: 0;
            transform: rotate(45deg);
        "></span>
                    <span
                        style="
            position: absolute;
            width: 100%;
            height: 2px;
            background: black;
            top: 50%;
            left: 0;
            transform: rotate(-45deg);
        "></span>
                </span>
            </button>

            <img id="previewImage" src="" alt=""
                style="max-width: 85vw; max-height: 80vh; border-radius: 8px; display: block;">
            <p id="previewImageName"
                style="color: white; text-align: center; margin-top: 12px; font-size: 16px; font-weight: 500;"></p>
        </div>
    </div>

    <div class="px-4">

        {{-- Header --}}
        <div class="flex items-center justify-end mb-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('asset.exportCsv') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-green-600 hover:text-green-800 hover:underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export CSV
                </a>
                @if (auth()->user()->accessLevel?->add_edit_asset)
                    <button onclick="openAddModal()"
                        class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Asset
                    </button>
                @endif
            </div>
        </div>

        {{-- Filter Form --}}
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-2">
            <form method="GET">
                <div class="flex flex-wrap gap-4 items-end mb-4">
                    {{-- Asset Name --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Asset Name</label>
                        <input type="text" name="asset_name" value="{{ $filters['asset_name'] ?? '' }}"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    {{-- Asset No --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Asset No</label>
                        <input type="text" name="asset_no" value="{{ $filters['asset_no'] ?? '' }}"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    {{-- Branch --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Branch</label>
                        <select name="branch_id"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">All</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ isset($filters['branch_id']) && $filters['branch_id'] == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Department --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Department</label>
                        <select name="department_id"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">All</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ isset($filters['department_id']) && $filters['department_id'] == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- User --}}
                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">User</label>
                        <input type="text" id="filterUserInput"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Type to search"
                            value="{{ isset($filters['user_id']) ? $users->firstWhere('id', $filters['user_id'])?->name : '' }}"
                            autocomplete="off">
                        <input type="hidden" name="user_id" id="filterUserIdHidden"
                            value="{{ $filters['user_id'] ?? '' }}">
                        <div id="filterUserDropdown"
                            class="hidden absolute left-0 top-full mt-1 w-48 max-h-48 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                        </div>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                        <select name="status"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">All</option>
                            <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active
                            </option>
                            <option value="disposed" {{ ($filters['status'] ?? '') === 'disposed' ? 'selected' : '' }}>
                                Disposed</option>
                            <option value="open" {{ ($filters['status'] ?? '') === 'open' ? 'selected' : '' }}>Open
                            </option>
                        </select>
                    </div>

                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const branchSelect = document.querySelector('select[name="branch_id"]');
                        const userInput = document.getElementById('filterUserInput');
                        const userHidden = document.getElementById('filterUserIdHidden');
                        const userDropdown = document.getElementById('filterUserDropdown');

                        @php
                            $userBranchMap = $users->map(function ($u) {
                                return [
                                    'id' => $u->id,
                                    'name' => $u->name,
                                    'branch_ids' => $u->branches->pluck('id')->toArray(),
                                ];
                            });
                        @endphp
                        const allUsers = @json($userBranchMap);

                        function getFilteredUsers() {
                            const selectedBranch = branchSelect.value;
                            return allUsers.filter(u =>
                                !selectedBranch || u.branch_ids.includes(parseInt(selectedBranch))
                            );
                        }

                        function renderDropdown(search) {
                            const filtered = getFilteredUsers().filter(u =>
                                !search || u.name.toLowerCase().includes(search.toLowerCase())
                            );
                            userDropdown.innerHTML = '';
                            if (filtered.length === 0) {
                                userDropdown.classList.add('hidden');
                                return;
                            }
                            filtered.forEach(u => {
                                const div = document.createElement('div');
                                div.textContent = u.name;
                                div.className = 'px-3 py-1.5 text-xs cursor-pointer hover:bg-indigo-50';
                                div.addEventListener('mousedown', function(e) {
                                    e.preventDefault();
                                    userInput.value = u.name;
                                    userHidden.value = u.id;
                                    userDropdown.classList.add('hidden');
                                });
                                userDropdown.appendChild(div);
                            });
                            userDropdown.classList.remove('hidden');
                        }

                        userInput.addEventListener('focus', function() {
                            renderDropdown(this.value);
                        });

                        userInput.addEventListener('input', function() {
                            userHidden.value = '';
                            renderDropdown(this.value);
                        });

                        userInput.addEventListener('blur', function() {
                            userDropdown.classList.add('hidden');
                            const match = allUsers.find(u => u.name === this.value);
                            if (!match) {
                                this.value = '';
                                userHidden.value = '';
                            }
                        });

                        branchSelect.addEventListener('change', function() {
                            if (userHidden.value) {
                                const currentUser = allUsers.find(u => u.id == userHidden.value);
                                const selectedBranch = branchSelect.value;
                                if (selectedBranch && currentUser && !currentUser.branch_ids.includes(parseInt(
                                        selectedBranch))) {
                                    userInput.value = '';
                                    userHidden.value = '';
                                }
                            }
                        });

                        updateUserOptions();
                    });
                </script>

                {{-- Buttons Row --}}
                <div class="flex gap-2 pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('master.list') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Transfer Selected Button (right-aligned) --}}
        <div class="flex justify-end mb-4">
            @if (auth()->user()->accessLevel?->change_color)
                <button id="changeColorBtn"
                    class="flex items-center px-2 py-1 text-xs rounded border border-purple-500 text-purple-500 bg-white hover:bg-purple-50 hidden mr-2"
                    onclick="openChangeColorModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                    Change Color
                </button>
            @endif
            @if (auth()->user()->accessLevel?->change_user_dept)
                <button id="changeUserDeptBtn"
                    class="flex items-center px-2 py-1 text-xs rounded border border-teal-500 text-teal-500 bg-white hover:bg-teal-50 hidden mr-2"
                    onclick="openChangeUserDeptModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Change User/Dept
                </button>
            @endif
            @if (auth()->user()->accessLevel?->transfer)
                <button id="transferSelectedBtn"
                    class="flex items-center px-2 py-1 text-xs rounded border border-orange-500 text-orange-500 bg-white hover:bg-orange-50 hidden mr-4">
                    <!-- Telegram Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            d="M21.923 2.92a1.08 1.08 0 0 0-1.317-.244L2.763 9.38a.81.81 0 0 0 .02 1.476l4.612 1.697 1.753 5.43c.183.565.759.925 1.348.894.567-.03 1.086-.401 1.287-.927l2.02-5.02 4.587 3.294c.35.253.816.214 1.116-.09a.804.804 0 0 0 .245-1.06l-5.57-8.67 6.648-5.784c.462-.402.534-1.075.172-1.542z" />
                    </svg>
                    Transfer
                </button>
            @endif

            <!-- Request Button -->
            {{-- <button id="requestBtn"
                class="flex items-center px-2 py-1 text-xs rounded border border-purple-500 text-purple-500 bg-white hover:bg-purple-50 mr-4">
                <!-- Document Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 2H8c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7l-5-5zM8 20V4h9.17L20 6.83V20H8z" />
                </svg>
                Request
            </button> --}}
        </div>

        {{-- Table --}}
        <style>
            /*
             * Sticky-column table.
             * #tbl-scroll is the ONE scroll container injected by JS after DataTables init.
             * Because <thead> and <tbody> live in the SAME <table> inside #tbl-scroll,
             * position:sticky works for BOTH header and body cells without any hacks.
             *
             * Background rule: frozen <td> cells use a PHP-computed solid colour so they
             * are NEVER transparent — scrolling content slides underneath, not through.
             */
            #tbl-scroll {
                overflow-x: auto;
                overflow-y: auto;
                clear: both;          /* sit below the floated DT length/filter controls */
                border-top: 1px solid #e5e7eb;
                margin-top: 0.5rem;
            }
            #assetsTable {
                border-collapse: separate;   /* required — collapse breaks sticky borders */
                border-spacing: 0;
                /* width + minWidth are applied by JS in initComplete, AFTER DataTables
                   has finished its own internal width setup. Setting them here in CSS
                   fights DataTables' width:100% assignment and causes header/body
                   misalignment before the table is fully initialised. */
                font-size: 0.8rem;
            }
            #assetsTable th,
            #assetsTable td {
                white-space: nowrap;
                border-bottom: 1px solid #e5e7eb;
            }
            #assetsTable th { padding: 0.45rem 0.75rem; }
            #assetsTable td { padding: 0.25rem 0.75rem; }
            /* NOTE: isolation:isolate is intentionally removed from tbody.
               It created a new stacking context for the entire tbody, which caused
               the tbody to paint over thead — hiding sort icons in sticky headers.
               Without it, z-index values compare directly: th.sc(30) > td.sc(10). */

            /* ── Sticky column rules ──
               Stacking order: thead th.sc (z:30) > tbody td.sc (z:10) > static td (no z)
               background-clip:padding-box fills the cell right to its border edge — no gap. */
            #assetsTable thead th.sc,
            #assetsTable tbody td.sc {
                position: sticky;
                will-change: transform;          /* own GPU compositing layer — always on top */
                background-clip: padding-box;    /* solid bg fills fully to border, no bleed gap */
            }
            #assetsTable thead th.sc {
                z-index: 30;                     /* above sticky td (10) AND scrolling tbody */
                background-color: #f3f4f6 !important;
            }
            #assetsTable tbody td.sc {
                z-index: 10;                     /* above static/non-positioned td siblings */
                background-color: #ffffff;       /* fallback; per-row inline style wins (no !important) */
            }
            /* ── Left-freeze widths (left offsets are computed + set by JS after render) ── */
            .sc-chk  { left:   0px; min-width:  50px; border-right: 1px solid #d1d5db; }
            .sc-code { min-width: 150px; border-right: 1px solid #d1d5db; }   /* left set by JS */
            .sc-name { min-width: 250px; box-shadow: 4px 0 6px -2px rgba(0,0,0,.10); cursor: pointer; } /* left set by JS */
            /* ── Right-freeze ── */
            .sc-act  { right: 0px; min-width: 110px; box-shadow: -4px 0 6px -2px rgba(0,0,0,.10); }
            #assetsTable thead th.sc-act { background-color: #f3f4f6 !important; }
        </style>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-4 py-3">

                {{-- DataTables length + filter will be floated here by DataTables --}}
                <table id="assetsTable" class="w-full text-sm">
                    <thead style="background-color:#f3f4f6; color:#374151;">
                        <tr>
                            <th class="sc sc-chk text-center" style="width:50px;min-width:50px"><input type="checkbox" id="selectAll"></th>
                            <th class="sc sc-code text-left" style="width:150px;min-width:150px">Asset Code</th>
                            <th class="sc sc-name text-left" style="width:250px;min-width:250px">Asset</th>
                            <th style="min-width:130px" class="text-left">Branch</th>
                            <th style="min-width:120px" class="text-left">Grouping</th>
                            <th style="min-width:160px" class="text-left">User</th>
                            <th style="min-width:140px" class="text-left">Department</th>
                            <th style="min-width:120px" class="text-left">Venue</th>
                            <th style="min-width:120px" class="text-left">Purchase Date</th>
                            <th style="min-width:100px" class="text-right">Cost (RM)</th>
                            <th style="min-width:70px"  class="text-center">UOM</th>
                            <th style="min-width:150px" class="text-left">Supplier</th>
                            <th style="min-width:120px" class="text-left">Invoice No</th>
                            <th style="min-width:80px"  class="text-center">Warranty</th>
                            <th style="min-width:110px" class="text-center">Warranty Period</th>
                            <th style="min-width:90px"  class="text-center">Lifespan</th>
                            <th style="min-width:110px" class="text-center">Svc Interval</th>
                            <th class="sc sc-act text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assets as $asset)
                            <tr class="asset-row-hover"
                                data-asset-log="{{ $asset->asset_log ?? 'No log available' }}">

                                {{-- ── FROZEN LEFT ── --}}
                                <td class="sc sc-chk text-center">
                                    <input type="checkbox" class="asset-checkbox" value="{{ $asset->id }}"
                                        data-name="{{ $asset->asset_name }}"
                                        data-no="{{ $asset->asset_no }}"
                                        data-cost="{{ $asset->asset_cost ?? 0 }}"
                                        data-branch-id="{{ $asset->branch_id }}"
                                        data-branch-name="{{ $asset->branch->branch_name ?? '' }}">
                                </td>

                                <td class="sc sc-code font-medium">
                                    <div class="flex items-center gap-1">
                                        @if($asset->color)
                                            <span class="flex-shrink-0 rounded-sm" style="width:10px;height:10px;display:inline-block;background-color:{{ $asset->color }};border:1px solid rgba(0,0,0,0.15);"></span>
                                        @endif
                                        {{ $asset->asset_no }}
                                    </div>
                                </td>

                                <td class="sc sc-name">
                                    <div class="flex items-center gap-2">
                                        @if ($asset->asset_image)
                                            <img src="{{ asset('storage/' . $asset->asset_image) }}"
                                                class="w-10 h-10 object-cover rounded border cursor-pointer hover:opacity-80 asset-image-preview flex-shrink-0"
                                                data-image="{{ asset('storage/' . $asset->asset_image) }}"
                                                data-name="{{ $asset->asset_name }}">
                                        @else
                                            <div class="w-10 h-10 bg-gray-200 rounded border flex items-center justify-center text-gray-400 flex-shrink-0">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="flex items-center gap-1 flex-wrap">
                                                <span class="font-medium">{{ $asset->asset_name ?? '-' }}</span>
                                                @if ($asset->dispose_status === 'pending')
                                                    <span class="px-1.5 py-0.5 text-xs bg-orange-100 text-orange-600 rounded-full">Pending</span>
                                                @elseif ($asset->is_disposed)
                                                    <span class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">Disposed</span>
                                                @elseif (!$asset->user_id && !$asset->department_id)
                                                    <span class="px-1.5 py-0.5 text-xs bg-teal-100 text-teal-700 rounded-full">Free Stock</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- ── SCROLLABLE ── --}}
                                <td>{{ $asset->branch->branch_name ?? '-' }}</td>
                                <td>{{ $asset->group->name ?? '-' }}</td>
                                <td>{{ $asset->user->name ?? '-' }}</td>
                                <td>{{ $asset->department->name ?? '-' }}</td>
                                <td>{{ $asset->venue ?? '-' }}</td>
                                <td>{{ $asset->asset_purchase_date ? \Carbon\Carbon::parse($asset->asset_purchase_date)->format('d/m/Y') : '-' }}</td>
                                <td class="text-right">{{ $asset->asset_cost ? number_format($asset->asset_cost, 2) : '-' }}</td>
                                <td class="text-center">{{ $asset->asset_uom ?? '-' }}</td>
                                <td>{{ $asset->supplier->name ?? '-' }}</td>
                                <td>{{ $asset->inv_no ?? '-' }}</td>
                                <td class="text-center">{{ $asset->has_warranty ? 'Yes' : 'No' }}</td>
                                <td class="text-center">{{ $asset->warranty_period ? $asset->warranty_period . ' mo' : '-' }}</td>
                                <td class="text-center">{{ $asset->asset_lifespan ? $asset->asset_lifespan . ' yr' : '-' }}</td>
                                <td class="text-center">{{ $asset->asset_service_interval ? $asset->asset_service_interval . ' mo' : '-' }}</td>

                                {{-- ── FROZEN RIGHT ── --}}
                                <td class="sc sc-act text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- View Details --}}
                                        <a href="{{ route('asset.view', $asset->id) }}" title="View Details"
                                            class="text-blue-500 hover:text-blue-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        @if (auth()->user()->accessLevel?->add_edit_asset && !$asset->is_disposed)
                                            {{-- Edit --}}
                                            <button onclick='openEditModal(@json($asset))' title="Edit"
                                                class="text-blue-500 hover:text-blue-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            {{-- Duplicate --}}
                                            <button onclick='openDuplicateModal(@json($asset))' title="Duplicate"
                                                class="text-purple-500 hover:text-purple-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                            @if (!$asset->is_disposed && (!$asset->dispose_status || $asset->dispose_status === 'rejected'))
                                                {{-- Dispose --}}
                                                <button onclick='openDisposeModal(@json($asset))' title="Dispose"
                                                    class="text-yellow-500 hover:text-yellow-700">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        @endif
                                        @if ($asset->dispose_status === 'pending' && auth()->user()->accessLevel?->approve_disaprove_dispose)
                                            {{-- Review Disposal --}}
                                            <button onclick='openApprovalModal(@json($asset))' title="Review Disposal"
                                                class="text-green-500 hover:text-green-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ADD MODAL - Updated form tag --}}
    <div id="addModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 overflow-auto">
        <div class="bg-white w-full max-w-5xl rounded shadow p-6 mx-4 my-8 relative">
            <h3 class="text-lg font-semibold mb-4">Add Asset</h3>

            {{-- Error container for Add form --}}
            <div id="addFormErrors"
                class="hidden mb-4 px-4 py-3 rounded-md bg-red-100 border border-red-400 text-red-800">
                <ul class="list-disc list-inside text-sm" id="addFormErrorList"></ul>
            </div>

            <form id="addForm" action="{{ route('asset.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('assetModule.asset.partials.form', ['asset' => null, 'isEdit' => false])
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeAddModal()"
                        class="px-4 py-2 text-sm border rounded">Cancel</button>
                    <button type="submit" id="addFormSubmitBtn"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded">Save</button>
                </div>
            </form>

        </div>
    </div>

    {{-- EDIT MODAL - Updated form tag --}}
    <div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 overflow-auto">
        <div class="bg-white w-full max-w-5xl rounded shadow p-6 mx-4 my-8 relative">
            <h3 class="text-lg font-semibold mb-4">Edit Asset</h3>

            {{-- Error container for Edit form --}}
            <div id="editFormErrors"
                class="hidden mb-4 px-4 py-3 rounded-md bg-red-100 border border-red-400 text-red-800">
                <ul class="list-disc list-inside text-sm" id="editFormErrorList"></ul>
            </div>

            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('assetModule.asset.partials.form', ['asset' => null, 'isEdit' => true])
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 text-sm border rounded">Cancel</button>
                    <button type="submit" id="editFormSubmitBtn"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded">Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- CHANGE COLOR MODAL --}}
    <div id="changeColorModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 overflow-auto">
        <div class="bg-white w-full max-w-md rounded shadow p-6 mx-4 relative">
            <h3 class="text-lg font-semibold mb-4">Change Color</h3>
            <p class="text-sm text-gray-500 mb-4">
                Apply color to <span id="changeColorCount" class="font-semibold">0</span> selected asset(s).
            </p>
            <div class="mb-4">
                <label class="block mb-1 text-sm font-medium">Select Color</label>
                <select id="bulkColorSelect" class="w-full border rounded px-3 py-2">
                    <option value="">-- No Color --</option>
                    @php
                        $presetColors = [
                            '#ef4444' => 'Red',
                            '#f97316' => 'Orange',
                            '#eab308' => 'Yellow',
                            '#22c55e' => 'Green',
                            '#06b6d4' => 'Cyan',
                            '#3b82f6' => 'Blue',
                            '#8b5cf6' => 'Purple',
                            '#ec4899' => 'Pink',
                            '#6b7280' => 'Gray',
                            '#000000' => 'Black',
                            '#ffffff' => 'White',
                            '#92400e' => 'Brown',
                        ];
                    @endphp
                    @foreach ($presetColors as $hex => $name)
                        <option value="{{ $hex }}"
                            style="background-color: {{ $hex }}; color: {{ in_array($hex, ['#000000', '#8b5cf6', '#3b82f6', '#ef4444', '#22c55e', '#92400e']) ? '#fff' : '#000' }}">
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeChangeColorModal()"
                    class="px-4 py-2 text-sm border rounded">Cancel</button>
                <button type="button" onclick="submitBulkColorChange()"
                    class="px-4 py-2 bg-purple-600 text-white text-sm rounded hover:bg-purple-700">Apply</button>
            </div>
        </div>
    </div>

    {{-- CHANGE USER/DEPT MODAL --}}
    <div id="changeUserDeptModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 overflow-auto">
        <div class="bg-white w-full max-w-md rounded shadow p-6 mx-4 relative">
            <h3 class="text-lg font-semibold mb-4">Change User / Department</h3>
            <p class="text-sm text-gray-500 mb-4">
                Apply to <span id="changeUserDeptCount" class="font-semibold">0</span> selected asset(s).
            </p>
            <div class="mb-4 relative">
                <label class="block mb-1 text-sm font-medium">User</label>
                <input type="text" id="bulkUserInput" class="w-full border rounded px-3 py-2 text-sm"
                    placeholder="Type to search" autocomplete="off">
                <input type="hidden" id="bulkUserIdHidden">
                <div id="bulkUserDropdown"
                    class="hidden absolute z-10 w-full bg-white border rounded shadow max-h-40 overflow-y-auto mt-1">
                </div>
            </div>
            <div class="mb-4">
                <label class="block mb-1 text-sm font-medium">Department</label>
                <select id="bulkDeptSelect" class="w-full border rounded px-3 py-2">
                    <option value="">-- No Change --</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-between gap-2">
                <button type="button" onclick="submitBulkClearUserDept()"
                    class="px-4 py-2 text-sm border border-red-300 text-red-600 rounded hover:bg-red-50">Clear
                    User/Dept</button>
                <div class="flex gap-2">
                    <button type="button" onclick="closeChangeUserDeptModal()"
                        class="px-4 py-2 text-sm border rounded">Cancel</button>
                    <button type="button" onclick="submitBulkUserDeptChange()"
                        class="px-4 py-2 bg-teal-600 text-white text-sm rounded hover:bg-teal-700">Apply</button>
                </div>
            </div>
        </div>
    </div>

    {{-- TRANSFER MODAL --}}
    <div id="transferModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 overflow-auto">
        <div class="bg-white w-full max-w-5xl rounded shadow p-6 mx-4 my-8 relative">

            <h3 class="text-lg font-semibold mb-4">Create Asset Transfer</h3>

            <form id="transferForm" method="POST" action="{{ route('assetTransfer.store') }}"
                enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="asset_ids" id="assetIdsInput">

                {{-- ONLY transfer fields --}}
                @include('assetModule.asset.partials.transfer')

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeTransferModal()" class="px-4 py-2 text-sm border rounded">
                        Cancel
                    </button>

                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded">
                        Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- DISPOSE MODAL --}}
    <div id="disposeModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 overflow-auto">
        <div class="bg-white w-full max-w-md rounded shadow p-6 mx-4 my-8 relative">
            <h3 class="text-lg font-semibold mb-4">Request Asset Disposal</h3>

            <form id="disposeForm" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">
                        Asset: <strong id="disposeAssetNo"></strong> - <span id="disposeAssetName"></span>
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Disposal *</label>
                    <textarea name="dispose_remark" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Enter reason for disposal..." required></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attachment (Optional)</label>
                    <input type="file" name="dispose_attachment" accept="image/*,.pdf"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Max 5MB (JPEG, PNG, JPG, GIF, PDF)</p>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeDisposeModal()"
                        class="px-4 py-2 text-sm border rounded hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700">Submit
                        Request</button>
                </div>
            </form>
        </div>
    </div>

    {{-- APPROVAL MODAL --}}
    <div id="approvalModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 overflow-auto">
        <div class="bg-white w-full max-w-lg rounded shadow p-6 mx-4 my-8 relative">
            <h3 class="text-lg font-semibold mb-4">Review Disposal Request</h3>

            <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">
                    Asset: <strong id="approvalAssetNo"></strong> - <span id="approvalAssetName"></span>
                </p>
                <p class="text-sm text-gray-600 mb-1">
                    Requested by: <strong id="approvalRequestedBy"></strong>
                </p>
                <p class="text-sm text-gray-600 mb-1">
                    Date: <strong id="approvalDate"></strong>
                </p>
                <div class="mt-2">
                    <p class="text-sm font-medium text-gray-700">Reason:</p>
                    <p class="text-sm text-gray-600" id="approvalReason"></p>
                </div>
                <div class="mt-2" id="approvalAttachmentContainer" style="display: none;">
                    <p class="text-sm font-medium text-gray-700">Attachment:</p>
                    <a id="approvalAttachment" href="#" target="_blank"
                        class="text-blue-600 hover:underline text-sm">View Attachment</a>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Remark (Required for rejection)</label>
                <textarea id="approvalRemarkInput" rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Enter remark..."></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeApprovalModal()"
                    class="px-4 py-2 text-sm border rounded hover:bg-gray-50">Cancel</button>
                <form id="rejectForm" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="approval_remark" id="rejectRemarkHidden">
                    <button type="submit" onclick="return submitReject()"
                        class="px-4 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">Reject</button>
                </form>
                <form id="approveForm" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="approval_remark" id="approveRemarkHidden">
                    <button type="submit" onclick="return submitApprove()"
                        class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">Approve</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.default.min.css" rel="stylesheet">

    <script>
        // Track selected asset IDs persistently across DataTable redraws
        window.selectedAssetIds = new Set();

        // Custom DataTable search plugin: always show selected rows
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'assetsTable') return true;
            var row = settings.aoData[dataIndex].nTr;
            if (row) {
                var cb = row.querySelector('.asset-checkbox');
                if (cb && window.selectedAssetIds.has(cb.value)) {
                    return true; // Always show selected rows
                }
            }
            return true; // Let DataTable's default search handle the rest
        });

        // Initialize DataTable
        $(document).ready(function() {
            var table = $('#assetsTable').DataTable({
                responsive: false,
                autoWidth: false,
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                order: [],
                columnDefs: [
                    // Explicit widths so DataTables' <col> elements match header+body consistently
                    { width: '50px',  targets: 0  },
                    { width: '150px', targets: 1  },
                    { width: '250px', targets: 2  },
                    { width: '110px', targets: 17 },
                    { orderable: false, searchable: false, targets: [0, 17] }
                ],
                language: {
                    search: "Search Asset:",
                    lengthMenu: "Show _MENU_ entries",
                    emptyTable: "No assets found."
                },
                drawCallback: function() {
                    // Re-bind checkboxes after DataTable redraw
                    bindCheckboxes();

                    // Highlight selected rows
                    document.querySelectorAll('.asset-checkbox').forEach(cb => {
                        if (window.selectedAssetIds.has(cb.value)) {
                            cb.closest('tr').classList.add('bg-blue-50');
                        } else {
                            cb.closest('tr').classList.remove('bg-blue-50');
                        }
                    });

                    // Re-apply sticky left offsets to newly drawn body rows
                    // (initComplete sets them for the first page; drawCallback handles
                    //  subsequent pages, search results, and length changes)
                    if (window._stickyW0 !== undefined) {
                        document.querySelectorAll('#assetsTable tbody td.sc-code').forEach(function(el) {
                            el.style.left = window._stickyW0 + 'px';
                        });
                        document.querySelectorAll('#assetsTable tbody td.sc-name').forEach(function(el) {
                            el.style.left = (window._stickyW0 + window._stickyW1) + 'px';
                        });
                    }
                },

                initComplete: function() {
                    // ── Step 1: wrap ONLY the <table> in one scroll container.
                    $('#assetsTable').wrap('<div id="tbl-scroll"></div>');

                    // ── Step 2: fit the scroll container to the remaining viewport height
                    // so the horizontal scrollbar is always visible without page-scrolling.
                    function fitTableHeight() {
                        var el = document.getElementById('tbl-scroll');
                        if (!el) return;

                        // Unconstrain temporarily so siblings below settle to natural height
                        el.style.maxHeight = '99999px';

                        var rect    = el.getBoundingClientRect();
                        var dtWrap  = el.parentElement;                  // dataTables_wrapper
                        var card    = el.closest('.bg-white');           // outer card

                        // Height of DT info + pagination rows below #tbl-scroll
                        var belowDT   = dtWrap.getBoundingClientRect().bottom - rect.bottom;
                        // Card bottom padding / border below the wrapper
                        var belowCard = card
                            ? card.getBoundingClientRect().bottom - dtWrap.getBoundingClientRect().bottom
                            : 0;

                        var available = window.innerHeight - rect.top - belowDT - belowCard - 24;
                        el.style.maxHeight = Math.max(200, available) + 'px';
                    }
                    fitTableHeight();
                    window.addEventListener('resize', fitTableHeight);

                    // ── Step 2: measure ACTUAL rendered column widths with offsetWidth
                    // (integer pixels, includes padding + border, triggers layout flush).
                    // Store globally so drawCallback can re-apply on every page/search draw.
                    var ths = document.querySelectorAll('#assetsTable thead th');
                    if (ths.length >= 3) {
                        var w0 = ths[0].offsetWidth; // checkbox column
                        var w1 = ths[1].offsetWidth; // asset-code column
                        window._stickyW0 = w0;
                        window._stickyW1 = w1;
                        document.querySelectorAll('.sc-code').forEach(function(el) {
                            el.style.left = w0 + 'px';
                        });
                        document.querySelectorAll('.sc-name').forEach(function(el) {
                            el.style.left = (w0 + w1) + 'px';
                        });
                    }
                }
            });

            // Override default search to keep selected rows visible
            var defaultSearch = table.search;
            $('#assetsTable_filter input').off('keyup search input').on('keyup', function() {
                var searchTerm = this.value.toLowerCase();

                // Clear default search
                table.search('');

                // Custom filtering: show matching rows + selected rows
                $.fn.dataTable.ext.search.pop(); // Remove previous custom filter
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'assetsTable') return true;

                    var row = settings.aoData[dataIndex].nTr;
                    if (row) {
                        var cb = row.querySelector('.asset-checkbox');
                        // Always show selected rows
                        if (cb && window.selectedAssetIds.has(cb.value)) {
                            return true;
                        }
                    }

                    // If no search term, show all
                    if (!searchTerm) return true;

                    // Check if any searchable column matches
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].toLowerCase().indexOf(searchTerm) !== -1) {
                            return true;
                        }
                    }
                    return false;
                });

                table.draw();
            });
        });

        // Rebind checkboxes after DataTable operations
        function bindCheckboxes() {
            const selectAll = document.getElementById('selectAll');
            const assetCheckboxes = document.querySelectorAll('.asset-checkbox');
            const transferBtn = document.getElementById('transferSelectedBtn');
            const changeColorBtn = document.getElementById('changeColorBtn');
            const changeUserDeptBtn = document.getElementById('changeUserDeptBtn');

            // Restore checked state from persistent Set
            assetCheckboxes.forEach(cb => {
                if (window.selectedAssetIds.has(cb.value)) {
                    cb.checked = true;
                }
            });

            // Update select all checkbox state
            if (selectAll) {
                const visibleCheckboxes = [...assetCheckboxes].filter(cb => cb.closest('tr').style.display !== 'none');
                const allChecked = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked);
                selectAll.checked = allChecked;
            }

            // Update button visibility based on persistent Set
            if (transferBtn) {
                transferBtn.classList.toggle('hidden', window.selectedAssetIds.size === 0);
            }
            if (changeColorBtn) {
                changeColorBtn.classList.toggle('hidden', window.selectedAssetIds.size === 0);
            }
            if (changeUserDeptBtn) {
                changeUserDeptBtn.classList.toggle('hidden', window.selectedAssetIds.size === 0);
            }

            // Re-attach change listeners to checkboxes
            assetCheckboxes.forEach(cb => {
                // Remove old listener by cloning
                const newCb = cb.cloneNode(true);
                cb.parentNode.replaceChild(newCb, cb);

                newCb.addEventListener('change', function() {
                    if (this.checked) {
                        const selectedBranchId = this.dataset.branchId;
                        const selectedBranchName = this.dataset.branchName;

                        // Check branch consistency against already selected assets
                        if (window.selectedAssetIds.size > 0) {
                            // Find branch of first selected asset from all DataTable rows
                            let firstBranchId = null;
                            let firstBranchName = null;
                            const table = $('#assetsTable').DataTable();
                            table.rows().every(function() {
                                const row = this.node();
                                const otherCb = row.querySelector('.asset-checkbox');
                                if (otherCb && window.selectedAssetIds.has(otherCb.value)) {
                                    firstBranchId = otherCb.dataset.branchId;
                                    firstBranchName = otherCb.dataset.branchName;
                                    return false; // break
                                }
                            });

                            if (firstBranchId && selectedBranchId !== firstBranchId) {
                                this.checked = false;
                                alert(
                                    `Cannot select assets from different branches.\nCurrently selected: ${firstBranchName}\nYou tried to add: ${selectedBranchName}`
                                );
                                return;
                            }
                        }

                        window.selectedAssetIds.add(this.value);
                        this.closest('tr').classList.add('bg-blue-50');
                    } else {
                        window.selectedAssetIds.delete(this.value);
                        this.closest('tr').classList.remove('bg-blue-50');
                    }

                    // Update action buttons
                    if (transferBtn) {
                        transferBtn.classList.toggle('hidden', window.selectedAssetIds.size === 0);
                    }
                    if (changeColorBtn) {
                        changeColorBtn.classList.toggle('hidden', window.selectedAssetIds.size === 0);
                    }
                    if (changeUserDeptBtn) {
                        changeUserDeptBtn.classList.toggle('hidden', window.selectedAssetIds.size === 0);
                    }

                    // Update select all state
                    if (selectAll) {
                        const visibleCbs = document.querySelectorAll('.asset-checkbox');
                        const allChecked = visibleCbs.length > 0 && [...visibleCbs].every(c => c.checked);
                        selectAll.checked = allChecked;
                    }
                });
            });
        }

        // AJAX Form Submission Handler
        function submitFormAjax(form, errorsContainer, errorsList, submitBtn, successCallback) {
            const formData = new FormData(form);
            const url = form.action;
            const originalBtnText = submitBtn.innerHTML;

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML =
                '<svg class="animate-spin h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';

            // Hide previous errors
            errorsContainer.classList.add('hidden');
            errorsList.innerHTML = '';

            fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    }
                    return response.json().then(data => {
                        throw {
                            status: response.status,
                            data: data
                        };
                    });
                })
                .then(data => {
                    if (data.success) {
                        // Success - reload the page
                        if (successCallback) successCallback(data);
                        window.location.reload();
                    } else if (data.errors) {
                        // Show errors
                        displayErrors(data.errors, errorsContainer, errorsList);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                })
                .catch(error => {
                    console.error('Form submission error:', error);
                    if (error.data && error.data.errors) {
                        displayErrors(error.data.errors, errorsContainer, errorsList);
                    } else if (error.data && error.data.message) {
                        errorsList.innerHTML = `<li>${error.data.message}</li>`;
                        errorsContainer.classList.remove('hidden');
                    } else {
                        errorsList.innerHTML = '<li>An unexpected error occurred. Please try again.</li>';
                        errorsContainer.classList.remove('hidden');
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
        }

        function displayErrors(errors, container, list) {
            list.innerHTML = '';
            if (typeof errors === 'object') {
                Object.keys(errors).forEach(key => {
                    const messages = Array.isArray(errors[key]) ? errors[key] : [errors[key]];
                    messages.forEach(msg => {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        list.appendChild(li);
                    });
                });
            } else if (typeof errors === 'string') {
                const li = document.createElement('li');
                li.textContent = errors;
                list.appendChild(li);
            }
            container.classList.remove('hidden');
            // Scroll to errors
            container.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        // Add Form AJAX submission
        document.getElementById('addForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitFormAjax(
                this,
                document.getElementById('addFormErrors'),
                document.getElementById('addFormErrorList'),
                document.getElementById('addFormSubmitBtn')
            );
        });

        // Edit Form AJAX submission
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitFormAjax(
                this,
                document.getElementById('editFormErrors'),
                document.getElementById('editFormErrorList'),
                document.getElementById('editFormSubmitBtn')
            );
        });

        // Dispose Modal Functions
        window.openDisposeModal = function(asset) {
            const form = document.getElementById('disposeForm');
            form.action = `/asset/${asset.id}/request-disposal`;

            document.getElementById('disposeAssetNo').textContent = asset.asset_no;
            document.getElementById('disposeAssetName').textContent = asset.asset_name || '-';

            // Reset form
            form.querySelector('textarea[name="dispose_remark"]').value = '';
            form.querySelector('input[name="dispose_attachment"]').value = '';

            const modal = document.getElementById('disposeModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        window.closeDisposeModal = function() {
            const modal = document.getElementById('disposeModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        // Approval Modal Functions
        window.openApprovalModal = function(asset) {
            document.getElementById('approvalAssetNo').textContent = asset.asset_no;
            document.getElementById('approvalAssetName').textContent = asset.asset_name || '-';
            document.getElementById('approvalRequestedBy').textContent = asset.dispose_requester?.name || '-';
            document.getElementById('approvalDate').textContent = asset.dispose_date ? new Date(asset.dispose_date)
                .toLocaleDateString() : '-';
            document.getElementById('approvalReason').textContent = asset.dispose_remark || '-';

            // Handle attachment
            const attachmentContainer = document.getElementById('approvalAttachmentContainer');
            const attachmentLink = document.getElementById('approvalAttachment');
            if (asset.dispose_attachment) {
                attachmentContainer.style.display = 'block';
                attachmentLink.href = `/storage/${asset.dispose_attachment}`;
            } else {
                attachmentContainer.style.display = 'none';
            }

            // Set form actions
            document.getElementById('approveForm').action = `/asset/${asset.id}/approve-disposal`;
            document.getElementById('rejectForm').action = `/asset/${asset.id}/reject-disposal`;

            // Reset remark
            document.getElementById('approvalRemarkInput').value = '';

            const modal = document.getElementById('approvalModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        window.closeApprovalModal = function() {
            const modal = document.getElementById('approvalModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        window.submitApprove = function() {
            const remark = document.getElementById('approvalRemarkInput').value;
            document.getElementById('approveRemarkHidden').value = remark;
            return confirm('Are you sure you want to approve this disposal?');
        };

        window.submitReject = function() {
            const remark = document.getElementById('approvalRemarkInput').value;
            if (!remark.trim()) {
                alert('Please enter a reason for rejection');
                return false;
            }
            document.getElementById('rejectRemarkHidden').value = remark;
            return confirm('Are you sure you want to reject this disposal?');
        };

        // Image Preview Functions
        function openTableImagePreview(imageSrc, imageName) {
            console.log('Opening image preview:', imageSrc, imageName);
            const modal = document.getElementById('imagePreviewModal');
            const img = document.getElementById('previewImage');
            const nameEl = document.getElementById('previewImageName');

            if (!modal) {
                console.error('Modal not found!');
                return;
            }

            img.src = imageSrc;
            img.alt = imageName;
            nameEl.textContent = imageName;

            modal.style.display = 'flex';
            console.log('Modal opened');
        }

        function closeImagePreview() {
            const modal = document.getElementById('imagePreviewModal');
            modal.style.display = 'none';
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImagePreview();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {

            // Add click event listeners to all asset images
            document.querySelectorAll('.asset-image-preview').forEach(img => {
                img.addEventListener('click', function() {
                    console.log('Image clicked!');
                    const imageSrc = this.getAttribute('data-image');
                    const imageName = this.getAttribute('data-name');
                    openTableImagePreview(imageSrc, imageName);
                });
            });

            /* ===============================
               Tom Select
            =============================== */
            document.querySelectorAll('.searchable-select').forEach(select => {
                new TomSelect(select, {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });
            });

            /* ===============================
               Generic modal helpers
            =============================== */
            function openModal(id) {
                const modal = document.getElementById(id);
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal(id) {
                const modal = document.getElementById(id);
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            window.openAddModal = function() {
                const form = document.getElementById('addForm');

                // Reset all text inputs
                form.querySelectorAll('input[type="text"], input[type="number"], input[type="date"]').forEach(
                    input => {
                        input.value = '';
                    });

                // Reset file input
                const fileInput = form.querySelector('input[type="file"]');
                if (fileInput) fileInput.value = '';

                // Reset select elements
                form.querySelectorAll('select').forEach(select => {
                    if (select.tomselect) {
                        select.tomselect.clear();
                    } else {
                        select.value = '';
                    }
                });

                openModal('addModal');
            };
            window.closeAddModal = () => closeModal('addModal');

            window.openDuplicateModal = function(asset) {
                // First reset the add form
                const form = document.getElementById('addForm');
                form.querySelectorAll('input[type="text"], input[type="number"], input[type="date"]').forEach(
                    input => {
                        input.value = '';
                    });
                const fileInput = form.querySelector('input[type="file"]');
                if (fileInput) fileInput.value = '';
                form.querySelectorAll('select').forEach(select => {
                    if (select.tomselect) {
                        select.tomselect.clear();
                    } else {
                        select.value = '';
                    }
                });

                // Pre-fill with asset data (leave asset_no blank since it must be unique)
                [
                    'old_asset_no', 'asset_name', 'asset_uom', 'asset_cost', 'inv_no',
                    'warranty_period', 'asset_lifespan', 'asset_service_interval', 'venue'
                ].forEach(name => {
                    const input = form.querySelector(`[name="${name}"]`);
                    if (input) input.value = asset[name] ?? '';
                });

                // Handle date input
                const dateInput = form.querySelector('[name="asset_purchase_date"]');
                if (dateInput) {
                    dateInput.value = asset.asset_purchase_date ? asset.asset_purchase_date.split('T')[0] : '';
                }

                // Handle warranty select
                const warrantySelect = form.querySelector('[name="has_warranty"]');
                if (warrantySelect) {
                    warrantySelect.value = (asset.has_warranty == 1 || asset.has_warranty === true) ? '1' :
                        (asset.has_warranty == 0 || asset.has_warranty === false) ? '0' : '';
                }

                // Handle TomSelect dropdowns
                ['group_id', 'branch_id', 'supplier_id', 'user_id', 'department_id'].forEach(name => {
                    const select = form.querySelector(`[name="${name}"]`);
                    if (select?.tomselect) select.tomselect.setValue(asset[name] ?? '');
                });

                // Handle color select
                const colorSelect = form.querySelector('[name="color"]');
                if (colorSelect) colorSelect.value = asset.color || '';

                openModal('addModal');
            };

            window.openEditModal = function(asset) {
                const form = document.getElementById('editForm');
                form.action = `/asset/${asset.id}`;

                // Populate text inputs
                [
                    'asset_no', 'old_asset_no', 'asset_name', 'asset_uom', 'asset_cost', 'inv_no',
                    'warranty_period', 'asset_lifespan', 'asset_service_interval', 'venue'
                ].forEach(name => {
                    const input = form.querySelector(`[name="${name}"]`);
                    if (input) input.value = asset[name] ?? '';
                });

                // Handle date input
                const dateInput = form.querySelector('[name="asset_purchase_date"]');
                if (dateInput) {
                    dateInput.value = asset.asset_purchase_date ? asset.asset_purchase_date.split('T')[0] : '';
                }

                // Handle warranty select
                const warrantySelect = form.querySelector('[name="has_warranty"]');
                if (warrantySelect) {
                    warrantySelect.value = (asset.has_warranty == 1 || asset.has_warranty === true) ? '1' :
                        (asset.has_warranty == 0 || asset.has_warranty === false) ? '0' : '';
                }

                // Handle TomSelect dropdowns (by ID)
                ['group_id', 'branch_id', 'supplier_id', 'user_id', 'department_id'].forEach(name => {
                    const select = form.querySelector(`[name="${name}"]`);
                    if (select?.tomselect) select.tomselect.setValue(asset[name] ?? '');
                });

                // Handle color select
                const colorSelect = form.querySelector('[name="color"]');
                if (colorSelect) colorSelect.value = asset.color || '';

                // Handle existing image display in edit modal
                const editForm = document.getElementById('editForm');
                const imageContainer = editForm.querySelector('#existingImageContainer');
                const existingImage = editForm.querySelector('#existingImage');
                const replaceText = editForm.querySelector('#replaceImageText');
                // const deleteBtn = editForm.querySelector('#deleteImageBtn');

                if (asset.asset_image) {
                    existingImage.src = `/storage/${asset.asset_image}`;
                    imageContainer.classList.remove('hidden');
                    replaceText.classList.remove('hidden');

                    // Set up delete button - store asset id for use in handler
                    // deleteBtn.dataset.assetId = asset.id;
                    // deleteBtn.onclick = function(e) {
                    //     e.preventDefault();
                    //     e.stopPropagation();

                    //     const assetId = this.dataset.assetId;
                    //     if (confirm('Are you sure you want to delete this image?')) {
                    //         fetch(`/asset/${assetId}/delete-image`, {
                    //             method: 'DELETE',
                    //             headers: {
                    //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    //                 'Accept': 'application/json',
                    //                 'Content-Type': 'application/json',
                    //             }
                    //         })
                    //         .then(response => {
                    //             if (!response.ok) {
                    //                 throw new Error(`HTTP error! status: ${response.status}`);
                    //             }
                    //             return response.json();
                    //         })
                    //         .then(data => {
                    //             if (data.success) {
                    //                 imageContainer.classList.add('hidden');
                    //                 replaceText.classList.add('hidden');
                    //                 existingImage.src = '';
                    //                 alert('Image deleted successfully');
                    //             } else {
                    //                 alert(data.message || 'Error deleting image');
                    //             }
                    //         })
                    //         .catch(error => {
                    //             console.error('Delete image error:', error);
                    //             alert('Error deleting image: ' + error.message);
                    //         });
                    //     }
                    // };
                } else {
                    imageContainer.classList.add('hidden');
                    replaceText.classList.add('hidden');
                    existingImage.src = '';
                }

                // Reset file input
                const fileInput = form.querySelector('input[type="file"]');
                if (fileInput) fileInput.value = '';

                openModal('editModal');
            };
            window.closeEditModal = () => closeModal('editModal');

            /* ===============================
               Transfer logic with dynamic add/remove
            =============================== */
            window.assetRowCounter = 0;

            const selectAll = document.getElementById('selectAll');
            const transferBtn = document.getElementById('transferSelectedBtn');
            const transferAssetsTable = document.getElementById('transferAssetsTable');
            const transferTotalCost = document.getElementById('transferTotalCost');

            // Select All checkbox handler
            selectAll?.addEventListener('change', function() {
                const visibleCheckboxes = document.querySelectorAll('.asset-checkbox');
                visibleCheckboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                    if (selectAll.checked) {
                        window.selectedAssetIds.add(cb.value);
                    } else {
                        window.selectedAssetIds.delete(cb.value);
                    }
                });
                const hasSelected = window.selectedAssetIds.size > 0;
                if (transferBtn) {
                    transferBtn.classList.toggle('hidden', !hasSelected);
                }
                const changeColorBtn2 = document.getElementById('changeColorBtn');
                if (changeColorBtn2) {
                    changeColorBtn2.classList.toggle('hidden', !hasSelected);
                }
                const changeUserDeptBtn2 = document.getElementById('changeUserDeptBtn');
                if (changeUserDeptBtn2) {
                    changeUserDeptBtn2.classList.toggle('hidden', !hasSelected);
                }
            });

            // Add asset row to the table with autocomplete
            window.addAssetRow = function() {
                window.assetRowCounter++;
                const row = document.createElement('tr');
                row.id = `asset-row-${window.assetRowCounter}`;
                row.innerHTML = `
                    <td class="border px-2 py-1 text-center">${window.assetRowCounter}</td>
                    <td class="border px-2 py-1 relative">
                        <input type="text" 
                            name="assets[${window.assetRowCounter}][asset_no]" 
                            class="w-full px-2 py-1 border rounded text-sm asset-no-input" 
                            placeholder="Type to search Asset No" 
                            data-row="${window.assetRowCounter}"
                            autocomplete="off"
                            required>
                        <input type="hidden" name="assets[${window.assetRowCounter}][asset_id]" class="asset-id-hidden">
                        <div class="autocomplete-dropdown hidden" id="dropdown-no-${window.assetRowCounter}"></div>
                    </td>
                    <td class="border px-2 py-1 relative">
                        <input type="text" 
                            name="assets[${window.assetRowCounter}][asset_name]" 
                            class="w-full px-2 py-1 border rounded text-sm asset-name-input" 
                            placeholder="Type to search Asset Name" 
                            data-row="${window.assetRowCounter}"
                            autocomplete="off"
                            required>
                        <div class="autocomplete-dropdown hidden" id="dropdown-name-${window.assetRowCounter}"></div>
                    </td>
                    <td class="border px-2 py-1">
                        <input type="number" 
                            name="assets[${window.assetRowCounter}][asset_cost]" 
                            step="0.01" 
                            class="w-full px-2 py-1 border rounded text-sm asset-cost-input" 
                            placeholder="0.00" 
                            oninput="updateTotalCost()" 
                            required>
                    </td>
                    <td class="border px-2 py-1 text-center">
                        <button type="button" onclick="removeAssetRow(${window.assetRowCounter})" 
                            class="text-red-600 hover:text-red-800 text-sm">
                            Remove
                        </button>
                    </td>
                `;

                transferAssetsTable.appendChild(row);
                setupAutocomplete(row);
                updateTotalCost();
            };

            // Remove asset row
            window.removeAssetRow = function(rowId) {
                const row = document.getElementById(`asset-row-${rowId}`);
                if (row) {
                    row.remove();
                    updateTotalCost();
                }
            };

            // Update total cost
            window.updateTotalCost = function() {
                const costInputs = document.querySelectorAll('.asset-cost-input');
                let total = 0;
                costInputs.forEach(input => {
                    const value = parseFloat(input.value) || 0;
                    total += value;
                });
                if (transferTotalCost) {
                    transferTotalCost.textContent = total.toFixed(2);
                }
            };

            // Update From Branch dropdown based on selected assets
            window.updateFromBranch = function(branchId) {
                if (!branchId) {
                    // If no branch ID, try to get from first asset in table
                    const firstAssetInput = document.querySelector('#transferAssetsTable .asset-no-input');
                    if (firstAssetInput) {
                        branchId = firstAssetInput.dataset.branchId;
                    }
                }

                if (branchId) {
                    const fromBranchSelect = document.getElementById('transfer_from_select');
                    if (fromBranchSelect) {
                        fromBranchSelect.value = branchId;
                    }
                }
            };

            // Open transfer modal with pre-selected assets
            transferBtn.addEventListener('click', () => {
                transferAssetsTable.innerHTML = '';
                window.assetRowCounter = 0;
                let selectedBranchId = null;

                // Use DataTable API to get ALL rows (including other pages)
                const table = $('#assetsTable').DataTable();
                const allCheckboxes = table.cells('.asset-checkbox').nodes();

                // Collect all checkboxes from all pages via DataTable nodes
                table.rows().every(function() {
                    const row = this.node();
                    const cb = row.querySelector('.asset-checkbox');
                    if (cb && window.selectedAssetIds.has(cb.value)) {
                        window.assetRowCounter++;
                        const cost = parseFloat(cb.dataset.cost) || 0;
                        const branchId = cb.dataset.branchId || '';
                        const branchName = cb.dataset.branchName || '';

                        if (selectedBranchId === null && branchId) {
                            selectedBranchId = branchId;
                        }

                        const tr = document.createElement('tr');
                        tr.id = `asset-row-${window.assetRowCounter}`;
                        tr.innerHTML = `
                            <td class="border px-2 py-1 text-center">${window.assetRowCounter}</td>
                            <td class="border px-2 py-1">
                                <input type="text" name="assets[${window.assetRowCounter}][asset_no]"
                                    value="${cb.dataset.no}"
                                    class="w-full px-2 py-1 border rounded text-sm bg-gray-50 asset-no-input"
                                    data-branch-id="${branchId}"
                                    data-branch-name="${branchName}"
                                    readonly>
                                <input type="hidden" name="assets[${window.assetRowCounter}][asset_id]" value="${cb.value}" class="asset-id-hidden">
                            </td>
                            <td class="border px-2 py-1">
                                <input type="text" name="assets[${window.assetRowCounter}][asset_name]"
                                    value="${cb.dataset.name}"
                                    class="w-full px-2 py-1 border rounded text-sm bg-gray-50" readonly>
                            </td>
                            <td class="border px-2 py-1">
                                <input type="number" name="assets[${window.assetRowCounter}][asset_cost]"
                                    value="${cost.toFixed(2)}"
                                    step="0.01"
                                    class="w-full px-2 py-1 border rounded text-sm asset-cost-input"
                                    oninput="updateTotalCost()">
                            </td>
                            <td class="border px-2 py-1 text-center">
                                <button type="button" onclick="removeAssetRow(${window.assetRowCounter})"
                                    class="text-red-600 hover:text-red-800 text-sm">
                                    Remove
                                </button>
                            </td>
                        `;
                        transferAssetsTable.appendChild(tr);
                    }
                });

                // Set the From Branch dropdown
                updateFromBranch(selectedBranchId);

                updateTotalCost();
                openModal('transferModal');
            });

            window.closeTransferModal = () => {
                closeModal('transferModal');
                // Clear selections
                window.selectedAssetIds.clear();
                document.querySelectorAll('.asset-checkbox').forEach(cb => cb.checked = false);
                if (selectAll) selectAll.checked = false;
                if (transferBtn) transferBtn.classList.add('hidden');
                const ccBtn = document.getElementById('changeColorBtn');
                if (ccBtn) ccBtn.classList.add('hidden');

                // Reset the transfer form
                const transferForm = document.getElementById('transferForm');
                if (transferForm) {
                    transferForm.reset();
                }
            };

            /* ===============================
               Click outside modal (ALL)
            =============================== */
            /* ===============================
               Change Color logic
            =============================== */
            window.openChangeColorModal = function() {
                document.getElementById('changeColorCount').textContent = window.selectedAssetIds.size;
                document.getElementById('bulkColorSelect').value = '';
                openModal('changeColorModal');
            };

            window.closeChangeColorModal = () => closeModal('changeColorModal');

            window.submitBulkColorChange = function() {
                const color = document.getElementById('bulkColorSelect').value;
                const assetIds = [...window.selectedAssetIds];

                if (assetIds.length === 0) {
                    alert('No assets selected');
                    return;
                }

                fetch('{{ route('asset.bulkChangeColor') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            asset_ids: assetIds,
                            color: color
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            closeChangeColorModal();
                            window.selectedAssetIds.clear();
                            location.reload();
                        } else {
                            alert(data.message || 'Error changing color');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error changing color');
                    });
            };

            /* ===============================
               Change User/Dept logic
            =============================== */
            @php
                $loggedInBranchIds = auth()->user()->branches->pluck('id')->toArray();
                $sameBranchUsers = $users->filter(function ($u) use ($loggedInBranchIds) {
                    return $u->branches->pluck('id')->intersect($loggedInBranchIds)->isNotEmpty();
                });
            @endphp
            const bulkUserList = @json(
                $sameBranchUsers->map(function ($u) {
                        return ['id' => $u->id, 'name' => $u->name];
                    })->values());

            (function() {
                const input = document.getElementById('bulkUserInput');
                const hidden = document.getElementById('bulkUserIdHidden');
                const dropdown = document.getElementById('bulkUserDropdown');
                if (!input) return;

                function renderBulkUserDropdown(search) {
                    const filtered = bulkUserList.filter(u =>
                        !search || u.name.toLowerCase().includes(search.toLowerCase())
                    );
                    dropdown.innerHTML = '';
                    if (filtered.length === 0) {
                        dropdown.classList.add('hidden');
                        return;
                    }
                    filtered.forEach(u => {
                        const div = document.createElement('div');
                        div.textContent = u.name;
                        div.className = 'px-3 py-1.5 text-xs cursor-pointer hover:bg-teal-50';
                        div.addEventListener('mousedown', function(e) {
                            e.preventDefault();
                            input.value = u.name;
                            hidden.value = u.id;
                            dropdown.classList.add('hidden');
                        });
                        dropdown.appendChild(div);
                    });
                    dropdown.classList.remove('hidden');
                }

                input.addEventListener('focus', function() {
                    renderBulkUserDropdown(this.value);
                });
                input.addEventListener('input', function() {
                    hidden.value = '';
                    renderBulkUserDropdown(this.value);
                });
                input.addEventListener('blur', function() {
                    dropdown.classList.add('hidden');
                    const match = bulkUserList.find(u => u.name === this.value);
                    if (!match) {
                        this.value = '';
                        hidden.value = '';
                    }
                });
            })();

            window.openChangeUserDeptModal = function() {
                document.getElementById('changeUserDeptCount').textContent = window.selectedAssetIds.size;
                document.getElementById('bulkUserInput').value = '';
                document.getElementById('bulkUserIdHidden').value = '';
                document.getElementById('bulkDeptSelect').value = '';
                openModal('changeUserDeptModal');
            };

            window.closeChangeUserDeptModal = () => closeModal('changeUserDeptModal');

            window.submitBulkClearUserDept = function() {
                const assetIds = [...window.selectedAssetIds];
                if (assetIds.length === 0) {
                    alert('No assets selected');
                    return;
                }
                if (!confirm('Clear user and department for ' + assetIds.length + ' asset(s)?')) return;

                fetch('{{ route('asset.bulkChangeUserDept') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            asset_ids: assetIds,
                            user_id: null,
                            department_id: null,
                            clear: true
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            closeChangeUserDeptModal();
                            window.selectedAssetIds.clear();
                            location.reload();
                        } else {
                            alert(data.message || 'Error clearing');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error clearing user/department');
                    });
            };

            window.submitBulkUserDeptChange = function() {
                const userId = document.getElementById('bulkUserIdHidden').value;
                const deptId = document.getElementById('bulkDeptSelect').value;
                const assetIds = [...window.selectedAssetIds];

                if (assetIds.length === 0) {
                    alert('No assets selected');
                    return;
                }

                if (!userId && !deptId) {
                    alert('Please select at least a user or department');
                    return;
                }

                fetch('{{ route('asset.bulkChangeUserDept') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            asset_ids: assetIds,
                            user_id: userId || null,
                            department_id: deptId || null
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            closeChangeUserDeptModal();
                            window.selectedAssetIds.clear();
                            location.reload();
                        } else {
                            alert(data.message || 'Error updating');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error updating user/department');
                    });
            };

            ['addModal', 'editModal', 'transferModal', 'changeColorModal', 'changeUserDeptModal'].forEach(id => {
                const modal = document.getElementById(id);
                if (!modal) return;

                modal.addEventListener('click', e => {
                    if (e.target === modal) closeModal(id);
                });
            });

        });

        /* ===============================
           Autocomplete Functions (Global Scope)
        =============================== */
        let searchTimeout = null;

        // Setup autocomplete for a row
        function setupAutocomplete(row) {
            const assetNoInput = row.querySelector('.asset-no-input');
            const assetNameInput = row.querySelector('.asset-name-input');

            if (assetNoInput) {
                assetNoInput.addEventListener('input', function(e) {
                    handleAssetSearch(e.target, 'asset_no');
                });
            }

            if (assetNameInput) {
                assetNameInput.addEventListener('input', function(e) {
                    handleAssetSearch(e.target, 'asset_name');
                });
            }
        }

        // Handle asset search with debounce
        function handleAssetSearch(input, searchField) {
            const query = input.value.trim();
            const rowId = input.dataset.row;
            const dropdownId = searchField === 'asset_no' ? `dropdown-no-${rowId}` : `dropdown-name-${rowId}`;
            const dropdown = document.getElementById(dropdownId);

            console.log('Searching:', query, 'Field:', searchField, 'RowId:', rowId);

            // Clear previous timeout
            if (searchTimeout) clearTimeout(searchTimeout);

            if (query.length < 2) {
                dropdown.classList.add('hidden');
                return;
            }

            // Show loading
            dropdown.innerHTML = '<div class="autocomplete-item text-gray-500 p-2">Searching...</div>';
            dropdown.classList.remove('hidden');

            // Debounce search
            searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(
                        `/asset/search?q=${encodeURIComponent(query)}&field=${searchField}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                    console.log('Response status:', response.status);

                    if (!response.ok) throw new Error('Search failed');

                    const assets = await response.json();
                    console.log('Found assets:', assets);

                    if (assets.length === 0) {
                        dropdown.innerHTML =
                            '<div class="autocomplete-item text-gray-500 p-2">No assets found</div>';
                        dropdown.classList.remove('hidden');
                        return;
                    }

                    // Display results
                    dropdown.innerHTML = assets.map(asset => {
                        const assetJson = JSON.stringify(asset).replace(/'/g, '&#39;');
                        const cost = parseFloat(asset.asset_cost) || 0;
                        return `
                            <div class="autocomplete-item" 
                                 data-asset='${assetJson}' 
                                 onclick="selectAsset(this, ${rowId})">
                                <div class="font-medium text-sm">${asset.asset_no}</div>
                                <div class="text-xs text-gray-600">${asset.asset_name || '-'}</div>
                                <div class="text-xs text-gray-500">Cost: RM ${cost.toFixed(2)}${asset.branch ? ` | Branch: ${asset.branch.branch_name}` : ''}</div>
                            </div>
                        `;
                    }).join('');

                    dropdown.classList.remove('hidden');

                } catch (error) {
                    console.error('Search error:', error);
                    dropdown.innerHTML =
                        '<div class="autocomplete-item text-red-500 p-2">Search failed. Please try again.</div>';
                    dropdown.classList.remove('hidden');
                }
            }, 300);
        }

        // Select asset from dropdown
        window.selectAsset = function(element, rowId) {
            try {
                const assetData = JSON.parse(element.dataset.asset);
                const row = document.getElementById(`asset-row-${rowId}`);

                console.log('Selecting asset:', assetData, 'for row:', rowId);

                if (!row) {
                    console.error('Row not found:', rowId);
                    return;
                }

                // Check if asset already added in another row
                const allAssetIds = Array.from(document.querySelectorAll('.asset-id-hidden'))
                    .map(input => input.value)
                    .filter(id => id);

                if (allAssetIds.includes(String(assetData.id))) {
                    alert('This asset is already added to the transfer list');
                    // Hide dropdowns
                    document.querySelectorAll(`#dropdown-no-${rowId}, #dropdown-name-${rowId}`).forEach(dd => {
                        dd.classList.add('hidden');
                    });
                    return;
                }

                // Check branch consistency - get the first asset's branch
                const allRows = document.querySelectorAll('#transferAssetsTable tr[id^="asset-row-"]');
                if (allRows.length > 0) {
                    // Find first row with asset data
                    for (let otherRow of allRows) {
                        if (otherRow.id === `asset-row-${rowId}`) continue; // Skip current row

                        const otherAssetIdInput = otherRow.querySelector('.asset-id-hidden');
                        if (otherAssetIdInput && otherAssetIdInput.value) {
                            // Get branch from the data stored in the row
                            const otherAssetNoInput = otherRow.querySelector('.asset-no-input');
                            const otherBranchId = otherAssetNoInput?.dataset?.branchId;
                            const otherBranchName = otherAssetNoInput?.dataset?.branchName;

                            if (otherBranchId && assetData.branch_id &&
                                String(otherBranchId) !== String(assetData.branch_id)) {
                                alert(
                                    `Cannot select assets from different branches.\nCurrently selected: ${otherBranchName}\nYou tried to add: ${assetData.branch?.branch_name || 'Unknown'}`
                                );
                                // Hide dropdowns
                                document.querySelectorAll(`#dropdown-no-${rowId}, #dropdown-name-${rowId}`).forEach(
                                    dd => {
                                        dd.classList.add('hidden');
                                    });
                                return;
                            }
                            break; // Only need to check against one existing asset
                        }
                    }
                }

                // Fill in the row data
                const assetNoInput = row.querySelector('.asset-no-input');
                const assetNameInput = row.querySelector('.asset-name-input');
                const assetCostInput = row.querySelector('.asset-cost-input');
                const assetIdInput = row.querySelector('.asset-id-hidden');

                if (assetNoInput) {
                    assetNoInput.value = assetData.asset_no;
                    assetNoInput.classList.add('bg-gray-50');
                    assetNoInput.setAttribute('readonly', 'readonly');
                    // Store branch data for validation
                    assetNoInput.dataset.branchId = assetData.branch_id || '';
                    assetNoInput.dataset.branchName = assetData.branch?.branch_name || '';
                }

                if (assetNameInput) {
                    assetNameInput.value = assetData.asset_name || '';
                    assetNameInput.classList.add('bg-gray-50');
                    assetNameInput.setAttribute('readonly', 'readonly');
                }

                if (assetCostInput) {
                    const cost = parseFloat(assetData.asset_cost) || 0;
                    assetCostInput.value = cost.toFixed(2);
                }

                if (assetIdInput) {
                    assetIdInput.value = assetData.id;
                }

                console.log('Asset selected successfully');

                // Hide dropdowns
                document.querySelectorAll(`#dropdown-no-${rowId}, #dropdown-name-${rowId}`).forEach(dd => {
                    dd.classList.add('hidden');
                });

                // Update From Branch dropdown
                updateFromBranch(assetData.branch_id);

                updateTotalCost();

            } catch (error) {
                console.error('Error selecting asset:', error);
                alert('Failed to select asset. Please try again.');
            }
        };

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.asset-no-input') &&
                !e.target.closest('.asset-name-input') &&
                !e.target.closest('.autocomplete-dropdown')) {
                document.querySelectorAll('.autocomplete-dropdown').forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
            }
        });
    </script>

    <style>
        /* Row hover: subtle gray — non-sticky transparent cells let it show through */
        #assetsTable tbody tr:hover,
        #assetsTable tbody tr.odd:hover,
        #assetsTable tbody tr.even:hover {
            background-color: #f9fafb !important;
        }

        /* Non-sticky cells: transparent so row hover background shows through */
        #assetsTable tbody tr>td:not(.sc),
        #assetsTable tbody tr:hover>td:not(.sc),
        #assetsTable tbody tr.odd>td:not(.sc),
        #assetsTable tbody tr.even>td:not(.sc),
        #assetsTable tbody tr.odd:hover>td:not(.sc),
        #assetsTable tbody tr.even:hover>td:not(.sc) {
            background-color: transparent !important;
            box-shadow: none !important;
        }
        /* Sticky cells: always white; hover matches row hover color */
        #assetsTable tbody tr:hover td.sc {
            background-color: #f9fafb;
        }

        .autocomplete-dropdown {
            position: relative;
            z-index: 800;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            margin-top: 2px;
        }

        /* Chrome, Edge */
        input[list]::-webkit-calendar-picker-indicator {
            display: none !important;
        }

        /* Firefox */
        input[list] {
            appearance: textfield;
            -moz-appearance: textfield;
        }

        /* DataTables Length (Show entries) Styling */
        .dataTables_wrapper .dataTables_length {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_length label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
            display: flex;
            align-items: center;
            gap: 0rem;
        }

        .dataTables_wrapper .dataTables_length select {
            margin: 0 0.5rem;
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            outline: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.25rem;
            appearance: none;
        }

        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.1);
        }

        /* DataTables Search Styling */
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_filter label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            margin-left: 0;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            outline: none;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #818cf8;
            ring: 2px;
            ring-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.1);
        }

        /* DataTables Info and Pagination Styling */
        .dataTables_wrapper .dataTables_info {
            font-size: 0.875rem;
            color: #6b7280;
            padding-top: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate {
            padding-top: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin: 0 0.125rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #4f46e5 !important;
            color: white !important;
            border: 1px solid #4f46e5 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #eef2ff !important;
            color: #4f46e5 !important;
            border: 1px solid #c7d2fe !important;
        }
    </style>
@endsection
