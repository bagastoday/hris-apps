<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran {{ $period }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 10px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .period { color: #64748b; margin: 0 0 18px; }
        .summary { margin-bottom: 18px; }
        .summary td { padding: 6px 14px 6px 0; }
        .summary strong { display: block; font-size: 13px; margin-top: 3px; }
        table.report { border-collapse: collapse; width: 100%; }
        .report th, .report td { border: 1px solid #cbd5e1; padding: 6px; }
        .report th { background: #f1f5f9; text-align: left; }
        .report .number { text-align: center; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        .empty { text-align: center; color: #64748b; padding: 18px; }
    </style>
</head>
<body>
    <h1>Laporan Kehadiran Pegawai</h1>
    <p class="period">Periode {{ \Carbon\Carbon::createFromFormat('!Y-m', $period)->translatedFormat('F Y') }}</p>

    <table class="summary">
        <tr>
            <td>Pegawai aktif<strong>{{ $stats['total_pegawai'] }}</strong></td>
            <td>Rata-rata kehadiran<strong>{{ $stats['rata_kehadiran'] }}%</strong></td>
            <td>Total terlambat<strong>{{ $stats['total_terlambat'] }}</strong></td>
            <td>Cuti disetujui<strong>{{ $stats['cuti_disetujui'] }}</strong></td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th>No.</th>
                <th>Kode Pegawai</th>
                <th>Nama Pegawai</th>
                <th>Departemen</th>
                <th class="number">Hadir</th>
                <th class="number">Terlambat</th>
                <th class="number">Izin</th>
                <th class="number">Sakit</th>
                <th class="number">Cuti</th>
                <th class="number">Alpha</th>
                <th class="number">Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekap as $index => $row)
                <tr>
                    <td class="number">{{ $index + 1 }}</td>
                    <td>{{ $row['code'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['dept'] }}</td>
                    <td class="number">{{ $row['hadir'] }}</td>
                    <td class="number">{{ $row['terlambat'] }}</td>
                    <td class="number">{{ $row['izin'] }}</td>
                    <td class="number">{{ $row['sakit'] }}</td>
                    <td class="number">{{ $row['cuti'] }}</td>
                    <td class="number">{{ $row['alpha'] }}</td>
                    <td class="number">{{ $row['persen'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="11">Belum ada data pegawai aktif.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
