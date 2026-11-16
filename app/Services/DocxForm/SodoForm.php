<?php

namespace App\Services\DocxForm;

/**
 * Sơ đồ vị trí thửa đất: the last sheet of the 01/TK-SDDPNN file, exported as its own form.
 * The drawing area stays empty on purpose (drawn by hand).
 */
class SodoForm
{
    public const START = 'SƠ ĐỒ VỊ TRÍ THỬA ĐẤT';

    public const SLOTS = [
        'sodo_ten' => ['Họ và tên:', 0],
        // "Hà Nội, ngày…….tháng…….năm…….": the three ellipsis tokens after the (literal) place
        'ky_ten_ngay' => ['Hà Nội, ngày', 'e0'], 'ky_ten_thang' => ['Hà Nội, ngày', 'e1'], 'ky_ten_nam' => ['Hà Nội, ngày', 'e2'],
    ];

    public static function fill(DocxTemplate $t, array $d): void
    {
        $t->keepRange(self::START, null);
        SlotFiller::apply($t, self::SLOTS, $d);

        // signing place: the template prints "Hà Nội"; empty keeps it
        $noi = trim((string) ($d['ky_ten_noi'] ?? ''));
        if ($noi !== '') {
            $t->replaceText($t->find('Hà Nội, ngày'), 'Hà Nội', $noi);
        }

        // the address line has two dotted runs: replace the whole line so no stray dots follow the text
        $addr = trim((string) ($d['sodo_diachi'] ?? ''));
        if ($addr !== '') {
            $t->setParagraphText($t->find('Địa chỉ:'), 'Địa chỉ: ' . preg_replace('/\s+/u', ' ', $addr));
        }
    }
}
