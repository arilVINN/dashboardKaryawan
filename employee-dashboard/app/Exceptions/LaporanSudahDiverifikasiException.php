<?php

namespace App\Exceptions;

use RuntimeException;

class LaporanSudahDiverifikasiException extends RuntimeException
{
    protected $message = 'Laporan ini sudah diverifikasi';
}
