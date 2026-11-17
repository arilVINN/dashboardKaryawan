<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Divisions that can open the content calendar
    |--------------------------------------------------------------------------
    |
    | Employees in these divisions can view the calendar and edit or comment
    | on its events. Matching is case-insensitive and checks stable division
    | codes first, then display names, then explicit ids.
    |
    */

    'divisions' => [
        'names' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env(
                'CONTENT_CALENDAR_DIVISIONS',
                'HR,Kelas Kerjasama,Publikasi Buku,Publikasi Jurnal,Academic Event,Kreatif Digital'
            ))
        ))),

        // Stable division codes are checked before display names, so a rename
        // in the HRD UI does not silently revoke calendar access.
        'codes' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('CONTENT_CALENDAR_DIVISION_CODES', 'HR,KJ,PB,PJ,AE,KD'))
        ))),

        'ids' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('CONTENT_CALENDAR_DIVISION_IDS', ''))
        ))),
    ],

];
