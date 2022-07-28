<?php
   require_once("./koneksi.php");

// Allow Cors (*)
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");
// End Allow Cors (*)

// Mengambil Request
   $str_id_nim = $_POST['str_id_nim'];
   $int_kd_perkuliahan_d = $_POST['int_kd_perkuliahan_d'];
   $sks=$_POST['num_sks'];
   $kd_mk_p=$_POST['str_kd_mk'];
//    var_dump($sks);
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

// Mengecek Masa IRS
    // Cek tanggal mulai, tanggal akhir, dan tanggal saat ini
    $today=date ("Y-m-d");
    $tanggal_mulai = strtotime('2022-06-25');
    $tgl_today = strtotime($today);
    $tanggal_akhir = strtotime('2022-07-30');
    if ($tgl_today < $tanggal_mulai){
        $ErrMasa = 'KRS belum dibuka';
    }else if ($tgl_today > $tanggal_akhir){
        $ErrMasa = 'KRS telah ditutup';
    }else if($tgl_today >= $tanggal_mulai && $tgl_today <= $tanggal_akhir){
        $ErrMasa = 'KRS';
    } else {
        $ErrMasa = 'Tanggal tidak ditemukan';
    }
    // Cek tanggal mulai, tanggal akhir, dan tanggal saat ini
// Mengecek Masa IRS

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
  // Cek biaya SPP
    $biayaSPP = "SELECT int_nominal FROM keu_jml_biaya where bol_semester = '" . $sms. "' and str_thn_ajaran = '" . $thnajaran_dash. "' and str_id_nim = '$str_id_nim'";

    $mhsSPP = mysqli_query($conn, $biayaSPP);
    $queryBiayaSPP = mysqli_fetch_object($mhsSPP);
  // Cek biaya SPP

 // Cek penangguhan SPP
    $penangguhanSPP = "SELECT int_penangguhan FROM keu_jml_biaya where bol_semester = '" . $sms. "' and str_thn_ajaran = '" . $thnajaran_dash. "' and str_id_nim = '$str_id_nim'";

    $penSPP = mysqli_query($conn, $penangguhanSPP);
    $queryPenangguhanSPP = mysqli_fetch_object($penSPP);
 // Cek penangguhan SPP

    
    if($queryPenangguhanSPP !== null && $queryBiayaSPP !== null){
        $ErrCekKeuangan = 'ada';
    } else {
        error_reporting(0);
        $ErrCekKeuangan = 'Data Pembayaran tidak ditemukan';
    }

    if ('1' === $queryPenangguhanSPP->int_penangguhan) {
        $ErrPembayaran = 'KRSPembayaran';
    } else {
        if ($bol_semester == 'SP') {
            $ErrPembayaran = 'KRSPembayaran';
        } else {
            if($akumulasi >= ($queryBiayaSPP->int_nominal * 0.5)) {
                $ErrPembayaran = 'KRSPembayaran';
            } else {
                $ErrPembayaran = 'Silahkan Lunasi Pembayaran atau Menghubungi Bagian Keuangan';
            }
        }
    }
  // End Penangguhan & Cek Akumulasi Pembayaran
// End Mengecek Pembayaran

// Mengambil status Mahasiswa Aktif / dll
    $sttsMhs = "SELECT * FROM mhs_mahasiswa WHERE str_id_nim = '$str_id_nim' ";

    $resultSttsMhs = mysqli_query($conn, $sttsMhs);
    $uSttsMhs = mysqli_fetch_object($resultSttsMhs);
    $sttsMahasiswa = $uSttsMhs->status_aktif;

    if($sttsMahasiswa == 'aktif'){
        $ErrStts = 'KRSaktif';
    } else {
        $ErrStts = 'KRS Tidak Bisa Diakses Karena Status Anda Saat Ini adalah'. ' '. $sttsMahasiswa . ' '. "Silahkan Hubungi Bagian BAAK";  
    }
// End Mengambil status Mahasiswa Aktif / dll

// Melakukan Pengecekan IPS dan SKS yang Bisa Diambil
    // Query untuk mengambil ips Mahasiswa
        $khs = "SELECT `a`.`id_mhs_mahasiswa` AS `id_mhs_mahasiswa`,`a`.`str_id_nim` AS `str_id_nim`,`a`.`str_nm_mhs` AS `str_nm_mhs`,`b`.`str_nm_prodi` AS `str_nm_prodi`,`b`.`str_kd_prodi` AS `str_kd_prodi`,`a`.`str_angkatan` AS `str_angkatan`,`b`.`str_thn_ajaran` AS `str_thn_ajaran`,`b`.`bol_semester` AS `bol_semester`,round((sum((`c`.`num_sks` * `c`.`num_bobot`)) / sum(`c`.`num_sks`)),2) AS `ips` from ((`mhs_mahasiswa` `a` join `v_semester_prodi` `b`) join `v_transkrip` `c`) where ((`a`.`str_kd_prodi` = `b`.`str_kd_prodi`) and (`b`.`str_thn_ajaran` = `c`.`str_thn_ajaran`) and (`b`.`bol_semester` = `c`.`bol_semester`) and (`a`.`str_id_nim` = `c`.`nim`) and str_id_nim = '".$str_id_nim."') group by `c`.`bol_semester`,`c`.`str_thn_ajaran`,`c`.`nim` order by `a`.`str_id_nim`,`b`.`str_thn_ajaran`,`b`.`bol_semester`";

        $result = mysqli_query($conn, $khs);
        foreach ($result as $mhs) {
        $mhs['ips'];
        }

        $lastElement = end($mhs);
        $flastElement = (float)$lastElement;
        // var_dump($flastElement);
    // End Query mengambil ips Mahasiswa

    // Mengambil jumlah SKS diprogramkan
        // $aSql = "SELECT COALESCE(
        // (SELECT SUM(z.sks) FROM (
        // SELECT d.num_sks as sks FROM aka_krs a
        // RIGHT JOIN aka_perkuliahan_detail b ON a.int_kd_perkuliahan_d=b.int_kd_perkuliahan_d
        // RIGHT JOIN aka_perkuliahan c ON b.str_kd_perkuliahan = c.str_kd_perkuliahan
        // RIGHT JOIN aka_matakuliah_detail d ON c.str_kd_mk = d.str_kd_mk
        // RIGHT JOIN mhs_mahasiswa e ON a.str_id_nim=e.str_id_nim
        // WHERE a.str_id_nim='" . $str_id_nim . "'
        // AND a.bol_semester = (select bol_semester_krs from pablic_reset) and a.str_thn_ajaran = (select str_thn_ajaran_krs from pablic_reset) and
        // (
        //     e.str_kd_prodi=d.str_kd_prodi
        //     OR d.str_kd_prodi='0004'
        //     OR (e.str_kd_prodi='0001' AND (d.str_kd_prodi='0006' OR d.str_kd_prodi='0007'))
        //     OR (e.str_kd_prodi='0002' AND (d.str_kd_prodi='0005' OR d.str_kd_prodi='0007'))
        //     OR (e.str_kd_prodi='0003' AND (d.str_kd_prodi='0005' OR d.str_kd_prodi='0006'))
        // )
        // AND ((c.bol_semester='Ganjil' AND (d.num_kd_semester=1 OR d.num_kd_semester=3 OR d.num_kd_semester=5 OR d.num_kd_semester=7))
        //     OR (c.bol_semester='Genap' AND (d.num_kd_semester=2 OR d.num_kd_semester=4 OR d.num_kd_semester=6 OR d.num_kd_semester=8)))
        // Group BY a.str_id_nim,  a.str_thn_ajaran, d.str_kd_mk) as z), 0) AS totalSKS";

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
        // var_dump($ttlSKS);

        // $msg = (int)$ttlSKS->totalSKS;
        $msg = (int)$ttlSKS->tot_sks;
        $intSKS = (int)$sks;
        // var_dump($msg);
        // var_dump($intSKS);
        
    // End Mengambil jumlah SKS diprogramkan

        // Menambahkan jumlah sks yang akan diambil dengan sks yg sudah diambil
        $plusSks = $msg + $intSKS;
        // var_dump($plusSks);
        // End Menambahkan jumlah sks yang akan diambil dengan sks yg sudah diambil

    // Melakukan pengecekan ips dan sks yang diambil
        if ($lastElement >= 3.25 && $plusSks <= 24) {
            $ErrIps = 'IPS';
        }else if( 2.75 <= $lastElement && $lastElement < 3.25 && $plusSks <= 21){
            $ErrIps = 'IPS';
        }else if( 2.00 <= $lastElement && $lastElement < 2.75 && $plusSks <= 18){
            $ErrIps = 'IPS';
        }else if($lastElement < 2.00 && $plusSks <= 15){
            $ErrIps = 'IPS';
        }else{
            $ErrIps = 'Maaf, Tidak Bisa Mengambil Mata Kuliah Lagi' ;
        }

        // if($plusSks > 24) {
        //     $ErrIps = 'Maaf, Tidak Bisa Mengambil Mata Kuliah Lagi' ;
        // }else {
        //     if ($flastElement >= 3.25 && $plusSks <= 24) {
        //         $ErrIps = 'IPS';
        //     }else if( 2.75 <= $flastElement && $flastElement < 3.25 && $plusSks <= 21){
        //         $ErrIps = 'IPS';
        //     }else if( 2.00 <= $flastElement && $flastElement < 2.75 && $plusSks <= 18){
        //         $ErrIps = 'IPS';
        //     }else if($flastElement < 2.00 && $plusSks <= 15){
        //         $ErrIps = 'IPS';
        //     }else{
        //         $ErrIps = 'Error' ;
        //     }
        // }
        
    // End Melakukan pengecekan ips dan sks yang diambil
// End Melakukan Pengecekan IPS dan SKS yang Bisa Diambil     

// Cek matakuliah syarat
    $angkatan = substr($str_id_nim, 0, 4);
    if('UM1714' == $kd_mk_p and $angkatan <= '2014') {
       $Err = '1';
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
            $Err5 = 'Mata kuliah tidak dapat di ambil, Syarat harus lulus BSC dan Technopreneur';
        }
    }else {
        $Err5 = '5';
    }
// End Cek Mengambil Makul Project Techno(SP1703) Harus Sudah ikut BSC DONE
     
if($ErrMasa == 'KRS'){
    if($ErrCekKeuangan == 'ada'){
        if($ErrPembayaran == 'KRSPembayaran'){
            if($ErrStts == 'KRSaktif'){
                if($ErrIps == 'IPS'){
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
                                            'status'=>true,
                                            'message'=>$msg
                                        );
                    
                                        // ENCODE menjadi data JSON
                                        echo json_encode($response);
                    
                                        // Akhir add ke tabel aka_krs
                                    // End ADD IRS
                                    }else{
                                        // echo $Err2;
                                        echo json_encode([
                                            'status'=>false,
                                            'message'=> $Err2
                                          ]);
                                    }
                                }else {
                                    // echo $Err5;
                                    echo json_encode([
                                        'status'=>false,
                                        'message'=> $Err5
                                      ]);
                                }
                            }else {
                                // echo $Err4;
                                echo json_encode([
                                    'status'=>false,
                                    'message'=> $Err4
                                  ]);
                            }
                        }else {
                            // echo $Err3;
                            echo json_encode([
                                'status'=>false,
                                'message'=> $Err3
                              ]);
                        }
                    } else {
                        // echo $Err;
                        echo json_encode([
                            'status'=>false,
                            'message'=> $Err
                        ]);
                    }
                }else {
                    echo json_encode([
                        'status'=>false,
                        'message'=> $ErrIps
                    ]);
                }
            }else {
                echo json_encode([
                    'status'=>false,
                    'message'=> $ErrStts
                ]);
            }
        } else {
            echo json_encode([
                'status'=>false,
                'message'=> $ErrPembayaran
            ]);
        }
    }else{
        echo json_encode([
            'status'=>false,
            'message'=> $ErrCekKeuangan
        ]);
    }
} else {
    echo json_encode([
        'status'=>false,
        'message'=> $ErrMasa
    ]);
}

mysqli_close($conn);
?>