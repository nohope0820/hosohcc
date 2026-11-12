<?php

namespace App\Services\DocxForm;

use RuntimeException;
use Symfony\Component\Process\Process;

/** docx -> pdf through headless LibreOffice (keeps the Word layout). */
class PdfConverter
{
    public static function convert(string $docx, string $outDir): string
    {
        $profile = $outDir . '/lo-profile-' . bin2hex(random_bytes(4));
        $p = new Process([
            'soffice', '--headless', '--norestore',
            '-env:UserInstallation=file://' . $profile,
            '--convert-to', 'pdf', '--outdir', $outDir, $docx,
        ]);
        $p->setTimeout(120);
        $p->run();

        $pdf = $outDir . '/' . pathinfo($docx, PATHINFO_FILENAME) . '.pdf';
        // remove throwaway LibreOffice profile
        if (is_dir($profile)) {
            (new Process(['rm', '-rf', $profile]))->run();
        }
        if (!$p->isSuccessful() || !is_file($pdf)) {
            throw new RuntimeException('PDF conversion failed: ' . $p->getErrorOutput());
        }
        return $pdf;
    }

    /**
     * Convert several docx files with one LibreOffice start (much faster than one by one).
     *
     * @param string[] $docxFiles
     * @return string[] pdf paths in the same order
     */
    public static function convertMany(array $docxFiles, string $outDir): array
    {
        $profile = $outDir . '/lo-profile-' . bin2hex(random_bytes(4));
        $p = new Process(array_merge([
            'soffice', '--headless', '--norestore',
            '-env:UserInstallation=file://' . $profile,
            '--convert-to', 'pdf', '--outdir', $outDir,
        ], $docxFiles));
        $p->setTimeout(240);
        $p->run();
        if (is_dir($profile)) {
            (new Process(['rm', '-rf', $profile]))->run();
        }
        $pdfs = [];
        foreach ($docxFiles as $f) {
            $pdf = $outDir . '/' . pathinfo($f, PATHINFO_FILENAME) . '.pdf';
            if (!is_file($pdf)) {
                throw new RuntimeException('PDF conversion failed for ' . basename($f) . ': ' . $p->getErrorOutput());
            }
            $pdfs[] = $pdf;
        }
        return $pdfs;
    }

    /** Join PDFs into one with poppler's pdfunite. */
    public static function merge(array $pdfs, string $target): void
    {
        if (count($pdfs) === 1) {
            copy($pdfs[0], $target);
            return;
        }
        $p = new Process(array_merge(['pdfunite'], $pdfs, [$target]));
        $p->setTimeout(60);
        $p->run();
        if (!$p->isSuccessful() || !is_file($target)) {
            throw new RuntimeException('PDF merge failed: ' . $p->getErrorOutput());
        }
    }
}
