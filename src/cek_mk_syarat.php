<?php
   require_once("./koneksi.php");

// Mengambil request
    $str_id_nim=$_GET['str_id_nim']; //2101030009
    // $sks=$_GET['num_sks'];
    $kd_mk_p=$_GET['str_kd_mk'];
// Akhir Mengambil request

// Cek matakuliah syarat

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

            $ErrSyarat = 'Mata Kuliah Syarat'. ' '.
            $ssRowS->str_nm_mk . ' '. "Tidak Terpenuhi\n";

            echo $ErrSyarat;
        };
    } else{
        echo $ErrSyarat = '1';
    }

// End Cek matakuliah syarat DONE
                   
mysqli_close($conn);
?>