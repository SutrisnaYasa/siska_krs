<?php
   require_once("./koneksi.php");

// Mengambil request
  $nim=$_GET['nim']; //2101030009
// Akhir Mengambil request


// Cek Master Pablic Reset
  $sQuery = "SELECT * from pablic_reset";
  $querypab = mysqli_query($conn, $sQuery);
  $sData = mysqli_fetch_object($querypab);

  $thn_ajaran = explode('/', $sData->str_thn_ajaran_krs);
  $thnajaran = $thn_ajaran[0] . '/' . $thn_ajaran[1];
  $thnajaran_dash = $thn_ajaran[0] . '-' . $thn_ajaran[1];
  $sms = $sData->num_kd_sms_krs;
  $bol_semester = $sData->bol_semester_krs;
// End Cek Master Pablic Reset

// Cek data Transaksi
  $tSql = "SELECT SUM(jml_trans_bayar) as jml_trans_bayar FROM keu_transaksi WHERE str_id_nim = '$nim' AND str_thn_ajaran = '{$thnajaran}' AND bol_semester = '{$bol_semester}'";

  $tQuery = mysqli_query($conn, $tSql);
  $tData = mysqli_fetch_object($tQuery);
  $akumulasi = $tData->jml_trans_bayar;
// End Cek data Transaksi

// Cek data Mahasiswa
  $sql = "SELECT * from mhs_mahasiswa where str_id_nim='" . $nim . "' ";
  $mhsQue = mysqli_query($conn, $sql);
  $angkatanQuery = mysqli_fetch_object($mhsQue);
  $angkatan = $angkatanQuery->str_angkatan;
// End Cek data Mahasiswa

// Penangguhan
  $penangguhan = "SELECT int_penangguhan FROM keu_jml_biaya where bol_semester = '0' and str_thn_ajaran = '2020-2021' and str_id_nim = '$nim'";

  $mhsPen = mysqli_query($conn, $penangguhan);
  $queryPenangguhan = mysqli_fetch_object($mhsPen);

  if ('1' === $queryPenangguhan->int_penangguhan) {
      echo 'boleh krs';
  } else {
      if ($bol_semester == 'SP') {
          echo 'krs sp';
      } else {
          $pembayaran = "SELECT * FROM `keu_total_spp` WHERE bol_semester like '%" . $bol_semester . "%' AND str_thn_ajaran = '" . $thn_ajaran . "' AND str_id_nim = '" . $nim . "'";
          $quePem = mysqli_query($conn, $pembayaran);
          $biaya = mysqli_fetch_object($quePem);

          if (!empty($biaya->int_total_biaya)) {
            $angsuran = $biaya->int_total_biaya;
          } else {
              $angsuran = 1;
          }
          if (!empty($biaya->str_keterangan)) {
              $keterangan = $biaya->bol_beasiswa;
          } else {
              $keterangan = '0';
          }
          $angsuranMinimal = 0.25 * $angsuran;
          if ('1' != $keterangan) {
              if ($total >= $angsuranMinimal) {
                  echo 'krs dibuka';
              } else {
                  echo 'akses ditolak';
              }
          } else {
              echo 'krs dibuka';
          }
      }
  }

// End Penangguhan

   mysqli_close($conn);
?>