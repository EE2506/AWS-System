@extends('pdf.layout')

@section('content')
    <!-- Document Type Title -->
    <div style="text-align: center; margin-bottom: 20px;">
        <div class="document-type">Delivery Receipt</div>
    </div>

    <!-- Info Section (To/Address Left, Date Right) -->
    <table style="width: 100%; margin-bottom: 20px; border: none;">
        <tr>
            <td style="width: 60%; vertical-align: top; border: none; padding: 0;">
                <div style="margin-bottom: 5px;">
                    <span style="font-weight: bold; font-size: 11px;">TO:</span>
                    <span
                        style="font-weight: bold; font-size: 11px; text-transform: uppercase;">{{ $document->recipient_name }}</span>
                </div>
                @if($document->recipient_address)
                    <div>
                        <span style="font-weight: bold; font-size: 11px;">ADDRESS:</span>
                        <span style="font-size: 11px; text-transform: uppercase;">{{ $document->recipient_address }}</span>
                    </div>
                @endif
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right; border: none; padding: 0;">
                <div style="font-size: 12px; font-weight: bold;">
                    DATE: {{ $document->document_date->format('F d, Y') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table" style="margin-top: 20px; border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th
                    style="width: 15%; text-align: center; background-color: white; color: black; border: 1px solid black; padding: 5px;">
                    QUANTITY</th>
                <th
                    style="width: 85%; text-align: center; background-color: white; color: black; border: 1px solid black; padding: 5px;">
                    DESCRIPTION</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->items as $item)
                <tr>
                    <td style="text-align: center; vertical-align: middle; border: 1px solid black; padding: 5px;">
                        {{ $item->quantity }}</td>
                    <td style="text-align: center; border: 1px solid black; padding: 5px;">
                        <div style="font-weight: bold; text-transform: uppercase;">{{ $item->name }}</div>
                        @if($item->description)
                            <div style="font-size: 0.9em; text-transform: uppercase; margin-top: 2px;">{{ $item->description }}
                            </div>
                        @endif
                        @if($item->remarks)
                            <div style="font-size: 0.8em; color: #555; margin-top: 2px;">{{ $item->remarks }}</div>
                        @endif
                    </td>
                </tr>
            @endforeach
            <!-- Empty rows filler to mimic the lined paper look if needed, 
                                 but for now just the items -->
        </tbody>
    </table>

    <!-- Footer / Received By -->
    <div style="position: absolute; bottom: 130px; left: 40px; right: 80px;">
        <div style="border-top: 1px solid #000; width: 250px; padding-top: 5px;">
            <div style="font-weight: bold; font-size: 11px;">RECEIVED BY:</div>
        </div>
    </div>
@endsection