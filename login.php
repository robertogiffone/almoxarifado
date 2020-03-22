<?php
require_once 'include/conexao.php';
session_start();

if( isset($_POST['usuario']) && !empty($_POST['usuario'])  )
{
	$usuario = addslashes($_POST['usuario']);
	$senha = md5($_POST['senha']);

	if(fazerLogin($db, $usuario, $senha))
	{
		header('LOCATION: index.php');
	}   
	else
	{
		$_SESSION['msg'] = array('msg'=>'Usuário/senha incorreto', 'tipo'=>'danger');
	}

}

function fazerLogin($db, $usuario, $senha)
{
	$sql = "SELECT
			id, usuario, matricula, nome, id_perfil
		FROM usuarios
		WHERE usuario = :usuario AND senha = :senha";
	$sql = $db->prepare($sql);
	$sql->bindValue(":usuario", $usuario);
	$sql->bindValue(":senha", $senha);
	$sql->execute();

	if($sql->rowCount() > 0)
	{
		$row = $sql->fetch();

		$_SESSION['lgusuario'] = array( 
			'id' => $row['id']
			,'usuario' => $row['usuario']
			,'matricula' => $row['matricula']
			,'nome' => $row['nome']
			,'id_perfil' => $row['id_perfil']
		);

		return true;
	}

	return false;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title> Almoxarifado </title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" type="text/css" href="assets/css/bootstrap4.min.css" />
		<link rel="stylesheet" type="text/css" href="assets/css/fontawesome.css" />
		<link rel="stylesheet" type="text/css" href="assets/css/login.css" />
		<script type="text/javascript" src="assets/js/jquery-3.3.1.min.js"></script>
		<script type="text/javascript" src="assets/js/bootstrap4.min.js"></script>
	</head>
	<body>
		
		<div class="container">
			<div class="d-flex justify-content-center h-100">
				<div class="card">
		
					<div class="card-body">
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
						<form method="POST">
							<div class="input-group form-group">
								<div class="input-group-prepend">
									<span class="input-group-text"><i class="fas fa-user"></i></span>
								</div>
								<input type="text" class="form-control" name="usuario" placeholder="Usuário" value="<?php echo isset($_POST['usuario']) && !empty($_POST['usuario']) ? $_POST['usuario']:''; ?>" required>
								
							</div>
							<div class="input-group form-group">
								<div class="input-group-prepend">
									<span class="input-group-text"><i class="fas fa-key"></i></span>
								</div>
								<input type="password" class="form-control" name="senha" placeholder="Senha">
							</div>
							<div class="form-group">
								<input type="submit" value="Login" class="btn login_btn form-control">
							</div>
						</form>
					</div>
					
				</div>
			</div>
		</div>	

	</body>

</html>