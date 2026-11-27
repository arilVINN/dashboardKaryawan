@php
    $groups = \App\Models\SuratIzin::JENIS_PENGAJUAN_GROUPS;
    $kategori = $suratIzin->kategori_pengajuan
        ?: \App\Models\SuratIzin::kategoriUntukJenis((string) $suratIzin->jenis_izin)
        ?: 'Izin';

    $legacyJenis = [
        'izin sakit' => 'Sakit',
        'cuti tahunan' => 'Tahunan',
        'izin keperluan pribadi' => 'Tidak Masuk',
        'cuti melahirkan' => 'Melahirkan',
        'izin keluarga' => 'Tidak Masuk',
    ];
    $lookupJenis = strtolower(trim((string) $suratIzin->jenis_izin));
    $selectedJenis = $legacyJenis[$lookupJenis] ?? $suratIzin->jenis_izin;

    $keperluan = $suratIzin->keperluan
        ?: ($kategori === 'Dinas Luar' ? 'Dinas' : 'Pribadi');

    $mulai = $suratIzin->tanggal_mulai ?? $suratIzin->tanggal_izin;
    $selesai = $suratIzin->tanggal_selesai ?? $mulai;
    $tanggalPengajuan = $suratIzin->tanggal_pengajuan ?? $suratIzin->created_at;
    $jamPengajuan = $suratIzin->jam_pengajuan_label;
    $jamOptions = \App\Models\SuratIzin::JENIS_DENGAN_JAM;
    $showSignature = $showSignature ?? false;
    $signatureDataUri = $signatureDataUri ?? null;
    $hrdSignatureDataUri = $hrdSignatureDataUri ?? null;

    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $formatTanggal = function ($value) use ($bulan) {
        if (! $value) {
            return '-';
        }

        $date = $value instanceof \Carbon\CarbonInterface
            ? $value
            : \Carbon\Carbon::parse($value);

        return $date->format('d').' '.$bulan[(int) $date->format('n')].' '.$date->format('Y');
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Form Pengajuan Perizinan Karyawan</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 9mm 9mm 10mm 9mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #000000;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8pt;
            line-height: 1.25;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .form-frame {
            width: 192mm;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo-cell {
            width: 22mm;
            padding: 1.8mm 0;
            text-align: center;
        }

        .logo {
            width: 17mm;
            height: 17mm;
        }

        .header-center {
            padding-top: 1.5mm;
            text-align: center;
        }

        .company-name {
            margin-bottom: 2mm;
            font-size: 9.5pt;
            font-weight: bold;
        }

        .title-box {
            width: 138mm;
            margin: 0 auto;
            padding: 2.1mm 2mm;
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 0.2pt;
            text-align: center;
        }

        .doc-table {
            padding: 1mm 0;
        }

        .doc-table td {
            padding: 1.6mm 3mm;
            vertical-align: middle;
        }

        .doc-left {
            width: 50%;
        }

        .doc-right {
            width: 50%;
        }

        .doc-label {
            display: inline-block;
            width: 27mm;
            font-weight: bold;
        }

        .doc-colon {
            display: inline-block;
            width: 3mm;
        }

        .info-table {
            width: 180mm;
        }

        .info-table td {
            padding: 1mm 2mm;
            vertical-align: bottom;
            line-height: 1.3;
        }

        .info-wrap {
            padding-right: 0;
        }

        .info-label {
            width: 30mm;
            padding-left: 3mm !important;
            font-weight: bold;
            white-space: nowrap;
        }

        .info-colon {
            width: 3mm;
            text-align: center;
        }

        .info-line {
            border-bottom: 1px solid #000000;
        }

        .info-spacer {
            width: 12mm;
        }

        .info-end-spacer {
            width: 6mm;
        }

        .info-right-label {
            width: 16mm;
            font-weight: bold;
            white-space: nowrap;
        }

        .keterangan-row td {
            vertical-align: top;
            padding-top: 1.4mm;
        }

        .keterangan-value {
            padding-bottom: 1.2mm !important;
            border-bottom: 1px solid #000000;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .keperluan-value {
            white-space: nowrap;
        }

        .jenis-table {
            margin-top: 2mm;
            margin-bottom: 4mm;
            page-break-inside: avoid;
        }

        .jenis-table td {
            vertical-align: top;
            padding: 0 2mm 0 0;
        }

        .jenis-table td.jenis-left {
            width: 62%;
            padding-left: 4.5mm;
        }

        .jenis-right {
            width: 38%;
        }

        .group-title {
            margin-bottom: 2mm;
            font-size: 8.5pt;
            font-weight: bold;
        }

        .group-title-spaced {
            margin-top: 4mm;
        }

        .option {
            margin-bottom: 2.2mm;
            font-size: 8pt;
            line-height: 1.3;
        }

        .jenis-table td .option:last-child {
            margin-bottom: 0;
        }

        .checkbox {
            display: inline-block;
            width: 3.5mm;
            height: 3.5mm;
            margin-right: 1.6mm;
            border: 1px solid #000000;
            font-size: 8pt;
            font-weight: bold;
            line-height: 3.3mm;
            text-align: center;
            vertical-align: -0.6mm;
        }

        .checkbox-checked {
            border-color: #111827;
            background: #111827;
            color: #ffffff;
        }

        .time-line {
            display: inline-block;
            min-width: 12mm;
            height: 2.7mm;
            margin-left: 1mm;
            border-bottom: 1px solid #000000;
            font-size: 7.4pt;
            line-height: 2.5mm;
            text-align: center;
            vertical-align: -0.3mm;
        }

        .approval {
            margin-top: 6mm;
            padding: 0 3mm 4mm;
            page-break-inside: avoid;
        }

        .approval-table td {
            width: 33.33%;
            padding: 0 3mm;
            vertical-align: top;
            text-align: center;
        }

        .approval-heading {
            font-size: 8pt;
            text-align: center;
        }

        .approval-signature-slot {
            height: 20mm;
            text-align: center;
        }

        .signature-image {
            width: 55mm;
            height: 18.3mm;
            margin-top: 1mm;
        }

        .signature-blank {
            display: inline-block;
            width: 30mm;
        }

        .approval-role {
            margin-top: 0.5mm;
            font-size: 8pt;
            text-align: center;
        }

        .approval-person {
            margin-top: 0.8mm;
            font-size: 8pt;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-frame">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if ($logoDataUri)
                        <img src="{{ $logoDataUri }}" class="logo" alt="Logo">
                    @endif
                </td>
                <td class="header-center">
                    <div class="company-name">PT. Sinergi Ilmiah Indonesia</div>
                    <div class="title-box">FORM PENGAJUAN PERIZINAN KARYAWAN</div>
                </td>
                <td class="logo-cell">
                    @if ($logoDataUri)
                        <img src="{{ $logoDataUri }}" class="logo" alt="Logo">
                    @endif
                </td>
            </tr>
        </table>

        <table class="doc-table">
            <tr>
                <td class="doc-left">
                    <span class="doc-label">No. Dokumen</span><span class="doc-colon">:</span>FR.03.02.1
                </td>
                <td class="doc-right">
                    <span class="doc-label">Tanggal Berlaku</span><span class="doc-colon">:</span>03 September 2026
                </td>
            </tr>
        </table>

        <div class="info-wrap">
            <table class="info-table">
                <tr>
                    <td class="info-label">Nama</td>
                    <td class="info-colon">:</td>
                    <td class="info-line" style="width: 50mm;">{{ $suratIzin->karyawan?->nama ?? '-' }}</td>
                    <td class="info-spacer"></td>
                    <td class="info-right-label">Divisi</td>
                    <td class="info-colon">:</td>
                    <td class="info-line" style="width: 35mm;">{{ $suratIzin->karyawan?->divisi?->nama_divisi ?? '-' }}</td>
                    <td class="info-end-spacer"></td>
                </tr>
                <tr>
                    <td class="info-label">Tanggal Pengajuan</td>
                    <td class="info-colon">:</td>
                    <td class="info-line" style="width: 50mm;">{{ $formatTanggal($tanggalPengajuan) }}</td>
                    <td colspan="4"></td>
                    <td class="info-end-spacer"></td>
                </tr>
                <tr>
                    <td class="info-label">Keperluan</td>
                    <td class="info-colon">:</td>
                    <td class="keperluan-value" colspan="5">
                        *) @if ($keperluan === 'Dinas')<b><u>Dinas</u></b> / Pribadi @else Dinas / <b><u>Pribadi</u></b> @endif
                    </td>
                    <td class="info-end-spacer"></td>
                </tr>
                <tr class="keterangan-row">
                    <td class="info-label">Keterangan</td>
                    <td class="info-colon">:</td>
                    <td class="keterangan-value" colspan="5">{!! nl2br(e($suratIzin->keterangan ?: '-')) !!}</td>
                    <td class="info-end-spacer"></td>
                </tr>
                <tr>
                    <td class="info-label">Untuk Tanggal</td>
                    <td class="info-colon">:</td>
                    <td class="info-line" style="width: 50mm; white-space: nowrap;">
                        @if ($mulai && $selesai && $selesai instanceof \Carbon\CarbonInterface && $mulai instanceof \Carbon\CarbonInterface && ! $selesai->equalTo($mulai))
                            {{ $mulai->format('d/m/Y') }} s/d {{ $selesai->format('d/m/Y') }}
                        @else
                            {{ $formatTanggal($mulai) }}
                        @endif
                    </td>
                    <td colspan="4"></td>
                    <td class="info-end-spacer"></td>
                </tr>
                <tr>
                    <td class="info-label">Jenis Pengajuan</td>
                    <td class="info-colon">:</td>
                    <td colspan="5"></td>
                    <td class="info-end-spacer"></td>
                </tr>
            </table>
        </div>

        <table class="jenis-table">
            <tr>
                <td class="jenis-left">
                    <div class="group-title">1. Izin</div>
                    @foreach ($groups['Izin'] as $jenis)
                        <div class="option">
                            <span class="checkbox {{ $selectedJenis === $jenis ? 'checkbox-checked' : '' }}">{!! $selectedJenis === $jenis ? '&#10004;' : '' !!}</span>{{ $jenis }}@if (in_array($jenis, $jamOptions, true)), jam<span class="time-line">{{ $selectedJenis === $jenis ? ($jamPengajuan ?? '') : '' }}</span>@endif
                        </div>
                    @endforeach

                    <div class="group-title group-title-spaced">2. Cuti</div>
                    @foreach ($groups['Cuti'] as $jenis)
                        <div class="option">
                            <span class="checkbox {{ $selectedJenis === $jenis ? 'checkbox-checked' : '' }}">{!! $selectedJenis === $jenis ? '&#10004;' : '' !!}</span>{{ $jenis }}
                        </div>
                    @endforeach
                </td>
                <td class="jenis-right">
                    <div class="group-title">3. Dinas Luar</div>
                    @foreach ($groups['Dinas Luar'] as $jenis)
                        <div class="option">
                            <span class="checkbox {{ $selectedJenis === $jenis ? 'checkbox-checked' : '' }}">{!! $selectedJenis === $jenis ? '&#10004;' : '' !!}</span>{{ $jenis }}
                        </div>
                    @endforeach
                </td>
            </tr>
        </table>

        <div class="approval">
            <table class="approval-table">
                <tr>
                    <td>
                        <div class="approval-heading">Disetujui Oleh,</div>
                        <div class="approval-signature-slot">
                            @if ($showSignature && $signatureDataUri)
                                <img src="{{ $signatureDataUri }}" class="signature-image" alt="Tanda tangan Kadiv">
                            @endif
                        </div>
                        <div class="approval-role">Kepala Divisi,</div>
                        <div class="approval-person">
                            @if ($suratIzin->kadiv?->nama)
                                ( {{ $suratIzin->kadiv->nama }} )
                            @else
                                (<span class="signature-blank"></span>)
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="approval-heading">Disetujui Oleh,</div>
                        <div class="approval-signature-slot">
                            @if ($showSignature && $hrdSignatureDataUri)
                                <img src="{{ $hrdSignatureDataUri }}" class="signature-image" alt="Tanda tangan HR">
                            @endif
                        </div>
                        <div class="approval-role">HR,</div>
                        <div class="approval-person">
                            @if ($suratIzin->hrd?->karyawan?->nama)
                                ( {{ $suratIzin->hrd->karyawan->nama }} )
                            @elseif ($suratIzin->hrd?->username)
                                ( {{ $suratIzin->hrd->username }} )
                            @else
                                (<span class="signature-blank"></span>)
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="approval-heading">Mengetahui,</div>
                        <div class="approval-signature-slot"></div>
                        <div class="approval-role">Direktur PT. Silindo</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
