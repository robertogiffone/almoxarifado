<?php
session_start();
require_once '../include/conexao.php';

function cadastrarReserva($db, $id_identificador, $numero_frota, $ordem_manutencao, $id_usuario)
{
	$sql = "INSERT INTO reservas(id_identificador, numero_frota, ordem_manutencao, id_usuario) VALUES (:id_identificador, :numero_frota, :ordem_manutencao, :id_usuario)";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":id_identificador", $id_identificador);
	$sql->bindValue(":numero_frota", $numero_frota);
	$sql->bindValue(":ordem_manutencao", $ordem_manutencao);
	$sql->bindValue(":id_usuario", $id_usuario);
	$sql->execute();
	$id_venda = $db->lastInsertId();

	return $id_venda;
}

function cadastrarItemReserva($db, $id_reserva, $id_produto, $quantidade)
{
	$sql = "INSERT INTO itens_reserva(id_reserva, id_produto, quantidade) VALUES (:id_reserva, :id_produto, :quantidade)";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":id_reserva", $id_reserva);
	$sql->bindValue(":id_produto", $id_produto);
	$sql->bindValue(":quantidade", $quantidade);
	$inseriu = $sql->execute();
	/*if($inseriu)
	{
		editarEstoque($db, $id_produto, $quantidade);
	}*/
	return $inseriu;
}

/*function editarEstoque($db, $id_produto, $quantidade)
{
	$sql = "UPDATE produtos SET quantidade = quantidade-:quantidade WHERE id = :id";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":quantidade", $quantidade);
	$sql->bindValue(":id", $id_produto);
	return $sql->execute();	
}*/

$dados = $_POST['dados'];
$identificador = $dados[0]['identificador'];
$numero_frota = $dados[0]['numero_frota'];            
$ordem_manutencao = $dados[0]['ordem_manutencao'];            
$id_usuario = $_SESSION['lgusuario']['id'];

$id_reserva = cadastrarReserva($db, $identificador, $numero_frota, $ordem_manutencao, $id_usuario);

foreach($dados as $value) 
{
	$id_produto = $value['id'];
	$qtd = $value['qtd'];

	$inseriu = cadastrarItemReserva($db, $id_reserva, $id_produto, $qtd);
}

$retorno = '0';
if($inseriu)
{
	$retorno = '1';
}
echo json_encode($retorno);