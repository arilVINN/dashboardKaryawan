<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajemen Staff - PT SILINDO</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .manajemen-content {
            background-color: #fff;
            font-family: 'PT Sans', sans-serif;
        }

        .manajemen-title {
            font-size: 24px;
            line-height: 32px;
            font-weight: 700;
            color: #000;
        }

        .manajemen-writer {
            margin-top: 18px;
            margin-left: 13px;
            font-size: 24px;
            line-height: 32px;
            font-weight: 700;
            color: #000;
        }

        /* =========================
           TABEL
        ========================= */

        .manajemen-table {
            margin-top: 8px;
            width: 100%;
            min-height: 746px;
            background: #fff;
            box-shadow: 0 4px 4px rgba(0, 0, 0, 0.25);
            overflow-x: auto;
        }

        .manajemen-table-header {
            min-width: 850px;
            height: 30px;

            display: grid;
            grid-template-columns: 40% 20.5% 21.25% 18.25%;

            align-items: center;

            background-color: #d9d9d9;

            font-size: 14px;
            color: #565e74;
        }

        .manajemen-header {
            height: 30px;
            padding: 0 10px;

            display: flex;
            align-items: center;
        }

        .manajemen-header.center {
            justify-content: center;
        }

        .manajemen-header.right {
            justify-content: flex-end;
        }

        .sort-arrow {
            width: 8px;
            height: 8px;
            margin-left: 10px;

            border-right: 1px solid #565e74;
            border-bottom: 1px solid #565e74;

            transform: rotate(45deg);
        }

        /* =========================
           BARIS
        ========================= */

        .manajemen-row {
            min-width: 850px;
            min-height: 58px;

            display: grid;
            grid-template-columns: 40% 20.5% 21.25% 18.25%;

            align-items: center;

            border-bottom: 1px solid #e5e7eb;

            font-size: 14px;
            color: #565e74;
        }

        .manajemen-cell {
            padding: 12px 10px;
            line-height: 22px;
        }

        .manajemen-cell.center {
            text-align: center;
        }

        .manajemen-cell.right {
            text-align: right;
        }

        /* =========================
           AKSI
        ========================= */

        .aksi-button {
            min-width: 80px;
            height: 32px;

            padding: 0 16px;

            border: 0;
            border-radius: 9px;

            background-color: #0e9dc3;

            color: #fff;
            font-family: 'PT Sans', sans-serif;
            font-size: 14px;

            cursor: pointer;
        }

        .aksi-button:hover {
            background-color: #0c88a9;
        }

        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 768px) {

            .manajemen-content {
                padding: 24px 20px !important;
            }

            .manajemen-title,
            .manajemen-writer {
                font-size: 22px;
            }

            .manajemen-writer {
                margin-left: 0;
            }
        }
    </style>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    {{-- SIDEBAR --}}
    @include('component.sidebar')

    {{-- AREA KANAN --}}
    <div class="flex-1 flex flex-col h-screen min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        @include('component.topbar')

        {{-- CONTENT --}}
        <main class="manajemen-content flex-1 overflow-y-auto p-8">

            {{-- JUDUL --}}
            <h1 class="manajemen-title">
                Manajement Staff
            </h1>

            {{-- DIVISI --}}
            <div class="manajemen-writer">
                Writer
            </div>

            {{-- TABEL --}}
            <div class="manajemen-table">

                {{-- HEADER TABEL --}}
                <div class="manajemen-table-header">

                    <div class="manajemen-header">
                        <span>Nama Divisi</span>
                        <span class="sort-arrow"></span>
                    </div>

                    <div class="manajemen-header center">
                        <span>Tugas</span>
                    </div>

                    <div class="manajemen-header right">
                        <span>Keterangan</span>
                        <span class="sort-arrow"></span>
                    </div>

                    <div class="manajemen-header center">
                        <span>Aksi</span>
                    </div>

                </div>


                {{-- ISI TABEL --}}
                <div>

                    {{-- STAFF 1 --}}
                    <div class="manajemen-row">

                        <div class="manajemen-cell">
                            Samuel Sigalingging
                        </div>

                        <div class="manajemen-cell center">
                            3 Tugas
                        </div>

                        <div class="manajemen-cell right">
                            Aktif
                        </div>

                        <div class="manajemen-cell center">
                            <button type="button" class="aksi-button">
                                Aksi
                            </button>
                        </div>

                    </div>


                    {{-- STAFF 2 --}}
                    <div class="manajemen-row">

                        <div class="manajemen-cell">
                            Andi Pratama
                        </div>

                        <div class="manajemen-cell center">
                            2 Tugas
                        </div>

                        <div class="manajemen-cell right">
                            Aktif
                        </div>

                        <div class="manajemen-cell center">
                            <button type="button" class="aksi-button">
                                Aksi
                            </button>
                        </div>

                    </div>


                    {{-- STAFF 3 --}}
                    <div class="manajemen-row">

                        <div class="manajemen-cell">
                            Rina Maharani
                        </div>

                        <div class="manajemen-cell center">
                            4 Tugas
                        </div>

                        <div class="manajemen-cell right">
                            Aktif
                        </div>

                        <div class="manajemen-cell center">
                            <button type="button" class="aksi-button">
                                Aksi
                            </button>
                        </div>

                    </div>


                    {{-- STAFF 4 --}}
                    <div class="manajemen-row">

                        <div class="manajemen-cell">
                            Dimas Saputra
                        </div>

                        <div class="manajemen-cell center">
                            1 Tugas
                        </div>

                        <div class="manajemen-cell right">
                            Aktif
                        </div>

                        <div class="manajemen-cell center">
                            <button type="button" class="aksi-button">
                                Aksi
                            </button>
                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>

</body>

</html>