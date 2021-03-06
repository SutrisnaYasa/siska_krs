<?php
   require_once("./koneksi.php");

// Mengambil request
    $str_id_nim=$_GET['str_id_nim']; //2101030009
    $sks=$_GET['num_sks'];
// Akhir Mengambil request

// Melakukan Pengecekan IPS dan SKS yang Bisa Diambil
    // Query untuk mengambil ips Mahasiswa
        $khs = "SELECT `a`.`id_mhs_mahasiswa` AS `id_mhs_mahasiswa`,`a`.`str_id_nim` AS `str_id_nim`,`a`.`str_nm_mhs` AS `str_nm_mhs`,`b`.`str_nm_prodi` AS `str_nm_prodi`,`b`.`str_kd_prodi` AS `str_kd_prodi`,`a`.`str_angkatan` AS `str_angkatan`,`b`.`str_thn_ajaran` AS `str_thn_ajaran`,`b`.`bol_semester` AS `bol_semester`,round((sum((`c`.`num_sks` * `c`.`num_bobot`)) / sum(`c`.`num_sks`)),2) AS `ips` from ((`mhs_mahasiswa` `a` join `v_semester_prodi` `b`) join `v_transkrip` `c`) where ((`a`.`str_kd_prodi` = `b`.`str_kd_prodi`) and (`b`.`str_thn_ajaran` = `c`.`str_thn_ajaran`) and (`b`.`bol_semester` = `c`.`bol_semester`) and (`a`.`str_id_nim` = `c`.`nim`) and str_id_nim = '".$str_id_nim."') group by `c`.`bol_semester`,`c`.`str_thn_ajaran`,`c`.`nim` order by `a`.`str_id_nim`,`b`.`str_thn_ajaran`,`b`.`bol_semester`";

        $result = mysqli_query($conn, $khs);
        foreach ($result as $mhs) {
        $mhs['ips'];
        }

        $lastElement = end($mhs);
        // var_dump($lastElement);

    // End Query mengambil ips Mahasiswa

    // Mengambil jumlah SKS diprogramkan
        $aSql = "SELECT SUM(sks) AS tot_sks FROM (
            SELECT d.num_sks as sks FROM aka_krs a
            RIGHT JOIN aka_perkuliahan_detail b ON a.int_kd_perkuliahan_d=b.int_kd_perkuliahan_d
            RIGHT JOIN aka_perkuliahan c ON b.str_kd_perkuliahan = c.str_kd_perkuliahan
            RIGHT JOIN aka_matakuliah_detail d ON c.str_kd_mk = d.str_kd_mk
            RIGHT JOIN mhs_mahasiswa e ON a.str_id_nim=e.str_id_nim
            WHERE a.str_id_nim='" . $str_id_nim . "'
            AND a.bol_semester = (select bol_semester_krs from pablic_reset) and a.str_thn_ajaran = (select str_thn_ajaran_krs from pablic_reset)) AS total_sks";

        $aQuery = mysqli_query($conn, $aSql);
        $ttlSKS = mysqli_fetch_object($aQuery);
        
        $msg = $ttlSKS->tot_sks;
        // var_dump($msg);
    // End Mengambil jumlah SKS diprogramkan

    if($lastElement >= 3.25){
        $max = '24 SKS';
    } else if (2.75 <= $lastElement && $lastElement < 3.25){
        $max = '21 SKS';   
    } else if (2.00 <= $lastElement && $lastElement < 2.75) {
        $max = '18 SKS';
    } else if ($lastElement < 2.00) {
        $max = '15 SKS';
    } else {
        $max = '0 SKS';
    }

    $plusSks = $msg + $sks;
    var_dump($plusSks);
    // Melakukan pengecekan ips dan sks yang diambil
        if ($lastElement >= 3.25 && $plusSks <= 24) {
            echo $tot = '24 SKS';
            // echo $msg;
        }else if( 2.75 <= $lastElement && $lastElement < 3.25 && $plusSks <= 21){
            echo $tot = '21 SKS';
            // echo $msg;
        }else if( 2.00 <= $lastElement && $lastElement < 2.75 && $plusSks <= 18){
            echo $tot = '18 SKS';
            // echo $msg;
        }else if($lastElement < 2.00 && $plusSks <= 15){
            echo $tot = '15 SKS';
            // echo $msg;
        }else{
            echo 'Maaf, Tidak bisa mengambil mata kuliah lagi, karena IPS anda pada semester sebelumnya adalah '.$lastElement.'. Maka maksimal SKS yang bisa diambil pada semester ini adalah '.$max.'' ;
        }
    // End Melakukan pengecekan ips dan sks yang diambil
// End Melakukan Pengecekan IPS dan SKS yang Bisa Diambil     
                   
mysqli_close($conn);
?>