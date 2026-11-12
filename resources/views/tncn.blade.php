@extends('layouts.app')
@php use App\Support\Ui as U; @endphp
@section('title', 'Tờ khai thuế thu nhập cá nhân (bất động sản)')
@section('sub', 'Mẫu số 03/BĐS-TNCN (Thông tư 89/2026/TT-BTC). Chỉ điền ô cần thiết, ô để trống giữ nguyên dòng chấm để viết tay.')
@section('fields')
<fieldset><legend>Kỳ tính thuế</legend>
  <div class="grid">{!! U::f('ky_ngay','[01] Ngày','s3').U::f('ky_thang','Tháng','s3').U::f('ky_nam','Năm','s3').U::f('bosung_lan','[03.1] Bổ sung lần thứ','s3')
    .U::f('thongbao_so','[03.2] Số Thông báo lần trước liền kề') !!}</div>
  {!! U::chk('lan_dau','[02] Lần đầu') !!}
</fieldset>

<fieldset><legend>I. Người chuyển nhượng, cho tặng, để thừa kế [04]</legend>
  {!! U::rows('nguoichuyen',['ten:Họ và tên','id:MST / Số định danh','ngaysinh:Ngày sinh','hc_so:Hộ chiếu số','hc_ngay:Ngày cấp','hc_noi:Nơi cấp','dienthoai:Điện thoại','tyle:Diện tích / tỷ lệ %'],6,'+ Thêm người') !!}
</fieldset>

<fieldset><legend>Khai thay / đại lý thuế (nếu có)</legend>
  <div class="grid">
    {!! U::f('khaithay_ten','[05] Tổ chức, cá nhân khai, nộp thuế thay','s8').U::f('khaithay_mst','[06] Mã số thuế','s4')
       .U::f('uq_so','[07] Văn bản ủy quyền: số','s3').U::f('uq_ngay','ngày','s3').U::f('uq_thang','tháng','s3').U::f('uq_nam','năm','s3')
       .U::f('dv_ten','[08] Tổ chức, cá nhân cung cấp dịch vụ thủ tục thuế','s8').U::f('dv_mst','[09] Mã số thuế','s4')
       .U::f('dv_hd_so','[10] Hợp đồng dịch vụ: số','s6').U::f('dv_hd_ngay','ngày','s6') !!}
  </div>
</fieldset>

<fieldset><legend>II. Người nhận chuyển nhượng, nhận thừa kế, quà tặng [11]</legend>
  {!! U::rows('nguoinhan',['ten:Họ và tên','id:MST / Số định danh','ngaysinh:Ngày sinh','hc_so:Hộ chiếu số','hc_ngay:Ngày cấp','hc_noi:Nơi cấp','tyle:Tỷ lệ sở hữu %'],6,'+ Thêm người') !!}
</fieldset>

<fieldset><legend>III. Loại bất động sản</legend>
  {!! U::chk('loai_dat','[12] Quyền sử dụng đất và tài sản gắn liền trên đất').U::chk('loai_nha','[13] Quyền sở hữu hoặc sử dụng nhà ở')
     .U::chk('loai_thue','[14] Quyền thuê đất, thuê mặt nước').U::chk('loai_khac','[15] Bất động sản khác') !!}
</fieldset>

<fieldset><legend>IV. Đặc điểm bất động sản — giấy tờ</legend>
  <div class="grid">
    {!! U::f('gt_loai','[16.1] Giấy tờ về quyền sử dụng đất: loại giấy tờ')
       .U::f('gt_so','[16.2] Số','s4').U::f('gt_noicap','[16.3] Nơi cấp','s4').U::f('gt_ngay','[16.4] Ngày cấp','s4')
       .U::f('hdduan_so','[17.1] HĐ mua bán nhà hình thành trong tương lai: số','s4').U::f('hdduan_ngay','[17.2] Ngày','s4').U::f('hdduan_tyle','[17.3] Tỷ lệ góp vốn','s4')
       .U::f('hdcn_so','[18.1] HĐ chuyển nhượng, trao đổi: số','s4').U::f('hdcn_noilap','[18.2] Nơi lập','s4').U::f('hdcn_ngaylap','[18.3] Ngày lập','s4')
       .U::f('hdcn_cq','[18.4] Cơ quan chứng thực','s8').U::f('hdcn_ngayct','[18.5] Ngày chứng thực','s4')
       .U::f('dichan_noilap','[19.1] Văn bản phân chia di sản/quà tặng: nơi lập','s6').U::f('dichan_ngaylap','[19.2] Ngày lập','s6')
       .U::f('dichan_cq','[19.3] Cơ quan chứng thực','s6').U::f('dichan_ngayct','[19.4] Ngày chứng thực','s6') !!}
  </div>
</fieldset>

<fieldset><legend>[20] Thông tin về đất</legend>
  <div class="grid">
    {!! U::f('dat_thua','[20.1] Thửa đất số','s6').U::f('dat_to','[20.2] Tờ bản đồ số','s6')
       .U::f('dat_diachi','[20.3] Địa chỉ').U::f('dat_xa','[20.4] Xã/Phường/Đặc khu','s6').U::f('dat_tinh','[20.5] Tỉnh/thành phố','s6') !!}
  </div>
  <p class="note">[20.6] Loại đất, vị trí thửa đất (tối đa 3 dòng)</p>
  {!! U::rows('datvitri',['duong:Đường','doan:Đoạn đường','vitri:Vị trí','loai:Loại đất','heso:Hệ số','dientich:Diện tích m²'],3,'+ Thêm dòng') !!}
  <div class="grid" style="margin-top:12px">
    {!! U::f('dat_nguongoc','[20.7] Nguồn gốc đất') !!}
  </div>
  <p class="note">[20.8] Thời hạn sử dụng đất</p>
  {!! U::chk('han_lau_dai','Ổn định lâu dài') !!}
  <div class="grid">
    {!! U::f('han_nam','Có thời hạn: số năm','s4').U::f('han_tu','Từ ngày (dd/mm/yyyy)','s4').U::f('han_den','Đến ngày (dd/mm/yyyy)','s4')
       .U::f('dat_giatri','[20.9] Giá trị đất thực tế chuyển giao (đồng)') !!}
  </div>
</fieldset>

<fieldset><legend>[21] Nhà ở, công trình xây dựng</legend>
  <p class="note">[21.1] Nhà ở riêng lẻ</p>
  <div class="grid">
    {!! U::f('nr1_loai','[21.2] Loại 1','s4').U::f('nr1_cap','Cấp nhà ở','s4').U::f('nr1_dtsan','Diện tích sàn (m²)','s4')
       .U::f('nr2_loai','[21.3] Loại 2','s4').U::f('nr2_cap','Cấp nhà ở','s4').U::f('nr2_dtsan','Diện tích sàn (m²)','s4')
       .U::f('nr_nam','[21.4] Năm hoàn thành xây dựng') !!}
  </div>
  <p class="note">[21.5] Nhà ở chung cư</p>
  <div class="grid">
    {!! U::f('cc_chuduan','[21.6] Chủ dự án','s6').U::f('cc_diachi','[21.7] Địa chỉ dự án, công trình','s6')
       .U::f('cc_dtxd','[21.8] Diện tích xây dựng','s6').U::f('cc_dtsan','[21.9] Diện tích sàn xây dựng (m²)','s6')
       .U::f('cc_chung','[21.10] Diện tích sở hữu chung (m²)','s6').U::f('cc_rieng','[21.11] Diện tích sở hữu riêng (m²)','s6')
       .U::f('cc_ketcau','[21.12] Kết cấu','s4').U::f('cc_tangnoi','[21.13] Số tầng nổi','s4').U::f('cc_tanghamm','[21.14] Số tầng hầm','s4')
       .U::f('cc_nam','[21.15] Năm hoàn thành xây dựng') !!}
  </div>
  <p class="note">[21.16] Nguồn gốc nhà</p>
  {!! U::chk('nha_tuxaydung','Tự xây dựng') !!}
  <div class="grid">{!! U::f('nha_namxd','[21.17] Năm hoàn thành (hoặc năm bắt đầu sử dụng nhà)') !!}</div>
  {!! U::chk('nha_chuyennhuong','Chuyển nhượng') !!}
  <div class="grid">
    {!! U::f('nha_ngaygiao','[21.18] Thời điểm làm giấy tờ chuyển giao nhà','s6').U::f('nha_giatri','[21.19] Giá trị nhà thực tế chuyển giao (đồng)','s6') !!}
  </div>
  <p class="note">[21.20] Công trình xây dựng (trừ nhà ở)</p>
  <div class="grid">
    {!! U::f('ct_chuduan','[21.21] Chủ dự án','s6').U::f('ct_diachi','[21.22] Địa chỉ dự án, công trình','s6')
       .U::f('ct_loai','[21.23] Loại công trình','s4').U::f('ct_hangmuc','Hạng mục công trình','s4').U::f('ct_cap','Cấp công trình','s4')
       .U::f('ct_dtxd','[21.24] Diện tích xây dựng','s6').U::f('ct_dtsan','[21.25] Diện tích sàn xây dựng (m²)','s6')
       .U::f('ct_heso','[21.26] Hệ số','s4').U::f('ct_dongia','[21.27] Đơn giá','s4').U::f('ct_giatri','[21.28] Giá trị công trình (đồng)','s4') !!}
  </div>
  <p class="note">[22] Tài sản gắn liền với đất</p>
  <div class="grid">
    {!! U::f('ts_loai','[22.1] Loại tài sản gắn liền với đất','s6').U::f('ts_giatri','[22.2] Giá trị tài sản (đồng)','s6') !!}
  </div>
</fieldset>

<fieldset><legend>V. Thu nhập</legend>
  {!! U::chk('tn_chuyennhuong','[23.1] Thu nhập từ chuyển nhượng bất động sản').U::chk('tn_thuake','[23.2] Thu nhập từ nhận thừa kế, quà tặng') !!}
  <div class="grid" style="margin-top:8px">
    {!! U::f('gia_cn','[24] Giá trị chuyển nhượng bất động sản và tài sản khác gắn liền với đất (đồng)','s6')
       .U::f('gia_tk','[25] Giá trị bất động sản nhận thừa kế, quà tặng (đồng)','s6') !!}
  </div>
  <p class="note">[26] Miễn thuế (tối đa 3 dòng; tích "ở duy nhất" nếu là nhà ở, đất ở duy nhất)</p>
  {!! U::rows('mien',['ten:Họ và tên','id:MST / Số định danh','duynhat:Nhà, đất ở duy nhất:chk','lydo_khac:Lý do miễn khác'],3,'+ Thêm dòng') !!}
</fieldset>

<fieldset><legend>VI. Hồ sơ kèm theo &amp; chữ ký</legend>
  <div class="grid">
    {!! U::f('hoso_1','Hồ sơ kèm theo 1','s6').U::f('hoso_2','Hồ sơ kèm theo 2','s6')
       .U::f('kt_ten','Người thực hiện dịch vụ thuế: họ và tên','s6').U::f('kt_chungchi','Chứng chỉ nghiệp vụ số','s6')
       .U::f('ky_ten_noi','Nơi ký','s3').U::f('ky_ten_ngay','ngày','s3').U::f('ky_ten_thang','tháng','s3').U::f('ky_ten_nam','năm','s3') !!}
  </div>
</fieldset>
@endsection
