<?php
session_start();
require_once '../include/conexao.php';

function aprovar($db, $id, $autorizado_por)
{
	$id_usuario = $_SESSION['lgusuario']['id'];
	$sql = "UPDATE venda 
	SET id_usuario_aprovacao = :id_usuario, data_aprovacao = NOW(), status = 2, autorizado_por = :autorizado_por 
	WHERE id = :id";
	$sql = $db->prepare($sql);
	$sql->bindValue(":id_usuario", $id_usuario);
	$sql->bindValue(":autorizado_por", $autorizado_por);
	$sql->bindValue(":id", $id);
	return $sql->execute();
}

function notificar($db, $id_venda)
{
	$sql = "UPDATE notificacoes SET ativo = 0 WHERE id_venda = :id";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":id", $id_venda);
	$sql->execute();

	$sql = "INSERT INTO notificacoes(id_venda, id_perfil, mensagem) 
	VALUES (:id_venda, :id_perfil, :mensagem)";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":id_venda", $id_venda);
	$sql->bindValue(":id_perfil", 4);
	$sql->bindValue(":mensagem", 'Novas peças disponíveis para liberação');
	return $sql->execute();
}

$id_venda = $_POST['id_venda'];
$autorizado_por = $_POST['autorizado_por'];

$aprovou = aprovar($db, $id_venda, $autorizado_por);

$retorno = '0';
if($aprovou)
{
	notificar($db, $id_venda);
	$retorno = '1';
}
echo json_encode($retorno);