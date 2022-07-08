<?php
   require_once("./koneksi.php");
// Allow Cors (*)
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");
// End Allow Cors (*)

// Mengambil request
   $str_id_nim = $_POST['str_id_nim'];
   $int_kd_perkuliahan_d = $_POST['int_kd_perkuliahan_d'];
// Akhir Mengambil request

// Query select data kursi masing masing kelas
   $Sql = "SELECT num_jml_sisa,num_jml_peserta from aka_perkuliahan_detail
   where int_kd_perkuliahan_d = '" . mysqli_real_escape_string($conn, $int_kd_perkuliahan_d) . "' ";

    // Query Update data jumlah kursi
    $Query = mysqli_query($conn, $Sql);
    $row = mysqli_fetch_object($Query);
    $sisa = $row->num_jml_sisa + 1;
    $peserta = $row->num_jml_peserta - 1;

    $inSql = "UPDATE aka_perkuliahan_detail set num_jml_sisa = '{$sisa}',num_jml_peserta = '{$peserta}'
    where int_kd_perkuliahan_d = '" . mysqli_real_escape_string($conn, $int_kd_perkuliahan_d) . "' ";

    $queryUpdate = mysqli_query($conn, $inSql);
    // End Query Update data jumlah kursi

// End Query select data kursi masing masing kelas

// Query Delete data IRS
    $Sql = "DELETE from aka_krs where str_id_nim = '" . $str_id_nim . "'
    and int_kd_perkuliahan_d = '" . $int_kd_perkuliahan_d . "' and str_thn_ajaran = (SELECT str_thn_ajaran_krs FROM pablic_reset)  and bol_semester = (SELECT bol_semester_krs FROM pablic_reset)  ";

    $queryDelete = mysqli_query($conn, $Sql);
    if($queryDelete) {
        $msg = "Delete Data IRS Berhasil";
    }else{
        $msg = "Delete Data IRS Gagal";
    }

    $response = array(
        'status'=>true,
        'message'=>$msg
    );

    echo json_encode($response);  
// End Query Delete data IRS

    mysqli_close($conn);
?>