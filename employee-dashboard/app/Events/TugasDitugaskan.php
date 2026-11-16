<?php

namespace App\Events;

use App\Models\Tugas;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TugasDitugaskan
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Tugas $tugas
    ) {}
}
