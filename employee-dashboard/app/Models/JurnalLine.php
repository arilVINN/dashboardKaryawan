<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalLine extends Model
{
    protected $table = 'jurnal_lines';

    protected $fillable = [
        'jurnal_entry_id',
        'account_id',
        'debit',
        'credit',
        'sort_order',
    ];

    protected $casts = [
        'debit' => 'integer',
        'credit' => 'integer',
        'sort_order' => 'integer',
    ];

    public function entry()
    {
        return $this->belongsTo(JurnalEntry::class, 'jurnal_entry_id');
    }

    public function account()
    {
        return $this->belongsTo(CoaAkun::class, 'account_id');
    }

    public function getIsDebitAttribute(): bool
    {
        return $this->debit > 0;
    }
}
