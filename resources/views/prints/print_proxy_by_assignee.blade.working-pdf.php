<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Proxy Assignment Report - {{ $userInfo->account_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 5mm 4mm 3mm 4mm;
        }

        body {
            font-family: 'Roboto', Arial, sans-serif;
            font-size: 13px;
            color: #2F4A3C;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header .company-name {
            font-size: 16px;
            font-weight: 400;
            margin-bottom: 8px;
            margin-top: 40px;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 2px;
            margin: 0 0 8px 0;
            text-transform: uppercase;
        }

        .header .subtitle {
            font-size: 14px;
            font-weight: 400;
            color: #444;
            margin-bottom: 18px;
        }

        .header .divider {
            width: 100%;
            max-width: 900px;
            border-bottom: 2px solid #8DA66E;
            margin: 0 auto 30px auto;
        }

        .proxy-info {
            background: #fafbfc;
            border: 1px solid #e0e6e3;
            border-radius: 4px;
            padding: 18px 24px 12px 24px;
            margin-bottom: 28px;
        }

        .proxy-info-title {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #2F4A3C;
            margin-bottom: 10px;
            border-bottom: 1px solid #8DA66E;
            padding-bottom: 6px;
        }

        .info-row {
            margin-bottom: 7px;
            display: flex;
            align-items: baseline;
        }

        .info-label {
            font-weight: 500;
            width: 140px;
            color: #2F4A3C;
            font-size: 13px;
        }

        .info-value {
            color: #222;
            font-size: 13px;
        }

        .summary {
            border: 1.5px solid #8DA66E;
            border-radius: 4px;
            padding: 24px 0 18px 0;
            margin-bottom: 30px;
            text-align: center;
            background: #fff;
        }

        .summary-title {
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 1px;
            color: #2F4A3C;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .total-count {
            font-size: 32px;
            font-weight: 700;
            color: #8DA66E;
            margin: 0 0 6px 0;
            line-height: 1;
        }

        .total-label {
            font-size: 12px;
            font-weight: 400;
            color: #2F4A3C;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: #fff;
        }

        table thead th {
            background: #8DA66E;
            color: #fff;
            font-size: 12px;
            font-weight: 500;
            padding: 10px 6px;
            border: 1px solid #8DA66E;
            text-align: left;
            letter-spacing: 0.5px;
        }

        table tbody td {
            font-size: 11px;
            color: #2F4A3C;
            padding: 8px 6px;
            border: 1px solid #e0e6e3;
            background: #fff;
        }

        table tbody tr:nth-child(even) td {
            background: #f6f8f5;
        }

        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            text-align: center;
            font-size: 10.5px;
            color: #666;
            padding: 12px 0 6px 0;
            border-top: 1px solid #8DA66E;
            background: #fff;
        }

        .footer .generated-date {
            font-weight: 500;
            color: #2F4A3C;
        }

        .footer-note {
            font-style: italic;
            font-size: 9.5px;
            margin-top: 6px;
            color: #888;
        }

        .watermark {
            display: none;
        }

        @media print {
            body {
                margin-bottom: 60px;
            }

            .footer {
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                background: #fff;
                border-top: 1px solid #8DA66E;
                font-size: 10px;
                padding: 8px 0 4px 0;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">Online Voting System</div>
        <h1>Proxy Assignment Report</h1>
        <div class="subtitle">2025 Annual Stockholders Meeting</div>
        <div class="divider"></div>
    </div>

    <div class="proxy-info">
        <div class="proxy-info-title">ASSIGNED PROXYHOLDER DETAILS</div>
        <div class="info-row">
            <span class="info-label">Account No :</span>
            <span class="info-value">{{ $userInfo->account_no }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Stockholder :</span>
            <span class="info-value">{{ $userInfo->full_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Contact Email :</span>
            <span class="info-value">{{ $userInfo->email }}</span>
        </div>
    </div>

    <div class="summary">
        <div class="summary-title">VALID PROXY ASSIGNMENTS RECEIVED</div>
        <div class="total-count">{{ count($proxyList) }}</div>
        <div class="total-label">TOTAL ASSIGNMENTS</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th style="width: 120px;">Account No.</th>
                <th>Stockholder</th>
                <th style="width: 120px;">Proxy Form No.</th>
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


    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                $text = __("{{ $footerText }}                              Page :pageNum/:pageCount", ["pageNum" => $PAGE_NUM, "pageCount" => $PAGE_COUNT]);
                $font = null;
                $size = 7;
                $color = array(0,0,0);
                $word_space = 0.0;  //  default
                $char_space = 0.0;  //  default
                $angle = 0.0;   //  default
 
                // Compute text width to center correctly
                $textWidth = $fontMetrics->getTextWidth($text, $font, $size);
 
                $x = ($pdf->get_width() - $textWidth) / 2;
                $y = $pdf->get_height() - 35;
 
                $pdf->text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
            ');
        }
    </script>
</body>

</html>