@extends('pdf.layout')

@section('content')
    <!-- Document Type Title -->
    <div style="text-align: center; margin-bottom: 15px;">
        <div class="document-type">Statement of Account</div>
    </div>

    <!-- Header Info Row: Name/Address on left, SOA no./Date on right -->
    <table style="width: 100%; margin-bottom: 15px; font-size: 11px;">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div><strong>Name:</strong> {{ $document->recipient_name }}</div>
                <div><strong>Address:</strong> {{ $document->recipient_address ?? 'N/A' }}</div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <div><strong>SOA #</strong> {!! $document->formatted_control_number !!}</div>
                <div><strong>Date:</strong> {{ $document->document_date->format('F d, Y') }}</div>
            </td>
        </tr>
    </table>

    @php
        $showPricing = $document->items->whereNotNull('unit_cost')->where('unit_cost', '>', 0)->count() > 0;
        $itemsPerPage = 10;
        $itemChunks = $document->items->chunk($itemsPerPage);
        $totalChunks = $itemChunks->count();
    @endphp

    @foreach($itemChunks as $chunkIndex => $chunk)
        <!-- Items Table with bordered rows -->
        <table class="items-table" style="border: 1px solid #000;">
            <thead>
                <tr style="background-color: #fff; color: #000; border-bottom: 1px solid #000;">
                    <th style="background-color: #fff; color: #000; border: 1px solid #000; padding: 8px; text-align: center; width: 8%;">ITEM</th>
                    <th style="background-color: #fff; color: #000; border: 1px solid #000; padding: 8px; text-align: left;">DESCRIPTION</th>
                    @if($showPricing)
                        <th style="background-color: #fff; color: #000; border: 1px solid #000; padding: 8px; text-align: center; width: 10%;">QTY</th>
                        <th style="background-color: #fff; color: #000; border: 1px solid #000; padding: 8px; text-align: right; width: 15%;">UNIT COST</th>
                        <th style="background-color: #fff; color: #000; border: 1px solid #000; padding: 8px; text-align: right; width: 18%;">TOTAL UNIT COST</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($chunk as $item)
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                            {{ str_pad($item->item_number, 2, '0', STR_PAD_LEFT) }}</td>
                        <td style="border: 1px solid #000; padding: 6px;">
                            <div>{{ $item->name }}</div>
                            @if($item->description)
                                <div style="font-size: 0.9em; color: #555;">{{ $item->description }}</div>
                            @endif
                        </td>
                        @if($showPricing)
                            <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{ $item->quantity }}</td>
                            <td style="border: 1px solid #000; padding: 6px; text-align: right;">
                                {{ number_format($item->unit_cost, 2) }}</td>
                            <td style="border: 1px solid #000; padding: 6px; text-align: right;">
                                {{ number_format($item->total_cost, 2) }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($chunkIndex < $totalChunks - 1)
            <!-- Page break after each chunk except the last -->
            <div style="page-break-after: always;"></div>
            
            <!-- Re-show document info on continuation pages -->
            <div style="text-align: center; margin-bottom: 15px;">
                <div class="document-type">Statement of Account (Continued)</div>
            </div>
            <table style="width: 100%; margin-bottom: 15px; font-size: 11px;">
                <tr>
                    <td style="width: 60%; vertical-align: top;">
                        <div><strong>Name:</strong> {{ $document->recipient_name }}</div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: top;">
                        <div><strong>SOA no.</strong> {{ $document->control_number ?? 'N/A' }}</div>
                        <div><strong>Page:</strong> {{ $chunkIndex + 2 }} of {{ $totalChunks }}</div>
                    </td>
                </tr>
            </table>
        @endif
    @endforeach

    <!-- Subtotal / Discount / Total Amount Due -->
    <table style="width: 100%; margin-top: 15px; font-size: 12px;">
        @if($document->discount > 0)
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 25%; text-align: center; font-weight: bold;">SUBTOTAL</td>
                <td style="width: 5%; text-align: center; font-weight: bold;">P</td>
                <td style="width: 20%; text-align: right; font-weight: bold;">
                    {{ number_format($document->items->sum('total_cost'), 2) }}</td>
            </tr>
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 25%; text-align: center; font-weight: bold;">DISCOUNT</td>
                <td style="width: 5%; text-align: center; font-weight: bold;"></td>
                <td style="width: 20%; text-align: right; font-weight: bold; color: #c00;">
                    -{{ number_format($document->discount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 25%; text-align: center; font-weight: bold;">TOTAL AMOUNT DUE</td>
            <td style="width: 5%; text-align: center; font-weight: bold;">P</td>
            <td style="width: 20%; text-align: right; font-weight: bold; border-bottom: 1px solid #000;">
                {{ number_format($document->total_amount, 2) }}</td>
        </tr>
    </table>

    <!-- Signature Section -->
    <table style="width: 90%; font-size: 10px; position: absolute; bottom: 130px; left: 40px; right: 80px;">
        <tr>
            <td style="width: 40%; vertical-align: bottom;">
                <div style="font-weight: bold;">Paul Air & Water Technology</div>
                <div>(082) 285-8203</div>
            </td>
            <td style="width: 60%; text-align: right; vertical-align: bottom;">
                <div>Received by: _______________________________</div>
                <div style="font-size: 9px; margin-top: 3px; text-align: right; padding-right: 20px;">Signature Over Printed
                    Name</div>
            </td>
        </tr>
    </table>
@endsection