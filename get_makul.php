<?php
   require_once("./koneksi.php");

// Mengambil request
    $nim=$_GET['nim']; //2101030009
// Akhir Mengambil request

// // Akhir dari Mengambil status FINAL atau Belum FINAL KRS

// Query mengambil data angkatan MHS

    $xfil = '';
    $uQueryck = "SELECT int_kd_kelas,str_kd_prodi,str_angkatan
    from mhs_mahasiswa where str_id_nim = '$nim'";

    $resultAngkatan=mysqli_query($conn, $uQueryck);
    $uDatack = mysqli_fetch_object($resultAngkatan);

    if ('0001' == $uDatack->str_kd_prodi) {
        $fProdi = '0001,0004,0006,0007';
    } elseif ('0003' == $uDatack->str_kd_prodi) {
        $fProdi = '0003,0004,0005,0006';
    } elseif ('0002' == $uDatack->str_kd_prodi) {
        $fProdi = '0002,0004,0005,0007';
    }

    if (2 == $uDatack->int_kd_kelas) {
        $inKls = '1,2';
    } else {
        $inKls = '1';
    }

    if ('2019' == $uDatack->str_angkatan || '2020' == $uDatack->str_angkatan || '2021' == $uDatack->str_angkatan || '2022' == $uDatack->str_angkatan) {
        $kurikulum = '2020';
    } else {
        $kurikulum = '2017';
    }


// Akhir Query mengambil data angkatan MHS

// Cek Semester SP
    $queryceksp = "SELECT num_kd_sms_krs FROM pablic_reset";

    $resultSP=mysqli_query($conn, $queryceksp);
    $uDataceksp = mysqli_fetch_object($resultSP);
    
    // $datasp = $uDataceksp->num_kd_sms_krs;

    // $cek = mysqli_num_rows($resultAngkatan);
    // if ($cek > 0) {
    //     $uQuery = "SELECT * FROM users where username='$nim'";
    //     $userQue = mysqli_query($conn, $uQuery);
    //     $uData = mysqli_fetch_object($userQue);
    //     if ('3' == $uDataceksp->num_kd_sms_krs) {
    //         $xfil = "AND a.str_kd_prodi in ({$fProdi})";
    //     } else {
    //         $xfil = "AND b.int_kd_kelas in ({$inKls})
    //         AND a.str_kd_prodi in ({$fProdi})";
    //     }
    // } elseif (!empty($_GET['nim'])) {
    //     $xfil = 'AND b.int_kd_kelas in (0001,0004,0006,0007)
    //     AND a.str_kd_prodi in (1)';
    // }  

// Akhir Cek Semester SP
   
// Query untuk menampilkan Kelas yang dibuka
if ('3' == $uDataceksp->num_kd_sms_krs) {
    $query = "SELECT b.int_kd_perkuliahan_d, b.str_nm_kelas, c.str_kd_mk, c.str_nm_mk, e.str_nm_kad, d.str_nama_hari, f.str_nm_ruang, MID(b.time_jam_awal,1,5) as awal, MID(b.time_jam_akhir,1,5) as akhir, g.num_sks, g.num_kd_semester, b.num_jml_sisa, a.str_desc, g.str_thn_kurikulum FROM aka_perkuliahan a
    INNER JOIN aka_perkuliahan_detail b ON a.str_kd_perkuliahan = b.str_kd_perkuliahan
    INNER JOIN aka_matakuliah c ON a.str_kd_mk = c.str_kd_mk
    INNER JOIN mst_hari d ON b.int_hari = d.str_kd_hari
    INNER JOIN uni_karidos e ON b.str_id_dosen = e.str_id_kad
    INNER JOIN aka_ruang f ON b.num_kd_ruang = f.num_kd_ruang
    INNER JOIN aka_matakuliah_detail g ON a.str_kd_mk = g.str_kd_mk
    AND a.`str_kd_prodi` = g.`str_kd_prodi`
    WHERE a.str_thn_ajaran = (SELECT str_thn_ajaran_krs FROM pablic_reset)
    AND a.bol_semester = (SELECT bol_semester_krs FROM pablic_reset)
    AND a.str_kd_prodi in ({$fProdi})
    AND g.str_thn_kurikulum = '" . mysqli_real_escape_string($conn, $kurikulum) . "'
    ORDER BY g.num_kd_semester,d.str_kd_hari,b.time_jam_awal";

    $result=mysqli_query($conn, $query);
    $row=mysqli_fetch_assoc($result);
    do {
        $hasil[]=$row;
    }while($row=mysqli_fetch_assoc($result));

    echo json_encode($hasil);

} else {
    $query = "SELECT b.int_kd_perkuliahan_d, b.str_nm_kelas, c.str_kd_mk, c.str_nm_mk, e.str_nm_kad, d.str_nama_hari, f.str_nm_ruang, MID(b.time_jam_awal,1,5) as awal, MID(b.time_jam_akhir,1,5) as akhir, g.num_sks, g.num_kd_semester, b.num_jml_sisa, a.str_desc, g.str_thn_kurikulum FROM aka_perkuliahan a
    INNER JOIN aka_perkuliahan_detail b ON a.str_kd_perkuliahan = b.str_kd_perkuliahan
    INNER JOIN aka_matakuliah c ON a.str_kd_mk = c.str_kd_mk
    INNER JOIN mst_hari d ON b.int_hari = d.str_kd_hari
    INNER JOIN uni_karidos e ON b.str_id_dosen = e.str_id_kad
    INNER JOIN aka_ruang f ON b.num_kd_ruang = f.num_kd_ruang
    INNER JOIN aka_matakuliah_detail g ON a.str_kd_mk = g.str_kd_mk
    AND a.`str_kd_prodi` = g.`str_kd_prodi`
    AND g.`num_kd_semester`%2 = (SELECT num_kd_sms_krs FROM pablic_reset)
    WHERE a.str_thn_ajaran = (SELECT str_thn_ajaran_krs FROM pablic_reset)
    AND a.bol_semester = (SELECT bol_semester_krs FROM pablic_reset)
    AND a.str_kd_prodi in ({$fProdi})
    AND b.int_kd_kelas in ({$inKls})
    AND g.str_thn_kurikulum = '" . mysqli_real_escape_string($conn, $kurikulum) . "'
    ORDER BY g.num_kd_semester,d.str_kd_hari,b.time_jam_awal";

    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    do {
        $hasil[]=$row;
    }while($row=mysqli_fetch_assoc($result));

    echo json_encode($hasil);

}


// Akhir Query untuk menampilkan Kelas yang dibuka

?>