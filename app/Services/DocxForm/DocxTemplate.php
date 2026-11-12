<?php

namespace App\Services\DocxForm;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use RuntimeException;
use ZipArchive;

/**
 * Fills the official .docx forms in place so layout/fonts/tables stay identical.
 *
 * A "slot" is a run of dots/ellipsis (".....", "……") inside a paragraph, counted from 0
 * left to right across runs. A value replaces the whole slot; the slot's first run keeps
 * its formatting. Slot "append" adds the value at the end of the paragraph instead.
 */
class DocxTemplate
{
    private const W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
    private const SLOT_RE = '/[.…]{2,}/u';
    /** Ellipsis tokens incl. a single "…" ("…/……/…", "…năm"); a lone "." is not a token. */
    private const ELL_RE = '/…[.…]*|\.{2,}/u';

    private string $path;
    private DOMDocument $dom;
    private DOMXPath $xp;
    private DOMElement $body;

    /** @var DOMElement[] top-level body children kept for the current form */
    private array $scope = [];

    public function __construct(string $path)
    {
        if (!is_file($path)) {
            throw new RuntimeException("Template not found: $path");
        }
        $this->path = $path;
        $zip = new ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        $this->dom = new DOMDocument();
        $this->dom->preserveWhiteSpace = true;
        $this->dom->loadXML($xml);
        $this->xp = new DOMXPath($this->dom);
        $this->xp->registerNamespace('w', self::W);
        $this->body = $this->xp->query('/w:document/w:body')->item(0);
    }

    /* ---------------------------------------------------------------- scope */

    /**
     * Keep only the body children from the paragraph starting with $startPrefix up to
     * (not including) the paragraph starting with $endPrefix. Page setup (final sectPr) is kept.
     */
    public function keepRange(string $startPrefix, ?string $endPrefix): void
    {
        $children = [];
        foreach ($this->body->childNodes as $c) {
            $children[] = $c;
        }
        $start = null;
        $end = count($children);
        foreach ($children as $i => $c) {
            $t = ltrim($this->text($c));
            if ($start === null && str_starts_with($t, $startPrefix)) {
                $start = $i;
            } elseif ($start !== null && $endPrefix !== null && str_starts_with($t, $endPrefix)) {
                $end = $i;
                break;
            }
        }
        if ($start === null) {
            throw new RuntimeException("Form start not found: $startPrefix");
        }
        $this->scope = [];
        foreach ($children as $i => $c) {
            $isFinalSect = $c instanceof DOMElement && $c->localName === 'sectPr';
            if ($isFinalSect) {
                continue; // always kept
            }
            if ($i >= $start && $i < $end) {
                $this->scope[] = $c;
            } else {
                $this->body->removeChild($c);
            }
        }
        // Trim trailing empty paragraphs (page breaks etc.) so no blank page follows the form.
        while ($this->scope) {
            $last = end($this->scope);
            $empty = $last instanceof DOMElement && $last->localName !== 'tbl'
                && trim($this->text($last)) === ''
                && !$this->xp->query('.//w:sectPr|.//w:drawing|.//w:pict', $last)->length;
            if (!$empty) {
                break;
            }
            $this->body->removeChild($last);
            array_pop($this->scope);
        }
        $this->dropOrphanBookmarks(); // after trimming: the trim can strand a bookmark end too
    }

    /** Cutting a form out of the bundle can strand one half of a bookmark; remove unmatched starts/ends. */
    private function dropOrphanBookmarks(): void
    {
        $starts = [];
        $ends = [];
        foreach ($this->xp->query('//w:bookmarkStart') as $n) {
            $starts[$n->getAttributeNS(self::W, 'id')][] = $n;
        }
        foreach ($this->xp->query('//w:bookmarkEnd') as $n) {
            $ends[$n->getAttributeNS(self::W, 'id')][] = $n;
        }
        foreach ($starts as $id => $nodes) {
            if (!isset($ends[$id])) {
                foreach ($nodes as $n) {
                    $n->parentNode->removeChild($n);
                }
            }
        }
        foreach ($ends as $id => $nodes) {
            if (!isset($starts[$id])) {
                foreach ($nodes as $n) {
                    $n->parentNode->removeChild($n);
                }
            }
        }
    }

    /* ----------------------------------------------------------- paragraphs */

    /** @return DOMElement[] paragraphs (incl. inside tables) in scope in document order */
    public function paragraphs(): array
    {
        $out = [];
        foreach ($this->xp->query('//w:body//w:p') as $p) {
            $out[] = $p;
        }
        return $out;
    }

    public function text(DOMNode $n): string
    {
        $s = '';
        foreach ($this->xp->query('.//w:t', $n) as $t) {
            $s .= $t->textContent;
        }
        return $s;
    }

    /**
     * Find the Nth paragraph whose text starts with $prefix (after trimming), then move $offset paragraphs on.
     */
    public function find(string $prefix, int $nth = 0, int $offset = 0): DOMElement
    {
        $all = $this->paragraphs();
        $hit = -1;
        foreach ($all as $i => $p) {
            if (str_starts_with(ltrim($this->text($p), " \u{a0}"), $prefix)) {
                if ($nth-- === 0) {
                    $hit = $i;
                    break;
                }
            }
        }
        if ($hit < 0 || !isset($all[$hit + $offset])) {
            throw new RuntimeException("Paragraph not found: $prefix");
        }
        return $all[$hit + $offset];
    }

    /** Remove tab characters (dot-leader tabs) so a filled line does not push its text to the right edge. */
    public function dropTabs(DOMElement $p): void
    {
        foreach ($this->xp->query('./w:r/w:tab', $p) as $tab) {
            $tab->parentNode->removeChild($tab);
        }
    }

    /** Width (in em) of the nth dot slot, from real font metrics. */
    public function slotWidthEm(DOMElement $p, int $slot = 0): float
    {
        $slots = $this->slots($p);
        return isset($slots[$slot]) ? FontMetrics::em($slots[$slot]) : 0.0;
    }

    /** Describe slots of a paragraph (debug helper). */
    public function slots(DOMElement $p): array
    {
        [$text] = $this->runMap($p);
        preg_match_all(self::SLOT_RE, $text, $m, PREG_OFFSET_CAPTURE);
        return array_map(fn ($x) => $x[0], $m[0]);
    }

    /* --------------------------------------------------------------- fill */

    /** Same as fillSlot() but counts ellipsis tokens (also single "…"). */
    public function fillEllipsis(DOMElement $p, int $idx, string $value, bool $pad = false): void
    {
        $this->fillMatch($p, self::ELL_RE, $idx, $value, $pad);
    }

    /**
     * @param bool $pad keep the dots after the value: the unused rest of the slot is refilled with "…"
     *                  (estimated by character width) so the line still runs to its end like the printed form
     */
    public function fillSlot(DOMElement $p, int|string $slot, string $value, bool $pad = false): void
    {
        if ($slot === 'append') {
            $value = $this->clean($value);
            if ($value !== '') {
                $this->appendText($p, $value);
            }
            return;
        }
        $this->fillMatch($p, self::SLOT_RE, (int) $slot, $value, $pad);
    }

    private function fillMatch(DOMElement $p, string $re, int $slot, string $value, bool $pad = false): void
    {
        $value = $this->clean($value);
        if ($value === '') {
            return;
        }
        [$text, $map] = $this->runMap($p);

        preg_match_all($re, $text, $m, PREG_OFFSET_CAPTURE);
        if (!isset($m[0][$slot])) {
            throw new RuntimeException('Slot ' . $slot . ' not found in: ' . mb_substr($text, 0, 60));
        }
        // preg offsets are bytes; map is built in bytes too.
        [$match, $byteStart] = $m[0][$slot];
        $byteEnd = $byteStart + strlen($match);

        if ($pad) {
            // keep the dots running to the end of the line: refill what the text leaves of the slot's width,
            // measured with real font metrics; the same dot style the printed line used
            $slotEm = FontMetrics::em($match);
            $dot = (mb_substr_count($match, '…') >= substr_count($match, '.')) ? '…' : '.';
            $left = $slotEm - FontMetrics::em($value . ' ') - 0.4; // small safety so the dots never wrap
            $n = (int) floor($left / FontMetrics::em($dot));
            if ($n * FontMetrics::em($dot) >= 1.2) { // not worth a stray "..." shorter than ~1em
                $value .= ' ' . str_repeat($dot, $n);
            }
        }
        // When another labelled field follows on the same line ("... : <value>   Hộp thư điện tử (nếu có): ..."),
        // leave a visible gap (about 1em) so two fields are not read as one long text.
        $rest = substr($text, $byteEnd);
        $nextSlot = preg_match($re, $rest, $nm, PREG_OFFSET_CAPTURE) ? $nm[0][1] : strlen($rest);
        $between = substr($rest, 0, $nextSlot);
        $labelFollows = (str_contains($between, ':') || preg_match('/^\s*\[/', $between))
            && !preg_match('/^\s*[;,.)]/', $between); // "123 ; Tờ bản đồ": the ";" already separates
        $gapAfter = $labelFollows ? str_repeat(' ', max(0, 4 - (strlen($rest) - strlen(ltrim($rest, ' '))))) : null;

        $first = true;
        foreach ($map as $seg) {
            [$node, $segStart, $segEnd] = $seg;
            if ($segEnd <= $byteStart || $segStart >= $byteEnd) {
                continue;
            }
            $cur = $node->textContent;
            $from = max($byteStart, $segStart) - $segStart;
            $to = min($byteEnd, $segEnd) - $segStart;
            $ins = '';
            if ($first) {
                $before = $byteStart > 0 ? substr($text, $byteStart - 1, 1) : ' ';
                $after = substr($text, $byteEnd, 1);
                $tail = $gapAfter ?? (($after === '' || $after === false || preg_match('/[\s,;.)\/]/', $after)) ? '' : ' ');
                $ins = (preg_match('/[\s\/]/', $before) ? '' : ' ') . $value . $tail;
            }
            $new = substr($cur, 0, $from) . $ins . substr($cur, $to);
            $first = false;
            $this->setText($node, $new);
        }
    }

    /** Insert $value right after the first occurrence of $label (label text is kept). */
    public function fillAfter(DOMElement $p, string $label, string $value): void
    {
        $value = $this->clean($value);
        if ($value === '') {
            return;
        }
        [$text, $map] = $this->runMap($p);
        $pos = strpos($text, $label);
        if ($pos === false) {
            throw new RuntimeException("Label '$label' not found in: " . mb_substr($text, 0, 60));
        }
        $off = $pos + strlen($label);
        foreach ($map as [$node, $a, $b]) {
            if ($off > $a && $off <= $b) {
                $cur = $node->textContent;
                $next = substr($text, $off, 1);
                $ins = ' ' . $value . (($next === '' || $next === false || preg_match('/[\s,;.)]/', $next)) ? '' : ' ');
                $this->setText($node, substr($cur, 0, $off - $a) . $ins . substr($cur, $off - $a));
                return;
            }
        }
    }

    /** Replace literal $old by $new (use {v} placeholder is resolved by the caller). */
    public function swapText(DOMElement $p, string $old, string $new): void
    {
        $this->replaceText($p, $old, $new);
    }

    /** Turn the nth checkbox glyph (☐ 🗆 □) of a paragraph into ☒. */
    public function tickBox(DOMElement $p, int $nth = 0): void
    {
        foreach ($this->xp->query('.//w:t', $p) as $t) {
            $s = $t->textContent;
            if (preg_match_all('/☐|🗆|□/u', $s, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as [$g, $o]) {
                    if ($nth-- === 0) {
                        $this->setText($t, substr($s, 0, $o) . '☒' . substr($s, $o + strlen($g)));
                        return;
                    }
                }
            }
        }
        throw new RuntimeException('Checkbox not found in: ' . mb_substr($this->text($p), 0, 50));
    }

    /**
     * Mark a drawn checkbox (empty rectangle shape with a text box) by writing "X" inside it.
     * Insets are zeroed so the letter fits the small box.
     */
    public function tickShape(DOMElement $p): void
    {
        $xp = $this->xp;
        $xp->registerNamespace('mc', 'http://schemas.openxmlformats.org/markup-compatibility/2006');
        $xp->registerNamespace('wps', 'http://schemas.microsoft.com/office/word/2010/wordprocessingShape');
        $tx = $xp->query('.//mc:Choice//wps:txbx/w:txbxContent', $p)->item(0)
            ?? $xp->query('.//wps:txbx/w:txbxContent', $p)->item(0);
        if (!$tx) {
            throw new RuntimeException('No checkbox shape in paragraph: ' . mb_substr($this->text($p), 0, 40));
        }
        $para = $xp->query('./w:p', $tx)->item(0);
        $body = $xp->query('ancestor::wps:wsp/wps:bodyPr', $tx)->item(0);
        if ($body) {
            foreach (['lIns', 'tIns', 'rIns', 'bIns'] as $a) {
                $body->setAttribute($a, '0');
            }
            $body->setAttribute('anchor', 'ctr');
        }
        $ppr = $this->dom->createElementNS(self::W, 'w:pPr');
        $jc = $this->dom->createElementNS(self::W, 'w:jc');
        $jc->setAttributeNS(self::W, 'w:val', 'center');
        $sp = $this->dom->createElementNS(self::W, 'w:spacing');
        foreach (['before' => '0', 'after' => '0', 'line' => '240', 'lineRule' => 'auto'] as $k => $v) {
            $sp->setAttributeNS(self::W, "w:$k", $v);
        }
        $ppr->appendChild($sp);
        $ppr->appendChild($jc);
        $r = $this->dom->createElementNS(self::W, 'w:r');
        $rpr = $this->dom->createElementNS(self::W, 'w:rPr');
        $rpr->appendChild($this->dom->createElementNS(self::W, 'w:b'));
        foreach (['w:sz', 'w:szCs'] as $n) {
            $e = $this->dom->createElementNS(self::W, $n);
            $e->setAttributeNS(self::W, 'w:val', '18');
            $rpr->appendChild($e);
        }
        $r->appendChild($rpr);
        $t = $this->dom->createElementNS(self::W, 'w:t');
        $t->appendChild($this->dom->createTextNode('X'));
        $r->appendChild($t);
        $para->appendChild($ppr);
        $para->appendChild($r);
    }

    /**
     * Re-split the width of a table's columns. $pct are relative weights, one per column
     * (cells and the table grid are both updated; merged cells are not supported).
     */
    public function setColumnWidths(int $table, array $weights): void
    {
        $tbl = $this->xp->query('/w:document/w:body/w:tbl')->item($table);
        if (!$tbl) {
            return;
        }
        $sum = array_sum($weights);
        $cols = $this->xp->query('./w:tblGrid/w:gridCol', $tbl);
        $total = 0;
        foreach ($cols as $c) {
            $total += (int) $c->getAttributeNS(self::W, 'w');
        }
        foreach ($cols as $i => $c) {
            if (isset($weights[$i]) && $total) {
                $c->setAttributeNS(self::W, 'w:w', (string) round($total * $weights[$i] / $sum));
            }
        }
        foreach ($this->xp->query('./w:tr', $tbl) as $tr) {
            foreach ($this->xp->query('./w:tc/w:tcPr/w:tcW', $tr) as $i => $w) {
                if (isset($weights[$i])) {
                    $w->setAttributeNS(self::W, 'w:w', (string) round(5000 * $weights[$i] / $sum));
                    $w->setAttributeNS(self::W, 'w:type', 'pct');
                }
            }
        }
    }

    /** Zero the left/first-line indent of every paragraph in a table row (negative indents spill text into the neighbour cell). */
    public function resetRowIndent(int $table, int $row): void
    {
        $tbl = $this->xp->query('/w:document/w:body/w:tbl')->item($table);
        $tr = $tbl ? $this->xp->query('./w:tr', $tbl)->item($row) : null;
        if (!$tr) {
            return;
        }
        foreach ($this->xp->query('.//w:p/w:pPr/w:ind', $tr) as $ind) {
            $ind->setAttributeNS(self::W, 'w:left', '0');
            $ind->setAttributeNS(self::W, 'w:firstLine', '0');
        }
    }

    /** Replace the visible text of a paragraph, keeping the formatting of its first text run. */
    public function setParagraphText(DOMElement $p, string $text): void
    {
        $runs = $this->xp->query('./w:r[w:t]', $p);
        foreach ($runs as $i => $r) {
            if ($i === 0) {
                $t = $this->xp->query('./w:t', $r)->item(0);
                $this->setText($t, $text);
            } else {
                $p->removeChild($r);
            }
        }
    }

    /**
     * A justified paragraph (jc=both) stretches the words of every wrapped line. Once text has been typed into it, that
     * shows up as huge gaps, so switch such a paragraph to left alignment. One-line paragraphs look the same either way.
     */
    public function unjustify(DOMElement $p): void
    {
        $jc = $this->xp->query('./w:pPr/w:jc', $p)->item(0);
        if ($jc && $jc->getAttributeNS(self::W, 'val') === 'both') {
            $jc->setAttributeNS(self::W, 'w:val', 'left');
        }
    }

    /** Start the paragraph on a new page. */
    public function pageBreakBefore(DOMElement $p): void
    {
        $ppr = $this->xp->query('./w:pPr', $p)->item(0);
        if (!$ppr) {
            $ppr = $this->dom->createElementNS(self::W, 'w:pPr');
            $p->insertBefore($ppr, $p->firstChild);
        }
        if ($this->xp->query('./w:pageBreakBefore', $ppr)->length) {
            return;
        }
        $pb = $this->dom->createElementNS(self::W, 'w:pageBreakBefore');
        $after = $this->xp->query('./w:pStyle|./w:keepNext|./w:keepLines', $ppr); // schema order
        $ref = $after->length ? $after->item($after->length - 1)->nextSibling : $ppr->firstChild;
        $ppr->insertBefore($pb, $ref);
    }

    /** Replace the whole text of a table cell. */
    public function setCell(int $table, int $row, int $col, string $value): void
    {
        $tc = $this->cell($table, $row, $col);
        foreach ($this->xp->query('.//w:t', $tc) as $t) {
            $t->parentNode->removeChild($t);
        }
        $p = $this->xp->query('./w:p', $tc)->item(0);
        $this->appendText($p, $this->clean($value));
    }

    public function tickCell(int $table, int $row, int $col): void
    {
        $p = $this->xp->query('./w:p', $this->cell($table, $row, $col))->item(0);
        $this->tickBox($p);
    }

    private function cell(int $table, int $row, int $col): DOMElement
    {
        $tbl = $this->xp->query('/w:document/w:body/w:tbl')->item($table);
        $tr = $tbl ? $this->xp->query('./w:tr', $tbl)->item($row) : null;
        $tc = $tr ? $this->xp->query('./w:tc', $tr)->item($col) : null;
        if (!$tc) {
            throw new RuntimeException("Cell $table/$row/$col not found");
        }
        return $tc;
    }

    /** Replace a literal substring (e.g. "☐" -> "☒", "…" in a given occurrence). */
    public function replaceText(DOMElement $p, string $search, string $replace, int $nth = 0): void
    {
        foreach ($this->xp->query('.//w:t', $p) as $t) {
            $pos = 0;
            $s = $t->textContent;
            while (($pos = strpos($s, $search, $pos)) !== false) {
                if ($nth-- === 0) {
                    $this->setText($t, substr($s, 0, $pos) . $replace . substr($s, $pos + strlen($search)));
                    return;
                }
                $pos += strlen($search);
            }
        }
        throw new RuntimeException("Text '$search' not found in paragraph");
    }

    /** Replace the first <w:sym> (Wingdings checkbox) of a paragraph by a plain "☒". */
    public function tickSym(DOMElement $p): void
    {
        $sym = $this->xp->query('.//w:sym', $p)->item(0);
        if (!$sym) {
            throw new RuntimeException('No symbol checkbox in paragraph');
        }
        $t = $this->dom->createElementNS(self::W, 'w:t');
        $this->setText($t, '☒');
        $sym->parentNode->replaceChild($t, $sym);
    }

    /** Put text into table cell (row, col) of the Nth table in scope. Row/col are 0-based. */
    public function fillCell(int $table, int $row, int $col, string $value): void
    {
        $value = $this->clean($value);
        if ($value === '') {
            return;
        }
        $tbls = $this->xp->query('/w:document/w:body/w:tbl');
        $tbl = $tbls->item($table);
        $tr = $this->xp->query('./w:tr', $tbl)->item($row);
        $tc = $this->xp->query('./w:tc', $tr)->item($col);
        if (!$tc) {
            throw new RuntimeException("Cell $table/$row/$col not found");
        }
        $p = $this->xp->query('./w:p', $tc)->item(0);
        $this->appendText($p, $value);
    }

    /** Debug: cell texts of every row of table $table. */
    public function tableDump(int $table): array
    {
        $tbl = $this->xp->query('/w:document/w:body/w:tbl')->item($table);
        if (!$tbl) {
            return [];
        }
        $out = [];
        foreach ($this->xp->query('./w:tr', $tbl) as $r => $tr) {
            $row = [];
            foreach ($this->xp->query('./w:tc', $tr) as $tc) {
                $row[] = mb_substr($this->text($tc), 0, 22);
            }
            $out[$r] = $row;
        }
        return $out;
    }

    public function tableRowCount(int $table): int
    {
        $tbl = $this->xp->query('/w:document/w:body/w:tbl')->item($table);
        return $this->xp->query('./w:tr', $tbl)->length;
    }

    /* -------------------------------------------------------------- output */

    public function save(string $target): void
    {
        copy($this->path, $target);
        $zip = new ZipArchive();
        $zip->open($target);
        $zip->addFromString('word/document.xml', $this->dom->saveXML());
        $zip->close();
    }

    /* ------------------------------------------------------------- helpers */

    private function clean(string $v): string
    {
        return trim(preg_replace('/\s+/u', ' ', $v));
    }

    private function setText(DOMElement $t, string $s): void
    {
        $t->nodeValue = '';
        $t->appendChild($this->dom->createTextNode($s));
        $t->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
    }

    /** @return array{0:string,1:array<int,array{0:DOMElement,1:int,2:int}>} */
    private function runMap(DOMElement $p): array
    {
        $text = '';
        $map = [];
        foreach ($this->xp->query('.//w:t', $p) as $t) {
            $s = $t->textContent;
            $map[] = [$t, strlen($text), strlen($text) + strlen($s)];
            $text .= $s;
        }
        return [$text, $map];
    }

    /**
     * Add a run after the paragraph's last text run (so it lands before a trailing dot-leader tab),
     * copying that run's formatting.
     */
    private function appendText(DOMElement $p, string $value): void
    {
        $runs = $this->xp->query('./w:r[w:t]', $p);
        $r = $this->dom->createElementNS(self::W, 'w:r');
        $anchor = null;
        if ($runs->length) {
            $anchor = $runs->item($runs->length - 1);
            for ($i = $runs->length - 1; $i >= 0; $i--) { // prefer a run with visible text (not a stray " ")
                if (trim($runs->item($i)->textContent) !== '') {
                    $anchor = $runs->item($i);
                    break;
                }
            }
            $rpr = $this->xp->query('./w:rPr', $anchor)->item(0);
            if ($rpr) {
                $r->appendChild($rpr->cloneNode(true));
            }
            $lastText = $anchor->textContent;
            $prefix = ($lastText !== '' && !preg_match('/\s$/u', $lastText)) ? ' ' : '';
        } else {
            $prefix = '';
            $ppr = $this->xp->query('./w:pPr/w:rPr', $p)->item(0);
            if ($ppr) {
                $copy = $ppr->cloneNode(true);
                // the paragraph-mark style of empty cells may carry an underline we do not want on the text
                foreach ($this->xp->query('./w:u', $copy) as $u) {
                    $copy->removeChild($u);
                }
                $r->appendChild($copy);
            }
        }
        $t = $this->dom->createElementNS(self::W, 'w:t');
        $this->setText($t, $prefix . $value);
        $r->appendChild($t);
        if ($anchor) {
            $p->insertBefore($r, $anchor->nextSibling);
        } else {
            $p->appendChild($r);
        }
    }
}
