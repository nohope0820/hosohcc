<?php

namespace App\Services\DocxForm;

/**
 * Mẫu 01/TK-SDDPNN - Tờ khai thuế sử dụng đất phi nông nghiệp (hộ gia đình, cá nhân) + sơ đồ vị trí thửa đất.
 * Map format: see SlotFiller.
 */
class SddpnnForm
{
    public const START = 'Mẫu số: 01/TK-SDDPNN';

    public const SLOTS = [
        'ky_nam' => ['[01]', 0],
        'lan_dau_x' => ['[02]', 0], 'bosung_lan' => ['[02]', 1],
        'nnt_ten' => ['[04]', 0], 'nnt_ngaysinh' => ['[05]', 0], 'nnt_mst' => ['[06]', 0], 'nnt_cccd' => ['[07]', 0],
        'nnt_sonha' => ['[08.1]', 0], 'nnt_duong' => ['[08.1]', 1],
        'nnt_to' => ['[08.3]', 0], 'nnt_xa' => ['[08.3]', 1],
        'nnt_tinh' => ['[08.5]', 0],
        'nnt_dc_tb' => ['[09]', 0], 'nnt_dienthoai' => ['[10]', 0],
        'dv_ten' => ['[11]', 0], 'dv_mst' => ['[12]', 0],
        'dv_hd_so' => ['[13]', 0], 'dv_hd_ngay' => ['[13]', 1],

        'nguongoc' => ['[15]', 0],
        'giao_ten' => ['[15.1]', 0], 'giao_mst' => ['[15.2]', 0], 'giao_cccd' => ['[15.3]', 0], 'giao_mapnn' => ['[15.4]', 0],
        'dat_sonha' => ['[16.1]', 0], 'dat_duong' => ['[16.1]', 1],
        'dat_to' => ['[16.3]', 0], 'dat_xa' => ['[16.3]', 1],
        'dat_tinh' => ['[16.5]', 0],
        'khaitonghop' => ['[18]', 0],
        'gcn_so' => ['[19.1]', 0], 'gcn_ngay' => ['[19.1]', 1],
        'gcn_thua' => ['[19.3]', 0], 'gcn_tobd' => ['[19.3]', 1],
        'gcn_dientich' => ['[19.5]', 0], 'gcn_loai' => ['[19.5]', 1],
        'dt_dungmd' => ['[20.1]', 0], 'dt_saimd' => ['[20.2]', 0], 'dt_chuasd' => ['[20.3]', 0],
        'dt_hanmuc' => ['[20.4]', 0], 'dt_lanchiem' => ['[20.5]', 0],
        'chua_dientich' => ['[21.1]', 0], 'chua_loai' => ['[21.1]', 1],
        'batdau_sd' => ['[22]', 0], 'thaydoi' => ['[23]', 0],
        'nha_loai' => ['[24.1]', 0], 'nha_dientich' => ['[24.1]', 1], 'nha_heso' => ['[24.1]', 2],
        'mien_lydo' => ['[25.1]', 1], 'mien_kyhan' => ['[25.2]', 0],

        'kt_ten' => ['Họ và tên:', 0, 0], 'kt_chungchi' => ['môn về thuế số:', 0],
        'sodo_ten' => ['Họ và tên:', 0, 1], 'sodo_diachi' => ['Địa chỉ:', 0],
    ];

    /** key => paragraph prefix (drawn checkbox rectangles) */
    private const SHAPES = [
        'tick_duynhat' => '[17]',
        'tick_cogcn' => '[19]',
        'tick_chuagcn' => '[21]',
    ];

    public static function fill(DocxTemplate $t, array $d): void
    {
        $t->keepRange(self::START, null);

        $d['lan_dau_x'] = !empty($d['lan_dau']) ? 'X' : '';
        SlotFiller::apply($t, self::SLOTS, $d); // one-line blanks: typed text simply replaces the dots

        foreach (self::SHAPES as $key => $prefix) {
            if (!empty($d[$key])) {
                $t->tickShape($t->find($prefix));
            }
        }

        // the location diagram is its own sheet: keep its title with the drawing area
        $t->pageBreakBefore($t->find('SƠ ĐỒ VỊ TRÍ THỬA ĐẤT'));

        $sign = $t->find('..., ngày');
        foreach (array_reverse(['ky_ten_noi', 'ky_ten_ngay', 'ky_ten_thang', 'ky_ten_nam'], true) as $i => $k) {
            if (trim((string) ($d[$k] ?? '')) !== '') {
                $t->fillSlot($sign, $i, $d[$k]);
            }
        }

        // [14] land users (table 0: STT, name, tax code, ID/passport, share)
        SlotFiller::table($t, 0, 1, [null, 'ten', 'mst', 'id', 'tyle'], $d['nguoisd'] ?? [], 7, 0);
    }
}
