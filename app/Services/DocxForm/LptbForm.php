<?php

namespace App\Services\DocxForm;

/**
 * Mẫu 01/LPTB - Tờ khai lệ phí trước bạ (nhà, đất).
 * Maps flat input keys -> paragraph prefix + slot in the official template.
 */
class LptbForm
{
    public const START = 'Mẫu số: 01/LPTB';
    public const END = 'Mẫu số: 03/BĐS';

    /** key => [paragraph prefix, slot index|'append', nth paragraph match, paragraph offset] */
    private const SLOTS = [
        'nnt_ten'        => ['[04]', 0],
        'nnt_ngaysinh'   => ['[05]', 'append'],
        'nnt_mst'        => ['[06]', 0],
        'nnt_cccd'       => ['[07]', 0],
        'nnt_diachi'     => ['[08]', 0],
        'nnt_xa'         => ['[09]', 0],
        'nnt_tinh'       => ['[09]', 1],
        'nnt_dienthoai'  => ['[11]', 0],
        'nnt_fax'        => ['[11]', 1],
        'nnt_email'      => ['[11]', 2],
        'dv_ten'         => ['được ủy quyền khai thay (nếu có):', 0],
        'dv_mst'         => ['[15]', 0],
        'dv_hopdong_so'  => ['[16]', 0],
        'dv_hopdong_ngay'=> ['[16]', 1],

        'dat_loai'       => ['1. Đất:', 0],
        'dat_thua'       => ['1.1.', 0],
        'dat_to'         => ['1.1.', 1],
        'dat_sonha'      => ['1.2.1.', 0],
        'dat_toanha'     => ['1.2.1.', 1],
        'dat_ngo'        => ['1.2.1.', 2],
        'dat_duong'      => ['Đường/Phố:', 0],
        'dat_thon'       => ['Đường/Phố:', 1],
        'dat_xa'         => ['1.2.2.', 0],
        'dat_tinh'       => ['1.2.3.', 0],
        'dat_vitri'      => ['1.3.', 0],
        'dat_mucdich'    => ['1.4.', 0],
        'dat_dientich'   => ['1.5.', 0],
        'giao_ten'       => ['- Tên tổ chức/cá nhân chuyển giao QSDĐ:', 0],
        'giao_mst'       => ['- Mã số thuế:', 0],
        'giao_cccd'      => ['- Số định danh cá nhân/ Số hộ chiếu:', 0],
        'giao_diachi'    => ['- Địa chỉ người giao QSDĐ:', 0],
        'giao_ngay'      => ['b) Thời điểm làm giấy tờ chuyển giao QSDĐ', 0],
        'giao_thang'     => ['b) Thời điểm làm giấy tờ chuyển giao QSDĐ', 1],
        'giao_nam'       => ['b) Thời điểm làm giấy tờ chuyển giao QSDĐ', 2],
        'dat_giatri'     => ['1.7.', 'append'],

        'nha_cap'        => ['Cấp nhà:', 0],
        'nha_loai'       => ['Cấp nhà:', 1],
        'nha_hang'       => ['Cấp nhà:', 2],
        'cc_chuduan'     => ['Chủ dự án:', 0],
        'cc_diachiduan'  => ['Chủ dự án:', 1],
        'cc_ketcau'      => ['Kết cấu:', 0],
        'cc_tangnoi'     => ['Kết cấu:', 1],
        'cc_tanghamm'    => ['Kết cấu:', 2],
        'cc_sohuuchung'  => ['Diện tích sở hữu chung', 0],
        'cc_sohuuriang'  => ['Diện tích sở hữu chung', 1],
        'nha_dtxd'       => ['Diện tích xây dựng (m2):', 0],
        'nha_dtsan'      => ['Diện tích sàn xây dựng (m2):', 0],
        'nha_nguongoc'   => ['2.3.', 0],
        'nha_namhoancong'=> ['- Năm hoàn công', 0],
        'nha_ngay'       => ['- Thời điểm làm giấy tờ chuyển giao nhà:', 0],
        'nha_thang'      => ['- Thời điểm làm giấy tờ chuyển giao nhà:', 1],
        'nha_nam'        => ['- Thời điểm làm giấy tờ chuyển giao nhà:', 2],
        'nha_giatri'     => ['2.4.', 0],

        'tong_giatri'    => ['3. Giá trị nhà, đất', 0, 0, 1],
        'mien_lydo'      => ['4. Tài sản thuộc diện', 0, 0, 1],
        'giayto_1'       => ['6. Giấy tờ có liên quan', 0, 0, 1],
        'giayto_2'       => ['6. Giấy tờ có liên quan', 0, 0, 2],
        'dv_nguoi_ten'   => ['Họ và tên:', 0],
        'dv_chungchi'    => ['Chứng chỉ nghiệp vụ', 0],
    ];

    public static function keys(): array
    {
        return array_keys(self::SLOTS);
    }

    /**
     * @param array<string,mixed> $d
     */
    public static function fill(DocxTemplate $t, array $d): void
    {
        $t->keepRange(self::START, self::END);

        // The official file has a typo in the co-owner table header: "M  Mã số thuế" (a stray "M" in its own run)
        $t->setParagraphText($t->find('M  Mã số thuế'), 'Mã số thuế');
        // ...and its header cells carry a negative left indent (-432) that pushes the text into the neighbouring column,
        // so "Mã số thuế" was covered by the previous header. Also drop the 6 leading spaces of "Tên tổ chức...".
        $t->setParagraphText($t->find('Tên tổ chức/cá nhân đồng sở hữu'), 'Tên tổ chức/cá nhân đồng sở hữu');
        $t->resetRowIndent(0, 0);
        // the STT column is only ~3% wide (its text used to hang out of the cell thanks to that indent)
        $t->setColumnWidths(0, [400, 1300, 600, 1800, 900]);

        SlotFiller::apply($t, self::SLOTS, $d); // one-line blanks: typed text simply replaces the dots

        // [01] Kỳ tính thuế: ngày … tháng … năm …..  (single-char ellipses)
        $p01 = $t->find('[01]');
        if (trim((string) ($d['ky_nam'] ?? '')) !== '') {
            $t->fillSlot($p01, 0, trim($d['ky_nam'])); // year is the only 2+ char slot
        }
        $day = trim((string) ($d['ky_ngay'] ?? ''));
        $month = trim((string) ($d['ky_thang'] ?? ''));
        if ($day !== '') {
            $t->replaceText($p01, '…', $day);
        }
        if ($month !== '') {
            // the day "…" is gone once filled, so the month is then the first remaining one
            $t->replaceText($p01, '…', $month, $day !== '' ? 0 : 1);
        }
        // [02] Lần đầu / [03] Bổ sung lần thứ
        $p02 = $t->find('[02]');
        if (!empty($d['lan_dau'])) {
            $t->tickSym($p02);
        }
        if (trim((string) ($d['bosung_lan'] ?? '')) !== '') {
            $t->replaceText($p02, '…', trim($d['bosung_lan']));
        }
        if (!empty($d['uy_quyen'])) {
            $t->replaceText($t->find('☐'), '☐', '☒');
        }

        // Place/date line in signature block: "..., ngày... tháng... năm..."
        $sign = $t->find('..., ngày');
        $date = [$d['ky_ten_noi'] ?? '', $d['ky_ten_ngay'] ?? '', $d['ky_ten_thang'] ?? '', $d['ky_ten_nam'] ?? ''];
        foreach (array_reverse($date, true) as $i => $v) {
            if (trim((string) $v) !== '') {
                $t->fillSlot($sign, $i, (string) $v);
            }
        }

        // 5. Co-owners table (table 0 in scope): header row 0, data rows after.
        foreach (($d['dongsohuu'] ?? []) as $i => $o) {
            $row = 1 + $i;
            if ($row >= $t->tableRowCount(0)) {
                break;
            }
            $t->fillCell(0, $row, 0, (string) ($i + 1));
            $t->fillCell(0, $row, 1, (string) ($o['ten'] ?? ''));
            $t->fillCell(0, $row, 2, (string) ($o['mst'] ?? ''));
            $t->fillCell(0, $row, 3, (string) ($o['cccd'] ?? ''));
            $t->fillCell(0, $row, 4, (string) ($o['tyle'] ?? ''));
        }
    }
}
