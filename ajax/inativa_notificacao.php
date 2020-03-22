<?php
session_start();
require_once '../include/conexao.php';

function inativar($db, $id)
{
	$sql = "UPDATE notificacoes SET ativo = 0 WHERE id = :id";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":id", $id);
	return $sql->execute();	
}

$id = $_POST['id'];            

$inativou = inativar($db, $id);

$retorno = '0';
if($inativou)
{
	$retorno = '1';
}
echo json_encode($retorno);