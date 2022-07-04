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

    $Err = '';

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

    do {
        $hasil2[]=$row;
    }while($row=mysqli_fetch_assoc($result2));

    //echo json_encode($hasil2);

// End Select yang di perlukan

// Cek sisa kursi
    $sSqlsisa = "SELECT num_jml_sisa from aka_perkuliahan_detail
    where int_kd_perkuliahan_d = '$int_kd_perkuliahan_d' ";

    $sQuerysisa = mysqli_query($conn, $sSqlsisa);
    $srowsisa = mysqli_fetch_object($sQuerysisa);
    $sisa = $srowsisa->num_jml_sisa;
    if (0 == $sisa) {
        $Err .= 'Kelas Sudah Penuh';
    }

    do {
        $hasil3[]=$row;
    }while($row=mysqli_fetch_assoc($result3));

    // echo json_encode($hasil3);
// End cek sisa kursi

// Cek matakuliah syarat
    $angkatan = substr($nim, 0, 4);
    if('UM1714' == $kd_mk_p and $angkatan <= '2014') {
        $Err .= '';
    } else {
        $ssSql = "SELECT * FROM aka_nilai where str_id_nim = '" . $nim . "' and str_kd_mk in (SELECT str_kd_mk_syarat FROM
        aka_matakuliah_syarat WHERE str_kd_mk = '" . $kd_mk_p . "')";

        $ssQuery = mysqli_query($conn, $ssSql);
        $cekssQuery=mysqli_num_rows($ssQuery);
        if (0 == $cekssQuery) {
            $sqlsyarat = "SELECT b.str_nm_mk FROM aka_matakuliah_syarat a
            INNER JOIN aka_matakuliah b ON a.str_kd_mk_syarat = b.str_kd_mk
            WHERE a.str_kd_mk = '" . $kd_mk_p . "' ";

            $ssQueryS=mysqli_query($conn, $sqlSyarat);
            $ssRowS = mysqli_fetch_object($ssQueryS);
            foreach ($ssRowS as $sData) {
                $Err .= 'Mata Kuliah Syarat' .
                $sData->str_nm_mk . "Tidak Terpenuhi\n";
            }
        }
    }
// End Cek matakuliah syarat

// Cek Matakuliah Project Techno
    if ('SP1703' == $kd_mk_p) {
        $Sql = "select * from aka_syarat where str_id_nim = '" . $nim . "'";
        $checkTechnoSql = "SELECT * FROM aka_nilai WHERE str_id_nim = '" . $nim . "' AND str_na NOT IN ('C-','D','E')  AND str_kd_mk IN (SELECT str_kd_mk_syarat FROM aka_matakuliah_syarat WHERE str_kd_mk = 'SP1703') ";

        $Query = mysqli_query($conn, $Sql);
        $cek1=mysqli_num_rows($Query);

        $technoQuery = mysqli_query($conn, $checkTechnoSql);
        $cek2=mysqli_num_rows($technoQuery);

        if ($cek1 < 1 && $cek2 < 1){
            $Err .= 'Mata kuliah tidak dapat di ambil, Syarat harus lulus BSC dan Technopreneur';
        }
    }
// End Cek Matakuliah Project Techno

// Cek Matakuliah Sama
    $Sql = "SELECT * from aka_krs a inner join aka_perkuliahan_detail b on a.int_kd_perkuliahan_d = b.int_kd_perkuliahan_d
    where a.str_id_nim = '" . $nim . "'
    and b.str_kd_perkuliahan = '" . escape_string($kd_mk) . "' and a.str_thn_ajaran = (SELECT str_thn_ajaran_krs FROM pablic_reset)  AND a.bol_semester = (SELECT bol_semester_krs FROM pablic_reset)  ";

    $Query=mysqli_query($conn, $Sql);
    $cekMakul=mysqli_num_rows($Query);
    if ($cekMakul > 0){
        $Err .= "Mata Kuliah Sudah diambil\n";
    }
// End Cek Matakuliah Sama

// Cek Jam dan Hari
    $jamSql = "SELECT b.* FROM aka_krs a INNER JOIN aka_perkuliahan_detail b ON a.int_kd_perkuliahan_d = b.int_kd_perkuliahan_d
    WHERE ((b.time_jam_awal BETWEEN '" . $j_awal . "' AND '" . $j_akhir . "')
    OR (b.time_jam_akhir BETWEEN '" . $j_awal . "' AND '" . $j_akhir . "'))
    and a.str_id_nim = '" . $nim . "'
    and int_hari = '" . $hari . "' and str_thn_ajaran = (SELECT str_thn_ajaran_krs FROM pablic_reset)
    AND bol_semester = (SELECT bol_semester_krs FROM pablic_reset)  ";

    $jamQuery=mysqli_query($conn, $jamSql);
    $cekJam=mysqli_num_rows($jamQuery);

    if ('UM1715' == $kd_mk_p or 'UM1712' == $kd_mk_p or 'SP1704' == $kd_mk_p) {
        $Err .= '';
    } else {
        if ($cekJam > 0 and $kd_mk_p) {
            $Err .= "Mata kuliah Bentrok\n";
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

        $result8=mysqli_query($conn, $inSql);
        $row=mysqli_fetch_assoc($result8);
    }
    
// End Cek Jam dan Hari

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

// // Mengambil jumlah sks diprogramkan
//     $aSql = "
//     SELECT COALESCE(
//     (SELECT SUM(z.sks) FROM (
//     SELECT d.num_sks as sks FROM aka_krs a
//     RIGHT JOIN aka_perkuliahan_detail b ON a.int_kd_perkuliahan_d=b.int_kd_perkuliahan_d
//     RIGHT JOIN aka_perkuliahan c ON b.str_kd_perkuliahan = c.str_kd_perkuliahan
//     RIGHT JOIN aka_matakuliah_detail d ON c.str_kd_mk = d.str_kd_mk
//     RIGHT JOIN mhs_mahasiswa e ON a.str_id_nim=e.str_id_nim
//     WHERE a.str_id_nim='" . $nim . "'
//     AND a.bol_semester = (select bol_semester_krs from pablic_reset) and a.str_thn_ajaran = (select str_thn_ajaran_krs from pablic_reset) and
//     (
//         e.str_kd_prodi=d.str_kd_prodi
//         OR d.str_kd_prodi='0004'
//         OR (e.str_kd_prodi='0001' AND (d.str_kd_prodi='0006' OR d.str_kd_prodi='0007'))
//         OR (e.str_kd_prodi='0002' AND (d.str_kd_prodi='0005' OR d.str_kd_prodi='0007'))
//         OR (e.str_kd_prodi='0003' AND (d.str_kd_prodi='0005' OR d.str_kd_prodi='0006'))
//     )
//     AND ((c.bol_semester='Ganjil' AND (d.num_kd_semester=1 OR d.num_kd_semester=3 OR d.num_kd_semester=5 OR d.num_kd_semester=7))
//         OR (c.bol_semester='Genap' AND (d.num_kd_semester=2 OR d.num_kd_semester=4 OR d.num_kd_semester=6 OR d.num_kd_semester=8)))
//     Group BY a.str_id_nim,  a.str_thn_ajaran, d.str_kd_mk) as z), 0) AS totalSKS";

//     $aQuery = mysqli_query($conn, $aSql);
//     $totalSKS = mysqli_fetch_object($aQuery);

//     $msg['totalSKS'] = '<b>' . $totalSKS->totalSKS . '</b>';
// // End Mengambil jumlah sks diprogramkan

mysqli_close($conn);
?>