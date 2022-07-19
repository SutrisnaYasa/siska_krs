<?php
   require_once("./koneksi.php");

// Allow Cors (*)
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");
// End Allow Cors (*)

// Mengambil Request
   $str_id_nim = $_GET['str_id_nim'];
   $int_kd_perkuliahan_d = $_POST['int_kd_perkuliahan_d'];
   $sks=$_POST['num_sks'];
// End Mengambil Request

// Mengambil Pablic_reset
   $query = "SELECT * from pablic_reset";

   $result=mysqli_query($conn, $query);
   $Data = mysqli_fetch_object($result);
    
   $thn_ajaran = explode('/', $Data->str_thn_ajaran_krs);
   $thnajaran = $thn_ajaran[0] . '/' . $thn_ajaran[1];
   $thnajaran_dash = $thn_ajaran[0] . '-' . $thn_ajaran[1];
   $sms = $Data->num_kd_sms_krs;
   $bol_semester = $Data->bol_semester_krs;

   // Generate str_kd_perwalian
   $kode = $str_id_nim . $thn_ajaran[0] . $thn_ajaran[1] . $sms;

// End Mengambil Pablic reset

// Mengecek Pembayaran
  // Cek data Transaksi
    $tSql = "SELECT SUM(jml_trans_bayar) as jml_trans_bayar FROM keu_transaksi WHERE str_id_nim = '$str_id_nim' AND str_thn_ajaran = '{$thnajaran}' AND bol_semester = '{$bol_semester}'";

    $tQuery = mysqli_query($conn, $tSql);
    $tData = mysqli_fetch_object($tQuery);
    $akumulasi = $tData->jml_trans_bayar;
    // End Cek data Transaksi

    // Cek data Mahasiswa
    $sql = "SELECT * from mhs_mahasiswa where str_id_nim='" . $str_id_nim . "' ";
    $mhsQue = mysqli_query($conn, $sql);
    $angkatanQuery = mysqli_fetch_object($mhsQue);
    $angkatan = $angkatanQuery->str_angkatan;
    // End Cek data Mahasiswa

    // Penangguhan & Cek Akumulasi Pembayaran
    $penangguhan = "SELECT * FROM keu_jml_biaya where bol_semester = '" . $sms. "' and str_thn_ajaran = '" . $thnajaran_dash. "' and str_id_nim = '$str_id_nim'";

    $mhsPen = mysqli_query($conn, $penangguhan);
    $queryPenangguhan = mysqli_fetch_object($mhsPen);
    $biaya_spp = $queryPenangguhan->int_nominal;

    if ('1' === $queryPenangguhan->int_penangguhan) {
        $ErrPembayaran = 'KRSPembayaran';
    } else {
            if ($bol_semester == 'SP') {
                $ErrPembayaran = 'KRSPembayaran';
            } else {
                if($akumulasi >= ($biaya_spp * 0.5)) {
                   echo $ErrPembayaran = 'KRSPembayaran';
                } else {
                   echo $ErrPembayaran = 'Silahkan Lunasi Pembayaran atau Menghubungi Bagian Keuangan';
                }
            }
    }
    
    // End Penangguhan & Cek Akumulasi Pembayaran
// End Mengecek Pembayaran



mysqli_close($conn);
?>