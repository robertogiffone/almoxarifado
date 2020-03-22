<?php
session_start();
require_once '../include/conexao.php';

function listaProdutos($db, $id_categoria)
{
	$array = array();
	
	$sql = "SELECT 
		p.id, p.nome_produto, p.quantidade, p.codigo, p.url_foto, c.categoria
	FROM 
		produtos p
		INNER JOIN categorias c ON p.id_categoria = c.id
	WHERE
		p.ativo = 1 AND p.id_categoria = $id_categoria
	ORDER BY
		c.categoria, p.nome_produto";

	$sql = $db->prepare($sql);
	$sql->execute();

	if($sql->rowCount() > 0)
	{
		$array = $sql->fetchAll(PDO::FETCH_ASSOC);
	}
	return $array;
}

$id_categoria = $_POST['id_categoria'];

$produtos = listaProdutos($db, $id_categoria);

/*
foreach($dados as $value) 
{
	$id_produto = $value['id'];
	$qtd = $value['qtd'];

	$inseriu = cadastrarItemVenda($db, $id_venda, $id_produto, $qtd);
}

$retorno = '0';
if($inseriu)
{
	$retorno = '1';
}
*/

echo json_encode($produtos);