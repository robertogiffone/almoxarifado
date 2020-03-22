<?php
session_start();
require_once '../include/conexao.php';

function cadastrarVenda($db, $numero_frota, $ordem_manutencao, $id_usuario, $solicitado_por, $codigo)
{
	$sql = "INSERT INTO venda(numero_frota, ordem_manutencao, id_usuario, solicitado_por, codigo) 
	VALUES (:numero_frota, :ordem_manutencao, :id_usuario, :solicitado_por, :codigo)";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":numero_frota", $numero_frota);
	$sql->bindValue(":ordem_manutencao", $ordem_manutencao);
	$sql->bindValue(":id_usuario", $id_usuario);
	$sql->bindValue(":solicitado_por", $solicitado_por);
	$sql->bindValue(":codigo", $codigo);
	$sql->execute();
	$id_venda = $db->lastInsertId();

	return $id_venda;
}

function cadastrarItemVenda($db, $id_venda, $id_produto, $quantidade)
{
	$sql = "INSERT INTO itens_venda(id_venda, id_produto, quantidade) VALUES (:id_venda, :id_produto, :quantidade)";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":id_venda", $id_venda);
	$sql->bindValue(":id_produto", $id_produto);
	$sql->bindValue(":quantidade", $quantidade);
	$inseriu = $sql->execute();
	if($inseriu)
	{
		editarEstoque($db, $id_produto, $quantidade);
	}
	return $inseriu;
}

function editarEstoque($db, $id_produto, $quantidade)
{
	$sql = "UPDATE produtos SET quantidade = quantidade-:quantidade WHERE id = :id";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":quantidade", $quantidade);
	$sql->bindValue(":id", $id_produto);
	return $sql->execute();	
}

function notificar($db, $id_venda)
{
	$sql = "INSERT INTO notificacoes(id_venda, id_perfil, mensagem) 
	VALUES (:id_venda, :id_perfil, :mensagem)";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":id_venda", $id_venda);
	$sql->bindValue(":id_perfil", 3);
	$sql->bindValue(":mensagem", 'Nova reserva disponível');
	return $sql->execute();
}

$dados = $_POST['dados'];
$numero_frota = $dados[0]['numero_frota'];
$ordem_manutencao = $dados[0]['ordem_manutencao'];
$solicitado_por = $dados[0]['solicitado_por'];
$codigo = $dados[0]['codigo'];        
$id_usuario = $_SESSION['lgusuario']['id'];

$id_venda = cadastrarVenda($db, $numero_frota, $ordem_manutencao, $id_usuario, $solicitado_por, $codigo);

foreach($dados as $value) 
{
	$id_produto = $value['id'];
	$qtd = $value['qtd'];

	$inseriu = cadastrarItemVenda($db, $id_venda, $id_produto, $qtd);
}

$retorno = '0';
if($inseriu)
{
	notificar($db, $id_venda);
	$retorno = '1';
}
echo json_encode($retorno);