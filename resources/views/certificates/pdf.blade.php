<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $certificate->title }} · {{ $certificate->code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; margin: 0; }
        .wrap { padding: 40px; text-align: center; }
        .border { border: 4px solid #E31837; padding: 36px; }
        .org { color: #E31837; font-size: 14px; letter-spacing: 3px; text-transform: uppercase; }
        h1 { font-size: 28px; margin: 18px 0 6px; }
        .sub { color: #6b7280; font-size: 13px; margin-bottom: 24px; }
        .name { font-size: 26px; font-weight: bold; margin: 16px 0; color: #111827; }
        .title { font-size: 16px; margin-bottom: 8px; }
        .meta { font-size: 13px; color: #4b5563; }
        .footer { margin-top: 28px; font-size: 11px; color: #6b7280; }
        .qr { margin-top: 18px; }
        .code { font-family: monospace; font-size: 12px; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="border">
            <div class="org">Palang Merah Remaja · PMR Wira</div>
            <h1>SERTIFIKAT</h1>
            <div class="sub">Diberikan kepada:</div>
            <div class="name">{{ $member?->full_name ?? '-' }}</div>
            <div class="title">Atas penyelesaian:</div>
            <div class="title"><strong>{{ $certificate->title }}</strong></div>
            <div class="meta">Tanggal terbit: {{ $issuedAt }}</div>
            <div class="qr">
                <img src="{{ $qrDataUri }}" alt="QR Verifikasi" width="120" height="120">
            </div>
            <div class="code">Kode: {{ $certificate->code }}</div>
            <div class="footer">
                Verifikasi di: {{ $verifyUrl }}<br>
                Dokumen ini sah tanpa tanda tangan basah bila kode verifikasi valid.
            </div>
        </div>
    </div>
</body>
</html>
