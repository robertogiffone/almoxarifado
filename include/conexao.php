<?php
//echo 'Teste'; exit;
try
{
$db = new PDO('mysql:dbname=almoxarifado;host=localhost;charset=utf8', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(Exception $e)
{
    echo $e->getMessage();
}
?>