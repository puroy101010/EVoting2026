<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOD Proxy Print</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 2cm 1.5cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #2F4A3C;
            background: white;
            font-weight: 400;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 0;
            background: transparent;
            color: #2F4A3C;
            border-bottom: 3px solid #8DA66E;
            position: relative;
        }

        .header-content {
            padding: 30px 20px 20px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #2F4A3C;
            position: relative;
        }

        .header h1::after {
            content: '';
            display: block;
            width: 100px;
            height: 3px;
            background: #8DA66E;
            margin: 10px auto;
        }

        .header .subtitle {
            font-size: 14px;
            font-weight: 400;
            color: #666;
            margin-bottom: 5px;
        }

        .header .company-name {
            font-size: 16px;
            font-weight: 500;
            color: #2F4A3C;
            margin-top: 5px;
        }

        .proxy-info {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #8DA66E;
            border: 1px solid #e9ecef;
        }

        .proxy-info h2 {
            font-size: 14px;
            margin-bottom: 16px;
            color: #2F4A3C;
            font-weight: 500;
            border-bottom: 1px solid #8DA66E;
            padding-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-row {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        .info-label {
            font-weight: 500;
            width: 140px;
            display: inline-block;
            color: #2F4A3C;
            font-size: 12px;
        }

        .info-label::after {
            content: ":";
            margin-left: 5px;
        }

        .info-value {
            color: #333;
            font-weight: 400;
            font-size: 12px;
        }

        .summary {
            text-align: center;
            margin: 25px 0;
            padding: 20px;
            background: transparent;
            color: #2F4A3C;
            border: 2px solid #8DA66E;
            position: relative;
        }

        .summary .summary-title {
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .summary .total-count {
            font-size: 36px;
            font-weight: 700;
            color: #8DA66E;
            display: block;
            margin: 15px 0;
            line-height: 1;
        }

        .summary .total-label {
            font-size: 12px;
            font-weight: 500;
            color: #2F4A3C;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            font-size: 11px;
            background: white;
            border: 1px solid #2F4A3C;
        }

        table th {
            background: #8DA66E;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-size: 10px;
            border-bottom: 1px solid #2F4A3C;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
            vertical-align: top;
            font-size: 11px;
        }

        table td:last-child {
            border-right: none;
        }

        table td strong {
            font-weight: 500;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table tbody tr:nth-child(odd) {
            background-color: white;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            padding: 20px;
            border-top: 1px solid #8DA66E;
            background: #f8f9fa;
        }

        .footer p {
            margin-bottom: 4px;
        }

        .footer .generated-date {
            font-weight: 500;
            color: #2F4A3C;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(141, 166, 110, 0.05);
            z-index: -1;
            pointer-events: none;
            font-weight: 300;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding-bottom: 100px;
            }

            .footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                margin-top: 0;
                padding: 15px 20px;
                border-top: 1px solid #8DA66E;
                background: #f8f9fa;
                z-index: 1000;
                display: none;
            }

            .no-print {
                display: none;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .header,
            .proxy-info,
            .summary {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="watermark"></div>

    <div class="header">
        <div class="header-content">
            <div class="company-name">Online Voting System</div>
            <h1>Proxy Assignment Report</h1>
            <div class="subtitle">2025 Annual Stockholders Meeting</div>
        </div>
    </div>

    <div class="proxy-info">
        <h2>Assigned Proxyholder Details</h2>
        <div class="info-row">
            <span class="info-label">Account No</span>
            <span class="info-value">{{ $userInfo->account_no }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Stockholder</span>
            <span class="info-value">{{ $userInfo->full_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Contact Email</span>
            <span class="info-value">{{ $userInfo->email }}</span>
        </div>
    </div>

    <div class="summary">
        <div class="summary-title">Valid Proxy Assignments Received</div>
        <span class="total-count">{{ count($proxyList) }}</span>
        <div class="total-label">Total Assignments</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Account No.</th>
                <th>Stockholder</th>
                <th>Proxy Form No.</th>
                <th>Assignor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($proxyList as $proxy)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $proxy['accountNo'] }}</td>
                <td>{{ $proxy['stockholder'] }}</td>
                <td>{{ $proxy['proxyFormNo'] }}</td>
                <td>{{ $proxy['assignorAccountNo'] }} - {{ $proxy['assignorName'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p class="generated-date">Report Generated: {{ date('F d, Y \a\t h:i A') }}</p>
        <p>2025 Annual Stockholders Meeting | Online Voting System</p>
        <p style="font-style: italic; font-size: 9pt; margin-top: 10px;">This document contains confidential shareholder information. Handle with appropriate care.</p>
    </div>
</body>

</html>