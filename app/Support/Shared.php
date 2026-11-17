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
        return self::withSuggestions($form, self::MAP[$form] ?? ['fields' => [], 'rows' => []]);
    }

    /** Whole map, for the export popup. */
    public static function all(): array
    {
        $out = [];
        foreach (self::MAP as $form => $cfg) {
            $out[$form] = self::withSuggestions($form, $cfg);
        }
        return $out;
    }

    private static function withSuggestions(string $form, array $cfg): array
    {
        $cfg['inherit'] = self::INHERIT[$form] ?? [];
        $cfg['offer'] = self::OFFER[$form] ?? [];
        foreach (self::PEERS as $key => $members) { // peers both publish and pick up the same suggestion
            foreach ($members as $m) {
                if ($m[0] === $form) {
                    $cfg['inherit'][$m[1]] = $key;
                    $cfg['offer'][$m[1]] = $key;
                }
            }
        }
        return $cfg;
    }

    /**
     * Suggestions (one way, unlike the shared fields above). A field listed in INHERIT starts with the value stored under
     * its source key while the user has not typed in it; it is shown as a suggestion (dashed border), the user edits or
     * clears it freely and then it is theirs. A field listed in OFFER publishes what the user types under a "sug.*" key
     * so later forms can pick it up. Source keys may also be shared keys (e.g. "nhan.0.xa": the taxpayer's ward).
     */
    /** Same fact asked in several forms under different names: suggestion key => [[form, field], ...]. */
    private const PEERS = [
        'sug.dat.thua' => [['lptb', 'dat_thua'], ['tncn', 'dat_thua'], ['sddpnn', 'gcn_thua']],
        'sug.dat.to' => [['lptb', 'dat_to'], ['tncn', 'dat_to'], ['sddpnn', 'gcn_tobd']],
        'sug.dat.dientich' => [['lptb', 'dat_dientich'], ['sddpnn', 'gcn_dientich'], ['sddpnn', 'chua_dientich'], ['sddpnn', 'dt_dungmd']],
        'sug.dat.giatri' => [['lptb', 'dat_giatri'], ['tncn', 'dat_giatri']],
        'sug.gcn.so' => [['sddpnn', 'gcn_so'], ['tncn', 'gt_so']],
        'sug.gcn.ngay' => [['sddpnn', 'gcn_ngay'], ['tncn', 'gt_ngay']],
        'sug.dv.hd.so' => [['lptb', 'dv_hopdong_so'], ['tncn', 'dv_hd_so'], ['sddpnn', 'dv_hd_so']],
        'sug.dv.hd.ngay' => [['lptb', 'dv_hopdong_ngay'], ['tncn', 'dv_hd_ngay'], ['sddpnn', 'dv_hd_ngay']],
        'sug.nha.giatri' => [['lptb', 'nha_giatri'], ['tncn', 'nha_giatri']],
        'sug.cc.chuduan' => [['lptb', 'cc_chuduan'], ['tncn', 'cc_chuduan']],
        'sug.cc.diachi' => [['lptb', 'cc_diachiduan'], ['tncn', 'cc_diachi']],
        'sug.cc.ketcau' => [['lptb', 'cc_ketcau'], ['tncn', 'cc_ketcau']],
        'sug.cc.tangnoi' => [['lptb', 'cc_tangnoi'], ['tncn', 'cc_tangnoi']],
        'sug.cc.tanghamm' => [['lptb', 'cc_tanghamm'], ['tncn', 'cc_tanghamm']],
        'sug.cc.chung' => [['lptb', 'cc_sohuuchung'], ['tncn', 'cc_chung']],
        'sug.cc.rieng' => [['lptb', 'cc_sohuuriang'], ['tncn', 'cc_rieng']],
    ];

    private const OFFER = [
        'mau18' => ['ky_ngay' => 'sug.ky.ngay', 'ky_thang' => 'sug.ky.thang', 'ky_nam' => 'sug.ky.nam'],
        'lptb' => [
            'ky_ten_ngay' => 'sug.ky.ngay', 'ky_ten_thang' => 'sug.ky.thang', 'ky_ten_nam' => 'sug.ky.nam',
        ],
        'sddpnn' => [
            'ky_ten_ngay' => 'sug.ky.ngay', 'ky_ten_thang' => 'sug.ky.thang', 'ky_ten_nam' => 'sug.ky.nam',
            'nnt_sonha' => 'sug.nnt.sonha', 'nnt_duong' => 'sug.nnt.duong', 'nnt_to' => 'sug.nnt.to',
        ],
        'sodo' => ['ky_ten_ngay' => 'sug.ky.ngay', 'ky_ten_thang' => 'sug.ky.thang', 'ky_ten_nam' => 'sug.ky.nam'],
    ];

    private const INHERIT = [
        'lptb' => [
            'ky_ten_ngay' => 'sug.ky.ngay', 'ky_ten_thang' => 'sug.ky.thang', 'ky_ten_nam' => 'sug.ky.nam',
            'dat_sonha' => 'sug.nnt.sonha', 'dat_duong' => 'sug.nnt.duong', 'dat_xa' => 'nhan.0.xa', 'dat_tinh' => 'nhan.0.tinh',
        ],
        'tncn' => [
            'dat_diachi' => 'nhan.0.diachi', 'dat_xa' => 'nhan.0.xa', 'dat_tinh' => 'nhan.0.tinh',
        ],
        'sddpnn' => [
            'ky_ten_ngay' => 'sug.ky.ngay', 'ky_ten_thang' => 'sug.ky.thang', 'ky_ten_nam' => 'sug.ky.nam',
            'dat_sonha' => 'sug.nnt.sonha', 'dat_duong' => 'sug.nnt.duong', 'dat_to' => 'sug.nnt.to',
            'dat_xa' => 'nhan.0.xa', 'dat_tinh' => 'nhan.0.tinh', 'chua_loai' => 'dat.mucdich',
        ],
        'sodo' => ['ky_ten_ngay' => 'sug.ky.ngay', 'ky_ten_thang' => 'sug.ky.thang', 'ky_ten_nam' => 'sug.ky.nam'],
    ];

    private const MAP = [
        'lptb' => [
            'fields' => [
                'nnt_ten' => 'nhan.0.ten', 'nnt_mst' => 'nhan.0.mst', 'nnt_cccd' => 'nhan.0.id', 'nnt_ngaysinh' => 'nhan.0.ngaysinh',
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
                'nguoichuyen' => ['prefix' => 'chuyen', 'offset' => 0, 'cols' => ['ten' => 'ten', 'id' => 'id', 'ngaysinh' => 'ngaysinh', 'dienthoai' => 'dienthoai']],
                'nguoinhan' => ['prefix' => 'nhan', 'offset' => 0, 'cols' => ['ten' => 'ten', 'id' => 'id', 'ngaysinh' => 'ngaysinh']],
            ],
        ],
        'sddpnn' => [
            'fields' => [
                'nnt_ten' => 'nhan.0.ten', 'nnt_mst' => 'nhan.0.mst', 'nnt_cccd' => 'nhan.0.id', 'nnt_ngaysinh' => 'nhan.0.ngaysinh',
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
        'sodo' => [
            'fields' => [
                'sodo_ten' => 'nhan.0.ten', 'sodo_diachi' => 'nhan.0.diachi',
                'ky_ten_noi' => 'ky.noi',
            ],
            'rows' => [],
        ],
        'mau18' => [
            'fields' => [
                'ten' => 'nhan.0.ten', 'diachi' => 'nhan.0.diachi', 'dienthoai' => 'nhan.0.dienthoai', 'email' => 'nhan.0.email',
                // identity paper: the number is the same "số định danh" the other forms ask for
                'idloai' => 'nhan.0.idloai', 'idso' => 'nhan.0.id', 'idngay' => 'nhan.0.idngay', 'idnoi' => 'nhan.0.idnoi',
                'ky_noi' => 'ky.noi',
            ],
            'rows' => [],
        ],
    ];
}
