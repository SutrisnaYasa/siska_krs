<?php
   require_once("./koneksi.php");

// Mengambil request
    $nim=$_GET['nim']; //2101030009
// Akhir Mengambil request

// Query mengambil data mhs
   $u_sql = "SELECT * FROM users
   INNER JOIN mhs_mahasiswa ON users.`username` = mhs_mahasiswa.str_id_nim WHERE username = '$nim'";

   $uResult = mysqli_query($conn, $u_sql);
   $u_Data = mysqli_fetch_object($uResult);
   $angkatan = $u_Data->str_angkatan;
   $ang_array = ['2013', '2014', '2015', '2016', '2017', '2018', '2019'];
// End Query mengambil data mhs

// Query mengambil Pablic Reset
    $sQuery = "SELECT * from pablic_reset";
    $QuerySs = mysqli_query($conn, $sQuery);
    $sData = mysqli_fetch_object($QuerySs);
    $thn_ajaran = explode('/', $sData->str_thn_ajaran_krs);
    $sms = $sData->num_kd_sms_krs;
    $kode = $nim . $thn_ajaran[0] . $thn_ajaran[1] . $sms;
// End Mengambil pablic reset

// Mengambil status KRS
  $status = 'Belum KRS';
  $perintah = "SELECT str_kd_perwalian, bol_final FROM aka_krs WHERE str_id_nim='" . $nim . "' AND str_thn_ajaran=(SELECT str_thn_ajaran_krs FROM pablic_reset LIMIT 1) and bol_semester = (SELECT bol_semester_krs FROM pablic_reset LIMIT 1)";
  $Query = mysqli_query($conn, $perintah);
  while ($result = mysqli_fetch_object($Query)) {
    if(true == $result->bol_final) {
      $status = 'Final';
    } else {
      $status = 'Belum Final';
    }  
  };
// End Mengambil Status KRS

// Mengambil data komentar
  $Sql = "SELECT a.str_komentar, b.display_name, a.time_stamp from aka_komentar a inner join users b on a.str_kd_user = b.id_users where a.str_kd_perwalian = '$kode' ORDER BY a.time_stamp DESC";
  $Query = mysqli_query($conn, $Sql);
  while ($data = mysqli_fetch_object($Query)) {
          $data->str_komentar;
  };
// End Mengambil data Komentar

// Mengambil nama pembimbing PA
  $sSql = "SELECT a.* from uni_karidos a, aka_group_wali b, mhs_mahasiswa c where a.str_id_kad = b.str_kd_dosen_wali_d AND b.int_id_group_wali= c.int_id_group_wali AND c.str_id_nim =  ' . $nim . ' "; 
  $qQuery = mysqli_query($conn, $sSql);
  // $data['DataKadD'] = mysqli_fetch_object($qQuery);
  while ($data = mysqli_fetch_object($qQuery)) {
    $data->aka_group_wali;
  };


  $sSql = "SELECT a.* from uni_karidos a, aka_group_wali b, mhs_mahasiswa c where a.str_id_kad = b.str_kd_dosen_wali_aktif AND b.int_id_group_wali= c.int_id_group_wali AND c.str_id_nim =  ' . $nim . ' ";
  $qQuery = mysqli_query($conn, $sSql);
  // $data['DataKadAktif'] = mysqli_fetch_object($qQuery);
  while ($data = mysqli_fetch_object($qQuery)) {
    $data->aka_group_wali;
  };

// End Mengambil nama pembimbing PA

// Mengambil jumlah SKS diprogramkan
  $aSql = "SELECT COALESCE(
      (SELECT SUM(z.sks) FROM (
      SELECT d.num_sks as sks FROM aka_krs a
      RIGHT JOIN aka_perkuliahan_detail b ON a.int_kd_perkuliahan_d=b.int_kd_perkuliahan_d
      RIGHT JOIN aka_perkuliahan c ON b.str_kd_perkuliahan = c.str_kd_perkuliahan
      RIGHT JOIN aka_matakuliah_detail d ON c.str_kd_mk = d.str_kd_mk
      RIGHT JOIN mhs_mahasiswa e ON a.str_id_nim=e.str_id_nim
      WHERE a.str_id_nim='" . $nim . "'
      AND a.bol_semester = (select bol_semester_krs from pablic_reset) and a.str_thn_ajaran = (select str_thn_ajaran_krs from pablic_reset) and
      (
          e.str_kd_prodi=d.str_kd_prodi
          OR d.str_kd_prodi='0004'
          OR (e.str_kd_prodi='0001' AND (d.str_kd_prodi='0006' OR d.str_kd_prodi='0007'))
          OR (e.str_kd_prodi='0002' AND (d.str_kd_prodi='0005' OR d.str_kd_prodi='0007'))
          OR (e.str_kd_prodi='0003' AND (d.str_kd_prodi='0005' OR d.str_kd_prodi='0006'))
      )
      AND ((c.bol_semester='Ganjil' AND (d.num_kd_semester=1 OR d.num_kd_semester=3 OR d.num_kd_semester=5 OR d.num_kd_semester=7))
            OR (c.bol_semester='Genap' AND (d.num_kd_semester=2 OR d.num_kd_semester=4 OR d.num_kd_semester=6 OR d.num_kd_semester=8)))
      Group BY a.str_id_nim,  a.str_thn_ajaran, d.str_kd_mk) as z), 0) AS totalSKS";

      $aQuery = mysqli_query($conn, $aSql);
      $ttlSKS = mysqli_fetch_object($aQuery);

      $msg = $ttlSKS->totalSKS;
      echo $msg;
// End Mengambil jumlah SKS diprogramkan

// Setup tanggal buka dan tutup KRS
    $paymentDate = strtotime(date('Y-m-d'));
    $contractDateBegin = strtotime('2022-06-25');
    $contractDateEnd = strtotime('2022-07-05');
// End Setup tanggal buka dan tutup KRS

// Cek tanggal KRS dibuka atau ditutup
    if ($paymentDate >= $contractDateBegin && $paymentDate <= $contractDateEnd) {
        echo json_encode([
          'status'=>true,
          'message'=>'KRS Dibuka'
        ]);
    } else {
      echo json_encode([
        'status'=>false,
        'message'=>'KRS Belum Dibuka'
      ]);
    }
// End Cek tanggal KRS dibuka atau ditutup

  mysqli_close($conn);
?>