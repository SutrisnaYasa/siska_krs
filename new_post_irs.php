<?php
   require_once("./koneksi.php");

   $str_id_nim = $_POST['str_id_nim'];
   $int_kd_perkuliahan_d = $_POST['int_kd_perkuliahan_d'];

// Mengambil Pablic_reset
   $query = "SELECT * from pablic_reset";

   $result=mysqli_query($conn, $query);
   $Data = mysqli_fetch_object($result);

   $thn_ajaran = explode('/', $Data->str_thn_ajaran_krs);
   $sms = $Data->num_kd_sms_krs;

   // Generate str_kd_perwalian
   $kode = $str_id_nim . $thn_ajaran[0] . $thn_ajaran[1] . $sms;

// End Mengambil Pablic reset

// Select yang diperlukan
    $sSql = "SELECT a.time_jam_awal, a.time_jam_akhir, a.int_hari, a.str_kd_perkuliahan, b.str_kd_mk
    FROM aka_perkuliahan_detail a
    INNER JOIN aka_perkuliahan b ON a.str_kd_perkuliahan = b.str_kd_perkuliahan WHERE a.int_kd_perkuliahan_d = '$int_kd_perkuliahan_d'";

    $sQuery=mysqli_query($conn, $sSql);
    $srow = mysqli_fetch_object($sQuery);
    $j_awal = $srow->time_jam_awal;
    $j_akhir = $srow->time_jam_akhir;
    $hari = $srow->int_hari;
    $kd_mk = $srow->str_kd_perkuliahan;
    $kd_mk_p = $srow->str_kd_mk;
// End Select yang di perlukan

// Cek Matakuliah Sama
    $Sql = "SELECT * from aka_krs a inner join aka_perkuliahan_detail b on a.int_kd_perkuliahan_d = b.int_kd_perkuliahan_d
    where a.str_id_nim = '" . $str_id_nim . "'
    and b.str_kd_perkuliahan = '". mysqli_real_escape_string($conn, $kd_mk) . "' and a.str_thn_ajaran = (SELECT str_thn_ajaran_krs FROM pablic_reset)  AND a.bol_semester = (SELECT bol_semester_krs FROM pablic_reset)  ";

    $Query=mysqli_query($conn, $Sql);
    $cekMakul=mysqli_num_rows($Query);
    if ($cekMakul > 0){
        echo 'Mata Kuliah Sudah diambil';
    }
// End Cek Matakuliah Sama

// Cek sisa kursi
    $sSqlsisa = "SELECT num_jml_sisa from aka_perkuliahan_detail
    where int_kd_perkuliahan_d = '$int_kd_perkuliahan_d' ";

    $sQuerysisa = mysqli_query($conn, $sSqlsisa);
    $srowsisa = mysqli_fetch_object($sQuerysisa);
    $sisa = $srowsisa->num_jml_sisa;
    if (0 == $sisa) {
        echo 'Kelas Sudah Penuh';
    }
// End cek sisa kursi

$Err = '';
// Cek Jam dan Hari
            $jamSql = "SELECT b.* FROM aka_krs a INNER JOIN aka_perkuliahan_detail b ON a.int_kd_perkuliahan_d = b.int_kd_perkuliahan_d
            WHERE ((b.time_jam_awal BETWEEN '" . $j_awal . "' AND '" . $j_akhir . "')
            OR (b.time_jam_akhir BETWEEN '" . $j_awal . "' AND '" . $j_akhir . "'))
            and a.str_id_nim = '" . $str_id_nim . "'
            and int_hari = '" . $hari . "' and str_thn_ajaran = (SELECT str_thn_ajaran_krs FROM pablic_reset)
            AND bol_semester = (SELECT bol_semester_krs FROM pablic_reset)  ";

            $jamQuery=mysqli_query($conn, $jamSql);
            $cekJam=mysqli_num_rows($jamQuery);

            if ('UM1715' == $kd_mk_p or 'UM1712' == $kd_mk_p or 'SP1704' == $kd_mk_p) {
                

            } else {
                if ($cekJam > 0 and $kd_mk_p) {
                    echo 'Mata kuliah Bentrok';
                }
            }

            if('' == $Err) {
                $Sql = "SELECT num_jml_sisa,num_jml_peserta from aka_perkuliahan_detail
                where int_kd_perkuliahan_d = '$int_kd_perkuliahan_d' ";

                $Query=mysqli_query($conn, $Sql);
                $row = mysqli_fetch_object($Query);

                $sisa = $row->num_jml_sisa - 1;
                $peserta = $row->num_jml_peserta + 1;

                $inSql = "update aka_perkuliahan_detail set num_jml_sisa = '{$sisa}',num_jml_peserta = '{$peserta}'
                where int_kd_perkuliahan_d = '$int_kd_perkuliahan_d' ";
                
            }
// End Cek Jam dan Hari



mysqli_close($conn);
?>