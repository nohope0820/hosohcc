@extends('layouts.app')
@php use App\Support\Ui as U; @endphp
@section('title', 'Đơn đăng ký biến động đất đai, tài sản gắn liền với đất')
@section('sub', 'Mẫu số 18. Văn bản dài được tự chia vào các dòng chấm của mẫu; dòng nào không dùng giữ nguyên dòng chấm để viết tay. "Kính gửi" cố định theo mẫu (Chi nhánh Văn phòng Đăng ký Đất đai Hai Bà Trưng).')
@section('fields')
<fieldset><legend>1. Người sử dụng đất, chủ sở hữu tài sản gắn liền với đất, người quản lý đất</legend>
  {!! U::ta('ten','a) Tên',2).U::ta('giayto_nhanthan','b) Giấy tờ nhân thân / pháp nhân (loại, số, ngày cấp, nơi cấp)',3).U::ta('diachi','c) Địa chỉ',2) !!}
  <div class="grid">{!! U::f('dienthoai','d) Điện thoại liên hệ','s6').U::f('email','Hộp thư điện tử','s6') !!}</div>
</fieldset>

<fieldset><legend>2. Nội dung biến động</legend>
  {!! U::ta('noidung','Nội dung biến động (vd: nhận chuyển nhượng, nhận tặng cho, cấp lại do mất, cấp đổi...)',6) !!}
  {!! U::sel('doi_gcn','2.1 Nhu cầu cấp đổi Giấy chứng nhận',['co'=>'Có nhu cầu cấp đổi','khong'=>'Không có nhu cầu cấp đổi'],'f') !!}
</fieldset>

<fieldset><legend>3. Giấy tờ nộp kèm theo đơn</legend>
  <div class="grid">
    {!! U::f('gt_1','(1)','s6').U::f('gt_2','(2)','s6').U::f('gt_3','(3)','s6') !!}
  </div>
</fieldset>

<fieldset><legend>Ngày ký</legend>
  <div class="grid">{!! U::f('ky_noi','Nơi','s3').U::f('ky_ngay','ngày','s3').U::f('ky_thang','tháng','s3').U::f('ky_nam','năm','s3') !!}</div>
</fieldset>

@endsection
