<?php
$servername = "localhost";
$username = "admin";
$password = "admin";
$dbname = "expense";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}