<?php
// logout.php
session_start();
session_unset();
session_destroy();

// Alihkan kembali ke halaman login setelah berhasil keluar
header("Location: login.php");
exit();
?>