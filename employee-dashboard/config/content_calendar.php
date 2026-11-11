<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Divisions that can open the content calendar
    |--------------------------------------------------------------------------
    |
    | Employees in these divisions can view the calendar and comment on
    | events. Editing stays limited to the event creator (see the policy).
    | Matching is case-insensitive and accepts ids as well, so production
    | can pin ids without renaming divisions.
    |
    */

    'divisions' => [
        'names' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('CONTENT_CALENDAR_DIVISIONS', 'Human Resources,Marketing Communication,MarKom'))
        ))),

        'ids' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('CONTENT_CALENDAR_DIVISION_IDS', ''))
        ))),
    ],

];
