@extends('layouts.app')
@php use App\Support\Ui as U; @endphp
@section('title', 'Tờ khai lệ phí trước bạ nhà, đất')
@section('sub', 'Mẫu số 01/LPTB (Thông tư 89/2026/TT-BTC). Chỉ điền ô cần thiết, ô để trống giữ nguyên dòng chấm để viết tay.')
@section('fields')
<fieldset><legend>Kỳ tính thuế</legend>
  <div class="grid">{!! U::f('ky_ngay','[01] Ngày','s3').U::f('ky_thang','Tháng','s3').U::f('ky_nam','Năm','s3').U::f('bosung_lan','[03] Bổ sung lần thứ','s3') !!}</div>
  {!! U::chk('lan_dau','[02] Lần đầu') !!}
  {!! U::chk('uy_quyen','Tổ chức, cá nhân được ủy quyền khai thay cho người nộp thuế') !!}
</fieldset>

<fieldset><legend>Người nộp thuế</legend>
  <div class="grid">
    {!! U::f('nnt_ten','[04] Người nộp thuế','s8').U::f('nnt_ngaysinh','[05] Ngày, tháng, năm sinh','s4')
       .U::f('nnt_mst','[06] Mã số thuế','s6').U::f('nnt_cccd','[07] Số định danh cá nhân / Số hộ chiếu','s6')
       .U::f('nnt_diachi','[08] Địa chỉ')
       .U::f('nnt_xa','[09] Xã/phường/đặc khu','s6').U::f('nnt_tinh','[10] Tỉnh/Thành phố','s6')
       .U::f('nnt_dienthoai','[11] Điện thoại','s4').U::f('nnt_fax','[12] Fax','s4').U::f('nnt_email','[13] Email','s4') !!}
  </div>
</fieldset>

<fieldset><legend>Đại lý thuế / người khai thay (nếu có)</legend>
  <div class="grid">
    {!! U::f('dv_ten','[14] Tên tổ chức, cá nhân').U::f('dv_mst','[15] Mã số thuế','s4')
       .U::f('dv_hopdong_so','[16] Hợp đồng dịch vụ: số','s4').U::f('dv_hopdong_ngay','ngày','s4') !!}
  </div>
</fieldset>

<fieldset><legend>1. Đất</legend>
  <div class="grid">
    {!! U::f('dat_loai','1. Đất (loại)').U::f('dat_thua','1.1 Thửa đất số','s6').U::f('dat_to','Tờ bản đồ số','s6')
       .U::f('dat_sonha','1.2.1 Số nhà','s3').U::f('dat_toanha','Tòa nhà','s3').U::f('dat_ngo','Ngõ/Hẻm','s6')
       .U::f('dat_duong','Đường/Phố','s6').U::f('dat_thon','Thôn/xóm/ấp','s6')
       .U::f('dat_xa','1.2.2 Xã/phường/đặc khu','s6').U::f('dat_tinh','1.2.3 Tỉnh/thành phố','s6')
       .U::f('dat_vitri','1.3 Vị trí thửa đất (mặt tiền đường phố hay ngõ, hẻm)')
       .U::f('dat_mucdich','1.4 Mục đích sử dụng đất','s8').U::f('dat_dientich','1.5 Diện tích (m²)','s4') !!}
  </div>
  <p class="note">1.6 Nguồn gốc: người/tổ chức chuyển giao quyền sử dụng đất</p>
  <div class="grid">
    {!! U::f('giao_ten','Tên tổ chức/cá nhân chuyển giao').U::f('giao_mst','Mã số thuế','s4').U::f('giao_cccd','Số định danh / Hộ chiếu','s8')
       .U::f('giao_diachi','Địa chỉ người giao')
       .U::f('giao_ngay','Thời điểm làm giấy tờ: ngày','s4').U::f('giao_thang','tháng','s4').U::f('giao_nam','năm','s4')
       .U::f('dat_giatri','1.7 Giá trị đất thực tế chuyển giao (nếu có)') !!}
  </div>
</fieldset>

<fieldset><legend>2. Nhà</legend>
  <div class="grid">
    {!! U::f('nha_cap','Cấp nhà','s4').U::f('nha_loai','Loại nhà','s4').U::f('nha_hang','Hạng nhà','s4')
       .U::f('cc_chuduan','Chung cư: chủ dự án','s6').U::f('cc_diachiduan','Địa chỉ dự án, công trình','s6')
       .U::f('cc_ketcau','Kết cấu','s4').U::f('cc_tangnoi','Số tầng nổi','s4').U::f('cc_tanghamm','Số tầng hầm','s4')
       .U::f('cc_sohuuchung','Diện tích sở hữu chung (m²)','s6').U::f('cc_sohuuriang','Diện tích sở hữu riêng (m²)','s6')
       .U::f('nha_dtxd','2.2 Diện tích xây dựng (m²)','s6').U::f('nha_dtsan','Diện tích sàn xây dựng (m²)','s6')
       .U::f('nha_nguongoc','2.3 Nguồn gốc nhà').U::f('nha_namhoancong','a) Tự xây dựng: năm hoàn công / bắt đầu sử dụng')
       .U::f('nha_ngay','b) Mua, thừa kế, tặng cho: ngày','s4').U::f('nha_thang','tháng','s4').U::f('nha_nam','năm','s4')
       .U::f('nha_giatri','2.4 Giá trị nhà (đồng)') !!}
  </div>
</fieldset>

<fieldset><legend>3–4. Giá trị và miễn lệ phí</legend>
  <div class="grid">
    {!! U::f('tong_giatri','3. Giá trị nhà, đất thực tế nhận chuyển nhượng, thừa kế, tặng cho (đồng)')
       .U::f('mien_lydo','4. Tài sản được miễn lệ phí trước bạ (lý do)') !!}
  </div>
</fieldset>

<fieldset><legend>5. Đồng chủ sở hữu (nếu có)</legend>
  {!! U::rows('dongsohuu',['ten:Tên','mst:Mã số thuế','cccd:Số định danh/Hộ chiếu','tyle:Tỷ lệ %'],10,'+ Thêm đồng sở hữu') !!}
</fieldset>

<fieldset><legend>6. Giấy tờ liên quan &amp; chữ ký</legend>
  <div class="grid">
    {!! U::f('giayto_1','Giấy tờ 1','s6').U::f('giayto_2','Giấy tờ 2','s6')
       .U::f('dv_nguoi_ten','Người thực hiện dịch vụ thuế: họ và tên','s6').U::f('dv_chungchi','Chứng chỉ nghiệp vụ số','s6')
       .U::f('ky_ten_noi','Nơi ký','s3').U::f('ky_ten_ngay','ngày','s3').U::f('ky_ten_thang','tháng','s3').U::f('ky_ten_nam','năm','s3') !!}
  </div>
</fieldset>
@endsection
