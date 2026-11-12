@php
  $labels = ['mau18' => 'Mẫu 18 Biến động đất đai', 'lptb' => '01/LPTB Lệ phí trước bạ', 'tncn' => '03/BĐS-TNCN Thuế TNCN', 'sddpnn' => '01/TK-SDDPNN Đất phi nông nghiệp'];
@endphp
<nav>
  @foreach(\App\Http\Controllers\FormController::forms() as $s => $f)
    <a href="/{{ $s }}" class="{{ request()->is($s) ? 'on' : '' }}">{{ $labels[$s] }}</a>
  @endforeach
  <a href="/bundle" class="{{ request()->is('bundle') ? 'on' : '' }}" style="margin-left:auto">⬇ Xuất trọn bộ hồ sơ</a>
</nav>
