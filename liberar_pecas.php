<?php
if(!isset($_GET['acao']))
{
	require_once 'include/topo.php';
}
require_once 'include/conexao.php';

if(isset($_GET['acao']) && $_GET['acao'] == 'deletar')
{
	$id = $_GET['id'];

	$deletou = deletarVenda($db, $id);

	if($deletou)
	{
		header('LOCATION: liberar_pecas.php');
	}
	else
	{
		$_SESSION['msg'] = array('msg'=>'Erro ao deletar', 'tipo'=>'danger');
	}

}

if(isset($_GET['acao']) && $_GET['acao'] == 'liberar')
{
	$id = $_GET['id'];

	$liberou = liberarPeca($db, $id);

	if($liberou)
	{
		header('LOCATION: liberar_pecas.php');
	}
	else
	{
		$_SESSION['msg'] = array('msg'=>'Erro ao aprovar', 'tipo'=>'danger');
	}

}

$vendas = listaVendas($db, $id_perfil_logado);

function listaVendas($db, $id_perfil_logado)
{
	$array = array();

	$sql = "SELECT 
				v.id, v.codigo, v.numero_frota, v.ordem_manutencao, v.solicitado_por, v.autorizado_por, u3.usuario AS usr_lib, v.status, GROUP_CONCAT(p.nome_produto) nome_produto, GROUP_CONCAT(iv.quantidade) quantidade
			FROM 
				venda v
				INNER JOIN usuarios u ON v.id_usuario = u.id
				INNER JOIN usuarios u2 ON v.id_usuario_aprovacao = u2.id
				INNER JOIN itens_venda iv ON v.id = iv.id_venda 
				INNER JOIN produtos p ON iv.id_produto = p.id
				LEFT JOIN usuarios u3 ON v.id_usuario_liberacao = u3.id
			WHERE v.status IN (2,3)";

	$sql .= " GROUP BY
				v.id, v.codigo, v.numero_frota, v.ordem_manutencao, v.solicitado_por, v.autorizado_por, v.status, u3.usuario";

	$sql = $db->prepare($sql);
	$sql->execute();

	if($sql->rowCount() > 0)
	{
		$array = $sql->fetchAll(PDO::FETCH_ASSOC);
	}
	return $array;
}

function liberarPeca($db, $id)
{
	$sql = "UPDATE notificacoes SET ativo = 0 WHERE id_venda = :id";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":id", $id);
	$sql->execute();

	$id_usuario = $_SESSION['lgusuario']['id'];
	$sql = "UPDATE venda SET id_usuario_liberacao = :id_usuario, data_liberacao = NOW(), status = 3 WHERE id = :id";
	$sql = $db->prepare($sql);
	$sql->bindValue(":id_usuario", $id_usuario);
	$sql->bindValue(":id", $id);
	return $sql->execute();
}

function deletarVenda($db, $id)
{
	$itensVenda = listaItensVenda($db, $id);

	foreach($itensVenda as $itemVenda) 
	{
		$id_produto = $itemVenda['id_produto'];
		$quantidade = $itemVenda['quantidade'];
		editarEstoque($db, $id_produto, $quantidade);
	}

	deletarItensVenda($db, $id);

	$sql = "DELETE FROM venda WHERE id = :id";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":id", $id);
	
	return $sql->execute();
}

function listaItensVenda($db, $id_venda)
{
	$array = array();

	$sql = "SELECT 
				id_produto, quantidade
			FROM 
				itens_venda WHERE id_venda = :id";

	$sql = $db->prepare($sql);
	$sql->bindValue(":id", $id_venda);
	$sql->execute();

	if($sql->rowCount() > 0)
	{
		$array = $sql->fetchAll(PDO::FETCH_ASSOC);
	}
	return $array;
}

function editarEstoque($db, $id_produto, $quantidade)
{
	$sql = "UPDATE produtos SET quantidade = quantidade+:quantidade WHERE id = :id";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":quantidade", $quantidade);
	$sql->bindValue(":id", $id_produto);
	return $sql->execute();	
}

function deletarItensVenda($db, $id_venda)
{
	$sql = "DELETE FROM itens_venda WHERE id_venda = :id";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":id", $id_venda);
	
	return $sql->execute();
}
?>
	
	<div class="container">
		
		<div class="col-sm-12 well">

			<?php
			if(!empty($_SESSION['msg']))
			{
				$tipo = $_SESSION['msg']['tipo'];
				echo "<div class='alert alert-$tipo' role='alert'>";
				echo $_SESSION['msg']['msg'];
				echo '</div>';
				unset($_SESSION['msg']);
			}
			?>

			<h2> Liberar peças </h2>

			<table id="tabela_vendas" class="table table-striped table-bordered datatable">
				
				<thead> 
					<tr>
						<th> Código </th>
						<th> Aplicação Nº de ordem veículo </th>
						<th> Número da ordem de manutenção </th>
						<th> Solicitado por </th>
						<th> Reservado/Autorizado por </th>
						<th> Liberado por </th>
						<th> Produtos </th>
						<th> Ações </th>
					</tr>
				</thead>
				
				<tbody>
				<?php
				foreach($vendas as $venda) 
				{
					$nome_produto = $venda['nome_produto'];
					$quantidade = $venda['quantidade'];

					$nome_produto = explode(',', $nome_produto);
					$quantidade = explode(',', $quantidade);
					$qtdProdutos = count($nome_produto);
					$status = $venda['status'];
				?>
					<tr>
						<td> <?php echo $venda['codigo']; ?> </td>
						<td> <?php echo $venda['numero_frota']; ?> </td>
						<td> <?php echo $venda['ordem_manutencao']; ?> </td>
						<td> <?php echo $venda['solicitado_por']; ?> </td>
						<td> <?php echo $venda['autorizado_por']; ?> </td>
						<td> <?php echo $venda['usr_lib']; ?> </td>
						<td>
						<?php
						for($i=0; $i < $qtdProdutos; $i++)
						{
						?>
							<p> <?php echo $nome_produto[$i].' - '.$quantidade[$i]; ?> </p>
						<?php
						}
						?>
						</td>
						<td>
							<?php
							if($status == 2)
							{
							?>
							<a title = 'Liberar peças' class="glyphicon glyphicon-ok pointer" style="color: #0F0;" href="liberar_pecas.php?acao=liberar&id=<?php echo $venda['id']; ?>"> </a>
							<a title = 'PDF' target="_blank" class="glyphicon glyphicon-file pointer" style="color: #F00;" href="pdf_venda.php?acao=liberar&id=<?php echo $venda['id']; ?>"> </a>
							<a title = 'Deletar' class="glyphicon glyphicon-trash pointer" style="color: #F00;" href="liberar_pecas.php?acao=deletar&id=<?php echo $venda['id']; ?>"> </a>							
							<?php
							}
							else if($status == 3)
							{
							?>
							Liberada
							<a title = 'PDF' target="_blank" class="glyphicon glyphicon-file pointer" style="color: #F00;" href="pdf_venda.php?acao=liberar&id=<?php echo $venda['id']; ?>"> </a>
							<?php
							}
							?>
						</td>
					</tr>
				<?php
				}
				?>	
				</tbody>
				
			</table>
			
		</div>
		
	</div>
	
	<script>

		$(document).ready(function() {
	    	$('.datatable').DataTable();
		} );

	</script>

<?php
require_once 'include/rodape.php';
?>