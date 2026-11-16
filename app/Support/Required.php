<?php

namespace App\Support;

/**
 * Key fields the bundle export reminds about when empty.
 * form => [label => spec]; spec = a field name, 'a|b' (any of them is enough) or [a, b, ...] (all of them).
 * Field names are the ones the form page posts (table cells as "rows[0][col]"). Reminder only: never blocks the export.
 */
class Required
{
    public static function all(): array
    {
        return self::MAP;
    }

    private const DATE = ['ky_ngay', 'ky_thang', 'ky_nam'];
    private const SIGN = ['ky_ten_ngay', 'ky_ten_thang', 'ky_ten_nam'];

    private const MAP = [
        'mau18' => [
            'Tên người đăng ký' => 'ten', 'Giấy tờ nhân thân (loại, số)' => ['idloai', 'idso'], 'Địa chỉ' => 'diachi',
            'Điện thoại liên hệ' => 'dienthoai', 'Nội dung biến động' => 'noidung', 'Ngày ký' => self::DATE,
        ],
        'lptb' => [
            'Người nộp thuế' => 'nnt_ten', 'Số định danh / Hộ chiếu' => 'nnt_cccd', 'Địa chỉ người nộp thuế' => 'nnt_diachi',
            'Xã/phường người nộp thuế' => 'nnt_xa', 'Tỉnh/Thành phố người nộp thuế' => 'nnt_tinh',
            'Người chuyển giao' => 'giao_ten', 'Thửa đất số' => 'dat_thua', 'Tờ bản đồ số' => 'dat_to',
            'Xã/phường của thửa đất' => 'dat_xa', 'Tỉnh/thành phố của thửa đất' => 'dat_tinh', 'Diện tích đất' => 'dat_dientich',
            'Giá trị nhà, đất' => 'tong_giatri', 'Ngày lập tờ khai' => self::DATE, 'Ngày ký' => self::SIGN,
        ],
        'tncn' => [
            'Người chuyển nhượng' => 'nguoichuyen[0][ten]', 'Số định danh người chuyển nhượng' => 'nguoichuyen[0][id]',
            'Người nhận' => 'nguoinhan[0][ten]', 'Thửa đất số' => 'dat_thua', 'Tờ bản đồ số' => 'dat_to',
            'Địa chỉ thửa đất' => 'dat_diachi', 'Xã/phường' => 'dat_xa', 'Tỉnh/thành phố' => 'dat_tinh',
            'Giá trị đất' => 'dat_giatri', 'Ngày lập tờ khai' => self::DATE,
        ],
        'sddpnn' => [
            'Năm tính thuế' => 'ky_nam', 'Họ và tên người nộp thuế' => 'nnt_ten', 'Số định danh / Hộ chiếu' => 'nnt_cccd',
            'Địa chỉ nhận thông báo thuế' => 'nnt_dc_tb', 'Xã/phường của thửa đất' => 'dat_xa', 'Tỉnh/thành phố của thửa đất' => 'dat_tinh',
            'Diện tích thửa đất' => 'gcn_dientich|chua_dientich', 'Ngày ký' => self::SIGN,
        ],
        'sodo' => [
            'Họ và tên' => 'sodo_ten', 'Địa chỉ' => 'sodo_diachi', 'Ngày ký' => self::SIGN,
        ],
    ];
}
