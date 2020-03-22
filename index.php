<?php
require_once 'include/conexao.php';

if(isset($_GET['acao']) && $_GET['acao'] == 'deletar')
{
	$id = $_GET['id'];

	$deletou = deletarProduto($db, $id);

	if($deletou)
	{
		header('LOCATION: index.php');
	}
	else
	{
		$_SESSION['msg'] = array('msg'=>'Erro ao deletar', 'tipo'=>'danger');
	}

}

$produtos = listaProduto($db);

function listaProduto($db)
{
	$array = array();

	$sql = "SELECT 
		p.id, p.nome_produto, p.quantidade, p.codigo, p.url_foto, c.categoria
	FROM 
		produtos p
		INNER JOIN categorias c ON p.id_categoria = c.id
	WHERE
		p.ativo = 1";

	$sql = $db->prepare($sql);
	$sql->execute();

	if($sql->rowCount() > 0)
	{
		$array = $sql->fetchAll(PDO::FETCH_ASSOC);
	}
	return $array;
}

function deletarProduto($db, $id)
{
	$sql = "UPDATE produtos SET ativo = 0 WHERE id = :id";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":id", $id);
	
	return $sql->execute();
}

require_once 'include/topo.php';
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

			<div class="col-sm-8">

				<h2> Produtos </h2>

			</div>
			<?php
			if($id_perfil_logado == 4 || $id_perfil_logado == 1)
			{
			?>
			<div class="col-sm-4" style="text-align: right;">

				<a class="btn btn-primary" href="produto.php"> 
					<i class="glyphicon glyphicon-plus"> </i>  NOVO PRODUTO 
				</a>

			</div>
			<?php
			}
			?>
			<div class="col-sm-12">

				<form class="form-horizontal" method="POST">
					
					<div class="form-group">
						<label for="arquivo_insumo" class="col-sm-2 control-label">Nome do produto</label>
						<div class="col-sm-10">
							<input type="text" id="nome_produto" name="nome_produto" class="form-control"
							placeholder="Informe parte do nome do produto">
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-offset-4 col-sm-8">
							<button type="submit" class="btn btn-primary">Pesquisar</button>
						</div>
					</div>
				</form>

			</div>

			<div class="col-sm-12">

				<div class="table-responsive">

					<table id="tabela_insumos" class="table table-striped table-bordered datatable">
						
						<thead> 
							<tr>
								<th> Foto </th>
								<th> Nome do produto </th>
								<th> Código </th>
								<th> Categoria </th>
								<th> Quantidade </th>
								<?php
								if($id_perfil_logado == 4 || $id_perfil_logado == 1)
                				{
								?>
								<th> Ações </th>
								<?php
								}
								?>
							</tr>
						</thead>

						<tbody>
						<?php
						foreach($produtos as $produto) 
						{
						?>
							<tr>
								<td> <img src="assets/img/produtos/<?php echo $produto['url_foto']; ?>" width="40" height="40"> </td>
								<td> <?php echo $produto['nome_produto']; ?> </td>
								<td> <?php echo $produto['codigo']; ?> </td>
								<td> <?php echo $produto['categoria']; ?> </td>
								<td> <?php echo $produto['quantidade']; ?> </td>
								<?php
								if($id_perfil_logado == 4 || $id_perfil_logado == 1)
                				{
								?>
								<td>
									<a title = 'Editar' class="glyphicon glyphicon-edit pointer" style="color: #00F;"
									href="editar_produto.php?id=<?php echo $produto['id']; ?>" > </a>
									
									<a title = 'Deletar' class="glyphicon glyphicon-trash pointer" style="color: #F00;"
									href="index.php?acao=deletar&id=<?php echo $produto['id']; ?>" > </a>							
								</td>
								<?php
								}
								?>
							</tr>
						<?php
						}
						?>	
						</tbody>
					</table>
				</div>

			</div>

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