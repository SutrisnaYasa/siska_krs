<?php
    $servername = "localhost";
    $database = "siska";
    $username = "root";
    $password = "";

    $conn = mysqli_connect($servername, $username, $password, $database);
    if(!$conn){
        die("Koneksi Tidak Berhasil" . mysqli_connect_error());
    }
    // echo "Koneksi berhasil";
?>