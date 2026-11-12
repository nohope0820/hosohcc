<?php

namespace App\Services\DocxForm;

/**
 * Mẫu 03/BĐS-TNCN - Tờ khai thuế thu nhập cá nhân (chuyển nhượng / thừa kế / quà tặng bất động sản).
 * Map format: see SlotFiller.
 */
class TncnForm
{
    public const START = 'Mẫu số: 03/BĐS';
    public const END = 'Mẫu số: 01/TK-SDDPNN';

    public const SLOTS = [
        'ky_ngay' => ['[01]', 'e0'], 'ky_thang' => ['[01]', 'e1'], 'ky_nam' => ['[01]', 'e2'],
        'bosung_lan' => ['[02]', 'e0'],
        'thongbao_so' => ['[03.2]', 'e0'],
        'khaithay_ten' => ['[05]', 'append'],
        'khaithay_mst' => ['[06]', 'append'],
        'uq_so' => ['[07]', 0], 'uq_ngay' => ['[07]', 1], 'uq_thang' => ['[07]', 2], 'uq_nam' => ['[07]', 'append'],
        'dv_ten' => ['[08]', 'append'],
        'dv_mst' => ['[09]', 'append'],
        'dv_hd_so' => ['[10]', 0], 'dv_hd_ngay' => ['[10]', 'append'],

        'gt_loai' => ['[16.1]', 'append'],
        'gt_so' => ['[16.2]', 0], 'gt_noicap' => ['[16.2]', 1], 'gt_ngay' => ['[16.2]', 'append'],
        'hdduan_so' => ['[17.1]', 0], 'hdduan_ngay' => ['[17.1]', 'append'],
        'hdduan_tyle' => ['[17.3]', 'append'],
        'hdcn_so' => ['[18.1]', 0], 'hdcn_noilap' => ['[18.1]', 1], 'hdcn_ngaylap' => ['[18.1]', 'append'],
        'hdcn_cq' => ['[18.4]', 0], 'hdcn_ngayct' => ['[18.4]', 'append'],
        'dichan_noilap' => ['[19.1]', 0], 'dichan_ngaylap' => ['[19.2]', 'append'],
        'dichan_cq' => ['[19.3]', 0], 'dichan_ngayct' => ['[19.4]', 0],

        'dat_thua' => ['[20.1]', 'after:Thửa đất số:'], 'dat_to' => ['[20.1]', 'append'],
        'dat_diachi' => ['[20.3]', 0], 'dat_xa' => ['[20.4]', 'append'], 'dat_tinh' => ['[20.5]', 0],
        'dat_nguongoc' => ['[20.7]', 'append'],
        'han_nam' => ['- Có thời hạn:', 'e0'],
        'han_tu_ngay' => ['- Có thời hạn:', 'e1'], 'han_tu_thang' => ['- Có thời hạn:', 'e2'], 'han_tu_nam' => ['- Có thời hạn:', 'e3'],
        'han_den_ngay' => ['- Có thời hạn:', 'e4'], 'han_den_thang' => ['- Có thời hạn:', 'e5'], 'han_den_nam' => ['- Có thời hạn:', 'e6'],
        'dat_giatri' => ['[20.9]', 'after:(nếu có):'],

        'nr1_loai' => ['[21.2]', 'e0'], 'nr1_cap' => ['[21.2]', 'e1'], 'nr1_dtsan' => ['[21.2]', 'after:Diện tích sàn xây dựng:'],
        'nr2_loai' => ['[21.3]', 'e0'], 'nr2_cap' => ['[21.3]', 'e1'], 'nr2_dtsan' => ['[21.3]', 'after:Diện tích sàn xây dựng:'],
        'nr_nam' => ['[21.4]', 'append'],
        'cc_chuduan' => ['[21.6]', 0], 'cc_diachi' => ['[21.6]', 'append'],
        'cc_dtxd' => ['[21.8]', 0], 'cc_dtsan' => ['[21.8]', 'after:sàn xây dựng:'],
        'cc_chung' => ['[21.10]', 0], 'cc_rieng' => ['[21.10]', 'after:sở hữu riêng:'],
        'cc_ketcau' => ['[21.12]', 0], 'cc_tangnoi' => ['[21.12]', 1], 'cc_tanghamm' => ['[21.12]', 'append'],
        'cc_nam' => ['[21.15]', 'append'],
        'nha_namxd' => ['Tự xây dựng', 'append'],
        'nha_ngaygiao' => ['Chuyển nhượng', 0],
        'nha_giatri' => ['[21.19]', 'e0'],
        'ct_chuduan' => ['[21.21]', 0], 'ct_diachi' => ['[21.21]', 'append'],
        'ct_loai' => ['[21.23]', 0], 'ct_hangmuc' => ['[21.23]', 1], 'ct_cap' => ['[21.23]', 'append'],
        'ct_dtxd' => ['[21.24]', 0], 'ct_dtsan' => ['[21.24]', 'after:sàn xây dựng:'],
        'ct_heso' => ['[21.26]', 'append'], 'ct_dongia' => ['[21.27]', 'append'],
        'ct_giatri' => ['[21.28]', 'after:(nếu có):'],
        'ts_loai' => ['[22.1]', 0],
        'ts_giatri' => ['[22.2]', 'swap:(nếu có):.|(nếu có): {v} '],

        'gia_cn' => ['[24]', 0],
        'gia_tk' => ['[25]', 0],
        'hoso_1' => ['- ....', 0, 0],
        'hoso_2' => ['- ....', 0, 1],
        'kt_ten' => ['Họ và tên: ', 0],
        'kt_chungchi' => ['Chứng chỉ nghiệp vụ', 0],
    ];

    private const TICKS = [
        'lan_dau' => ['[02]'],
        'loai_dat' => ['[12]'], 'loai_nha' => ['[13]'], 'loai_thue' => ['[14]'], 'loai_khac' => ['[15]'],
        'han_lau_dai' => ['- Ổn định lâu dài'],
        'nha_tuxaydung' => ['Tự xây dựng'], 'nha_chuyennhuong' => ['Chuyển nhượng'],
        'tn_chuyennhuong' => ['[23.1]'], 'tn_thuake' => ['[23.2]'],
    ];

    public static function fill(DocxTemplate $t, array $d): void
    {
        $t->keepRange(self::START, self::END);

        // "dd/mm/yyyy" shortcuts for the validity period
        foreach (['han_tu', 'han_den'] as $k) {
            $parts = explode('/', trim((string) ($d[$k] ?? '')));
            if (count($parts) === 3) {
                [$d[$k . '_ngay'], $d[$k . '_thang'], $d[$k . '_nam']] = array_map('trim', $parts);
            }
        }

        SlotFiller::apply($t, self::SLOTS, $d); // one-line blanks: typed text simply replaces the dots
        SlotFiller::ticks($t, self::TICKS, $d);

        // signature date "..., ngày... tháng... năm..."
        $sign = $t->find('..., ngày');
        foreach (array_reverse(['ky_ten_noi', 'ky_ten_ngay', 'ky_ten_thang', 'ky_ten_nam'], true) as $i => $k) {
            if (trim((string) ($d[$k] ?? '')) !== '') {
                $t->fillSlot($sign, $i, $d[$k]);
            }
        }

        // Tables: 0 = transferors, 1 = recipients, 2 = land price rows, 3 = exemptions
        SlotFiller::table($t, 0, 3, [null, 'ten', 'id', 'ngaysinh', 'hc_so', 'hc_ngay', 'hc_noi', 'dienthoai', 'tyle'], $d['nguoichuyen'] ?? [], 6);
        SlotFiller::table($t, 1, 3, [null, 'ten', 'id', 'ngaysinh', 'hc_so', 'hc_ngay', 'hc_noi', 'tyle'], $d['nguoinhan'] ?? [], 6);
        SlotFiller::table($t, 2, 2, [null, 'duong', 'doan', 'vitri', 'loai', 'heso', 'dientich'], $d['datvitri'] ?? [], 3);

        $mien = array_slice(array_values($d['mien'] ?? []), 0, 3);
        SlotFiller::table($t, 3, 2, [null, 'ten', 'id', null, 'lydo_khac'], $mien, 3);
        foreach ($mien as $i => $r) {
            if (!empty($r['duynhat'])) {
                $t->tickCell(3, 2 + $i, 3);
            }
        }
    }
}
