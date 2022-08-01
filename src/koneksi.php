<?php
    $servername = "localhost";
    $database = "siska";
    $username = "root";
    $password = "";

    // $servername = "103.80.88.77";
    // $database = "krs_siska";
    // $username = "irs_tester";
    // $password = "EPTOtridzkVlXyJwzMBIoSRBx";

    // $servername = "27.112.79.162";
    // $database = "siska";
    // $username = "bagus";
    // $password = "Asd@1234";

    $conn = mysqli_connect($servername, $username, $password, $database);
    if(!$conn){
        die("Koneksi Tidak Berhasil" . mysqli_connect_error());
    }
    //echo "Koneksi berhasil";
?>