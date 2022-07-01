<?php
    require_once("./koneksi.php");

    //Mendapatkan variabel post
    $tahun_ajar = isset($_POST["tahun_ajar"]) ? $_POST["tahun_ajar"] : "";
    $semester = isset($_POST["semester"]) ? $_POST["semester"] : "";
    $tanggal_mulai = isset($_POST["tanggal_mulai"]) ? $_POST["tanggal_mulai"] : "";
    $tanggal_akhir = isset($_POST["tanggal_akhir"]) ? $_POST["tanggal_akhir"] : "";
    $status = isset($_POST["status"]) ? $_POST["status"] : "";

    // Query untuk menambahkan data
    $sql = "INSERT INTO `tahun_ajar` (`tahun_ajar`, `semester`, `tanggal_mulai`, `tanggal_akhir`, `status` )
    VALUES ('".$tahun_ajar."','".$semester."','".$tanggal_mulai."', '".$tanggal_akhir."', '".$status."')";
    

    //Running Query
    $query = mysqli_query($conn, $sql);
    if($query) {
        $msg = "Simpan Data Tahun Ajar Berhasil";
    }else{
        $msg = "Simpan Data Tahun Ajar Gagal";
    }

    // Mengambil respon untuk di encode menjadi JSON
    $response = array(
        'status'=>true,
        'msg'=>$msg
    );

    // ENCODE menjadi data JSON
    echo json_encode($response);

    mysqli_close($conn);
?>