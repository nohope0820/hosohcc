@extends('layouts.app')
@php use App\Support\Ui as U; @endphp
@section('title', 'Tờ khai thuế sử dụng đất phi nông nghiệp')
@section('sub', 'Mẫu số 01/TK-SDDPNN (Thông tư 89/2026/TT-BTC), hộ gia đình, cá nhân. Gồm cả trang sơ đồ vị trí thửa đất. Ô để trống giữ nguyên dòng chấm để viết tay.')
@section('fields')
<fieldset><legend>Kỳ tính thuế</legend>
  <div class="grid">{!! U::f('ky_nam','[01] Năm','s4').U::f('bosung_lan','[03] Bổ sung lần thứ','s4') !!}</div>
  {!! U::chk('lan_dau','[02] Lần đầu') !!}
</fieldset>

<fieldset><legend>1. Người nộp thuế</legend>
  <div class="grid">
    {!! U::f('nnt_ten','[04] Họ và tên','s8').U::f('nnt_ngaysinh','[05] Ngày, tháng, năm sinh','s4')
       .U::f('nnt_mst','[06] Mã số thuế','s6').U::f('nnt_cccd','[07] Số định danh cá nhân / Số hộ chiếu','s6')
       .U::f('nnt_sonha','[08.1] Số nhà','s3').U::f('nnt_duong','[08.2] Đường/phố','s9').U::f('nnt_to','[08.3] Tổ/thôn','s3').U::f('nnt_xa','[08.4] Xã/Phường/Đặc khu','s9')
       .U::f('nnt_tinh','[08.5] Tỉnh/Thành phố')
       .U::f('nnt_dc_tb','[09] Địa chỉ nhận thông báo thuế').U::f('nnt_dienthoai','[10] Điện thoại') !!}
  </div>
</fieldset>

<fieldset><legend>2. Dịch vụ làm thủ tục về thuế (nếu có)</legend>
  <div class="grid">
    {!! U::f('dv_ten','[11] Tên tổ chức, cá nhân cung cấp dịch vụ').U::f('dv_mst','[12] Mã số thuế','s4')
       .U::f('dv_hd_so','[13] Hợp đồng dịch vụ: số','s4').U::f('dv_hd_ngay','ngày','s4') !!}
  </div>
</fieldset>

<fieldset><legend>3. Thửa đất chịu thuế</legend>
  <p class="note">[14] Thông tin người sử dụng đất (tối đa 7 dòng)</p>
  {!! U::rows('nguoisd',['ten:Họ và tên','mst:Mã số thuế','id:Số định danh / Hộ chiếu','tyle:Tỷ lệ'],7,'+ Thêm người') !!}
  <div class="grid" style="margin-top:12px">{!! U::f('nguongoc','[15] Nguồn gốc thửa đất') !!}</div>
  <p class="note">Nếu nhận chuyển nhượng, thừa kế, tặng cho, góp vốn</p>
  <div class="grid">
    {!! U::f('giao_ten','[15.1] Tên tổ chức, cá nhân chuyển giao quyền sử dụng đất')
       .U::f('giao_mst','[15.2] Mã số thuế','s4').U::f('giao_cccd','[15.3] Số định danh / Hộ chiếu','s4').U::f('giao_mapnn','[15.4] Mã phi nông nghiệp','s4') !!}
  </div>
  <p class="note">[16] Địa chỉ thửa đất</p>
  <div class="grid">
    {!! U::f('dat_sonha','[16.1] Số nhà','s3').U::f('dat_duong','[16.2] Đường/phố','s9').U::f('dat_to','[16.3] Tổ/thôn','s3').U::f('dat_xa','[16.4] Xã/Phường/Đặc khu','s9')
       .U::f('dat_tinh','[16.5] Tỉnh/Thành phố') !!}
  </div>
  {!! U::chk('tick_duynhat','[17] Là thửa đất duy nhất') !!}
  <div class="grid" style="margin-top:8px">{!! U::f('khaitonghop','[18] Đăng ký kê khai tổng hợp tại (Xã/phường/đặc khu)') !!}</div>
</fieldset>

<fieldset><legend>[19] Đã có giấy chứng nhận</legend>
  {!! U::chk('tick_cogcn','Đã có giấy chứng nhận') !!}
  <div class="grid">
    {!! U::f('gcn_so','[19.1] Số giấy chứng nhận','s6').U::f('gcn_ngay','[19.2] Ngày cấp','s6')
       .U::f('gcn_thua','[19.3] Thửa đất số','s6').U::f('gcn_tobd','[19.4] Tờ bản đồ số','s6')
       .U::f('gcn_dientich','[19.5] Diện tích','s4').U::f('gcn_loai','[19.6] Loại đất / Mục đích sử dụng','s8') !!}
  </div>
  <p class="note">[20] Tổng diện tích thực tế sử dụng cho mục đích phi nông nghiệp</p>
  <div class="grid">
    {!! U::f('dt_dungmd','[20.1] Diện tích đất sử dụng đúng mục đích','s6').U::f('dt_saimd','[20.2] Diện tích đất sử dụng không đúng mục đích','s6')
       .U::f('dt_chuasd','[20.3] Diện tích đất chưa sử dụng theo đúng quy định','s6').U::f('dt_hanmuc','[20.4] Hạn mức (nếu có)','s6')
       .U::f('dt_lanchiem','[20.5] Diện tích đất lấn, chiếm','s6') !!}
  </div>
</fieldset>

<fieldset><legend>[21] Chưa có giấy chứng nhận</legend>
  {!! U::chk('tick_chuagcn','Chưa có giấy chứng nhận') !!}
  <div class="grid">
    {!! U::f('chua_dientich','[21.1] Diện tích','s4').U::f('chua_loai','[21.2] Loại đất / Mục đích đang sử dụng','s8')
       .U::f('batdau_sd','[22] Thời điểm bắt đầu sử dụng đất','s6').U::f('thaydoi','[23] Thời điểm thay đổi thông tin của thửa đất','s6') !!}
  </div>
</fieldset>

<fieldset><legend>4–5. Nhà chung cư; miễn, giảm thuế</legend>
  <p class="note">[24] Đất ở nhà nhiều tầng nhiều hộ ở, nhà chung cư</p>
  <div class="grid">
    {!! U::f('nha_loai','[24.1] Loại nhà','s4').U::f('nha_dientich','[24.2] Diện tích','s4').U::f('nha_heso','[24.3] Hệ số phân bổ','s4') !!}
  </div>
  <p class="note">[25] Miễn, giảm thuế</p>
  <div class="grid">
    {!! U::f('mien_lydo','[25.1] Trường hợp miễn, giảm').U::f('mien_kyhan','[25.2] Kỳ tính thuế / Khoảng thời gian đề nghị miễn, giảm') !!}
  </div>
</fieldset>

<fieldset><legend>Chữ ký &amp; sơ đồ vị trí thửa đất</legend>
  <div class="grid">
    {!! U::f('kt_ten','Người thực hiện dịch vụ thuế: họ và tên','s6').U::f('kt_chungchi','Chứng chỉ nghiệp vụ số','s6')
       .U::f('ky_ten_noi','Nơi ký','s3').U::f('ky_ten_ngay','ngày','s3').U::f('ky_ten_thang','tháng','s3').U::f('ky_ten_nam','năm','s3')
       .U::f('sodo_ten','Sơ đồ: họ và tên','s6').U::f('sodo_diachi','Sơ đồ: địa chỉ','s6') !!}
  </div>
  <p class="note">Vùng vẽ sơ đồ để trống trên bản in, bạn vẽ tay.</p>
</fieldset>
@endsection
