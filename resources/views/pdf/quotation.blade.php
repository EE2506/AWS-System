@extends('pdf.layout')

@section('content')
    <!-- Title centered at top -->
    <div style="text-align: center; margin-top: 15px; margin-bottom: 15px;">
        <span style="font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px;">Quotation</span>
    </div>

    <!-- TO / Address on left, Date / Control # on right -->
    <table style="width: 100%; margin-bottom: 20px; font-size: 11px; border-collapse: collapse;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding: 3px 0;">
                <div style="margin-bottom: 5px;">
                    <span style="font-weight: bold;">TO:</span>
                    <span style="text-transform: uppercase; font-weight: bold;">{{ $document->recipient_name }}</span>
                </div>
                @if($document->recipient_address)
                    <div>
                        <span style="font-weight: bold;">ADDRESS:</span>
                        <span style="text-transform: uppercase;">{{ $document->recipient_address }}</span>
                    </div>
                @endif
            </td>
            <td style="width: 45%; text-align: right; vertical-align: top; padding: 3px 0;">
                <div style="margin-bottom: 4px;">
                    <span style="font-weight: bold;">DATE:</span>
                    <span style="font-weight: bold;">{{ $document->document_date->format('M. d, Y') }}</span>
                </div>
                <div>
                    <span style="font-weight: bold;">CONTROL #</span>
                    {!! $document->formatted_control_number !!}
                </div>
            </td>
        </tr>
    </table>

    @php
        $showPricing = true;
        $itemsPerPage = 10;
        $itemChunks = $document->items->chunk($itemsPerPage);
        $totalChunks = $itemChunks->count();
    @endphp

    @foreach($itemChunks as $chunkIndex => $chunk)
        <!-- Items Table -->
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 10px;">
            <thead>
                <tr>
                    <th
                        style="border: 1px solid #000; padding: 8px; text-align: center; width: 8%; background-color: #ffffff; color: #000; font-size: 10px;">
                        QTY.</th>
                    <th
                        style="border: 1px solid #000; padding: 8px; text-align: center; width: 10%; background-color: #ffffff; color: #000; font-size: 10px;">
                        Unit</th>
                    <th
                        style="border: 1px solid #000; padding: 8px; text-align: center; background-color: #ffffff; color: #000; font-size: 10px;">
                        Description / Details of Item</th>
                    @if($showPricing)
                        <th
                            style="border: 1px solid #000; padding: 8px; text-align: center; width: 12%; background-color: #ffffff; color: #000; font-size: 10px;">
                            Unit Price</th>
                        <th
                            style="border: 1px solid #000; padding: 8px; text-align: center; width: 12%; background-color: #ffffff; color: #000; font-size: 10px;">
                            Total</th>
                    @endif
                    <th
                        style="border: 1px solid #000; padding: 8px; text-align: center; width: 13%; background-color: #ffffff; color: #000; font-size: 10px;">
                        Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chunk as $item)
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px 4px; text-align: center; vertical-align: top;">
                            {{ $item->quantity }}</td>
                        <td
                            style="border: 1px solid #000; padding: 6px 4px; text-align: center; vertical-align: top; text-transform: uppercase;">
                            UNIT</td>
                        <td style="border: 1px solid #000; padding: 6px 8px; vertical-align: top;">
                            <div style="font-weight: bold; text-transform: uppercase;">{{ $item->name }}</div>
                            @if($item->description)
                                <div style="font-size: 0.9em; text-transform: uppercase; margin-top: 2px;">{{ $item->description }}
                                </div>
                            @endif
                        </td>
                        @if($showPricing)
                            <td style="border: 1px solid #000; padding: 6px 4px; text-align: right; vertical-align: top;">
                                {{ number_format($item->unit_cost, 2) }}</td>
                            <td style="border: 1px solid #000; padding: 6px 4px; text-align: right; vertical-align: top;">
                                {{ number_format($item->total_cost, 2) }}</td>
                        @endif
                        <td style="border: 1px solid #000; padding: 6px 4px; text-align: center; vertical-align: top;">
                            {{ $item->remarks ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($chunkIndex < $totalChunks - 1)
            <div style="page-break-after: always;"></div>

            <!-- Continuation page header -->
            <div style="text-align: center; margin-top: 15px; margin-bottom: 10px;">
                <span style="font-size: 14px; font-weight: bold; text-transform: uppercase;">Quotation (Continued)</span>
            </div>
            <table style="width: 100%; margin-bottom: 15px; font-size: 11px; border-collapse: collapse;">
                <tr>
                    <td style="width: 55%; vertical-align: top; padding: 3px 0;">
                        <span style="font-weight: bold;">TO:</span>
                        <span style="text-transform: uppercase; font-weight: bold;">{{ $document->recipient_name }}</span>
                    </td>
                    <td style="width: 45%; text-align: right; vertical-align: top; padding: 3px 0;">
                        <div>
                            <span style="font-weight: bold;">CONTROL #</span>
                            {!! $document->formatted_control_number !!}
                        </div>
                        <div>
                            <span style="font-weight: bold;">Page:</span>
                            {{ $chunkIndex + 2 }} of {{ $totalChunks }}
                        </div>
                    </td>
                </tr>
            </table>
        @endif
    @endforeach

    {{-- Total Amount (only if pricing exists) --}}
    @if($showPricing)
        <table style="width: 100%; margin-top: 10px; font-size: 12px; border-collapse: collapse;">
            @if($document->discount > 0)
                <tr>
                    <td style="width: 50%;"></td>
                    <td style="width: 25%; text-align: center; font-weight: bold;">SUBTOTAL</td>
                    <td style="width: 5%; text-align: center; font-weight: bold;">P</td>
                    <td style="width: 20%; text-align: right; font-weight: bold;">
                        {{ number_format($document->items->sum('total_cost'), 2) }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;"></td>
                    <td style="width: 25%; text-align: center; font-weight: bold;">DISCOUNT</td>
                    <td style="width: 5%; text-align: center; font-weight: bold;"></td>
                    <td style="width: 20%; text-align: right; font-weight: bold; color: #c00;">
                        -{{ number_format($document->discount, 2) }}
                    </td>
                </tr>
            @endif
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 25%; text-align: center; font-weight: bold;">TOTAL AMOUNT</td>
                <td style="width: 5%; text-align: center; font-weight: bold;">P</td>
                <td style="width: 20%; text-align: right; font-weight: bold; border-bottom: 2px solid #000;">
                    {{ number_format($document->total_amount, 2) }}
                </td>
            </tr>
        </table>
    @endif

    <!-- Signature Section -->
    <table style="width: 90%; font-size: 10px; border-collapse: collapse; position: absolute; bottom: 130px; left: 40px; right: 80px;">
        <tr>
            <td style="width: 50%; vertical-align: bottom; padding-right: 30px;">
                <div style="margin-bottom: 5px;">Prepared by: _______________________________</div>
                <div style="font-size: 9px; padding-left: 75px;">Signature Over Printed Name</div>
            </td>
            <td style="width: 50%; vertical-align: bottom; text-align: right; padding-left: 30px;">
                <div style="margin-bottom: 5px;">Approved by: _______________________________</div>
                <div style="font-size: 9px; padding-right: 30px;">Signature Over Printed Name</div>
            </td>
        </tr>
    </table>
@endsection