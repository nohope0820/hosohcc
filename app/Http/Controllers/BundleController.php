<?php

namespace App\Http\Controllers;

use App\Services\DocxForm\DocxTemplate;
use App\Services\DocxForm\PdfConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Export of the whole file set: the browser sends the data of every selected form, we return one PDF or one .docx. */
class BundleController extends Controller
{
    public function generate(Request $request)
    {
        $registry = FormController::forms();
        $request->validate([
            'format' => 'required|in:pdf,docx',
            'forms' => 'required|array|min:1',
            'forms.*' => 'string|in:' . implode(',', array_keys($registry)),
            'data' => 'nullable|array',
        ]);

        // keep the official order (Mẫu 18, LPTB, TNCN, SDDPNN, sơ đồ) whatever order the browser sent
        $slugs = array_values(array_filter(array_keys($registry), fn ($s) => in_array($s, $request->input('forms'), true)));

        $dir = DocxTemplate::tmpDir();
        $run = Str::random(8);
        $docx = [];
        $first = null;
        foreach ($slugs as $i => $slug) {
            [$class, , $name] = $registry[$slug];
            parse_str((string) ($request->input("data.$slug") ?? ''), $raw); // same "a[0][b]=c" format a form posts
            $t = DocxTemplate::official();
            $class::fill($t, FormController::sanitize($raw));
            if ($request->input('format') === 'docx') {
                if ($first === null) {
                    $first = $t;
                } else {
                    $first->append($t);
                }
                continue;
            }
            $file = sprintf('%s/b%s-%d-%s.docx', $dir, $run, $i + 1, $slug);
            $t->save($file);
            $docx[] = $file;
        }

        $cleanup = $docx;
        try {
            if ($request->input('format') === 'docx') {
                // one Word file: the forms are appended to the first one, each starting on a new page
                $out = "$dir/b$run.docx";
                $first->save($out);
                return response()->download($out, 'bo-ho-so-nha-dat.docx')->deleteFileAfterSend();
            }

            $pdfs = PdfConverter::convertMany($docx, $dir);
            $cleanup = array_merge($docx, $pdfs);
            $merged = "$dir/b$run.pdf";
            PdfConverter::merge($pdfs, $merged);
            return response()->file($merged, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => ($request->input('format') === 'pdf' ? 'attachment' : 'inline') . '; filename="bo-ho-so-nha-dat.pdf"',
            ])->deleteFileAfterSend();
        } catch (\Throwable $e) {
            return FormController::pdfFailure($e);
        } finally {
            foreach ($cleanup as $f) {
                @unlink($f);
            }
        }
    }
}
