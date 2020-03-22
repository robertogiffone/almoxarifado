<?php
session_start();
unset($_SESSION['lgusuario']);
header("LOCATION: login.php");