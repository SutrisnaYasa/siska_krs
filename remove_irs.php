<?php
   require_once("./koneksi.php");

   // Mengambil request
   $str_id_nim = $_POST['str_id_nim'];
   $int_kd_perkuliahan_d = $_POST['int_kd_perkuliahan_d'];
   // Akhir Mengambil request

// Query select data IRS
   $Sql = "SELECT num_jml_sisa,num_jml_peserta from aka_perkuliahan_detail
   where int_kd_perkuliahan_d = '" . mysqli_real_escape_string($conn, $int_kd_perkuliahan_d) . "' ";
// End Query select data IRS

// Query Update data jumlah kursi
   $Query = mysqli_query($conn, $Sql);
   $row = mysqli_fetch_object($Query);
   $sisa = $row->num_jml_sisa + 1;
   $peserta = $row->num_jml_peserta - 1;

   $inSql = "UPDATE aka_perkuliahan_detail set num_jml_sisa = '{$sisa}',num_jml_peserta = '{$peserta}'
   where int_kd_perkuliahan_d = '" . mysqli_real_escape_string($conn, $int_kd_perkuliahan_d) . "' ";

   $queryUpdate = mysqli_query($conn, $inSql);
// End Query Update data jumlah kursi

// Query Delete data IRS
    $Sql = "DELETE from aka_krs where str_id_nim = '" . $str_id_nim . "'
    and int_kd_perkuliahan_d = '" . $int_kd_perkuliahan_d . "' and str_thn_ajaran = (SELECT str_thn_ajaran_krs FROM pablic_reset)  and bol_semester = (SELECT bol_semester_krs FROM pablic_reset)  ";

    $queryDelete = mysqli_query($conn, $Sql);
    $msg['success'] = true;
// End Query Delete data IRS

// Mengambil jumlah SKS diprogramkan
    $aSql = "SELECT COALESCE(
        (SELECT SUM(z.sks) FROM (
        SELECT d.num_sks as sks FROM aka_krs a
        RIGHT JOIN aka_perkuliahan_detail b ON a.int_kd_perkuliahan_d=b.int_kd_perkuliahan_d
        RIGHT JOIN aka_perkuliahan c ON b.str_kd_perkuliahan = c.str_kd_perkuliahan
        RIGHT JOIN aka_matakuliah_detail d ON c.str_kd_mk = d.str_kd_mk
        RIGHT JOIN mhs_mahasiswa e ON a.str_id_nim=e.str_id_nim
        WHERE a.str_id_nim='" . $str_id_nim . "'
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
        $totalSKS = mysqli_fetch_object($aQuery);

        $msg['totalSKS'] = '<b>' . $totalSKS->totalSKS . '</b>';
// End Mengambil jumlah SKS diprogramkan

    echo json_encode($msg);        

    mysqli_close($conn);
?>