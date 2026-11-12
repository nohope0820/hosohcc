<?php

namespace App\Http\Controllers;

use App\Services\DocxForm\DocxTemplate;
use App\Services\DocxForm\LptbForm;
use App\Services\DocxForm\Mau18Form;
use App\Services\DocxForm\PdfConverter;
use App\Services\DocxForm\SddpnnForm;
use App\Services\DocxForm\TncnForm;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormController extends Controller
{
    /** slug => [class, title, download name] */
    private const FORMS = [
        'mau18' => [Mau18Form::class, 'Đơn đăng ký biến động đất đai (Mẫu 18)', 'don-dang-ky-bien-dong-mau-18'],
        'lptb' => [LptbForm::class, 'Tờ khai lệ phí trước bạ nhà, đất', 'to-khai-le-phi-truoc-ba'],
        'tncn' => [TncnForm::class, 'Tờ khai thuế TNCN (chuyển nhượng, thừa kế, quà tặng bất động sản)', 'to-khai-thue-tncn-bds'],
        'sddpnn' => [SddpnnForm::class, 'Tờ khai thuế sử dụng đất phi nông nghiệp', 'to-khai-thue-sddpnn'],
    ];

    public static function forms(): array
    {
        return self::FORMS;
    }

    public function show(string $form)
    {
        abort_unless(isset(self::FORMS[$form]), 404);
        return view($form, ['slug' => $form, 'title' => self::FORMS[$form][1], 'shared' => \App\Support\Shared::for($form)]);
    }

    public function generate(Request $request, string $form)
    {
        abort_unless(isset(self::FORMS[$form]), 404);
        [$class, , $name] = self::FORMS[$form];

        $request->validate(['format' => 'required|in:docx,pdf,print']);
        $data = self::sanitize($request->except(['_token']));

        $dir = DocxTemplate::tmpDir();
        $docx = $dir . '/' . $form . '-' . Str::random(10) . '.docx';

        $t = DocxTemplate::official();
        $class::fill($t, $data);
        $t->save($docx);

        if ($data['format'] === 'docx') {
            return response()->download($docx, $name . '.docx')->deleteFileAfterSend();
        }

        try {
            $pdf = PdfConverter::convert($docx, $dir);
        } catch (\Throwable $e) {
            return self::pdfFailure($e);
        } finally {
            @unlink($docx);
        }
        return response()->file($pdf, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($data['format'] === 'pdf' ? 'attachment' : 'inline') . '; filename="' . $name . '.pdf"',
        ])->deleteFileAfterSend();
    }

    /** Short, readable reason for the browser (and the full exception in storage/logs). */
    public static function pdfFailure(\Throwable $e)
    {
        \Illuminate\Support\Facades\Log::error('PDF export failed: ' . $e);
        return response('Không tạo được PDF: ' . mb_substr($e->getMessage(), 0, 300), 503, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    /** Strings only, trimmed and capped (2000 chars: long free-text fields exist); nested arrays (table rows) up to 2 levels, max 20 rows. */
    public static function sanitize(array $in, int $depth = 0): array
    {
        $out = [];
        foreach ($in as $k => $v) {
            if (is_array($v)) {
                if ($depth < 2) {
                    $out[$k] = self::sanitize(array_slice($v, 0, 20, true), $depth + 1);
                }
            } elseif (is_scalar($v)) {
                $out[$k] = mb_substr(trim((string) $v), 0, 2000);
            }
        }
        return $out;
    }
}
