<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transfer Note - {{ $transfer->transfer_running_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            padding: 40px;
        }
        .logo {
            text-align: center;
            margin-bottom: 25px;
        }
        .logo img {
            width: 150px;
        }
        .header {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .info-left {
            width: 55%;
        }
        .info-right {
            width: 40%;
            text-align: left;
        }
        .label {
            font-weight: bold;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        .table th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .table td.text-center,
        .table th.text-center {
            text-align: center;
        }
        .table td.text-right,
        .table th.text-right {
            text-align: right;
        }
        .remark-section {
            margin-top: 15px;
            margin-bottom: 30px;
        }
        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-block {
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9fafb;
        }

        /* For DomPDF compatibility */
        table {
            width: 100%;
        }
        .flex-row {
            width: 100%;
            overflow: hidden;
        }
        .flex-row::after {
            content: "";
            display: table;
            clear: both;
        }
        .col-left {
            float: left;
            width: 55%;
        }
        .col-right {
            float: right;
            width: 40%;
        }
        .sig-left {
            float: left;
            width: 45%;
        }
        .sig-right {
            float: right;
            width: 45%;
        }
    </style>
</head>
<body>
    {{-- Logo --}}
    <div class="logo">
        <img src="{{ storage_path('app/public/ams/images/universal group - black logo.jpg') }}" alt="Logo">
    </div>

    {{-- Header --}}
    <div class="header">Transfer Note</div>

    {{-- Transfer Information --}}
    <div class="info-section">
        <div class="flex-row">
            <div class="col-left">
                <p><span class="label">From:</span> {{ $transfer->fromBranch->branch_name ?? '-' }} <span class="label" style="margin-left: 20px;">To:</span> {{ $transfer->toBranch->branch_name ?? '-' }}</p>
            </div>
            <div class="col-right">
                <p><span class="label">Date:</span> {{ $transfer->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="flex-row" style="margin-top: 8px;">
            <div class="col-left">
                <p><span class="label">Purpose:</span> {{ $transfer->transfer_purpose ?? '-' }}</p>
            </div>
            <div class="col-right">
                <p><span class="label">Ref. No.:</span> {{ $transfer->transfer_running_no }}</p>
            </div>
        </div>

        <div class="flex-row" style="margin-top: 8px;">
            <div class="col-left">
                <p><span class="label">Status:</span> {{ ucfirst(str_replace('_', ' ', $transfer->transfer_status)) }}</p>
            </div>
            <div class="col-right">
                @if($transfer->shipping)
                    <p><span class="label">Shipping:</span> {{ $transfer->shipping->name ?? '-' }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Items Table --}}
    <table class="table">
        <thead>
            <tr>
                <th style="width: 25%;">Code</th>
                <th style="width: 40%;">Name</th>
                <th class="text-right" style="width: 15%;">Cost (RM)</th>
                <th class="text-center" style="width: 20%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalCost = 0;
                $filteredAssets = $transfer->assets->filter(function ($asset) {
                    return ($asset->pivot->asset_transfer_status ?? 'pending') !== 'rejected';
                });
            @endphp
            @forelse($filteredAssets as $index => $asset)
                @php
                    $pivot = $asset->pivot ?? null;
                    $assetStatus = $pivot->asset_transfer_status ?? 'pending';
                    $cost = $asset->asset_cost ?? 0;
                    $totalCost += $cost;
                @endphp
                <tr>
                    <td>{{ $asset->asset_no }}</td>
                    <td>{{ $asset->asset_name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($cost, 2) }}</td>
                    <td class="text-center">{{ ucfirst(str_replace('_', ' ', $assetStatus)) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No items in this transfer</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="2" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>RM {{ number_format($totalCost, 2) }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- Remark --}}
    <div class="remark-section">
        <p><span class="label">Remark:</span> {{ $transfer->transfer_remark ?? '-' }}</p>
    </div>

    {{-- Signature Section --}}
    <div class="flex-row" style="margin-top: 40px;">
        <div class="sig-left">
            @if($transfer->approver || $transfer->rejector)
                <p style="margin-bottom: 5px;">Requested by: <strong>{{ $transfer->creator->name ?? '-' }}</strong></p>
                {{-- <p style="margin-bottom: 15px;">{{ $transfer->toBranch->branch_name ?? '-' }}</p> --}}

                @if($transfer->approver)
                    <p style="margin-bottom: 5px;">Approved by: <strong>{{ $transfer->approver->name ?? '-' }}</strong></p>
                @elseif($transfer->rejector)
                    <p style="margin-bottom: 5px;">Rejected by: <strong>{{ $transfer->rejector->name ?? '-' }}</strong></p>
                @endif
                {{-- <p>{{ $transfer->fromBranch->branch_name ?? '-' }}</p> --}}
            @else
                <p style="margin-bottom: 5px;">Issued by: <strong>{{ $transfer->creator->name ?? '-' }}</strong></p>
                <p>{{ $transfer->fromBranch->branch_name ?? '-' }}</p>
            @endif
        </div>
        <div class="sig-right">
            <p style="margin-bottom: 40px;">Receiver's Signature:</p>
            <div class="signature-line">
                <p>Name: ___________________________</p>
                <p style="margin-top: 10px;">Date: ___________________________</p>
            </div>
        </div>
    </div>
</body>
</html>
