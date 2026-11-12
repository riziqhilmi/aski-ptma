<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 34px 42px; }
        body { font-family: DejaVu Sans, sans-serif; color: #172554; font-size: 11px; line-height: 1.5; }
        h1 { margin: 0; font-size: 25px; color: #123b64; }
        h2 { margin: 0; font-size: 13px; }
        p { margin: 5px 0; }
        .muted { color: #64748b; }
        .brand { color: #166534; font-size: 12px; font-weight: bold; }
        .logo { width: 62px; height: 61px; margin: 0 0 5px; }
        .header { border-bottom: 1px solid #dbe3ec; padding-bottom: 18px; }
        .status { float: right; border-radius: 4px; padding: 5px 9px; font-weight: bold; }
        .paid { background: #dcfce7; color: #166534; }
        .unpaid { background: #fef3c7; color: #92400e; }
        .watermark { position: fixed; top: 37%; left: 13%; width: 74%; z-index: -1; text-align: center; font-size: 88px; font-weight: bold; letter-spacing: 5px; transform: rotate(-38deg); }
        .watermark-paid { color: rgba(34, 197, 94, .14); }
        .watermark-unpaid { color: rgba(239, 68, 68, .14); }
        .success-note { margin: 16px 0; color: #166534; }
        .meta { width: 100%; margin: 18px 0; border-collapse: collapse; }
        .meta td { width: 33.33%; padding: 8px 10px 8px 0; vertical-align: top; }
        .label { display: block; color: #64748b; font-size: 9px; }
        .parties { width: 100%; margin: 10px 0 18px; border-collapse: collapse; }
        .parties td { width: 50%; padding: 8px 10px 8px 0; vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .items th { padding: 9px 7px; border-bottom: 1px solid #cbd5e1; color: #64748b; text-align: left; font-size: 9px; }
        .items td { padding: 11px 7px; border-bottom: 1px solid #e2e8f0; }
        .right { text-align: right !important; }
        .total td { border-top: 1px solid #cbd5e1; border-bottom: 0; font-size: 13px; font-weight: bold; }
        .total .amount { color: #166534; }
        .details { width: 100%; margin-top: 27px; border-collapse: collapse; }
        .details td { width: 50%; padding: 0 18px 0 0; vertical-align: top; }
        .detail-list { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .detail-list td { width: auto; padding: 6px 4px; border-bottom: 1px solid #e2e8f0; }
        .detail-list td:first-child { color: #64748b; }
        .notice { margin-top: 8px; padding: 10px; background: #fffbeb; color: #78350f; }
        .footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 9px; }
    </style>
</head>
<body>
    <div class="watermark {{ $invoice->status === 'paid' ? 'watermark-paid' : 'watermark-unpaid' }}">{{ $invoice->status === 'paid' ? 'PAID' : 'UNPAID' }}</div>
    <header class="header">
        <span class="status {{ $invoice->status === 'paid' ? 'paid' : 'unpaid' }}">{{ $invoice->status === 'paid' ? 'DIBAYAR' : 'BELUM DIBAYAR' }}</span>
        <img class="logo" src="{{ public_path('images/logo-aski.png') }}" alt="Logo ASKI-PTMA">
        <p class="brand">ASKI-PTMA</p>
        <h1>INVOICE</h1>
        <p class="muted">Tagihan Keanggotaan</p>
    </header>

    @php($verifiedPayment = $invoice->payments->firstWhere('status', \App\Models\Payment::VERIFIED))
    @if ($verifiedPayment)
        <p class="success-note">Pembayaran telah diverifikasi pada {{ ($verifiedPayment->reviewed_at ?? $verifiedPayment->created_at)?->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB.</p>
    @endif

    <table class="meta">
        <tr>
            <td><span class="label">Nomor Invoice</span><strong>{{ $invoice->number }}</strong></td>
            <td><span class="label">Dibuat Pada</span><strong>{{ $invoice->issued_at?->format('d/m/Y') }}</strong></td>
            <td><span class="label">Jatuh Tempo</span><strong>{{ $invoice->due_at?->format('d/m/Y') }}</strong></td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td><span class="label">Ditagihkan Kepada</span><strong>{{ $invoice->user->name }}</strong><br>{{ $invoice->user->email }}</td>
            <td><span class="label">Keanggotaan</span><strong>{{ $invoice->duration_months }} bulan</strong><br>ASKI-PTMA</td>
        </tr>
    </table>

    <table class="items">
        <thead><tr><th>#</th><th>Deskripsi</th><th class="right">Jumlah</th><th class="right">Subtotal (IDR)</th></tr></thead>
        <tbody><tr><td>1</td><td>Iuran keanggotaan ASKI-PTMA ({{ $invoice->duration_months }} bulan)</td><td class="right">1</td><td class="right">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td></tr></tbody>
        <tfoot><tr class="total"><td colspan="3" class="right">Total Tagihan</td><td class="right amount">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td></tr></tfoot>
    </table>

    <table class="details">
        <tr>
            <td>
                <h2>Detail Pembayaran</h2>
                @if ($verifiedPayment)
                    <table class="detail-list">
                        <tr><td>Status bukti</td><td class="right">Terverifikasi</td></tr>
                        <tr><td>Diverifikasi</td><td class="right">{{ ($verifiedPayment->reviewed_at ?? $verifiedPayment->created_at)?->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</td></tr>
                    </table>
                @else
                    <p class="muted">Belum ada pembayaran yang diverifikasi.</p>
                @endif
            </td>
            <td>
                <h2>Informasi Pembayaran</h2>
                @if ($invoice->status === 'unpaid' && $setting->isConfigured())
                    <table class="detail-list">
                        <tr><td>Bank</td><td class="right">{{ $setting->bank_name }}</td></tr>
                        <tr><td>Nomor rekening</td><td class="right">{{ $setting->account_number }}</td></tr>
                        <tr><td>Atas nama</td><td class="right">{{ $setting->account_holder }}</td></tr>
                    </table>
                @elseif ($invoice->status === 'unpaid')
                    <p class="notice">Instruksi rekening belum tersedia. Silakan hubungi Admin.</p>
                @else
                    <p class="muted">Tagihan telah dibayar.</p>
                @endif
            </td>
        </tr>
    </table>

    <footer class="footer">Dokumen ini diterbitkan oleh ASKI-PTMA. Nominal dan status mengikuti data tagihan pada sistem.</footer>
</body>
</html>
