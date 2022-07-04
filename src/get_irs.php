<?php
   require_once("./koneksi.php");

// Allow Cors (*)
   header('Access-Control-Allow-Origin: *');
   header('Access-Control-Allow-Methods: GET, POST');
   header("Access-Control-Allow-Headers: X-Requested-With");
// End Allow Cors (*)

// Mengambil request
   $nim=$_GET['nim']; //2101030009
// Akhir Mengambil request

// Query mengambil status FINAL atau Belum FINAL KRS
   $status = false;
   $perintah = "SELECT str_kd_perwalian, bol_final FROM aka_krs WHERE str_id_nim='" . $nim . "' AND str_thn_ajaran=(SELECT str_thn_ajaran_krs FROM pablic_reset LIMIT 1) AND bol_semester=(SELECT bol_semester_krs FROM pablic_reset LIMIT 1)";
   $Query = mysqli_query($conn, $perintah);
   while ($rowStatus = mysqli_fetch_object($Query)) {
       if(true == $rowStatus->bol_final) {
           $status = true;
       }else {
           $status = false;
       }

   };
    //var_dump($status);
// Akhir dari Mengambil status FINAL atau Belum FINAL KRS

// Cek semester SP atau tidak
   $Queryceksp = "SELECT num_kd_sms_krs FROM pablic_reset";
   $cekspQuery = mysqli_query($conn, $Queryceksp);
   $uDataceksp = mysqli_fetch_object($cekspQuery);
// Cek semester SP atau tidak

// QUERY untuk menampilkan data krs yang diambil oleh mahasiswa
   if ('3' == $uDataceksp->num_kd_sms_krs){
        $query = "SELECT b.int_kd_perkuliahan_d, b.str_nm_kelas, c.str_kd_mk, c.str_nm_mk, e.str_nm_kad, d.str_nama_hari, f.str_nm_ruang,
        MID(b.time_jam_awal,1,5) as awal, MID(b.time_jam_akhir,1,5) as akhir, g.num_sks, g.num_kd_semester, b.num_jml_sisa, v.str_desc FROM aka_krs a 
        INNER JOIN aka_perkuliahan_detail b ON a.int_kd_perkuliahan_d = b.int_kd_perkuliahan_d 
        INNER JOIN aka_perkuliahan v ON b.str_kd_perkuliahan = v.str_kd_perkuliahan AND a.str_thn_ajaran = v.str_thn_ajaran AND a.bol_semester = v.bol_semester
        INNER JOIN aka_matakuliah c ON v.str_kd_mk = c.str_kd_mk
        INNER JOIN mst_hari d ON b.int_hari = d.str_kd_hari
        INNER JOIN uni_karidos e ON b.str_id_dosen = e.str_id_kad
        INNER JOIN aka_ruang f ON b.num_kd_ruang = f.num_kd_ruang
        INNER JOIN aka_matakuliah_detail g ON v.str_kd_mk = g.str_kd_mk AND v.`str_kd_prodi` = g.`str_kd_prodi`
        WHERE a.str_thn_ajaran=(SELECT str_thn_ajaran_krs FROM pablic_reset)
        AND a.bol_semester=(SELECT bol_semester_krs FROM pablic_reset)
        and a.str_id_nim = '" . $nim . "' 
        ORDER BY g.num_kd_semester,d.str_kd_hari,b.time_jam_awal";

        $result=mysqli_query($conn, $query);
        $row=mysqli_fetch_assoc($result);

        do {
            $hasil[]=$row;
        }while($row=mysqli_fetch_assoc($result));

        echo json_encode($hasil);

   } else {
        $query = "SELECT b.int_kd_perkuliahan_d, b.str_nm_kelas, c.str_kd_mk, c.str_nm_mk, e.str_nm_kad, d.str_nama_hari, f.str_nm_ruang,
        MID(b.time_jam_awal,1,5) as awal, MID(b.time_jam_akhir,1,5) as akhir, g.num_sks, g.num_kd_semester, b.num_jml_sisa, v.str_desc FROM aka_krs a 
        INNER JOIN aka_perkuliahan_detail b ON a.int_kd_perkuliahan_d = b.int_kd_perkuliahan_d 
        INNER JOIN aka_perkuliahan v ON b.str_kd_perkuliahan = v.str_kd_perkuliahan AND a.str_thn_ajaran = v.str_thn_ajaran AND a.bol_semester = v.bol_semester
        INNER JOIN aka_matakuliah c ON v.str_kd_mk = c.str_kd_mk
        INNER JOIN mst_hari d ON b.int_hari = d.str_kd_hari
        INNER JOIN uni_karidos e ON b.str_id_dosen = e.str_id_kad
        INNER JOIN aka_ruang f ON b.num_kd_ruang = f.num_kd_ruang
        INNER JOIN aka_matakuliah_detail g ON v.str_kd_mk = g.str_kd_mk AND v.`str_kd_prodi` = g.`str_kd_prodi` AND g.`num_kd_semester`%2 = (SELECT num_kd_sms_krs FROM pablic_reset)
        WHERE a.str_thn_ajaran=(SELECT str_thn_ajaran_krs FROM pablic_reset)
        AND a.bol_semester=(SELECT bol_semester_krs FROM pablic_reset)
        and a.str_id_nim = '" . $nim . "' 
        ORDER BY g.num_kd_semester,d.str_kd_hari,b.time_jam_awal";

        $result=mysqli_query($conn, $query);
        $row=mysqli_fetch_assoc($result);

        do {
            $hasil[]=$row;
        }while($row=mysqli_fetch_assoc($result));

        echo json_encode($hasil);
   }
// End QUERY untuk menampilkan data krs yang diambil oleh mahasiswa
   
mysqli_close($conn);
?>