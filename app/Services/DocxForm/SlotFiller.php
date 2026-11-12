<?php

namespace App\Services\DocxForm;

/**
 * Applies a field map to a DocxTemplate.
 *
 * Map entry: key => [paragraph prefix, spec, nth match = 0, paragraph offset = 0]
 * spec: int            nth dot slot (>=2 dots/…)
 *       'eN'           nth ellipsis token (also a lone "…")
 *       'append'       text added at the end of the paragraph
 *       'after:LABEL'  text inserted right after LABEL
 *       'swap:OLD|NEW' literal OLD replaced by NEW, where {v} is the value
 */
class SlotFiller
{
    /** @param array<string,array> $map @param array<string,mixed> $d */
    public static function apply(DocxTemplate $t, array $map, array $d, bool $pad = false): void
    {
        $ops = [];
        foreach ($map as $key => $def) {
            $v = trim((string) ($d[$key] ?? ''));
            if ($v === '') {
                continue;
            }
            [$prefix, $spec, $nth, $off] = $def + [2 => 0, 3 => 0];
            $rank = -1; // non-numeric specs run after numeric ones
            if (is_int($spec)) {
                $rank = $spec;
            } elseif (preg_match('/^e(\d+)$/', $spec, $m)) {
                $rank = (int) $m[1];
            }
            $ops[] = [$prefix . '|' . $nth . '|' . $off, $prefix, $nth, $off, $spec, $v, $rank];
        }
        // per paragraph: higher token index first, so a filled token never shifts the ones still to do
        usort($ops, fn ($a, $b) => [$a[0], $b[6]] <=> [$b[0], $a[6]]);

        // locate every paragraph first: filling one may change the text other lookups rely on
        foreach ($ops as $i => $o) {
            $ops[$i][] = $t->find($o[1], $o[2], $o[3]);
        }

        foreach ($ops as [, , , , $spec, $v, , $p]) {
            $t->unjustify($p);
            if (is_int($spec)) {
                $t->fillSlot($p, $spec, $v, $pad);
            } elseif ($spec === 'append') {
                $t->fillSlot($p, 'append', $v);
            } elseif (preg_match('/^e(\d+)$/', $spec, $m)) {
                $t->fillEllipsis($p, (int) $m[1], $v, $pad);
            } elseif (str_starts_with($spec, 'after:')) {
                $t->fillAfter($p, substr($spec, 6), $v);
            } elseif (str_starts_with($spec, 'swap:')) {
                [$old, $new] = explode('|', substr($spec, 5), 2);
                $t->swapText($p, $old, str_replace('{v}', $v, $new));
            } else {
                throw new \InvalidArgumentException("Bad spec $spec");
            }
        }
    }

    /** @param array<string,array{0:string,1?:int,2?:int}> $ticks key => [prefix, nth paragraph, nth box] */
    public static function ticks(DocxTemplate $t, array $ticks, array $d): void
    {
        foreach ($ticks as $key => $def) {
            if (empty($d[$key])) {
                continue;
            }
            [$prefix, $nthP, $nthBox] = $def + [1 => 0, 2 => 0];
            $t->tickBox($t->find($prefix, $nthP), $nthBox);
        }
    }

    /**
     * Fill table rows. $cols = list of keys for each cell index (null = skip).
     * Rows that already hold a number ("1", "2") keep it; the "…" row and blank rows get the running number.
     */
    public static function table(DocxTemplate $t, int $table, int $firstRow, array $cols, array $rows, int $max, int $numberFrom = 2): void
    {
        $avail = $t->tableRowCount($table) - $firstRow;
        foreach (array_slice(array_values($rows), 0, min($max, $avail)) as $i => $r) {
            $row = $firstRow + $i;
            $any = false;
            foreach ($cols as $c => $k) {
                if ($k !== null && trim((string) ($r[$k] ?? '')) !== '') {
                    $any = true;
                    $t->setCell($table, $row, $c, (string) $r[$k]);
                }
            }
            if ($any && $i >= $numberFrom) {
                $t->setCell($table, $row, 0, (string) ($i + 1));
            }
        }
    }

    /**
     * Spread a long text over several dotted lines (each line gets as many words as roughly fit the dots).
     * Lines that stay unused keep their dots; text that does not fit goes to the last line.
     *
     * @param array<int,array{0:string,1?:int,2?:int}> $targets [prefix, nth, offset] per line
     */
    public static function lines(DocxTemplate $t, string $text, array $targets, bool $pad = false): void
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        if ($text === '') {
            return;
        }
        $paras = [];
        foreach ($targets as $def) {
            [$prefix, $nth, $off] = $def + [1 => 0, 2 => 0];
            $paras[] = $t->find($prefix, $nth, $off);
        }
        $words = explode(' ', $text);
        foreach ($paras as $i => $p) {
            if (!$words) {
                break;
            }
            $cap = $t->slotWidthEm($p, 0) - 0.5; // em available on this line
            $last = $i === count($paras) - 1;
            $line = '';
            while ($words && ($last || $line === '' || FontMetrics::em($line . ' ' . $words[0]) <= $cap)) {
                $line .= ($line === '' ? '' : ' ') . array_shift($words);
            }
            $t->fillSlot($p, 0, $line, $pad);
        }
    }
}
