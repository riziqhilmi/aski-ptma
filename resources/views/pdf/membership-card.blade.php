<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        html, body { width: 242.65pt; margin: 0; padding: 0; font-family: "DejaVu Sans", sans-serif; }
        .card { position: relative; width: 242.65pt; height: 153.07pt; overflow: hidden; background: #fff; border: 1px solid #dce7e1; border-radius: 14pt; color: #173d35; }
        .accent { height: 3pt; background: {{ $data['accent_color'] ?? '#d4a72c' }}; }
        .header { height: 44pt; padding: 7pt 12pt; background: {{ $data['background_color'] ?? '#0b6254' }}; color: #fff; }
        .header-table, .details-table { width: 100%; border-collapse: collapse; }
        .logo-cell { width: 35pt; vertical-align: middle; }
        .logo-frame { width: 30pt; height: 30pt; border-radius: 50%; background: #fff; text-align: center; }
        .logo { width: 27pt; height: 27pt; margin-top: 1.5pt; }
        .brand-cell { vertical-align: middle; padding-left: 7pt; }
        .brand { font-size: 8pt; font-weight: bold; letter-spacing: .35pt; }
        .document-title { margin-top: 2pt; color: #e8f1ed; font-size: 5.5pt; letter-spacing: 1pt; text-transform: uppercase; }
        .badge-cell { width: 57pt; text-align: right; vertical-align: middle; }
        .badge { display: inline-block; padding: 4pt 5pt; border: 1px solid rgba(255,255,255,.45); border-radius: 8pt; color: #fff; font-size: 5pt; font-weight: bold; letter-spacing: .4pt; }
        .body { height: 67pt; padding: 9pt 13pt 5pt; }
        .eyebrow { color: #778b83; font-size: 5pt; font-weight: bold; letter-spacing: 1pt; text-transform: uppercase; }
        .member-name { margin-top: 3pt; color: #173d35; font-size: 12pt; font-weight: bold; line-height: 1.15; }
        .member-type { margin-top: 3pt; color: #5d7169; font-size: 6pt; }
        .institution { margin-top: 2pt; color: #5d7169; font-size: 5.5pt; }
        .footer { position: absolute; right: 0; bottom: 0; left: 0; height: 38pt; padding: 6pt 13pt; background: #f1f6f3; border-top: 1px solid #e0e9e4; }
        .details-table td { vertical-align: top; }
        .number-cell { width: 57%; }
        .detail-label { color: #71847c; font-size: 4.5pt; font-weight: bold; letter-spacing: .7pt; text-transform: uppercase; }
        .number { margin-top: 3pt; color: {{ $data['background_color'] ?? '#0b6254' }}; font-size: 8pt; font-weight: bold; letter-spacing: .4pt; }
        .period { margin-top: 3pt; color: #294a40; font-size: 6pt; font-weight: bold; }
        .issued { position: absolute; right: 13pt; bottom: 5pt; color: #71847c; font-size: 4.5pt; }
    </style>
</head>
<body>
    <div class="card">
        <div class="accent"></div>
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-cell"><div class="logo-frame"><img class="logo" src="{{ $logo }}" alt="Logo ASKI-PTMA"></div></td>
                    <td class="brand-cell">
                        <div class="brand">ASKI-PTMA</div>
                        <div class="document-title">{{ $data['title'] ?? 'Kartu Anggota' }}</div>
                    </td>
                    <td class="badge-cell"><span class="badge">KARTU RESMI</span></td>
                </tr>
            </table>
        </div>
        <div class="body">
            <div class="eyebrow">Anggota</div>
            <div class="member-name">{{ $data['name'] ?? '-' }}</div>
            <div class="member-type">{{ ($data['account_type'] ?? 'individual') === 'institution' ? 'Anggota Institusi' : 'Anggota Individu' }}</div>
            @if (($data['account_type'] ?? null) === 'institution' && filled($data['institution_name'] ?? null))
                <div class="institution">{{ $data['institution_name'] }}</div>
            @endif
        </div>
        <div class="footer">
            <table class="details-table">
                <tr>
                    <td class="number-cell">
                        <div class="detail-label">Nomor Anggota</div>
                        <div class="number">{{ $data['member_number'] ?? '-' }}</div>
                    </td>
                    <td>
                        <div class="detail-label">Masa Berlaku</div>
                        <div class="period">{{ \Illuminate\Support\Carbon::parse($data['period_start'])->format('d/m/Y') }} - {{ \Illuminate\Support\Carbon::parse($data['period_end'])->format('d/m/Y') }}</div>
                    </td>
                </tr>
            </table>
            <div class="issued">Terbit {{ \Illuminate\Support\Carbon::parse($data['issued_at'])->format('d/m/Y') }} · {{ $data['number'] ?? '' }}</div>
        </div>
    </div>
</body>
</html>
