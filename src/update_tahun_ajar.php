<?php
//Include koneksi ke db
require_once("./koneksi.php");

//Menangkap variabel paramete get
$id_tahun_ajar = $_GET['id_tahun_ajar'];

// Field yang mau di update
$tahun_ajar = $_POST['tahun_ajar'];
$semester = $_POST['semester'];
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_akhir = $_POST['tanggal_akhir'];
$status = $_POST['status'];

// Query untuk update tabel set_jadwal
$sql = "UPDATE `tahun_ajar` SET `tahun_ajar` = '".$tahun_ajar."',`semester` = '".$semester."',`tanggal_mulai` = '".$tanggal_mulai."',`tanggal_akhir` = '".$tanggal_akhir."',`status` = '".$status."'
WHERE `tahun_ajar`.`id_tahun_ajar` = ".$id_tahun_ajar.";";

$query = mysqli_query($conn, $sql);
if($query){
    $msg = "Update data berhasil";
}else{
    $msg = "Update data gagal";
}

$response = array(
    'status'=>'OK',
    'msg'=>$msg
);

echo json_encode($response);
?>