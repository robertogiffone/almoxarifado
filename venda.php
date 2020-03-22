<?php
require_once 'include/topo.php';
require_once 'include/conexao.php';

$categorias = listaCategorias($db);
//$data_atual = date('d/m/Y'); echo $data_atual;

function listaCategorias($db)
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
?>	
	<div class="container">
		
		<div class="col-sm-10 col-sm-offset-2 well">

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

			<h2> Reserva de material </h2>
			
			<form method="POST" id="form_venda">
				
				<div class="row">
					<div class="col-sm-4">
					
						<div class="form-group">
							<label for="codigo_predefinido">Código pré definido</label>
							<select id="codigo_predefinido" name="codigo_predefinido" class="form-control">
								<option value=""> Selecione caso deseje utilizar </option>
								<option value="VHA2-VHAB-414-300151830"> VHA2-VHAB-414-300151830 </option>
								<option value="VHA3-VHAB-414-300151830"> VHA3-VHAB-414-300151830 </option>
							</select>
						</div>
						
					</div>

					<div class="col-sm-8">
						<label for="codigo">Centro - Depósito -  Data da solicitação - Segmento - Centro Custo Requisitante</label>
						<input type="text" id="codigo" name="codigo" class="form-control" required>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-4">
						<label for="numero_frota">Aplicação Nº de ordem veículo </label>
						<input type="text" id="numero_frota" name="numero_frota" class="form-control">
					</div>

					<div class="col-sm-4">
						<label for="ordem_manutencao">Número da ordem de manutenção</label>
						<input type="text" id="ordem_manutencao" name="ordem_manutencao" class="form-control">
					</div>

					<div class="col-sm-4">
						<label for="colisitado_por">Solicitado Por</label>
						<input type="text" id="solicitado_por" name="solicitado_por" class="form-control" required>
					</div>
				</div>
				
				<div class="row">
					<div class="col-sm-4">
					
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
						
					</div>
					
					<div class="col-sm-4">
					
						<div class="form-group">
							<label for="produto">Produto</label>
							<select id="produto" name="produto" class="form-control" required>
								<option value=""> Selecione </option>
							</select>
						</div>
						
					</div>
					
				
					<div class="col-sm-3" style="line-height: 80px;">
						<div class="form-group">
							<button type="submit" class="btn btn-primary" id="btn_adicionar_produto">Adicionar produto</button>
						</div>
					</div>
				</div>
			
			</form>

			<table id="tabela_produtos" class="table table-striped" style="display: none;">
				<caption> Produtos adicionados </caption>
				<thead> 
					<tr>
						<th> Categoria </th>
						<th> Foto </th>
						<th> Nome do produto </th>
						<th> Código do produto </th>
						<th> Quantidade </th>
						<th> Ações </th>
					</tr>
				</thead>
				
				<tbody>
				
				</tbody>
				
			</table>
			
			<button type="button" class="btn btn-primary" id="btn_finalizar" style="display: none;">Finalizar</button>
			
		</div>
		
	</div>
	
	<script>
		
		$(document).ready(function() {
			$('#produto').select2({
				templateResult: formatState
			});
		});
		
		function formatState (state) 
		{
			if (!state.id) {
				return state.text;
			}

			var baseUrl = "assets/img/produtos";
			var $state = $(
				'<span><img height="70" width="70" /> <span></span></span>'
			);

			var imagem = state.element.value.split(';');
			imagem = imagem[3];
			//console.log(imagem);
			// Use .text() instead of HTML string concatenation to avoid script injection issues
			$state.find("span").text(state.text);
			//$state.find("img").attr("src", baseUrl + "/" + state.element.value.toLowerCase() + ".png");
			$state.find("img").attr("src", baseUrl + "/" + imagem);

			return $state;
		};
		
		var arrayProdutos = new Array(); //Para gravar produtos que estão na tabela
		
		$('#codigo_predefinido').change( function()
		{
			var codigo_predefinido = $(this).val();
			//console.log(id_categoria);
			
			if(codigo_predefinido != '')
			{
				var data = new Date();
				var day = data.getDate();
				var month = data.getMonth()+1;
				var year = data.getFullYear();
				data = day+'/'+month+'/'+year;
				
				var inicio = codigo_predefinido.substr(0, 10);
				var fim = codigo_predefinido.substr(10, 13);
				codigo_predefinido = inicio+data+'-'+codigo_predefinido;

				$('#codigo').val(codigo_predefinido);
			}
			else
			{
				$('#codigo').val('');	
			}
		});

		$('#categoria').change( function()
		{
			var id_categoria = $(this).val();
			//console.log(id_categoria);
			
			listaProdutos(id_categoria);
		});
		
		function listaProdutos(id_categoria)
		{
			$.ajax(
			{
				url: 'ajax/lista_produto.php'
				,type: 'POST'	
				,dataType: 'json'
		        ,data: 
		        {
		        	id_categoria: id_categoria
		        }
				,success: function(produtos)
				{
					var select_produto = $('#produto');
			
					select_produto.empty();
					select_produto.append('<option value=""> Selecione </option>');
					if(produtos.length > 0)
					{
						var qtd = produtos.length;
						for(i = 0; i < produtos.length; i++)
						{
							var id = produtos[i].id;
							var nome_produto = produtos[i].nome_produto;
							var quantidade = produtos[i].quantidade;
							var codigo = produtos[i].codigo;
							var url_foto = produtos[i].url_foto;
							var categoria = produtos[i].categoria;
							
							//console.log(produtos[i].id);
							var valor = id+';'+nome_produto+';'+quantidade+';'+url_foto+';'+categoria+';'+codigo;
							
							var option = '<option value="';
							option += valor+'">';
							option += nome_produto;
							option += '</option>';
							//console.log(option);
							select_produto.append(option);
							
						}
					}
					
		    	}
		    	,error: function(result, status, error){
		        	alert(error);
		    	}
			});
		}
		
		$('#form_venda').submit( function()
		{
			var produto = $('#produto').val();
			
			var tbody = $("#tabela_produtos tbody");
			
			if(produto != '')
			{
				var dados_produto = produto.split(';');
				var id = dados_produto[0];
				var nome_produto = dados_produto[1];
				var qtd = dados_produto[2];
				var foto = '<img src="assets/img/produtos/'+dados_produto[3]+'" width="40" height="40">';
				var categoria = dados_produto[4];
				var codigo = dados_produto[5];
				var quantidade = '<input type="number" class="quantidade_venda" style="width: 50px;" value="1">';
				var acao_deletar = '<i title = "Deletar" onclick="excluir(this)" class="glyphicon glyphicon-trash pointer" style="color: #F00;" </i>';
				
				var linha = '<tr data-produto="'+produto+'">';
				linha += '<td>'+categoria+'</td>';
				linha += '<td>'+foto+'</td>';
				linha += '<td>'+nome_produto+'</td>';
				linha += '<td>'+codigo+'</td>';
				linha += '<td>'+quantidade+'</td>';
				linha += '<td>'+acao_deletar+'</td>';
				linha += '</tr>';
				
				tbody.append(linha);
				
				arrayProdutos.push(produto);
				
				var qtd_linhas = $("#tabela_produtos tbody tr").length;
				
				if(qtd_linhas > 0)
				{
					$("#tabela_produtos").show();
					$("#btn_finalizar").show();
				}
			
				$('#produto option:selected').remove();
			
			}
				
			return false;
		})
		
		function excluir(item)
		{
			var tr = $(item).closest('tr');
			
			tr.fadeOut(400, function() 
			{
				var produto = tr.attr("data-produto");
				
				tr.remove();

				var qtd_linhas = $("#tabela_produtos tbody tr").length;
				if(qtd_linhas == 0)
				{
					$("#tabela_produtos").hide();
					$("#btn_finalizar").hide();
				}
				
				var index = arrayProdutos.indexOf(produto);
				arrayProdutos.splice(index, 1);
				
				var dados_produto = produto.split(';');
				var option = '<option value=">'+produto+'">';
				option += dados_produto[1];
				option += '</option>';
				$('#produto').append(option);
				
			});
			
			return false;
		}
		
		$('#btn_finalizar').click( function()
		{
			var numero_frota = $('#numero_frota').val();
			var ordem_manutencao = $('#ordem_manutencao').val();
			var solicitado_por = $('#solicitado_por').val();
			var codigo = $('#codigo').val();
			var dados = new Array();
			
			var table = $('#tabela_produtos tbody');
			var i = 0;
			table.find('tr').each(function() 
			{
				var produto = $(this).attr("data-produto");
				var dados_produto = produto.split(';');
				var id = dados_produto[0];
				var qtd = $(this).find('td input.quantidade_venda').val();
				
				dados.push({ 
					id: id, qtd: qtd, numero_frota: numero_frota, ordem_manutencao: ordem_manutencao, solicitado_por: solicitado_por, codigo: codigo
				});
				
			});
			
			if(dados.length > 0)
			{
				cadastroVenda(dados);
			}
		});
		
		function cadastroVenda(dados)
		{
			$.ajax(
			{
				url: 'ajax/cadastro_venda.php'
				,type: 'POST'	
				,dataType: 'json'
		        ,data: 
		        {
		        	dados: dados
		        }
				,success: function(result){
		        	if(result == '1')
		        	{
		        		alert('Inserido com sucesso');
		        		window.location.reload();
		        	}
		        	else
		        	{
		        		alert('Erro na tentativa de cadastro');
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