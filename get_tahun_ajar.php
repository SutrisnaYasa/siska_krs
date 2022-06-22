<?php
   require_once("./koneksi.php");

if(isset($_GET['id_tahun_ajar'])) {
    $id_tahun_ajar=$_GET['id_tahun_ajar'];
    $query="SELECT * FROM tahun_ajar where id_tahun_ajar='$id_tahun_ajar'";
    $result=mysqli_query($conn, $query);
    $row=mysqli_fetch_assoc($result);

    $data=array(
        'id_tahun_ajar'=>$row['id_tahun_ajar'],
        'tahun_ajar'=>$row['tahun_ajar'],
        'semester'=>$row['semester'],
        'tanggal_mulai'=>$row['tanggal_mulai'],
        'tanggal_akhir'=>$row['tanggal_akhir'],
        'status'=>$row['status']
    );

    $hasil[]=$data;

    echo json_encode($hasil);
} else {
    $query="SELECT * FROM tahun_ajar";
    $result=mysqli_query($conn, $query);
    $row=mysqli_fetch_assoc($result);

    do {
        $hasil[]=$row;
    }while($row=mysqli_fetch_assoc($result));

    echo json_encode($hasil);
}
?>