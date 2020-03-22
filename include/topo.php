<?php
session_start();
require_once 'conexao.php';

if(estaLogado() === false)
{
  header('LOCATION: login.php');
}

$id_perfil_logado = $_SESSION['lgusuario']['id_perfil'];

function estaLogado()
{
  if( isset($_SESSION['lgusuario']) && !empty($_SESSION['lgusuario']) )
  {
    return true;
  }
  return false;
}

$notificacoes = listaNotificacoes($db, $id_perfil_logado);

function listaNotificacoes($db, $id_perfil)
{
  $array = array();

  $sql = "SELECT 
        id, mensagem
      FROM 
        notificacoes
      WHERE
        ativo = 1 AND id_perfil = :id_perfil";

  $sql = $db->prepare($sql);
  $sql->bindValue(":id_perfil", $id_perfil);
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
    <link rel="stylesheet" type="text/css" href="assets/css/jquery-ui.min.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/select2.min.css" />
	  <link rel="stylesheet" type="text/css" href="assets/css/template.css" />
    <script type="text/javascript" src="assets/js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="assets/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="assets/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="assets/js/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript" src="assets/js/select2.min.js"></script>
    <style>
      .list-notificacao{
        min-width: 400px;
        background: #ffffff;
      }

      .list-notificacao li{
         border-bottom : 1px #d8d8d8 solid;
         text-align    : justify;
         padding       : 5px 10px 5px 10px;
         cursor: pointer;
         font-size: 12px;
      }

      .list-notificacao li:hover{
        background: #f1eeee;
      }

      .list-notificacao li:hover .exclusaoNotificacao{
        display: block;
      }

      .list-notificacao li  p{
        color: black;
        width: 305px;
      }

      .list-notificacao li .exclusaoNotificacao{
        width: 25px;
        min-height: 40px;
        position: absolute;
        right: 0;
        display: none;
      }

      .list-notificacao .media img{
        width: 40px;
        height: 40px;
        float:left;
        margin-right: 10px;
      }

      .badgeAlert {
        display: inline-block;
        min-width: 10px;
        padding: 3px 7px;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
        line-height: 1;
        vertical-align: baseline;
        white-space: nowrap;
        text-align: center;
        background-color: #d9534f;
        border-radius: 10px;
        position: absolute;
        margin-top: -10px;
        margin-left: -10px
      }
    </style>
    
    <script>
      function excluirItemNotificacao(e){
        var id = e.id;
        
        $('#item_notification_'+id).remove();
        
        inativaNotificacao(id);
        
        var qtd = $('.badgeAlert').html();
        $('.badgeAlert').html(qtd-1);
      }
      
      function inativaNotificacao(id)
      {
        $.ajax(
        {
          url: 'ajax/inativa_notificacao.php'
          ,type: 'POST' 
          ,dataType: 'json'
              ,data: 
              {
                id: id
              }
          ,success: function(result){
                if(result == '1')
                {
                  //alert('Inativado com sucesso');
                  //window.location.reload();
                }
                else
                {
                  //alert('Erro na tentativa de inativar');
                }
            }
            ,error: function(result, status, error){
                alert(error);
            }
        });
      }
    </script>
  </head>
  <body>

  <!-- Static navbar -->
  <nav class="navbar navbar-default">
        <div class="container-fluid">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Almoxarifado </a>
          </div>
          <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
              <li><a href="index.php">Início</a></li>
              <?php
              if($id_perfil_logado == 3 || $id_perfil_logado == 1)
              {
              ?>
              <li><a href="usuario.php">Cadastro usuário</a></li>
              <?php
              }
              if($id_perfil_logado == 4 || $id_perfil_logado == 1)
              {
              ?>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Produto <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a href="index.php"> Listar </a></li>
                  <li><a href="produto.php"> Cadastrar </a></li>
                </ul>
              </li>
              <?php
              }
              if($id_perfil_logado != 4)
              {
              ?>
              <?php
              if($id_perfil_logado == 3 || $id_perfil_logado == 1 || $id_perfil_logado == 2)
              {
              ?>
                <li><a href="lista_venda.php"> Listar requisição </a></li>
              <?php
              }
              if($id_perfil_logado == 2 || $id_perfil_logado == 1)
              {
              ?>
                <li><a href="venda.php"> Requisição </a></li>
              <?php
              }
              }
              if($id_perfil_logado == 4 || $id_perfil_logado == 1)
              {
              ?>
              <li><a href="liberar_pecas.php">Liberar peças</a></li>
			        <?php
              }
              ?>
              <!--
			        <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Reserva <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a href="lista_reserva.php"> Listar </a></li>
                  <li><a href="reserva.php"> Cadastrar </a></li>
                </ul>
              </li>
			        -->
            </ul>
            <ul class="nav navbar-nav navbar-right">
              
              <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <span class="glyphicon glyphicon-bell alertNotificacao"></span>
                    <span class='badgeAlert'><?php echo count($notificacoes); ?> </span>
                    <span class="caret"></span></a>
                  <ul class="list-notificacao dropdown-menu">
                  <?php
                  foreach ($notificacoes as $n)
                  {
                    $id_notificacao = $n['id'];
                    $mensagem = $n['mensagem'];
                  ?>
                    <li id='item_notification_<?php echo $id_notificacao; ?>'>
                        <div class="media">
                           <div class="media-body">
                              <div class='exclusaoNotificacao'>
                                  <button class='btn btn-danger btn-xs button_exclusao' id='<?php echo $id_notificacao; ?>' onclick='excluirItemNotificacao(this)'>x</button>
                              </div>
                              <p> <?php echo $mensagem; ?> </p>
                           </div>
                        </div>
                     </li>
                  <?php
                  }
                  ?>
                  </ul>
              </li>  

              <li><a href="logout.php">Sair</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div><!--/.container-fluid -->
  </nav>