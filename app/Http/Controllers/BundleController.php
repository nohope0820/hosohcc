<?php

namespace App\Http\Controllers;

use App\Services\DocxForm\DocxTemplate;
use App\Services\DocxForm\PdfConverter;
use App\Support\Shared;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ZipArchive;

/** Export of the whole file set: the browser sends the data of every selected form, we return one PDF or a zip of .docx. */
class BundleController extends Controller
{
    public function show()
    {
        return view('bundle', ['forms' => FormController::forms(), 'shared' => Shared::all()]);
    }

    public function generate(Request $request)
    {
        $registry = FormController::forms();
        $request->validate([
            'format' => 'required|in:pdf,print,zip',
            'forms' => 'required|array|min:1',
            'forms.*' => 'string|in:' . implode(',', array_keys($registry)),
            'data' => 'nullable|array',
        ]);

        // keep the official order (Mẫu 18, LPTB, TNCN, SDDPNN) whatever order the browser sent
        $slugs = array_values(array_filter(array_keys($registry), fn ($s) => in_array($s, $request->input('forms'), true)));

        $dir = DocxTemplate::tmpDir();
        $run = Str::random(8);
        $docx = [];
        $names = [];
        foreach ($slugs as $i => $slug) {
            [$class, , $name] = $registry[$slug];
            parse_str((string) ($request->input("data.$slug") ?? ''), $raw); // same "a[0][b]=c" format a form posts
            $t = DocxTemplate::official();
            $class::fill($t, FormController::sanitize($raw));
            $file = sprintf('%s/b%s-%d-%s.docx', $dir, $run, $i + 1, $slug);
            $t->save($file);
            $docx[] = $file;
            $names[] = sprintf('%d-%s', $i + 1, $name);
        }

        $cleanup = $docx;
        try {
            if ($request->input('format') === 'zip') {
                $zipPath = "$dir/b$run.zip";
                $zip = new ZipArchive();
                $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                foreach ($docx as $i => $f) {
                    $zip->addFile($f, $names[$i] . '.docx');
                }
                $zip->close();
                $cleanup[] = $zipPath;
                $resp = response()->download($zipPath, 'bo-ho-so-nha-dat.zip')->deleteFileAfterSend();
                $cleanup = $docx; // the zip is removed by the response
                return $resp;
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
