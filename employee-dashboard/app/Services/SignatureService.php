<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Support\FileStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class SignatureService
{
    private const CANVAS_WIDTH = 900;

    private const CANVAS_HEIGHT = 300;

    private const CANVAS_MARGIN = 20;

    public function pathFor(Karyawan $employee): ?string
    {
        $path = $employee->tanda_tangan;

        if (! $path || ! Storage::disk('private')->exists($path)) {
            return null;
        }

        return $path;
    }

    public function store(UploadedFile $file, Karyawan $employee): string
    {
        $path = $file->storeAs('tanda_tangan', $this->filename($employee), 'private');

        if (! $path) {
            throw new RuntimeException('Gagal menyimpan tanda tangan.');
        }

        try {
            $this->process($path);
        } catch (\Throwable $e) {
            Storage::disk('private')->delete($path);

            throw $e;
        }

        return $this->finalize($path, $employee);
    }

    public function storeDataUri(string $dataUri, Karyawan $employee): string
    {
        if (! preg_match('/^data:image\/png;base64,([A-Za-z0-9+\/=\r\n]+)$/', $dataUri, $matches)) {
            throw new InvalidArgumentException('Format tanda tangan yang digambar tidak valid.');
        }

        $binary = base64_decode(preg_replace('/\s+/', '', $matches[1]), true);

        if ($binary === false || $binary === '') {
            throw new InvalidArgumentException('Tanda tangan yang digambar tidak valid.');
        }

        $path = 'tanda_tangan/'.$this->filename($employee);

        if (! Storage::disk('private')->put($path, $binary)) {
            throw new RuntimeException('Gagal menyimpan tanda tangan.');
        }

        try {
            $this->process($path);
        } catch (\Throwable $e) {
            Storage::disk('private')->delete($path);

            throw $e;
        }

        return $this->finalize($path, $employee);
    }

    public function snapshot(Karyawan $employee, string $prefix): ?string
    {
        $source = $this->pathFor($employee);

        if (! $source) {
            return null;
        }

        $safePrefix = preg_replace('/[^A-Za-z0-9_-]/', '', $prefix) ?: 'signature';
        $destination = 'tanda_tangan_surat_izin/'
            .$safePrefix.'-'.now()->format('YmdHis').'-'.Str::lower(Str::random(4)).'.png';

        return Storage::disk('private')->copy($source, $destination) ? $destination : null;
    }

    public function dataUri(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = FileStorage::diskFor($path);

        if ($disk === null) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode(
            (string) Storage::disk($disk)->get($path)
        );
    }

    private function finalize(string $path, Karyawan $employee): string
    {
        $oldPath = $employee->tanda_tangan;
        $employee->update(['tanda_tangan' => $path]);

        if ($oldPath && $oldPath !== $path && Storage::disk('private')->exists($oldPath)) {
            Storage::disk('private')->delete($oldPath);
        }

        return $path;
    }

    private function filename(Karyawan $employee): string
    {
        return $employee->id_karyawan.'-'.now()->format('YmdHis')
            .'-'.Str::lower(Str::random(4)).'.png';
    }

    /**
     * Bersihkan latar putih/transparan, pangkas ruang kosong, lalu simpan
     * sebagai PNG transparan berukuran standar agar konsisten di PDF.
     */
    private function process(string $path): void
    {
        $absolute = Storage::disk('private')->path($path);
        $contents = is_file($absolute) ? file_get_contents($absolute) : false;
        $source = $contents !== false ? @imagecreatefromstring($contents) : false;

        if (! $source) {
            throw new InvalidArgumentException('File tanda tangan tidak dapat dibaca.');
        }

        if (function_exists('imagepalettetotruecolor') && ! imageistruecolor($source)) {
            imagepalettetotruecolor($source);
        }

        $width = imagesx($source);
        $height = imagesy($source);

        if ($width < 16 || $height < 16) {
            imagedestroy($source);

            throw new InvalidArgumentException('Ukuran tanda tangan terlalu kecil.');
        }

        $rgba = imagecreatetruecolor($width, $height);
        imagealphablending($rgba, false);
        imagesavealpha($rgba, true);
        imagecopy($rgba, $source, 0, 0, 0, 0, $width, $height);
        imagedestroy($source);

        $ink = imagecreatetruecolor($width, $height);
        imagealphablending($ink, false);
        imagesavealpha($ink, true);
        imagefill($ink, 0, 0, imagecolorallocatealpha($ink, 0, 0, 0, 127));

        $minX = $width;
        $minY = $height;
        $maxX = -1;
        $maxY = -1;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgbaValue = imagecolorat($rgba, $x, $y);
                $alpha = ($rgbaValue >> 24) & 0x7F;

                if ($alpha >= 120) {
                    continue;
                }

                $red = ($rgbaValue >> 16) & 0xFF;
                $green = ($rgbaValue >> 8) & 0xFF;
                $blue = $rgbaValue & 0xFF;
                $luminance = (int) round(0.299 * $red + 0.587 * $green + 0.114 * $blue);

                if ($luminance >= 245) {
                    continue;
                }

                // Semakin gelap piksel, semakin opak. Ini menghapus latar
                // putih dan mempertahankan tepi anti-alias tanda tangan.
                $newAlpha = (int) round(127 * ($luminance / 255));
                $newColor = ($newAlpha << 24) | ($red << 16) | ($green << 8) | $blue;

                imagesetpixel($ink, $x, $y, $newColor);

                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
            }
        }

        imagedestroy($rgba);

        if ($maxX < $minX || $maxY < $minY) {
            imagedestroy($ink);

            throw new InvalidArgumentException('Tanda tangan tidak terdeteksi. Gunakan gambar tanda tangan yang jelas.');
        }

        $contentWidth = $maxX - $minX + 1;
        $contentHeight = $maxY - $minY + 1;
        $padding = (int) max(8, round(max($contentWidth, $contentHeight) * 0.08));

        $cropX = max(0, $minX - $padding);
        $cropY = max(0, $minY - $padding);
        $cropWidth = min($width - $cropX, $contentWidth + (2 * $padding));
        $cropHeight = min($height - $cropY, $contentHeight + (2 * $padding));

        $canvas = imagecreatetruecolor(self::CANVAS_WIDTH, self::CANVAS_HEIGHT);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));

        $availableWidth = self::CANVAS_WIDTH - (2 * self::CANVAS_MARGIN);
        $availableHeight = self::CANVAS_HEIGHT - (2 * self::CANVAS_MARGIN);
        $scale = min($availableWidth / $cropWidth, $availableHeight / $cropHeight);
        $newWidth = max(1, (int) round($cropWidth * $scale));
        $newHeight = max(1, (int) round($cropHeight * $scale));

        imagecopyresampled(
            $canvas,
            $ink,
            (int) ((self::CANVAS_WIDTH - $newWidth) / 2),
            (int) ((self::CANVAS_HEIGHT - $newHeight) / 2),
            $cropX,
            $cropY,
            $newWidth,
            $newHeight,
            $cropWidth,
            $cropHeight
        );

        imagedestroy($ink);

        $written = imagepng($canvas, $absolute);
        imagedestroy($canvas);

        if (! $written) {
            throw new RuntimeException('Gagal memproses tanda tangan.');
        }
    }
}
