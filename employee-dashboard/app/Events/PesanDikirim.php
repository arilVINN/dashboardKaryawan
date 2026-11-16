<?php

namespace App\Events;

use App\Models\Pesan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PesanDikirim
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Pesan $pesan
    ) {}
}
