@extends('layouts.app', ['module' => 'marketing'])

@section('content')
    <div class="px-4">

        {{-- ============ TABS ============ --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-8 -mb-px">
                @foreach ($tabs as $key => $label)
                    <a href="{{ url('/marketing/transactions/' . $key) }}"
                        class="px-1 py-3 text-sm font-medium border-b-2 transition-colors
                            {{ $tab === $key
                                ? 'border-emerald-600 text-emerald-700'
                                : 'border-transparent text-gray-400 hover:text-gray-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- ============ TAB CONTENT ============ --}}
        @switch($tab)
            @case('marketing-in')
                @include('marketingModule.transaction.tabs.marketing-in')
                @break
            @case('transfer-list')
                @include('marketingModule.transaction.tabs.transfer-list')
                @break
            @case('request')
                @include('marketingModule.transaction.tabs.request')
                @break
            @case('transfer')
                @include('marketingModule.transaction.tabs.transfer')
                @break
            @case('invoice')
                @include('marketingModule.transaction.tabs.invoice')
                @break
        @endswitch
    </div>
@endsection
