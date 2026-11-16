@extends('layouts.app')
@php use App\Support\Ui as U; @endphp
@section('title', 'Sơ đồ vị trí thửa đất')
@section('sub', 'Trang cuối của bộ tờ khai 01/TK-SDDPNN. Vùng vẽ sơ đồ để trống trên bản in, bạn vẽ tay.')
@section('fields')
<fieldset><legend>Người kê khai</legend>
  <div class="grid">
    {!! U::f('sodo_ten','Họ và tên','s6').U::f('sodo_diachi','Địa chỉ','s6') !!}
  </div>
</fieldset>
<fieldset><legend>Nơi, ngày ký</legend>
  <div class="grid">
    {!! U::f('ky_ten_noi','Nơi ký (để trống = Hà Nội)','s3').U::f('ky_ten_ngay','ngày','s3').U::f('ky_ten_thang','tháng','s3').U::f('ky_ten_nam','năm','s3') !!}
  </div>
  <p class="note">Ngày, tháng, năm gợi ý theo ngày ký bạn vừa nhập ở các mẫu trước (ô viền nét đứt). Sai thì sửa hoặc xóa.</p>
</fieldset>
@endsection
