<?php
//Include koneksi ke db
require_once("./koneksi.php");

//Menangkap variabel paramete get
$id_kalender = $_GET['id_kalender'];

// Field yang mau di update
$id_tahun_ajar = $_POST['id_tahun_ajar'];
$keterangan = $_POST['keterangan'];
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_tutup = $_POST['tanggal_tutup'];

// Query untuk update tabel set_jadwal
$sql = "UPDATE `kalender` SET `id_tahun_ajar` = '".$id_tahun_ajar."',`keterangan` = '".$keterangan."',`tanggal_mulai` = '".$tanggal_mulai."',`tanggal_tutup` = '".$tanggal_tutup."'
WHERE `kalender`.`id_kalender` = ".$id_kalender.";";

$query = mysqli_query($conn, $sql);
if($query){
    $msg = "Update data berhasil";
}else{
    $msg = "Update data gagal";
}

$response = array(
    'status'=>true,
    'msg'=>$msg
);

echo json_encode($response);
?>