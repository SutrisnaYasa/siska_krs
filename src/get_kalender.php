<?php
   require_once("./koneksi.php");

if(isset($_GET['id_kalender'])) {
    $id_kalender=$_GET['id_kalender'];
    $query="SELECT * FROM kalender where id_kalender='$id_kalender'";
    $result=mysqli_query($conn, $query);
    $row=mysqli_fetch_assoc($result);

    $data=array(
        'id_kalender'=>$row['id_kalender'],
        'id_tahun_ajar'=>$row['id_tahun_ajar'],
        'keterangan'=>$row['keterangan'],
        'tanggal_mulai'=>$row['tanggal_mulai'],
        'tanggal_tutup'=>$row['tanggal_tutup']
    );

    $hasil[]=$data;

    echo json_encode($hasil);
} else {
    $query="SELECT * FROM kalender";
    $result=mysqli_query($conn, $query);
    $row=mysqli_fetch_assoc($result);

    do {
        $hasil[]=$row;
    }while($row=mysqli_fetch_assoc($result));

    echo json_encode($hasil);
}
?>