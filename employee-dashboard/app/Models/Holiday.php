<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    /**
     * National public holiday from the SKB 3 Menteri calendar.
     */
    public const TYPE_NATIONAL = 'libur_nasional';

    /**
     * Joint leave ("cuti bersama") published alongside national holidays.
     */
    public const TYPE_JOINT_LEAVE = 'cuti_bersama';

    /**
     * Types that represent an official day off.
     *
     * @var array<int, string>
     */
    public const DAY_OFF_TYPES = [
        self::TYPE_NATIONAL,
        self::TYPE_JOINT_LEAVE,
    ];

    protected $table = 'holidays';

    protected $fillable = [
        'date',
        'name',
        'type',
        'source',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
