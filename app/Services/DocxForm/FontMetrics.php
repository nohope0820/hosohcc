<?php

namespace App\Services\DocxForm;

/**
 * Text width in em, from the real advance widths of Liberation Serif (metric-compatible with Times New Roman,
 * the font of the forms). Reads the TrueType cmap (format 4) and hmtx tables; no kerning.
 * Falls back to an average character width when the font file is missing.
 */
class FontMetrics
{
    private const FONT = '/usr/share/fonts/truetype/liberation/LiberationSerif-Regular.ttf';
    private const FALLBACK_EM = 0.46;

    private static ?array $m = null;

    public static function em(string $text): float
    {
        $m = self::load();
        if (!$m) {
            return mb_strlen($text) * self::FALLBACK_EM;
        }
        $w = 0;
        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
            $gid = $m['cmap'][mb_ord($ch)] ?? 0;
            $w += $m['adv'][min($gid, $m['n'] - 1)] ?? 0;
        }
        return $w / $m['upem'];
    }

    private static function load(): ?array
    {
        if (self::$m !== null) {
            return self::$m ?: null;
        }
        self::$m = [];
        $d = @file_get_contents(self::FONT);
        if ($d === false) {
            return null;
        }
        $u16 = fn (int $o) => unpack('n', substr($d, $o, 2))[1];
        $u32 = fn (int $o) => unpack('N', substr($d, $o, 4))[1];
        $tables = [];
        for ($i = 0, $n = $u16(4); $i < $n; $i++) {
            $o = 12 + $i * 16;
            $tables[substr($d, $o, 4)] = $u32($o + 8);
        }
        foreach (['head', 'hhea', 'hmtx', 'cmap'] as $t) {
            if (!isset($tables[$t])) {
                return null;
            }
        }
        $upem = $u16($tables['head'] + 18);
        $nh = $u16($tables['hhea'] + 34);
        $adv = [];
        for ($g = 0; $g < $nh; $g++) {
            $adv[$g] = $u16($tables['hmtx'] + $g * 4);
        }

        // cmap: Windows Unicode BMP (3,1), format 4
        $cm = $tables['cmap'];
        $sub = null;
        for ($i = 0, $n = $u16($cm + 2); $i < $n; $i++) {
            $o = $cm + 4 + $i * 8;
            if ($u16($o) === 3 && $u16($o + 2) === 1) {
                $sub = $cm + $u32($o + 4);
            }
        }
        if ($sub === null || $u16($sub) !== 4) {
            return null;
        }
        $segX2 = $u16($sub + 6);
        $seg = $segX2 / 2;
        $endO = $sub + 14;
        $startO = $endO + $segX2 + 2;
        $deltaO = $startO + $segX2;
        $rangeO = $deltaO + $segX2;
        $map = [];
        for ($s = 0; $s < $seg; $s++) {
            $end = $u16($endO + $s * 2);
            $start = $u16($startO + $s * 2);
            $delta = $u16($deltaO + $s * 2);
            $ro = $u16($rangeO + $s * 2);
            for ($c = $start; $c <= $end && $c < 0xFFFF; $c++) {
                if ($ro === 0) {
                    $g = ($c + $delta) & 0xFFFF;
                } else {
                    $p = $rangeO + $s * 2 + $ro + ($c - $start) * 2;
                    $g = $u16($p);
                    if ($g !== 0) {
                        $g = ($g + $delta) & 0xFFFF;
                    }
                }
                if ($g) {
                    $map[$c] = $g;
                }
            }
        }
        return self::$m = ['upem' => $upem, 'adv' => $adv, 'n' => $nh, 'cmap' => $map];
    }
}
