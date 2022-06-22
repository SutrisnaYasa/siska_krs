<?php
    require_once("./koneksi.php");

    //Mendapatkan variabel post
    $id_tahun_ajar = isset($_POST["id_tahun_ajar"]) ? $_POST["id_tahun_ajar"] : "";
    $keterangan = isset($_POST["keterangan"]) ? $_POST["keterangan"] : "";
    $tanggal_mulai = isset($_POST["tanggal_mulai"]) ? $_POST["tanggal_mulai"] : "";
    $tanggal_tutup = isset($_POST["tanggal_tutup"]) ? $_POST["tanggal_tutup"] : "";

    // Query untuk menambahkan data
    $sql = "INSERT INTO `kalender` (`id_tahun_ajar`, `keterangan`, `tanggal_mulai`, `tanggal_tutup` )
    VALUES ('".$id_tahun_ajar."','".$keterangan."','".$tanggal_mulai."', '".$tanggal_tutup."')";
    

    //Running Query
    $query = mysqli_query($conn, $sql);
    if($query) {
        $msg = "Simpan Data Kalender Berhasil";
    }else{
        $msg = "Simpan Data Kalender Gagal";
    }

    // Mengambil respon untuk di encode menjadi JSON
    $response = array(
        'status'=>'OK',
        'msg'=>$msg
    );

    // ENCODE menjadi data JSON
    echo json_encode($response);

    mysqli_close($conn);
?>