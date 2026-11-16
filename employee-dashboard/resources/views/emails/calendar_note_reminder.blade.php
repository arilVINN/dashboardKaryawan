<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat Catatan Kalender</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px;
            color: #334155;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #146C94;
            color: #ffffff;
            padding: 28px 32px;
            text-align: left;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        .content {
            padding: 32px;
        }
        .badge {
            display: inline-block;
            background-color: #e0f2fe;
            color: #0e7490;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }
        .note-card {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        .note-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 10px 0;
        }
        .note-meta {
            font-size: 13px;
            color: #0e7490;
            margin-bottom: 8px;
        }
        .note-desc {
            font-size: 14px;
            color: #475569;
            line-height: 1.6;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed #7dd3fc;
        }
        .btn {
            display: inline-block;
            background-color: #146C94;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 16px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PT Silindo - Dashboard Karyawan</h1>
        </div>

        <div class="content">
            <span class="badge">Pengingat Catatan</span>

            <p style="margin: 0 0 12px 0; font-size: 15px;">
                Halo <strong>{{ $note->user?->karyawan?->nama ?? 'Staff' }}</strong>,
            </p>
            <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.5; color: #475569;">
                Ini pengingat untuk catatan kalender Anda. Mohon periksa kembali detailnya
                agar tidak ada agenda yang terlewat.
            </p>

            <div class="note-card">
                <div class="note-title">{{ $note->judul }}</div>

                <div class="note-meta">
                    📅 <strong>Tanggal Catatan:</strong>
                    {{ $note->tanggal->locale('id')->translatedFormat('l, d F Y') }}
                </div>

                @if($note->reminderLabel())
                    <div class="note-meta">
                        🔔 <strong>Pengingat:</strong>
                        {{ $note->reminderLabel() }}
                    </div>
                @endif

                @if($note->detail)
                    <div class="note-desc">
                        <strong>Detail:</strong><br>
                        {!! nl2br(e($note->detail)) !!}
                    </div>
                @endif
            </div>

            <div style="text-align: center; margin-top: 28px;">
                <a href="{{ $calendarUrl }}" class="btn">Buka Kalender</a>
            </div>
        </div>

        <div class="footer">
            Email ini dikirim otomatis oleh sistem PT Silindo. Mohon untuk tidak membalas email ini secara langsung.
        </div>
    </div>
</body>
</html>
