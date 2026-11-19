<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shared employee form options
    |--------------------------------------------------------------------------
    |
    | Single source of truth for the select options used by the employee form
    | and its API validation. Keep labels human readable (sentence case).
    |
    */

    'jenis_kelamin' => ['Laki-laki', 'Perempuan'],

    'status' => ['Aktif', 'Tidak Aktif'],

    'pendidikan' => ['SMK', 'D3', 'S1', 'S2', 'S3'],

    'job_level' => ['Magang', 'Junior', 'Senior', 'Manager'],

    /*
     * Jabatan titles that predate the jabatan table. Kept for legacy imports
     * and API clients that still post a plain `jabatan` string.
     */
    'jabatan_legacy' => [
        'Direktur Utama',
        'Kepala Divisi',
        'Staff',
        'HRD Manager',
        'Markom',
        'Layouter',
    ],

    /*
     * Upload constraints. Real MIME types are checked server side as well.
     */
    'photo' => [
        'types' => ['image/jpeg', 'image/png', 'image/webp'],
        'max_kb' => 2048,
    ],

    'document' => [
        'types' => ['application/pdf', 'image/jpeg', 'image/png'],
        'max_kb' => 5120,
        'jenis' => [
            'lamaran' => 'Surat lamaran',
            'ijazah' => 'Ijazah',
            'transkrip' => 'Transkrip nilai',
            'ktp' => 'KTP',
            'kk' => 'Kartu keluarga (KK)',
            'offering_letter' => 'Offering letter',
            'lainnya' => 'Lainnya / SK',
        ],
    ],
];
