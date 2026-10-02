<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajement Pesan</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=PT+Sans:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@500&display=swap">

    <style>
        body {
            margin: 0;
            line-height: normal;
            font-family: 'PT Sans', sans-serif;
        }

        .detail-pesan-page {
            width: 100%;
            min-height: 100%;
            position: relative;
            background-color: #fff;
            overflow-x: hidden;
            text-align: left;
            font-size: 14px;
            color: #565e74;
        }

        /* JUDUL */
        .manajement-pesan {
            position: absolute;
            top: 32px;
            left: 30px;
            font-size: 24px;
            line-height: 32px;
            color: #000;
            font-weight: 700;
        }

        /* AREA CONTENT */
        .detail-pesan-content {
            position: relative;
            padding: 32px 30px 60px;
        }

        /* TABEL */
        .tabel {
            margin-top: 70px;
            width: 100%;
            min-height: 500px;
            box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
            border-radius: 2px;
            overflow: hidden;
            color: #565e74;
        }

        .frame-parent {
            width: 100%;
            height: 30px;
            background-color: #d9d9d9;
            display: grid;
            grid-template-columns: 40% 20.5% 21.3% 18.2%;
            align-items: center;
            box-sizing: border-box;
        }

        .nama-divisi-parent,
        .tugas-wrapper,
        .keterangan-parent {
            height: 30px;
            display: flex;
            align-items: center;
            box-sizing: border-box;
        }

        .nama-divisi-parent {
            padding: 0 10px;
            gap: 10px;
        }

        .tugas-wrapper {
            justify-content: center;
            padding: 0 10px;
        }

        .keterangan-parent {
            justify-content: flex-end;
            padding: 0 10px;
            gap: 10px;
        }

        .aksi-header {
            justify-content: center;
            padding: 0 10px;
        }

        .cari {
            position: relative;
            line-height: 22px;
        }

        /* ISI TABEL */
        .tabel-body {
            background-color: #fff;
            min-height: 500px;
        }

        .tabel-row {
            min-height: 64px;
            display: grid;
            grid-template-columns: 40% 20.5% 21.3% 18.2%;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            box-sizing: border-box;
            color: #565e74;
            background-color: #fff;
        }

        .tabel-row:hover {
            background-color: #f8fafc;
        }

        .cell {
            padding: 14px 20px;
            box-sizing: border-box;
            line-height: 20px;
        }

        .cell-center {
            text-align: center;
        }

        .cell-right {
            text-align: right;
        }

        /* STATUS */
        .status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 13px;
            line-height: 18px;
            white-space: nowrap;
        }

        .status-selesai {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-proses {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-menunggu {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .status-revisi {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        /* BUTTON AKSI */
        .buttontertiary {
            border-radius: 9px;
            background-color: #0e9dc3;
            width: 148.4px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            box-sizing: border-box;
            text-align: center;
            font-size: 16px;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .buttontertiary:hover {
            background-color: #0b8eaf;
        }

        .aksi {
            position: relative;
            line-height: 24px;
        }

        /* BUTTON DI DALAM TABEL */
        .aksi-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 80px;
            height: 32px;
            padding: 0 12px;
            border: none;
            border-radius: 7px;
            background-color: #0e9dc3;
            color: #fff;
            font-family: 'PT Sans', sans-serif;
            font-size: 13px;
            cursor: pointer;
        }

        .aksi-button:hover {
            background-color: #0b8eaf;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .detail-pesan-content {
                padding: 24px 20px 40px;
            }

            .manajement-pesan {
                left: 20px;
            }

            .tabel {
                overflow-x: auto;
            }

            .frame-parent,
            .tabel-row {
                min-width: 850px;
            }
        }
    </style>
</head>

<body class="bg-white flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    @include('component.sidebar')

    {{-- AREA KANAN --}}
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        @include('component.topbar')

        {{-- CONTENT --}}
        <main class="flex-1 overflow-y-auto">

            <div class="detail-pesan-page">

                <div class="detail-pesan-content">

                    {{-- JUDUL --}}
                    <b class="manajement-pesan">
                        Manajement Pesan
                    </b>

                    {{-- TOMBOL AKSI --}}
                    <div class="flex justify-end">
                        <button type="button" class="buttontertiary">
                            <span class="aksi">Aksi</span>
                        </button>
                    </div>

                    {{-- TABEL --}}
                    <div class="tabel">

                        {{-- HEADER TABEL --}}
                        <div class="frame-parent">

                            <div class="nama-divisi-parent">
                                <div class="cari">
                                    Nama Divisi
                                </div>
                            </div>

                            <div class="tugas-wrapper">
                                <div class="cari">
                                    Tugas
                                </div>
                            </div>

                            <div class="keterangan-parent">
                                <div class="cari">
                                    Keterangan
                                </div>
                            </div>

                            <div class="aksi-header">
                                <div class="cari">
                                    Aksi
                                </div>
                            </div>

                        </div>

                        {{-- ISI TABEL --}}
                        <div class="tabel-body">

                            {{-- DATA 1 --}}
                            <div class="tabel-row">

                                <div class="cell">
                                    Content Writer
                                </div>

                                <div class="cell cell-center">
                                    Review artikel website
                                </div>

                                <div class="cell cell-center">
                                    <span class="status status-proses">
                                        Sedang dikerjakan
                                    </span>
                                </div>

                                <div class="cell cell-center">
                                    <button type="button" class="aksi-button">
                                        Lihat
                                    </button>
                                </div>

                            </div>

                            {{-- DATA 2 --}}
                            <div class="tabel-row">

                                <div class="cell">
                                    Digital Marketing
                                </div>

                                <div class="cell cell-center">
                                    Konten promosi
                                </div>

                                <div class="cell cell-center">
                                    <span class="status status-menunggu">
                                        Menunggu
                                    </span>
                                </div>

                                <div class="cell cell-center">
                                    <button type="button" class="aksi-button">
                                        Lihat
                                    </button>
                                </div>

                            </div>

                            {{-- DATA 3 --}}
                            <div class="tabel-row">

                                <div class="cell">
                                    IT Support
                                </div>

                                <div class="cell cell-center">
                                    Pemeriksaan sistem
                                </div>

                                <div class="cell cell-center">
                                    <span class="status status-selesai">
                                        Selesai
                                    </span>
                                </div>

                                <div class="cell cell-center">
                                    <button type="button" class="aksi-button">
                                        Lihat
                                    </button>
                                </div>

                            </div>

                            {{-- DATA 4 --}}
                            <div class="tabel-row">

                                <div class="cell">
                                    Human Resource
                                </div>

                                <div class="cell cell-center">
                                    Rekap data karyawan
                                </div>

                                <div class="cell cell-center">
                                    <span class="status status-revisi">
                                        Perlu revisi
                                    </span>
                                </div>

                                <div class="cell cell-center">
                                    <button type="button" class="aksi-button">
                                        Lihat
                                    </button>
                                </div>

                            </div>

                            {{-- DATA 5 --}}
                            <div class="tabel-row">

                                <div class="cell">
                                    Content Writer
                                </div>

                                <div class="cell cell-center">
                                    Pembuatan artikel baru
                                </div>

                                <div class="cell cell-center">
                                    <span class="status status-menunggu">
                                        Menunggu
                                    </span>
                                </div>

                                <div class="cell cell-center">
                                    <button type="button" class="aksi-button">
                                        Lihat
                                    </button>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>

</body>
</html>