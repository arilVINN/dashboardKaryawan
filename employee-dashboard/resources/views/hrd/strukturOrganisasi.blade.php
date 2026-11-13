<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struktur Organisasi - PT Silindo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ======= Clean Modern Profile Card Styles ======= */
        .org-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
            border: 1.5px solid #cbd5e1;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .org-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(44, 74, 110, 0.12);
            border-color: #94a3b8;
        }
        .org-card-photo {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            object-fit: cover;
            background: #e2e8f0;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #475569;
            font-size: 18px;
        }
        /* Connector Lines */
        .v-line { width: 1.5px; background: #475569; }
        .h-line { height: 1.5px; background: #475569; }
    </style>
</head>

<body class="flex h-screen overflow-hidden bg-slate-50">
    @include('component_hrd.sidebar')

    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
        @include('component.topbar')

        <main class="flex-1 overflow-y-auto p-8 pt-6">
            @include('component.breadcrumbs', [
                'parentText' => 'Dashboard',
                'parentUrl'  => route('hrd.dashboard'),
                'currentPage'=> 'Struktur Organisasi',
                'inline'     => true,
            ])

            <div class="mb-6">
                <x-page-heading title="Struktur Organisasi" icon="building" />
                <p class="mt-1 text-sm text-slate-500">Bagan organisasi dan struktural PT Silindo. Klik pada kartu untuk melihat detail data diri.</p>
            </div>

            <section aria-label="Bagan struktur organisasi">
                <div class="overflow-x-auto pb-12">
                    {{-- Canvas Lebar Tetap 1200px agar presisi --}}
                    <div style="width: 1200px; margin: 0 auto; position: relative;">

                        {{-- ============================================================ --}}
                        {{-- LEVEL 1: PENGAWAS PERUSAHAAN                                 --}}
                        {{-- ============================================================ --}}
                        <div class="flex justify-center">
                            <div class="org-card" style="width: 280px;"
                                 data-employee='{"nama":"Sudiyono, S.E., M.M.","jabatan":"Pengawas Perusahaan","divisi":"Pengawasan","tanggal_lahir":"12 Mei 1975","email":"sudiyono@silindo.co.id","foto":""}'>
                                <div class="org-card-photo">S</div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-slate-900 truncate">Sudiyono, S.E., M.M.</h4>
                                    <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Pengawas Perusahaan</p>
                                </div>
                            </div>
                        </div>

                        {{-- Garis L1 -> L2 --}}
                        <div class="v-line mx-auto" style="height: 24px;"></div>

                        {{-- ============================================================ --}}
                        {{-- LEVEL 2: DIREKTUR UTAMA                                      --}}
                        {{-- ============================================================ --}}
                        <div class="flex justify-center">
                            <div class="org-card" style="width: 300px;"
                                 data-employee='{"nama":"Dr. (c) Meida R., M.M., M.H., Ph.D","jabatan":"Direktur Utama","divisi":"Direksi","tanggal_lahir":"20 Agustus 1980","email":"meida@silindo.co.id","foto":""}'>
                                <div class="org-card-photo">M</div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-slate-900 truncate">Dr. (c) Meida R., M.M.</h4>
                                    <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Direktur Utama</p>
                                </div>
                            </div>
                        </div>

                        {{-- ============================================================ --}}
                        {{-- KANVAS KONEKTOR UTAMA (Menyambung dari Dir Utama menembus ke Bawah) --}}
                        {{-- ============================================================ --}}
                        <div style="position: relative; width: 1200px; height: 250px;">
                            {{-- Main vertical line continues from the director to the department branch. --}}
                            <div class="v-line" style="position: absolute; left: 600px; top: 0; height: 250px;"></div>

                            {{-- 2. Kotak Direktur Operasional diletakkan secara absolute menimpa garis tengah --}}
                            <div style="position: absolute; left: 450px; top: 44px; width: 300px; z-index: 2;">
                                <div class="org-card"
                                     data-employee='{"nama":"Jeffry R., S.E., M.H.","jabatan":"Direktur Operasional","divisi":"Operasional","tanggal_lahir":"10 Desember 1982","email":"jeffry@silindo.co.id","foto":""}'>
                                    <div class="org-card-photo">J</div>
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-slate-900 truncate">Jeffry R., S.E., M.H.</h4>
                                        <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5">Direktur Operasional</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Director branch: Legal and Personal Assistant, then Operations Director. --}}
                            <div class="h-line" style="position: absolute; left: 160px; width: 440px; top: 24px;"></div>
                            <div class="v-line" style="position: absolute; left: 160px; top: 24px; height: 192px;"></div>
                            <div class="v-line" style="position: absolute; left: 380px; top: 24px; height: 192px;"></div>

                            {{-- Operations branch: Finance & Accounting and HR & GA. --}}
                            <div class="h-line" style="position: absolute; left: 600px; width: 440px; top: 130px;"></div>
                            <div class="v-line" style="position: absolute; left: 820px; top: 130px; height: 86px;"></div>
                            <div class="v-line" style="position: absolute; left: 1040px; top: 130px; height: 86px;"></div>
                        </div>

                        {{-- ============================================================ --}}
                        {{-- LEVEL PENDUKUNG: LEGAL, PA, FINANCE, & HR (Sejajar)          --}}
                        {{-- ============================================================ --}}
                        <div style="position: relative; width: 1200px; height: 75px; margin-top: -34px;">
                            <div class="v-line" style="position: absolute; left: 600px; top: 0; height: 85px;"></div>

                            {{-- 1. LEGAL --}}
                            <div class="org-card" style="position: absolute; left: 75px; top: 0; width: 170px;"
                                 data-employee='{"nama":"Yemima N.P., S.H.","jabatan":"Legal","divisi":"Legal &amp; Compliance","tanggal_lahir":"15 Maret 1992","email":"yemima@silindo.co.id","foto":""}'>
                                <div class="org-card-photo" style="width: 42px; height: 42px; font-size: 14px;">Y</div>
                                <div class="min-w-0">
                                    <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Yemima N.P.</h4>
                                    <p class="text-[10px] text-slate-500 font-medium mt-0.5">Legal</p>
                                </div>
                            </div>

                            {{-- 2. PERSONAL ASSISTANT --}}
                            <div class="org-card" style="position: absolute; left: 295px; top: 0; width: 170px;"
                                 data-employee='{"nama":"Setianingsih, S.Pd.","jabatan":"Personal Assistant","divisi":"Direksi","tanggal_lahir":"05 November 1990","email":"setianingsih@silindo.co.id","foto":""}'>
                                <div class="org-card-photo" style="width: 42px; height: 42px; font-size: 14px;">S</div>
                                <div class="min-w-0">
                                    <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Setianingsih</h4>
                                    <p class="text-[10px] text-slate-500 font-medium mt-0.5">Personal Asst.</p>
                                </div>
                            </div>

                            {{-- 3. FINANCE & ACCOUNTING --}}
                            <div class="org-card" style="position: absolute; left: 735px; top: 0; width: 170px;"
                                 data-employee='{"nama":"Jeffry R., S.E., M.H.","jabatan":"Finance &amp; Accounting","divisi":"Finance","tanggal_lahir":"10 Desember 1982","email":"finance@silindo.co.id","foto":""}'>
                                <div class="org-card-photo" style="width: 42px; height: 42px; font-size: 14px;">J</div>
                                <div class="min-w-0">
                                    <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Jeffry R.</h4>
                                    <p class="text-[10px] text-slate-500 font-medium mt-0.5">Finance</p>
                                </div>
                            </div>

                            {{-- 4. HR & GA --}}
                            <div class="org-card" style="position: absolute; left: 955px; top: 0; width: 170px;"
                                 data-employee='{"nama":"Agnes Galih C.R., M.Si.","jabatan":"HR &amp; GA","divisi":"HR &amp; GA","tanggal_lahir":"25 Juni 1988","email":"agnes@silindo.co.id","foto":""}'>
                                <div class="org-card-photo" style="width: 42px; height: 42px; font-size: 14px;">A</div>
                                <div class="min-w-0">
                                    <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Agnes Galih</h4>
                                    <p class="text-[10px] text-slate-500 font-medium mt-0.5">HR & GA</p>
                                </div>
                            </div>

                        </div>

                        {{-- ============================================================ --}}
                        {{-- GARIS T LEVEL 4: 6 DEPARTEMEN                                --}}
                        {{-- ============================================================ --}}
                        <div style="position: relative; width: 1200px; height: 32px; margin-top: 10px;">
                            <div class="h-line" style="position: absolute; left: 110px; width: 980px; top: 0;"></div>
                            @foreach ([110, 306, 502, 698, 894, 1090] as $colX)
                                <div class="v-line" style="position: absolute; left: {{ $colX }}px; top: 0; height: 32px;"></div>
                            @endforeach
                        </div>

                        {{-- ============================================================ --}}
                        {{-- LEVEL 4: 6 KOTAK DEPARTEMEN                                  --}}
                        {{-- ============================================================ --}}
                        <div style="display: grid; grid-template-columns: repeat(6, 180px); justify-content: center; gap: 16px; width: 1200px;">

                            {{-- 1. KELAS KERJASAMA --}}
                            <div class="org-card self-start" style="padding: 8px;"
                                 data-employee='{"nama":"Siti Z.B., S.Pd., M.A.","jabatan":"Admin Kerjasama","divisi":"Kelas Kerjasama","tanggal_lahir":"10 Januari 1993","email":"siti@silindo.co.id","foto":""}'>
                                <div class="org-card-photo" style="width: 40px; height: 40px; font-size: 14px;">S</div>
                                <div class="min-w-0">
                                    <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Siti Z.B.</h4>
                                    <p class="text-[9.5px] text-slate-500 mt-0.5">Admin Kerjasama</p>
                                </div>
                            </div>

                            {{-- 2. PUBLIKASI BUKU --}}
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="org-card" style="padding: 8px;"
                                     data-employee='{"nama":"Durotur Rohma, S.Sos.","jabatan":"Koord. Buku","divisi":"Publikasi Buku","tanggal_lahir":"12 April 1991","email":"durotur@silindo.co.id","foto":""}'>
                                    <div class="org-card-photo" style="width: 40px; height: 40px; font-size: 14px;">D</div>
                                    <div class="min-w-0">
                                        <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Durotur R.</h4>
                                        <p class="text-[9.5px] text-slate-500 mt-0.5">Koord. Buku</p>
                                    </div>
                                </div>
                                <div class="org-card" style="padding: 8px;"
                                     data-employee='{"nama":"Cindy Reta Q., S.S.","jabatan":"Admin Buku","divisi":"Publikasi Buku","tanggal_lahir":"18 September 1995","email":"cindy@silindo.co.id","foto":""}'>
                                    <div class="org-card-photo" style="width: 40px; height: 40px; font-size: 14px;">C</div>
                                    <div class="min-w-0">
                                        <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Cindy Reta</h4>
                                        <p class="text-[9.5px] text-slate-500 mt-0.5">Admin Buku</p>
                                    </div>
                                </div>
                                <div class="org-card" style="padding: 8px;"
                                     data-employee='{"nama":"Vega Rossy D.P., S.E.","jabatan":"Marcom Buku","divisi":"Publikasi Buku","tanggal_lahir":"04 Juli 1994","email":"vega@silindo.co.id","foto":""}'>
                                    <div class="org-card-photo" style="width: 40px; height: 40px; font-size: 14px;">V</div>
                                    <div class="min-w-0">
                                        <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Vega Rossy</h4>
                                        <p class="text-[9.5px] text-slate-500 mt-0.5">Marcom Buku</p>
                                    </div>
                                </div>
                                <div class="org-card" style="padding: 8px;"
                                     data-employee='{"nama":"Argha Y.P., M.Hum.","jabatan":"Layouter","divisi":"Publikasi Buku","tanggal_lahir":"22 Februari 1992","email":"argha@silindo.co.id","foto":""}'>
                                    <div class="org-card-photo" style="width: 40px; height: 40px; font-size: 14px;">A</div>
                                    <div class="min-w-0">
                                        <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Argha Y.P.</h4>
                                        <p class="text-[9.5px] text-slate-500 mt-0.5">Layouter</p>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. PUBLIKASI JURNAL --}}
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="org-card" style="padding: 8px;"
                                     data-employee='{"nama":"Vini A., S.P., M.P.","jabatan":"Koord. Jurnal","divisi":"Publikasi Jurnal","tanggal_lahir":"30 Juli 1989","email":"vini@silindo.co.id","foto":""}'>
                                    <div class="org-card-photo" style="width: 40px; height: 40px; font-size: 14px;">V</div>
                                    <div class="min-w-0">
                                        <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Vini A.</h4>
                                        <p class="text-[9.5px] text-slate-500 mt-0.5">Koord. Jurnal</p>
                                    </div>
                                </div>
                                <div class="org-card" style="padding: 8px;"
                                     data-employee='{"nama":"Yovita A., S.Tp., M.P.","jabatan":"Staff Operasional","divisi":"Publikasi Jurnal","tanggal_lahir":"19 Mei 1992","email":"yovita@silindo.co.id","foto":""}'>
                                    <div class="org-card-photo" style="width: 40px; height: 40px; font-size: 14px;">Y</div>
                                    <div class="min-w-0">
                                        <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Yovita A.</h4>
                                        <p class="text-[9.5px] text-slate-500 mt-0.5">Staff Operasional</p>
                                    </div>
                                </div>
                                <div class="org-card" style="padding: 8px;"
                                     data-employee='{"nama":"Annisa S.F., S.Pd., M.P.","jabatan":"Admin Jurnal","divisi":"Publikasi Jurnal","tanggal_lahir":"14 Agustus 1994","email":"annisa@silindo.co.id","foto":""}'>
                                    <div class="org-card-photo" style="width: 40px; height: 40px; font-size: 14px;">A</div>
                                    <div class="min-w-0">
                                        <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Annisa S.F.</h4>
                                        <p class="text-[9.5px] text-slate-500 mt-0.5">Admin Jurnal</p>
                                    </div>
                                </div>
                            </div>

                            {{-- 4. ACADEMIC EVENT --}}
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="org-card" style="padding: 8px;"
                                     data-employee='{"nama":"Puntoadji P., M.A.B.","jabatan":"Koord. AE","divisi":"Academic Event","tanggal_lahir":"14 Februari 1987","email":"puntoadji@silindo.co.id","foto":""}'>
                                    <div class="org-card-photo" style="width: 40px; height: 40px; font-size: 14px;">P</div>
                                    <div class="min-w-0">
                                        <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Puntoadji P.</h4>
                                        <p class="text-[9.5px] text-slate-500 mt-0.5">Koord. AE</p>
                                    </div>
                                </div>
                                <div class="org-card" style="padding: 8px;"
                                     data-employee='{"nama":"Grace Juliana Leu, S.H.","jabatan":"Admin AE","divisi":"Academic Event","tanggal_lahir":"09 Oktober 1995","email":"grace@silindo.co.id","foto":""}'>
                                    <div class="org-card-photo" style="width: 40px; height: 40px; font-size: 14px;">G</div>
                                    <div class="min-w-0">
                                        <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Grace Juliana</h4>
                                        <p class="text-[9.5px] text-slate-500 mt-0.5">Admin AE</p>
                                    </div>
                                </div>
                            </div>

                            {{-- 5. KREATIF DIGITAL --}}
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="org-card" style="padding: 8px;"
                                     data-employee='{"nama":"Muhammad M., S.Or.","jabatan":"Digital Marketing","divisi":"Kreatif Digital","tanggal_lahir":"01 Oktober 1994","email":"muhammad@silindo.co.id","foto":""}'>
                                    <div class="org-card-photo" style="width: 40px; height: 40px; font-size: 14px;">M</div>
                                    <div class="min-w-0">
                                        <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Muhammad M.</h4>
                                        <p class="text-[9.5px] text-slate-500 mt-0.5">Digital Mkg.</p>
                                    </div>
                                </div>
                                <div class="org-card" style="padding: 8px;"
                                     data-employee='{"nama":"Eko Rustanto, S.Ds.","jabatan":"Desain Grafis","divisi":"Kreatif Digital","tanggal_lahir":"27 November 1993","email":"eko@silindo.co.id","foto":""}'>
                                    <div class="org-card-photo" style="width: 40px; height: 40px; font-size: 14px;">E</div>
                                    <div class="min-w-0">
                                        <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Eko Rustanto</h4>
                                        <p class="text-[9.5px] text-slate-500 mt-0.5">Desain Grafis</p>
                                    </div>
                                </div>
                            </div>

                            {{-- 6. IT --}}
                            <div class="org-card self-start" style="padding: 8px;"
                                 data-employee='{"nama":"Wardi, S.Kom., M.Pd.","jabatan":"IT Support","divisi":"IT","tanggal_lahir":"05 Desember 1992","email":"wardi@silindo.co.id","foto":""}'>
                                <div class="org-card-photo" style="width: 40px; height: 40px; font-size: 14px;">W</div>
                                <div class="min-w-0">
                                    <h4 class="text-[11px] font-bold text-slate-900 leading-tight">Wardi, S.Kom.</h4>
                                    <p class="text-[9.5px] text-slate-500 mt-0.5">IT Support</p>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </section>
        </main>
    </div>

    {{-- ============================================================ --}}
    {{-- MODAL POP-UP DETAIL KARYAWAN                                 --}}
    {{-- ============================================================ --}}
    <div id="employee-profile-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm"
         role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="employee-profile-title">

        <div class="relative w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl">
            <button type="button" data-profile-close aria-label="Tutup profil"
                class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>

            <div class="px-7 pb-5 pt-7 sm:px-9">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-700">Struktur organisasi</p>
                <h3 id="employee-profile-title" class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Profil Karyawan</h3>
            </div>

            <div class="grid gap-6 px-7 pb-8 sm:px-9 md:grid-cols-[176px_minmax(0,1fr)]">
                <div class="mx-auto h-44 w-40 overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200 md:mx-0">
                    <img id="employee-profile-photo" alt="" class="hidden h-full w-full object-cover">
                    <div id="employee-profile-initial"
                         class="flex h-full w-full items-center justify-center bg-gradient-to-br from-sky-100 to-indigo-100 text-5xl font-bold text-sky-800">
                        U
                    </div>
                </div>

                <dl class="divide-y divide-slate-200">
                    <div class="grid grid-cols-[minmax(100px,0.8fr)_minmax(0,1.2fr)] items-center gap-4 py-3 first:pt-0">
                        <dt class="text-sm text-slate-500">Nama Lengkap</dt>
                        <dd id="employee-profile-name" class="break-words text-right text-sm font-bold text-slate-900"></dd>
                    </div>
                    <div class="grid grid-cols-[minmax(100px,0.8fr)_minmax(0,1.2fr)] items-center gap-4 py-3">
                        <dt class="text-sm text-slate-500">Jabatan</dt>
                        <dd id="employee-profile-position" class="break-words text-right text-sm font-bold text-slate-900"></dd>
                    </div>
                    <div class="grid grid-cols-[minmax(100px,0.8fr)_minmax(0,1.2fr)] items-center gap-4 py-3">
                        <dt class="text-sm text-slate-500">Divisi</dt>
                        <dd id="employee-profile-division" class="break-words text-right text-sm font-bold text-slate-900"></dd>
                    </div>
                    <div class="grid grid-cols-[minmax(100px,0.8fr)_minmax(0,1.2fr)] items-center gap-4 py-3">
                        <dt class="text-sm text-slate-500">Tanggal Lahir</dt>
                        <dd id="employee-profile-birthday" class="break-words text-right text-sm font-bold text-slate-900"></dd>
                    </div>
                    <div class="grid grid-cols-[minmax(100px,0.8fr)_minmax(0,1.2fr)] items-center gap-4 py-3 last:pb-0">
                        <dt class="text-sm text-slate-500">Email</dt>
                        <dd id="employee-profile-email" class="break-words text-right text-sm font-bold text-slate-900"></dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('employee-profile-modal');
            if (!modal) return;

            const photo = document.getElementById('employee-profile-photo');
            const initial = document.getElementById('employee-profile-initial');
            const fields = {
                name: document.getElementById('employee-profile-name'),
                position: document.getElementById('employee-profile-position'),
                division: document.getElementById('employee-profile-division'),
                birthday: document.getElementById('employee-profile-birthday'),
                email: document.getElementById('employee-profile-email')
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
            };

            const openModal = (card) => {
                let employee;
                try {
                    employee = JSON.parse(card.dataset.employee);
                } catch (error) {
                    console.error('Gagal membaca data profil karyawan:', error);
                    return;
                }

                fields.name.textContent = employee.nama || '-';
                fields.position.textContent = employee.jabatan || '-';
                fields.division.textContent = employee.divisi || '-';
                fields.birthday.textContent = employee.tanggal_lahir || '-';
                fields.email.textContent = employee.email || '-';

                initial.textContent = (employee.nama || 'U').trim().charAt(0).toUpperCase();
                if (employee.foto) {
                    photo.onerror = () => {
                        photo.classList.add('hidden');
                        initial.classList.remove('hidden');
                    };
                    photo.src = employee.foto.startsWith('http') || employee.foto.startsWith('/')
                        ? employee.foto
                        : '/storage/' + employee.foto;
                    photo.alt = 'Foto profil ' + (employee.nama || 'karyawan');
                    photo.classList.remove('hidden');
                    initial.classList.add('hidden');
                } else {
                    photo.removeAttribute('src');
                    photo.classList.add('hidden');
                    initial.classList.remove('hidden');
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
            };

            document.querySelectorAll('[data-employee]').forEach((card) => {
                card.setAttribute('role', 'button');
                card.setAttribute('tabindex', '0');
                card.addEventListener('click', () => openModal(card));
                card.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        openModal(card);
                    }
                });
            });

            modal.querySelectorAll('[data-profile-close]').forEach((button) => {
                button.addEventListener('click', closeModal);
            });
            modal.addEventListener('click', (event) => {
                if (event.target === modal) closeModal();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                    closeModal();
                }
            });
        });
    </script>

</body>

</html>