<?php

namespace App\Support;

/** Tiny HTML helpers for the form pages. */
class Ui
{
    public static function f(string $name, string $label, string $cls = 'f'): string
    {
        return '<div class="' . $cls . '"><label for="' . e($name) . '">' . e($label)
            . '</label><input type="text" id="' . e($name) . '" name="' . e($name) . '"></div>';
    }

    public static function ta(string $name, string $label, int $rows = 3, string $cls = 'f'): string
    {
        return '<div class="' . $cls . '"><label for="' . e($name) . '">' . e($label) . '</label><textarea id="' . e($name)
            . '" name="' . e($name) . '" rows="' . $rows . '"></textarea></div>';
    }

    /** @param array<string,string> $options value => label */
    public static function sel(string $name, string $label, array $options, string $cls = 'f'): string
    {
        $o = '<option value=""></option>';
        foreach ($options as $v => $l) {
            $o .= '<option value="' . e($v) . '">' . e($l) . '</option>';
        }
        return '<div class="' . $cls . '"><label for="' . e($name) . '">' . e($label) . '</label><select id="' . e($name)
            . '" name="' . e($name) . '">' . $o . '</select></div>';
    }

    public static function chk(string $name, string $label): string
    {
        return '<div class="chk"><input type="checkbox" id="' . e($name) . '" name="' . e($name)
            . '" value="1"><label for="' . e($name) . '">' . e($label) . '</label></div>';
    }

    /**
     * Repeating rows (co-owners, persons...). $cols: "key:Label" or "key:Label:chk" for a checkbox.
     */
    public static function rows(string $name, array $cols, int $max, string $addLabel = '+ Thêm dòng'): string
    {
        return '<div class="rows" data-name="' . e($name) . '" data-max="' . $max . '" data-cols="' . e(implode('|', $cols)) . '"></div>'
            . '<button type="button" class="sec add-row" data-for="' . e($name) . '">' . e($addLabel) . '</button>';
    }
}
