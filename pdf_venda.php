<?php
session_start();
require_once 'include/conexao.php';
require_once 'vendor/autoload.php';


/*
v.id, v.numero_frota,v.ordem_manutencao,v.id_usuario,v.status,v.data_cadastro,v.id_usuario_aprovacao,v.data_aprovacao,v.id_usuario_liberacao,v.data_liberacao,v.solicitado_por,v.autorizado_por,v.codigo,u3.usuario AS usr_lib
*/

function listaVenda($db, $id)
{
	$array = array();

	$sql = "SELECT 
				v.id, v.numero_frota,v.ordem_manutencao,v.id_usuario,v.status,v.data_cadastro,v.id_usuario_aprovacao,v.data_aprovacao,v.id_usuario_liberacao,v.data_liberacao,v.solicitado_por,v.autorizado_por,v.codigo,u3.usuario AS usr_lib
			FROM 
				venda v
				LEFT JOIN usuarios u3 ON v.id_usuario_liberacao = u3.id
			WHERE
				v.id = :id";

	$sql = $db->prepare($sql);
	$sql->bindValue(":id", $id);
	$sql->execute();

	if($sql->rowCount() > 0)
	{
		$array = $sql->fetchAll(PDO::FETCH_ASSOC);
	}
	return $array;
}

function listaItensVendas($db, $id_venda)
{
	$array = array();

	$sql = "SELECT 
				p.nome_produto, p.codigo, iv.quantidade
			FROM 
				itens_venda iv 
				INNER JOIN produtos p ON iv.id_produto = p.id
			WHERE
				iv.id_venda = :id";

	$sql = $db->prepare($sql);
	$sql->bindValue(":id", $id_venda);
	$sql->execute();

	if($sql->rowCount() > 0)
	{
		$array = $sql->fetchAll(PDO::FETCH_ASSOC);
	}
	return $array;
}

$id = $_GET['id']; //Vai ser o id da venda

//Montar dados
$venda = listaVenda($db, $id); //var_dump($venda); exit;
$codigo = $venda[0]['codigo'];
$numero_frota = $venda[0]['numero_frota'];
$numero_om = $venda[0]['ordem_manutencao'];
if(empty($numero_om))
{
	$numero_om = 'OM NÃO ABERTA';
}
$nome_manutencao = $venda[0]['solicitado_por']; //Nome do mecanico, lanterneiro, etc
$nome_solicitando = $_SESSION['lgusuario']['nome'];
$matricula_solicitando = $_SESSION['lgusuario']['matricula'];
$nome_controlador = $venda[0]['autorizado_por']; //Nome do controlador de manutenção
$nome_almoxarife = $venda[0]['usr_lib']; //Nome do almoxarife
$itensVenda = listaItensVendas($db, $id);

/*
var_dump($venda); exit;

foreach($itensVenda as $itemVenda)
{
	$nome_produto = $itemVenda['nome_produto'];
	$qtd = $itemVenda['quantidade'];
	
	//echo $nome_produto.' - '.$qtd.'<br/>';
}
*/

$html = "<h4>$codigo</h4> <h4>Número de Frota: $numero_frota</h4> <h4>Número da OM: $numero_om</h4>";
$html .= "<table>";
$html .= "<thead> <tr> <th> Produto </th> <th> Código do Produto </th> <th> Quantidade </th> </tr> </thead>";
$html .= "</tbody>";
foreach($itensVenda as $itemVenda)
{
	$nome_produto = $itemVenda['nome_produto'];
	$codigo_produto = $itemVenda['codigo'];
	$qtd = $itemVenda['quantidade'];
	
	$html .= "<tr>";
	$html .= "<td> $nome_produto </td>";
	$html .= "<td> $codigo_produto </td>";
	$html .= "<td> $qtd </td>";
	$html .= "</tr>";
}
$html .= "</tbody>";
$html .= "</table>";
$html .= "<p>USUÁRIO: $nome_solicitando. MATRICULA: $matricula_solicitando</p>";
$html .= "<p>SOLICITANDO: $nome_manutencao</p>";
$html .= "<p>ATENDIDO: $nome_almoxarife</p>";
$html .= "<p>RESERVADO: $nome_controlador</p>";
$html .= "<p>AUTORIZADO: $nome_controlador</p>";

//ATENDIDO:Nome do almoxarife| RESERVADO: Nome do Controlador| AUTORIZADO POR: Nome do controlador|

//exit;
$mpdf = new \Mpdf\Mpdf();
$mpdf->SetDisplayMode('fullpage');
$css = file_get_contents("assets/css/pdf_venda.css");
$mpdf->WriteHTML($css,1);
$mpdf->WriteHTML($html);

$mpdf->allow_charset_conversion=true;
$mpdf->charset_in='UTF-8';
//$mpdf->charset_in='iso-8859-1';

$nome_arquivo = 'pecas'.$id.'.pdf';

$mpdf->Output($nome_arquivo, \Mpdf\Output\Destination::INLINE);
//$mpdf->Output($nome_arquivo, 'D');
?>