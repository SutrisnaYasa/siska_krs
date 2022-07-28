<?php
    $servername = "localhost";
    $database = "siska";
    $username = "root";
    $password = "";

    // $servername = "103.80.88.77";
    // $database = "krs_siska";
    // $username = "irs_tester";
    // $password = "EPTOtridzkVlXyJwzMBIoSRBx";

    $conn = mysqli_connect($servername, $username, $password, $database);
    if(!$conn){
        die("Koneksi Tidak Berhasil" . mysqli_connect_error());
    }
    //echo "Koneksi berhasil";
?>