@extends('pdf.layout')

@section('content')
    <!-- Title centered at top -->
    <div style="text-align: center; margin-top: 15px; margin-bottom: 15px;">
        <span style="font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px;">Purchased
            Order</span>
    </div>

    <!-- TO / Address on left, Control No + Date on right -->
    <table style="width: 100%; margin-bottom: 20px; font-size: 11px; border-collapse: collapse;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding: 3px 0;">
                <div style="margin-bottom: 5px;">
                    <span style="font-weight: bold;">TO:</span>
                    <span style="text-transform: uppercase; font-weight: bold;">{{ $document->recipient_name }}</span>
                </div>
                @if($document->recipient_address)
                    <div>
                        <span style="font-weight: bold;">Address:</span>
                        <span style="text-transform: uppercase;">{{ $document->recipient_address }}</span>
                    </div>
                @endif
            </td>
            <td style="width: 45%; text-align: right; vertical-align: top; padding: 3px 0;">
                <div style="margin-bottom: 4px;">
                    <span style="font-weight: bold;">Control No.</span>
                    <span
                        style="font-size: 16px; font-weight: bold; color: #c00;">{!! $document->formatted_control_number !!}</span>
                </div>
                <div>
                    <span style="font-weight: bold;">Date:</span>
                    <span>{{ $document->document_date->format('n/d/Y') }}</span>
                </div>
            </td>
        </tr>
    </table>

    @php
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
                    <th
                        style="border: 1px solid #000; padding: 8px; text-align: center; width: 15%; background-color: #ffffff; color: #000; font-size: 10px;">
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
                        <td style="border: 1px solid #000; padding: 6px 4px; text-align: center; vertical-align: top;">
                            {{ $item->remarks ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($chunkIndex < $totalChunks - 1)
            <div style="page-break-after: always;"></div>

            <!-- Continuation page header -->
            <div style="text-align: center; margin-bottom: 10px;">
                <span style="font-size: 14px; font-weight: bold; text-transform: uppercase;">Purchased Order (Continued)</span>
            </div>
            <table style="width: 100%; margin-bottom: 15px; font-size: 11px; border-collapse: collapse;">
                <tr>
                    <td style="width: 55%; vertical-align: top; padding: 3px 0;">
                        <span style="font-weight: bold;">TO:</span>
                        <span style="text-transform: uppercase; font-weight: bold;">{{ $document->recipient_name }}</span>
                    </td>
                    <td style="width: 45%; text-align: right; vertical-align: top; padding: 3px 0;">
                        <div>
                            <span style="font-weight: bold;">Control No.</span>
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

    <!-- Signature Section -->
    <table
        style="width: 90%; font-size: 10px; border-collapse: collapse; position: absolute; bottom: 130px; left: 40px; right: 80px;">
        <tr>
            <td style="width: 50%; vertical-align: bottom; padding-right: 30px;">
                <div style="margin-bottom: 5px;">Requested by: _______________________________</div>
                <div style="font-size: 9px; padding-left: 80px;">Signature Over Printed Name</div>
            </td>
            <td style="width: 50%; vertical-align: bottom; text-align: right; padding-left: 30px;">
                <div style="margin-bottom: 5px;">Approved by: _______________________________</div>
                <div style="font-size: 9px; padding-right: 30px;">Signature Over Printed Name</div>
            </td>
        </tr>
    </table>
@endsection