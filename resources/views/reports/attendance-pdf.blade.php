<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        .kop { border-bottom: 3px solid #E31837; padding-bottom: 10px; margin-bottom: 16px; text-align: center; }
        .kop .org { color: #E31837; font-size: 13px; letter-spacing: 2px; text-transform: uppercase; }
        .kop h1 { font-size: 18px; margin: 6px 0 2px; }
        .kop .sub { color: #6b7280; font-size: 11px; }
        .meta { margin-bottom: 12px; font-size: 11px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: left; }
        th { background-color: #f3f4f6; font-size: 11px; text-transform: uppercase; }
        .summary { margin-bottom: 14px; }
        .summary span { display: inline-block; margin-right: 14px; font-size: 11px; }
        .footer { margin-top: 18px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="kop">
        <div class="org">{{ $orgName }}</div>
        <h1>Rekap Absensi Kegiatan</h1>
        <div class="sub">{{ $schoolName }}</div>
    </div>

    <div class="meta">
        @if ($recap['event'])
            Kegiatan: <strong>{{ $recap['event'] }}</strong> &middot;
        @endif
        @if ($recap['kelas'])
            Kelas: <strong>{{ $recap['kelas'] }}</strong> &middot;
        @endif
        Periode: {{ $recap['periode'] }}
    </div>

    <div class="summary">
        @foreach ($recap['summary'] as $status => $total)
            <span><strong>{{ $total }}</strong> {{ $status }}</span>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpa</th>
                <th>Terlambat</th>
                <th>Total</th>
                <th>% Hadir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($recap['rows'] as $row)
                <tr>
                    <td>{{ $row['member'] }}</td>
                    <td>{{ $row['kelas'] }}</td>
                    <td>{{ $row['hadir'] }}</td>
                    <td>{{ $row['izin'] }}</td>
                    <td>{{ $row['sakit'] }}</td>
                    <td>{{ $row['alpa'] }}</td>
                    <td>{{ $row['terlambat'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['persentase'] }}</td>
                </tr>
            @empty
                <tr><td colspan="9">Belum ada data absensi pada filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak: {{ $generatedAt }} &middot; {{ $orgName }}
    </div>
</body>
</html>
