<?php
require_once 'include/conexao.php';

$id = $_GET['id']; 
$produto = listaProduto($db, $id);

if( isset($_POST['nome_produto']) && !empty($_POST['nome_produto'])  )
{
	$nome_produto = addslashes($_POST['nome_produto']);
	$quantidade = $_POST['quantidade'];
	$codigo = $_POST['codigo'];
	$id_categoria = $_POST['categoria'];
	
	$url_foto = '';

	if($_FILES['foto']['size'] > 0)
	{
		$foto = $_FILES['foto'];

		$ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
		$md5name = md5(time().rand(0,9999));

		if($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg' || $ext == 'gif')
	    {
			move_uploaded_file($foto['tmp_name'], "assets/img/produtos/".$md5name.'.'.$ext);
			$url_foto = $md5name.'.'.$ext;
		}
	}

	if(editarProduto($db, $id, $nome_produto, $quantidade, $codigo, $id_categoria, $url_foto))
	{
		//$_SESSION['msg'] = array('msg'=>'Produto editado com sucesso', 'tipo'=>'success');
		header('LOCATION: index.php');
	}   
	else
	{
		$_SESSION['msg'] = array('msg'=>'Erro ao editar', 'tipo'=>'danger');
	}

}

$categorias = listaCategoria($db);

function listaProduto($db, $id)
{
	$array = array();

	$sql = "SELECT 
		id, nome_produto, quantidade, codigo, url_foto, id_categoria
	FROM 
		produtos 
	WHERE
		id = :id";

	$sql = $db->prepare($sql);
	$sql->bindValue(":id", $id);
	$sql->execute();

	if($sql->rowCount() > 0)
	{
		$array = $sql->fetchAll(PDO::FETCH_ASSOC);
	}
	return $array;
}

function listaCategoria($db)
{
	$array = array();

	$sql = "SELECT 
		id, categoria
	FROM 
		categorias";

	$sql = $db->prepare($sql);
	$sql->execute();

	if($sql->rowCount() > 0)
	{
		$array = $sql->fetchAll(PDO::FETCH_ASSOC);
	}
	return $array;
}

function editarProduto($db, $id, $nome_produto, $quantidade, $codigo, $id_categoria, $url_foto)
{
	if(empty($url_foto))
	{
		$sql = "UPDATE produtos SET nome_produto = :nome_produto, quantidade = :quantidade, id_categoria = :id_categoria, codigo = :codigo
		WHERE id = :id";
	}	
	else
	{
		$sql = "UPDATE produtos SET nome_produto = :nome_produto, quantidade = :quantidade, id_categoria = :id_categoria, url_foto = :url_foto, codigo = :codigo
		WHERE id = :id";
	}	
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":nome_produto", $nome_produto);
	$sql->bindValue(":quantidade", $quantidade);
	$sql->bindValue(":codigo", $codigo);
	$sql->bindValue(":id_categoria", $id_categoria);
	
	if(!empty($url_foto))
	{
		$sql->bindValue(":url_foto", $url_foto);
	}	
	$sql->bindValue(":id", $id);
	
	return $sql->execute();
}

require_once 'include/topo.php';
?>
	
	<div class="container">

		<div class="col-sm-6 col-sm-offset-3 well">

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

			<h2> Editar produto </h2>

			<form method="POST" enctype="multipart/form-data">
	
				<div class="form-group">
					<label for="nome_produto">Nome do produto</label>
					<input type="text" id="nome_produto" name="nome_produto" value="<?php echo $produto[0]['nome_produto']; ?>" class="form-control" required>
				</div>

				<div class="form-group">
					<label for="codigo">Código do produto</label>
					<input type="text" id="codigo" name="codigo" value="<?php echo $produto[0]['codigo']; ?>" class="form-control">
				</div>

				<div class="form-group">
					<label for="categoria">Categoria</label>
					<select id="categoria" name="categoria" class="form-control" required>
						<option value=""> Selecione </option>
					<?php
					foreach ($categorias as $categoria) 
					{
						$selected = '';
						if($categoria['id'] == $produto[0]['id_categoria'])
						{
							$selected = 'selected';
						}
					?>
						<option value="<?php echo $categoria['id']; ?>" <?php echo $selected; ?>> 
							<?php echo $categoria['categoria']; ?> 
						</option>
					<?php
					}
					?>
					</select>
				</div>

				<div class="form-group">
					<label for="quantidade">Quantidade</label>
					<input type="number" id="quantidade" name="quantidade" min="1" max="100" value="<?php echo $produto[0]['quantidade']; ?>" class="form-control" required>
				</div>

				<div class="form-group">
					<label for="foto">Foto</label>
					<input type="file" id="foto" name="foto">
					<img src="assets/img/produtos/<?php echo $produto[0]['url_foto']; ?>" width="80" height="80">
					<p> Caso deseje alterar faça upload de outra foto </p>
				</div>

				<div class="form-group" style="margin-top: 20px;">
					<div class="col-sm-offset-4 col-sm-8">
						<button type="submit" class="btn btn-primary">Editar</button>
					</div>
				</div>
				
			</form>

		</div>

	</div>

	<script>
		$('#quantidade').blur( function()
		{
			var qtd = $('#quantidade').val();

			if( isNaN(qtd) )
			{
				$('#quantidade').val('');
			}
			else
			{
				if(qtd > 100)
				{
					$('#quantidade').val(100);
				}
				else if(qtd < 1)
				{
					$('#quantidade').val(1);
				}
			}

		});
	</script>

<?php
require_once 'include/rodape.php';
?>