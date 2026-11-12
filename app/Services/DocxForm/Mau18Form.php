<?php

namespace App\Services\DocxForm;

/**
 * Mẫu số 18 - Đơn đăng ký biến động đất đai, tài sản gắn liền với đất (+ Bảng 18a).
 * Has its own page setup (section break paragraph in the bundle).
 */
class Mau18Form
{
    public const START = 'Mẫu số 18.';
    public const END = 'Mẫu số: 01/LPTB';

    /** key => [prefix, slot] */
    private const SLOTS = [
        'dienthoai' => ['d) Điện thoại', 0],
        'email' => ['d) Điện thoại', 1],
        'gt_1' => ['(1)', 0], 'gt_2' => ['(2)', 0], 'gt_3' => ['(3)', 0], 'gt_4' => ['(4)', 0],
        'gt_5' => ['(5)', 0], 'gt_6' => ['(6)', 0], 'gt_7' => ['(7)', 0], 'gt_8' => ['(8)', 0],
    ];

    /** key => list of dotted-line paragraphs [prefix, nth, offset] that receive the wrapped text */
    private const LINES = [
        'kinhgui' => [['Kính gửi:'], ['Kính gửi:', 0, 1]],
        'ten' => [['a) Tên(2):'], ['a) Tên(2):', 0, 1]],
        'giayto_nhanthan' => [['b) Giấy tờ nhân thân'], ['b) Giấy tờ nhân thân', 0, 1], ['b) Giấy tờ nhân thân', 0, 2]],
        'diachi' => [['c) Địa chỉ(2):'], ['c) Địa chỉ(2):', 0, 1]],
        'noidung' => [
            ['2. Nội dung biến động', 0, 1], ['2. Nội dung biến động', 0, 2], ['2. Nội dung biến động', 0, 3],
            ['2. Nội dung biến động', 0, 4], ['2. Nội dung biến động', 0, 5], ['2. Nội dung biến động', 0, 6],
            ['2. Nội dung biến động', 0, 7], ['2. Nội dung biến động', 0, 8], ['2. Nội dung biến động', 0, 9],
            ['2. Nội dung biến động', 0, 10],
        ],
    ];

    public static function fill(DocxTemplate $t, array $d): void
    {
        $t->keepRange(self::START, self::END, true);

        // slots and lines are all located before anything is changed (see SlotFiller)
        for ($n = 1; $n <= 8; $n++) { // "(1)<tab leader>……": the leader would shove the text to the right margin
            if (trim((string) ($d["gt_$n"] ?? '')) !== '') {
                $t->dropTabs($t->find("($n)"));
            }
        }
        SlotFiller::apply($t, self::SLOTS, $d); // one-line blanks: typed text simply replaces the dots
        foreach (self::LINES as $key => $targets) {
            // Multi-line blanks (Kính gửi, Tên, Giấy tờ, Địa chỉ, Nội dung): the text is spread over the dotted lines and the
            // last used line is topped up with dots to the printed width. "Kính gửi" is centred in the template, so this
            // padding is what keeps it at the exact position of the blank form.
            SlotFiller::lines($t, (string) ($d[$key] ?? ''), $targets, true);
        }
        // 2.1 "…… nhu cầu cấp đổi Giấy chứng nhận"
        $doi = $d['doi_gcn'] ?? '';
        if ($doi === 'co' || $doi === 'khong') {
            $t->fillSlot($t->find('2.1.'), 0, $doi === 'co' ? 'Có' : 'Không có');
        }

        // "……., ngày……tháng……năm……"
        $sign = $t->find('……., ngày');
        foreach (array_reverse(['ky_noi', 'ky_ngay', 'ky_thang', 'ky_nam'], true) as $i => $k) {
            if (trim((string) ($d[$k] ?? '')) !== '') {
                $t->fillSlot($sign, $i, $d[$k]);
            }
        }

        // Bảng 18a (table 1): STT, name, address, tax code, ID, area/share, note
        SlotFiller::table($t, 1, 1, [null, 'ten', 'diachi', 'mst', 'id', 'tyle', 'ghichu'], $d['nguoisd'] ?? [], 6, 0);
    }
}
