<?php
require_once 'include/topo.php';
require_once 'include/conexao.php';

if( isset($_POST['nome_produto']) && !empty($_POST['nome_produto'])  )
{
	$nome_produto = addslashes($_POST['nome_produto']);
	$quantidade = $_POST['quantidade'];
	$id_categoria = $_POST['categoria'];
	$codigo = $_POST['codigo'];
	$foto = $_FILES['foto'];

	$ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
	$md5name = md5(time().rand(0,9999));
	
	$url_foto = '';
	if($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg' || $ext == 'gif')
    {
		move_uploaded_file($foto['tmp_name'], "assets/img/produtos/".$md5name.'.'.$ext);
		$url_foto = $md5name.'.'.$ext;
	}

	if(cadastrarProduto($db, $nome_produto, $quantidade, $id_categoria, $codigo, $url_foto))
	{
		$_SESSION['msg'] = array('msg'=>'Produto cadastrado com sucesso', 'tipo'=>'success');
	}   
	else
	{
		$_SESSION['msg'] = array('msg'=>'Erro ao cadastrar', 'tipo'=>'danger');
	}

}

$categorias = listaCategoria($db);

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

function cadastrarProduto($db, $nome_produto, $quantidade, $id_categoria, $codigo, $url_foto)
{
	$sql = "INSERT INTO produtos(nome_produto, quantidade, id_categoria, codigo, url_foto) 
	VALUES (:nome_produto, :quantidade, :id_categoria, :codigo, :url_foto)";
	
	$sql = $db->prepare($sql);
	$sql->bindValue(":nome_produto", $nome_produto);
	$sql->bindValue(":quantidade", $quantidade);
	$sql->bindValue(":id_categoria", $id_categoria);
	$sql->bindValue(":codigo", $codigo);
	$sql->bindValue(":url_foto", $url_foto);

	return $sql->execute();
}
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

			<h2> Cadastro produto </h2>

			<form method="POST" enctype="multipart/form-data">
	
				<div class="form-group">
					<label for="nome_produto">Nome do produto</label>
					<input type="text" id="nome_produto" name="nome_produto" class="form-control" required>
				</div>

				<div class="form-group">
					<label for="categoria">Categoria</label>
					<select id="categoria" name="categoria" class="form-control" required>
						<option value=""> Selecione </option>
						<?php
						foreach ($categorias as $categoria) 
						{
						?>
						<option value="<?php echo $categoria['id']; ?>"> <?php echo $categoria['categoria']; ?> </option>
						<?php
						}
						?>
					</select>
				</div>

				<div class="form-group">
					<label for="quantidade">Quantidade</label>
					<input type="number" id="quantidade" name="quantidade" maxlength="3" min="1" max="100" class="form-control" required>
				</div>

				<div class="form-group">
					<label for="codigo">Código do produto</label>
					<input id="codigo" name="codigo" class="form-control">
				</div>

				<div class="form-group">
					<label for="foto">Foto</label>
					<input type="file" id="foto" name="foto" required>
				</div>

				<div class="form-group" style="margin-top: 20px;">
					<div class="col-sm-offset-4 col-sm-8">
						<button type="submit" class="btn btn-primary">Cadastrar</button>
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