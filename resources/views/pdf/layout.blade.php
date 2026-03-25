<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Document' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 170px 25px 210px 25px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #1f2937;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 200px 20px 10px 20px;
        }

        /* Header Section with Logo */
        .header-section {
            text-align: center;
            padding: 10px 0 15px 0;
            border-bottom: 2px solid #1e40af;
            margin-bottom: 20px;
        }

        .logo-img {
            max-height: 130px;
            width: auto;
        }

        /* Fixed Header - repeats on every page */
        .header-fixed {
            position: fixed;
            top: 0px;
            left: 0;
            right: 0;
            height: 140px;
            text-align: center;
            border-bottom: 2px solid #1e40af;
            background-color: white;
            padding: 10px 25px;
        }

        /* Sticky Footer at bottom of page */
        .footer-fixed {
            position: fixed;
            bottom: 10px;
            left: 25px;
            right: 25px;
            height: 50px;
            text-align: center;
            color: #64748b;
            font-size: 9px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            background-color: white;
        }

        .footer-fixed p {
            margin: 2px 0;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #1e40af;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .company-tagline {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .document-type {
            font-size: 18px;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            margin-top: 15px;
        }

        /* Document Info */
        .document-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .info-left,
        .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .info-right {
            text-align: right;
        }

        .info-label {
            font-weight: bold;
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
        }

        .info-value {
            color: #1f2937;
            margin-bottom: 8px;
        }

        /* Recipient Box */
        .recipient-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .recipient-title {
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            font-size: 11px;
            text-transform: uppercase;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #1e40af;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        .items-table th:nth-child(1) {
            width: 5%;
        }

        .items-table th:nth-child(4),
        .items-table th:nth-child(5) {
            text-align: right;
        }

        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .items-table td:nth-child(4),
        .items-table td:nth-child(5) {
            text-align: right;
        }

        .items-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Totals */
        .totals-section {
            margin-top: 20px;
            text-align: right;
        }

        .totals-table {
            display: inline-table;
            text-align: right;
        }

        .totals-row {
            display: table-row;
        }

        .totals-label,
        .totals-value {
            display: table-cell;
            padding: 5px 10px;
        }

        .totals-label {
            font-weight: bold;
            color: #64748b;
        }

        .totals-value {
            min-width: 120px;
        }

        .grand-total {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            border-top: 2px solid #1e40af;
            padding-top: 10px;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 10px;
        }

        /* Signatures */
        .signatures {
            margin-top: 60px;
            display: table;
            width: 100%;
        }
    </style>
</head>

<body>
    <!-- Fixed Footer at bottom of every page -->
    <div class="footer-fixed">
        <p style="font-style: italic;">"The Lord detests dishonest scales, but He delights in accurate weights." —
            Proverbs 11:1</p>
        <p>&copy; {{ date('Y') }} AWS Document System | HVAC Installation • Maintenance • Servicing</p>
    </div>

    <!-- Fixed Header at top of every page -->
    <div class="header-fixed">
        <img src="{{ public_path('images/logo.jpg') }}" alt="AWS Document System" class="logo-img">
    </div>

    <div class="container">
        @yield('content')
    </div>
</body>

</html>