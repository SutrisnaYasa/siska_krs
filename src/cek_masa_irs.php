<?php
    require_once("./koneksi.php");

// Mengecek Masa KRS
// Query untuk mengambil data dari tabel tahun ajar
    $sql = "SELECT * FROM tahun_ajar where status ='1'";
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_array($query);
// Query untuk mengambil data dari tabel tahun ajar

// Cek tanggal mulai, tanggal akhir, dan tanggal saat ini
    $today=date ("Y-m-d");
    $tanggal_mulai = strtotime($data["tanggal_mulai"]);
    $tgl_today = strtotime($today);
    $tanggal_akhir = strtotime($data["tanggal_akhir"]);
    $cek_stts = $data["status"];
    if ($tgl_today < $tanggal_mulai){
        echo json_encode([
            'status'=>false,
            'message'=>'KRS belum dibuka'
        ]);
    }else if ($tgl_today > $tanggal_akhir){
        echo json_encode([
            'status'=>false,
            'message'=>'KRS telah ditutup'
        ]);
    }else if($tgl_today >= $tanggal_mulai && $tgl_today <= $tanggal_akhir && $cek_stts === '1'){
        echo json_encode([
            'status'=>true,
            'message'=>'KRS dibuka'
        ]);
    } else {
        echo json_encode([
        'status'=>false,
        'message'=>'Tanggal tidak ditemukan'
        ]);
    }
// Cek tanggal mulai, tanggal akhir, dan tanggal saat ini
// Mengecek Masa KRS
    mysqli_close($conn);
?>