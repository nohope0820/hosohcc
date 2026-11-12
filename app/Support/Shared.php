<?php

namespace App\Support;

/**
 * Fields that mean the same thing in several forms. They are typed once and the browser copies them to the
 * other forms (kept in localStorage "hcc-shared").
 *
 * Only identity and text values are shared. Dates and figures (areas, values, contract / certificate / plot
 * numbers, percentages, house numbers) stay per form on purpose: they differ from one form to the next.
 *
 * Shared key = "<who/what>.<index>.<field>" for people (nhan = receiver / taxpayer, chuyen = transferor),
 * "<group>.<field>" for the rest (dat = land, dv = tax agent, ky = signing place).
 */
class Shared
{
    /** @return array{fields: array<string,string>, rows: array<string,array{prefix:string,offset:int,cols:array<string,string>}>} */
    public static function for(string $form): array
    {
        return self::MAP[$form] ?? ['fields' => [], 'rows' => []];
    }

    /** Whole map, for the bundle page. */
    public static function all(): array
    {
        return self::MAP;
    }

    private const MAP = [
        'lptb' => [
            'fields' => [
                'nnt_ten' => 'nhan.0.ten', 'nnt_mst' => 'nhan.0.mst', 'nnt_cccd' => 'nhan.0.id',
                'nnt_diachi' => 'nhan.0.diachi', 'nnt_xa' => 'nhan.0.xa', 'nnt_tinh' => 'nhan.0.tinh',
                'nnt_dienthoai' => 'nhan.0.dienthoai', 'nnt_email' => 'nhan.0.email',
                'giao_ten' => 'chuyen.0.ten', 'giao_mst' => 'chuyen.0.mst', 'giao_cccd' => 'chuyen.0.id',
                'giao_diachi' => 'chuyen.0.diachi',
                'dv_ten' => 'dv.ten', 'dv_mst' => 'dv.mst', 'dv_nguoi_ten' => 'dv.nguoi', 'dv_chungchi' => 'dv.chungchi',
                'dat_duong' => 'dat.duong', 'dat_xa' => 'dat.xa', 'dat_tinh' => 'dat.tinh', 'dat_mucdich' => 'dat.mucdich',
                'ky_ten_noi' => 'ky.noi',
            ],
            // co-owners are the 2nd, 3rd... receivers (the 1st is the taxpayer above)
            'rows' => ['dongsohuu' => ['prefix' => 'nhan', 'offset' => 1, 'cols' => ['ten' => 'ten', 'mst' => 'mst', 'cccd' => 'id']]],
        ],
        'tncn' => [
            'fields' => [
                'dv_ten' => 'dv.ten', 'dv_mst' => 'dv.mst', 'kt_ten' => 'dv.nguoi', 'kt_chungchi' => 'dv.chungchi',
                'dat_xa' => 'dat.xa', 'dat_tinh' => 'dat.tinh', 'dat_nguongoc' => 'dat.nguongoc',
                'ky_ten_noi' => 'ky.noi',
            ],
            'rows' => [
                'nguoichuyen' => ['prefix' => 'chuyen', 'offset' => 0, 'cols' => ['ten' => 'ten', 'id' => 'id', 'dienthoai' => 'dienthoai']],
                'nguoinhan' => ['prefix' => 'nhan', 'offset' => 0, 'cols' => ['ten' => 'ten', 'id' => 'id']],
            ],
        ],
        'sddpnn' => [
            'fields' => [
                'nnt_ten' => 'nhan.0.ten', 'nnt_mst' => 'nhan.0.mst', 'nnt_cccd' => 'nhan.0.id',
                'nnt_xa' => 'nhan.0.xa', 'nnt_tinh' => 'nhan.0.tinh', 'nnt_dc_tb' => 'nhan.0.diachi',
                'nnt_dienthoai' => 'nhan.0.dienthoai',
                'dv_ten' => 'dv.ten', 'dv_mst' => 'dv.mst', 'kt_ten' => 'dv.nguoi', 'kt_chungchi' => 'dv.chungchi',
                'giao_ten' => 'chuyen.0.ten', 'giao_mst' => 'chuyen.0.mst', 'giao_cccd' => 'chuyen.0.id',
                'dat_duong' => 'dat.duong', 'dat_xa' => 'dat.xa', 'dat_tinh' => 'dat.tinh',
                'nguongoc' => 'dat.nguongoc', 'gcn_loai' => 'dat.mucdich',
                'ky_ten_noi' => 'ky.noi',
            ],
            'rows' => ['nguoisd' => ['prefix' => 'nhan', 'offset' => 0, 'cols' => ['ten' => 'ten', 'mst' => 'mst', 'id' => 'id']]],
        ],
        'mau18' => [
            'fields' => [
                'dienthoai' => 'nhan.0.dienthoai', 'email' => 'nhan.0.email', 'diachi' => 'nhan.0.diachi',
                'ky_noi' => 'ky.noi',
            ],
            'rows' => ['nguoisd' => ['prefix' => 'nhan', 'offset' => 0, 'cols' => ['ten' => 'ten', 'diachi' => 'diachi', 'mst' => 'mst', 'id' => 'id']]],
        ],
    ];
}
