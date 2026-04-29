{{-- resources/views/pdf/quotation.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; }
    .header { background: #1e3a5f; color: white; padding: 20px; display: flex; justify-content: space-between; }
    .company-name { font-size: 22px; font-weight: bold; letter-spacing: 2px; }
    .doc-title { font-size: 16px; text-align: right; }
    .doc-number { font-size: 12px; opacity: 0.8; }
    .content { padding: 20px; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
    .info-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; }
    .info-box h4 { color: #1e3a5f; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; margin: 16px 0; }
    thead th { background: #1e3a5f; color: white; padding: 8px 10px; text-align: left; font-size: 10px; }
    tbody td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    .total-row { background: #1e3a5f !important; color: white; font-weight: bold; }
    .footer { margin-top: 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
    .sign-box { border-top: 2px solid #1e3a5f; padding-top: 8px; text-align: center; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; }
    .badge-draft { background: #e2e8f0; color: #475569; }
    .badge-approved { background: #dcfce7; color: #166534; }
</style>
</head>
<body>
    <div class="header">
        <div>
            <div class="company-name">⚙ CNC FLOW</div>
            <div style="font-size:10px; opacity:0.7; margin-top:4px">Precision CNC Machining & Custom Parts</div>
        </div>
        <div style="text-align:right">
            <div class="doc-title">SURAT PENAWARAN HARGA</div>
            <div class="doc-number">{{ $quotation->nomor }}</div>
            <span class="badge badge-{{ $quotation->status }}">{{ strtoupper($quotation->status) }}</span>
        </div>
    </div>

    <div class="content">
        <div class="info-grid">
            <div class="info-box">
                <h4>Kepada Yth.</h4>
                <strong>{{ $customer->company ?? $customer->name }}</strong><br>
                {{ $customer->name }}<br>
                {{ $customer->address }}<br>
                {{ $customer->phone }}
            </div>
            <div class="info-box">
                <h4>Detail Dokumen</h4>
                <table style="border:none; margin:0">
                    <tr><td>Tanggal</td><td>: {{ $quotation->tanggal->format('d F Y') }}</td></tr>
                    <tr><td>Berlaku s/d</td><td>: {{ $quotation->berlaku_sampai->format('d F Y') }}</td></tr>
                    @if($customer->npwp)
                    <tr><td>NPWP</td><td>: {{ $customer->npwp }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="30%">Nama Part</th>
                    <th width="15%">Material</th>
                    <th width="10%">Qty</th>
                    <th width="10%">Satuan</th>
                    <th width="15%">Harga Satuan</th>
                    <th width="15%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $item->part_name }}</strong>
                        @if($item->keterangan)
                        <br><small style="color:#64748b">{{ $item->keterangan }}</small>
                        @endif
                    </td>
                    <td>{{ $item->material ?? '-' }}</td>
                    <td>{{ number_format($item->qty, 0) }}</td>
                    <td>{{ $item->satuan }}</td>
                    <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="6" style="text-align:right; padding-right:12px">TOTAL</td>
                    <td>Rp {{ number_format($quotation->total_harga, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @if($quotation->catatan)
        <div class="info-box" style="margin-top:16px">
            <h4>Catatan</h4>
            {{ $quotation->catatan }}
        </div>
        @endif

        <div class="footer">
            <div>
                <p style="margin-bottom:60px">Hormat kami,</p>
                <div class="sign-box">Tim Sales CNC Flow</div>
            </div>
            <div>
                <p style="margin-bottom:60px">Disetujui oleh,</p>
                <div class="sign-box">{{ $customer->name }} / {{ $customer->company }}</div>
            </div>
        </div>
    </div>
</body>
</html>