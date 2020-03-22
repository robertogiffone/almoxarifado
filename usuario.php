<?php
require_once 'include/conexao.php';
session_start();

if(estaLogado() === false)
{
  header('LOCATION: login.php');
}

function estaLogado()
{
  if( isset($_SESSION['lgusuario']) && !empty($_SESSION['lgusuario']) )
  {
    return true;
  }
  return false;
}

if( isset($_POST['usuario']) && !empty($_POST['usuario'])  )
{
	$usuario = addslashes($_POST['usuario']);
	$senha = md5($_POST['senha']);
	$matricula = addslashes($_POST['matricula']);
	$nome = addslashes($_POST['nome']);
	$id_perfil = addslashes($_POST['perfil']);
	
	if(cadastrarUsuario($db, $usuario, $senha, $matricula, $nome, $id_perfil))
	{
		$_SESSION['msg'] = array('msg'=>'Usuário cadastrado com sucesso', 'tipo'=>'success');
	}   
	else
	{
		$_SESSION['msg'] = array('msg'=>'Erro ao cadastrar', 'tipo'=>'danger');
	}

}

$perfis = listaPerfis($db);

function cadastrarUsuario($db, $usuario, $senha, $matricula, $nome, $id_perfil)
{
		$sql = "INSERT INTO usuarios(usuario, senha, matricula, nome, id_perfil) VALUES (:usuario, :senha, :matricula, :nome, :id_perfil)";
		
		$sql = $db->prepare($sql);
		$sql->bindValue(":usuario", $usuario);
		$sql->bindValue(":senha", $senha);
		$sql->bindValue(":matricula", $matricula);
		$sql->bindValue(":nome", $nome);
		$sql->bindValue(":id_perfil", $id_perfil);
		
		return $sql->execute();
}

function listaPerfis($db)
{
	$array = array();

	$sql = "SELECT 
				id, perfil
			FROM 
				perfis
			WHERE
			id != 1";

	$sql = $db->prepare($sql);
	$sql->execute();

	if($sql->rowCount() > 0)
	{
		$array = $sql->fetchAll(PDO::FETCH_ASSOC);
	}
	return $array;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title> Almoxarifado </title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="assets/css/usuario.css" />
		<script type="text/javascript" src="assets/js/jquery-3.3.1.min.js"></script>
		<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
	</head>
	<body>

		<form class="login" method="POST">
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
			<h1 class="h3 mb-3 font-weight-normal text-center"> Almoxarifado </h1>
			
			<h3 class="h3 mb-3 font-weight-normal text-center"> Cadastro usuário </h3>
	
			<label for="inputUsuario" class="sr-only">Usuário</label>
			<input type="text" name="usuario" id="inputUsuario" placeholder="Usuário" class="form-control" required />

			<label for="inputPassword" class="sr-only">Senha</label>
			<input type="password" name="senha" id="inputPassword" placeholder="Senha" class="form-control" required />
			
			<label for="inputMatricula" class="sr-only">Matrícula</label>
			<input type="text" name="matricula" id="inputMatricula" placeholder="Matrícula" class="form-control" required />
			
			<label for="inputNome" class="sr-only">Nome</label>
			<input type="text" name="nome" id="inputNome" placeholder="Nome" class="form-control" required />
			
			<div class="form-group">
				<label>Perfil</label>
				<select id="perfil" name="perfil" class="form-control" required>
					<option value=""> Selecione </option>
				<?php
				foreach($perfis as $perfil) 
				{
				?>
					<option value="<?php echo $perfil['id']; ?>"> <?php echo $perfil['perfil']; ?> </option>
				<?php
				}
				?>
				</select>
			</div>
						
			<button type="submit" class="btn btn-lg btn-primary btn-block" > Cadastrar usuário </button>
			<a class="btn btn-lg btn-primary btn-block" href="index.php" style="margin-top: -20px; margin-bottom: 10px;"> Voltar </a>

			<p class="mt-5 mb-3 text-muted text-center"> Almoxarifado </p>

		</form>

	</body>

</html>