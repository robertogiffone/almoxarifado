<?php
if(!isset($_GET['acao']) || $_GET['acao'] != 'deletar')
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
		header('LOCATION: lista_venda.php');
	}
	else
	{
		$_SESSION['msg'] = array('msg'=>'Erro ao deletar', 'tipo'=>'danger');
	}

}

$vendas = listaVendas($db, $id_perfil_logado);

function listaVendas($db, $id_perfil_logado)
{
	$array = array();

	$sql = "SELECT 
				v.id, v.codigo, v.numero_frota, v.ordem_manutencao, v.solicitado_por, GROUP_CONCAT(p.nome_produto) nome_produto, GROUP_CONCAT(iv.quantidade) quantidade, GROUP_CONCAT(IF(p.codigo IS NULL, '', p.codigo)) codigo_produto
			FROM 
				venda v
				INNER JOIN usuarios u ON v.id_usuario = u.id
				INNER JOIN itens_venda iv ON v.id = iv.id_venda 
				INNER JOIN produtos p ON iv.id_produto = p.id
			WHERE v.status = 1";

	$sql .= " GROUP BY
				v.id, v.codigo, v.numero_frota, v.ordem_manutencao, v.solicitado_por";

	$sql = $db->prepare($sql);
	$sql->execute();

	if($sql->rowCount() > 0)
	{
		$array = $sql->fetchAll(PDO::FETCH_ASSOC);
	}
	return $array;
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

			<?php
			if($id_perfil_logado == 2)
			{
			?>
			<h2> Requisições </h2>
			<?php
			}
			else if($id_perfil_logado == 3)
			{
			?>
			<h2> Aprovação de Requisição </h2>
			<?php
			}
			?>

			<table id="tabela_vendas" class="table table-striped table-bordered datatable">
				
				<thead> 
					<tr>
						<th> Código </th>
						<th> Aplicação Nº de ordem veículo </th>
						<th> Número da ordem de manutenção </th>
						<th> Solicitado por </th>
						<th> Produtos </th>
					<?php
					if($id_perfil_logado == 3)
					{
					?>
						<th> Reservado/Autorizado por </th>
					<?php
					}
					?>
						<th> Ações </th>
					</tr>
				</thead>
				
				<tbody>
				<?php
				foreach($vendas as $venda) 
				{
					$nome_produto = $venda['nome_produto'];
					$quantidade = $venda['quantidade'];
					$codigo_produto = $venda['codigo_produto'];

					$nome_produto = explode(',', $nome_produto);
					$quantidade = explode(',', $quantidade);
					$codigo_produto = explode(',', $codigo_produto);
					$qtdProdutos = count($nome_produto);
				?>
					<tr>
						<td> <?php echo $venda['codigo']; ?> </td>
						<td> <?php echo $venda['numero_frota']; ?> </td>
						<td> <?php echo $venda['ordem_manutencao']; ?> </td>
						<td> <?php echo $venda['solicitado_por']; ?> </td>
						<td>
						<?php
						for($i=0; $i < $qtdProdutos; $i++)
						{
							$valor = '';
							if(empty($codigo_produto[$i]))
							{
								$valor = $nome_produto[$i].' - '.$quantidade[$i];	
							}
							else
							{
								$valor = $nome_produto[$i].' - '.$codigo_produto[$i].' - '.$quantidade[$i];
							}
						?>
							<p> <?php echo $valor; ?> </p>
						<?php
						}
						?>
						</td>
					<?php
					if($id_perfil_logado == 3)
					{
					?>
						<td>
							<input type="text" id="autorizado_por<?php echo $venda['id']; ?>">
						</td>
					<?php
					}
					?>
						<td>
						<?php
						if($id_perfil_logado == 3)
						{
						?>
							<i title = 'Aprovar' onclick="aprovar(<?php echo $venda['id']; ?>)" class="glyphicon glyphicon-ok pointer" style="color: #0F0;"> </i>
						<?php
						}
						?>
							<a href="lista_venda.php?acao=deletar&id=<?php echo $venda['id']; ?>">		
								<i title = 'Deletar' class="glyphicon glyphicon-trash pointer" style="color: #F00;" > </i>
							</a>	
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

		function aprovar(id_venda)
		{
			var autorizado_por = $('#autorizado_por'+id_venda).val();

			if(autorizado_por == '')
			{
				alert('Reservado/Autorizado por é obrigatório');
			}
			else
			{
				aprovarReserva(id_venda, autorizado_por);
			}

		}

		function aprovarReserva(id_venda, autorizado_por)
		{
			$.ajax(
			{
				url: 'ajax/aprovar_venda.php'
				,type: 'POST'	
				,dataType: 'json'
		        ,data: 
		        {
		        	id_venda: id_venda
		        	,autorizado_por: autorizado_por
		        }
				,success: function(result){
		        	if(result == '1')
		        	{
		        		alert('Aprovado com sucesso');
		        		window.location.reload();
		        	}
		        	else
		        	{
		        		alert('Erro na tentativa de aprovar');
		        	}
		    	}
		    	,error: function(result, status, error){
		        	alert(error);
		    	}
			});
		}

	</script>

<?php
require_once 'include/rodape.php';
?>