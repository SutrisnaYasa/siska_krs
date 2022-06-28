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
    INNER JOIN aka_perkuliahan b ON a.str_kd_perkuliahan = b.str_kd_perkuliahan WHERE a.int_kd_perkuliahan_d = '" . mysqli_real_escape_string($conn, $int_kd_perkuliahan_d) ."' ";

    $sQuery = mysqli_query($conn, $sSql);
    $srow = mysqli_fetch_object($sQuery);
    $j_awal = $srow->time_jam_awal;
    $j_akhir = $srow->time_jam_akhir;
    $hari = $srow->int_hari;
    $kd_mk = $srow->str_kd_perkuliahan;
    $kd_mk_p = $srow->str_kd_mk;
// End Select yang di perlukan

// Cek matakuliah syarat
    $angkatan = substr($str_id_nim, 0, 4);
    if('UM1714' == $kd_mk_p and $angkatan <= '2014') {
       $Err = '';
    } else {
        $ssSql = "SELECT * FROM aka_nilai where str_id_nim = '" . $str_id_nim . "' and str_kd_mk in (SELECT str_kd_mk_syarat FROM
        aka_matakuliah_syarat WHERE str_kd_mk = '" . $kd_mk_p . "')";

        $ssQuery = mysqli_query($conn, $ssSql);
        $cekssQuery=mysqli_num_rows($ssQuery);

        if (0 == $cekssQuery) {
            $sqlsyarat = "SELECT b.str_nm_mk FROM aka_matakuliah_syarat a
            INNER JOIN aka_matakuliah b ON a.str_kd_mk_syarat = b.str_kd_mk
            WHERE a.str_kd_mk = '" . $kd_mk_p . "' ";

            $ssQueryS = mysqli_query($conn, $sqlsyarat);
            while ($ssRowS = mysqli_fetch_object($ssQueryS)) {

                $Err = 'Mata Kuliah Syarat'. ' '.
                $ssRowS->str_nm_mk . ' '. "Tidak Terpenuhi\n";

                // echo $Err;
            };
        } else{
            $Err = '1';
        }
    }
// End Cek matakuliah syarat DONE

// Cek sisa kursi
    $sSqlsisa = "SELECT num_jml_sisa from aka_perkuliahan_detail
    where int_kd_perkuliahan_d = '" . mysqli_real_escape_string($conn, $int_kd_perkuliahan_d) ."' ";

    $sQuerysisa = mysqli_query($conn, $sSqlsisa);
    $srowsisa = mysqli_fetch_object($sQuerysisa);
    $sisa = $srowsisa->num_jml_sisa;
    if (0 == $sisa) {
        $Err2 = 'Kelas Sudah Penuh';
    } else{
        $Err2 = '2';
    }
// End cek sisa kursi DONE

// Cek Matakuliah Sama
    $Sql = "SELECT * from aka_krs a inner join aka_perkuliahan_detail b on a.int_kd_perkuliahan_d = b.int_kd_perkuliahan_d
    where a.str_id_nim = '" . $str_id_nim . "'
    and b.str_kd_perkuliahan = '" . mysqli_real_escape_string($conn, $kd_mk) . "' and a.str_thn_ajaran = (SELECT str_thn_ajaran_krs FROM pablic_reset)  AND a.bol_semester = (SELECT bol_semester_krs FROM pablic_reset)  ";

    $Query=mysqli_query($conn, $Sql);
    $cekMakul=mysqli_num_rows($Query);
    if ($cekMakul > 0){
        $Err3 = "Mata Kuliah Sudah diambil";
    } else {
       $Err3 = '3';
    }
// End Cek Matakuliah Sama DONE

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
       $Err4 = '';
    } else {
        if ($cekJam > 0 and $kd_mk_p) {
            $Err4 = "Mata kuliah Bentrok\n";
        } else {
            $Err4 = '';
        }
    }

    if('' == $Err4) {
        $Sql = "SELECT num_jml_sisa,num_jml_peserta from aka_perkuliahan_detail
        where int_kd_perkuliahan_d = '" . mysqli_real_escape_string($conn, $int_kd_perkuliahan_d) . "' ";

        $Query=mysqli_query($conn, $Sql);
        $row = mysqli_fetch_object($Query);
        $sisa = $row->num_jml_sisa - 1;
        $peserta = $row->num_jml_peserta + 1;

        $inSql = "update aka_perkuliahan_detail set num_jml_sisa = '{$sisa}',num_jml_peserta = '{$peserta}'
			where int_kd_perkuliahan_d = '" . mysqli_real_escape_string($conn, $int_kd_perkuliahan_d) . "' ";

        $QueryUpdate=mysqli_query($conn, $inSql);

    } 

// End Cek Jam dan Hari DONE

// Jika Mengambil Makul Project Techno(SP1703) Harus Sudah ikut BSC
    if ('SP1703' == $kd_mk_p) {
        $Sql = "select * from aka_syarat where str_id_nim = '" . $str_id_nim . "'";
        $checkTechnoSql = "SELECT * FROM aka_nilai WHERE str_id_nim = '" . $str_id_nim . "' AND str_na NOT IN ('C-','D','E')  AND str_kd_mk IN (SELECT str_kd_mk_syarat FROM aka_matakuliah_syarat WHERE str_kd_mk = 'SP1703') ";

        $Query = mysqli_query($conn, $Sql);
        $cek1=mysqli_num_rows($Query);

        $technoQuery = mysqli_query($conn, $checkTechnoSql);
        $cek2=mysqli_num_rows($technoQuery);

        if ($cek1 < 1 && $cek2 < 1){
            echo $Err5 = 'Mata kuliah tidak dapat di ambil, Syarat harus lulus BSC dan Technopreneur';
        }
    }else {
        $Err5 = '5';
    }
// End Cek Mengambil Makul Project Techno(SP1703) Harus Sudah ikut BSC DONE
     
if($Err = '1'){
    if($Err3 == '3'){
        if($Err4 == ''){
            if($Err5 == '5'){
                if($Err2 == '2'){
                    // ADD IRS
                    // Menampilkan seluruh data POST
                    foreach ($_POST as $param_name => $param_val) {
                        if ($param_name == "str_id_nim"){
                            $str_id_nim = $param_val;
                        }
                        else if($param_name == "int_kd_perkuliahan_d"){
                            $int_kd_perkuliahan_d = $param_val;
                        }
                        else if($param_name == "str_kd_perwalian"){
                            $str_kd_perwalian = $param_val;
                        }
                        else if($param_name == "tgljam_perwalian"){
                            $tgljam_perwalian = $param_val;
                        }
                        else if($param_name == "str_tahun_ajaran"){
                            $str_tahun_ajaran = $param_val;
                        }
                        else {
                            $bol_semester = $param_val;
                        }
                    }
                    // Akhir Menampilkan seluruh data POST

                    // Query add ke tabel aka_krs
                    $sql = "INSERT INTO aka_krs (str_id_nim,int_kd_perkuliahan_d,str_kd_perwalian,tgljam_perwalian,str_thn_ajaran,bol_semester) VALUES ('" . $str_id_nim . "','" . $int_kd_perkuliahan_d . "','" . $kode . "','',(SELECT str_thn_ajaran_krs FROM pablic_reset) ,(SELECT bol_semester_krs FROM pablic_reset) )";

                    //Running Query
                    $query = mysqli_query($conn, $sql);
                    if($query) {
                        $msg = "Simpan Data IRS Berhasil";
                    }else{
                        $msg = "Simpan Data IRS Gagal";
                    }
                    // End Running Query

                    // Mengambil respon untuk di encode menjadi JSON
                    $response = array(
                        'status'=>'OK',
                        'msg'=>$msg
                    );

                    // ENCODE menjadi data JSON
                    echo json_encode($response);

                    // Akhir add ke tabel aka_krs
                // End ADD IRS
                }else{
                    echo $Err2;
                }
            }else {
                echo $Err5;
            }
        }else {
            echo $Err4;
        }
    }else {
        echo $Err3;
    }
} else {
    echo $Err;
}

mysqli_close($conn);
?>