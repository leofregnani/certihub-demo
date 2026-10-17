<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Certificate {{ $certificate->code }}</title>
    <style>
        @page { margin: 0; }
        * { font-family: 'DejaVu Serif', serif; }
        body { margin: 0; padding: 0; color: #451a03; background: #fffdf7; }
        .frame {
            width: 262mm; height: 168mm;
            margin: 10mm auto 0 auto; padding: 8mm 10mm;
            border: 4px double #b45309; text-align: center;
        }
        .institution { font-size: 13px; letter-spacing: 4px; color: #92400e; text-transform: uppercase; margin-top: 6mm; }
        .title { font-size: 46px; letter-spacing: 8px; color: #78350f; margin: 6mm 0 0 0; }
        .sub { font-size: 15px; font-style: italic; margin-top: 4mm; }
        .name { font-size: 36px; color: #111827; margin: 7mm auto 0 auto; padding: 0 10mm 2mm 10mm; border-bottom: 1px solid #b45309; display: inline-block; }
        .text { font-size: 16px; line-height: 1.6; margin-top: 7mm; }
        table.footer { width: 100%; margin-top: 14mm; border-collapse: collapse; }
        table.footer td { width: 33%; text-align: center; vertical-align: bottom; font-size: 11px; }
        .line { border-top: 1px solid #451a03; margin: 0 8mm; padding-top: 2mm; }
        .qr { width: 24mm; height: 24mm; }
        .code { font-family: 'DejaVu Sans Mono', monospace; font-size: 10px; margin-top: 1mm; }
        .hint { font-size: 8px; color: #92400e; }
    </style>
</head>
<body>
<div class="frame">
    <div class="institution">{{ $certificate->institution_name }}</div>
    <div class="title">CERTIFICATE</div>
    <div class="sub">of completion, proudly presented to</div>
    <div class="name">{{ $certificate->participant_name }}</div>
    <div class="text">
        for successfully completing the course<br>
        <strong>{{ $certificate->course_name }}</strong><br>
        with a total workload of <strong>{{ $certificate->workload_hours }} hours</strong>.
    </div>

    <table class="footer">
        <tr>
            <td>
                <div class="line">
                    {{ $signatory }}<br>{{ $signatoryRole }}
                </div>
            </td>
            <td>
                @if($qr)
                    <img class="qr" src="{{ $qr }}" alt="QR code">
                @endif
                <div class="code">{{ $certificate->code }}</div>
                <div class="hint">Validate at {{ config('certihub.frontend_url') }}/validate</div>
            </td>
            <td>
                <div class="line">
                    Issued on {{ $certificate->issued_at->format('F j, Y') }}
                </div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
