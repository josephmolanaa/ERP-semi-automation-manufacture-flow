{{-- resources/views/emails/quotation.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .btn { 
            display: inline-block; padding: 12px 24px; 
            background: #2563eb; color: white; 
            text-decoration: none; border-radius: 6px;
            font-weight: bold;
        }
        .btn-reject { background: #dc2626; }
        .box { background: #f8fafc; border-left: 4px solid #2563eb; padding: 16px; margin: 16px 0; }
    </style>
</head>
<body>
    <p>Yth. <strong>{{ $customer->name }}</strong>,</p>

    <p>Terima kasih atas kepercayaan Anda. Berikut kami sampaikan penawaran harga dari <strong>CNC Flow</strong>:</p>

    <div class="box">
        <strong>Nomor Penawaran:</strong> {{ $quotation->nomor }}<br>
        <strong>Tanggal:</strong> {{ $quotation->tanggal->format('d F Y') }}<br>
        <strong>Berlaku s/d:</strong> {{ $quotation->berlaku_sampai->format('d F Y') }}<br>
        <strong>Total:</strong> Rp {{ number_format($quotation->total_harga, 0, ',', '.') }}
    </div>

    <p>Detail lengkap terlampir dalam file PDF. Untuk persetujuan, silakan klik tombol di bawah:</p>

    <p>
        <a href="{{ $approvalUrl }}&action=approve" class="btn">✅ Setujui Penawaran</a>
        &nbsp;&nbsp;
        <a href="{{ $approvalUrl }}&action=reject" class="btn btn-reject">❌ Tolak Penawaran</a>
    </p>

    <p><small>Link ini berlaku hingga {{ $quotation->berlaku_sampai->format('d F Y') }}. 
    Anda tidak perlu login untuk memberikan persetujuan.</small></p>

    <hr>
    <p>Hormat kami,<br><strong>Tim Sales CNC Flow</strong></p>
</body>
</html>