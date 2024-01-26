<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait Upload
{
    public function UploadFile(UploadedFile $file, string $disk = 'public'): ?string {
        $now = Carbon::now();
        $fileName = $now->format('Y_m_d_H_i_s_v_u');
        $year = $now->format('Y');
        $month = $now->format('m');
        $storedFile = $file->storeAs(
            "uploads/$year/$month",
            $fileName . "." . $file->getClientOriginalExtension(),
            $disk
        );
        if (is_bool($storedFile)) {
            return null;
        }
        return $storedFile;
    }

    public function DeleteFile(string $path, string $disk = 'public'): bool {
        return Storage::disk($disk)->delete($path);
    }
}
