<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Approval Quotation</title>
    <style>
        body {
            align-items: center;
            background: #f8fafc;
            color: #0f172a;
            display: flex;
            font-family: Arial, sans-serif;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        main {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            max-width: 520px;
            padding: 32px;
            text-align: center;
        }

        h1 {
            font-size: 24px;
            margin: 0 0 12px;
        }

        p {
            line-height: 1.5;
            margin: 0 0 12px;
        }
    </style>
</head>
<body>
    <main>
        <h1>Terima kasih</h1>
        <p>{{ $message }}</p>
        <p>Nomor penawaran: <strong>{{ $quotation->nomor }}</strong></p>
    </main>
</body>
</html>
