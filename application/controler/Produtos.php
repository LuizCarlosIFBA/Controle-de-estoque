<?php

class Produtos {
	
	
	public $tabela;
	public $id;
	public $conexaoDB;
	public $quantidadeatual;

	
	#Construtor - Conecta e seleciona a tabela
	function __construct($tabela="produto",$produto="0") {
		
		$this->conexaoDB=new DB();
		$this->tabela=$tabela;
		$produto=$this->conexaoDB->PesquisaUnica($this->tabela,$produto);
		$this->quantidadeatual=$produto['estoque_atual'];
		}
	
	#Cadastra ou chama o o metodo para atualizacao dos produtos
	function Cadastrar($id=""){
		
		$funcao=(empty($id))?"Insert into":"Update";
		$where=(empty($id))?" ":" where id = $id";
		$dados=$_POST;
		
        $campos="";		
		foreach ($dados as $campo=>$valor){
			$campos.=$campo."='$valor', ";			
		}
		
		$campos=strip_tags($campos);
		$campos=substr($campos,0,-2);
		
		//teste:
		//echo "$funcao $this->tabela SET $campos $where";
		//die();
		
		$this->conexaoDB->ExecutaQuery("$funcao $this->tabela SET $campos $where");
		header("Location:main.php");
		
		
	}
	
	
	#Atualiza produto
	function Atualizar($id){
		
		
		$this->Cadastrar($id);
		
		
	}
	
	#Deleta produto
	function Deletar($id){
		
		$this->conexaoDB->ExecutaQuery("Delete from $this->tabela where id=$id");
		header("Location:main.php?url=$this->tabela&acao=listar");
	}
	
	
	#Lista produtos
	function Listar(){
		
		
		$produtos=$this->conexaoDB->PesquisaCampos("id,nome,estoque_atual",$this->tabela);
		
		echo '<h1>Listando '.$this->tabela.'</h1><br><br>';
		echo '<table align="center">';
		echo '<tr><td class="header">ID</td><td class="header">Nome</td><td class="header">Estoque atual</td><td class="header"  align="center" colspan="2"> Ações</td></tr>';
		while($produto=mysql_fetch_array($produtos)){
			
			echo '<tr>';
			echo '<td>'.$produto['id'].'</td>';
			echo '<td>'.$produto['nome'].'</td>';
			echo '<td>'.$produto['estoque_atual'].'</td>';	
			echo '<td><a href="main.php?url='.$this->tabela.'&acao=formeditar&id='.$produto['id'].'">Editar</a></td>';
			echo '<td><a href="main.php?url='.$this->tabela.'&acao=deletar&id='.$produto['id'].'">Excluir</a></td>';
			echo '</tr>';
		}
		echo '</table>';
	}

	#Lista fornecedores
	function ListarFornecedores(){
		
		
		$produtos=$this->conexaoDB->PesquisaCampos("id,nome,telefone,cidade,estado",$this->tabela);
		
		echo '<h1>Listando '.$this->tabela.'</h1><br><br>';
		echo '<table align="center">';
		echo '<tr><td class="header">Nome</td><td class="header">Telefone</td><td class="header">Cidade</td><td class="header">Estado</td><td class="header"  align="center" colspan="2"> Ações</td></tr>';
		while($produto=mysql_fetch_array($produtos)){

			

			echo '<tr>';

			echo '<td>'.$produto['nome'].'</td>';
			echo '<td>'.$produto['telefone'].'</td>';
			echo '<td>'.$produto['cidade'].'</td>';
			echo '<td>'.$produto['estado'].'</td>';	
			echo '<td><a href="main.php?url='.$this->tabela.'&acao=formeditar&id='.$produto['id'].'">Editar</a></td>';

			echo '<td><a href="main.php?url='.$this->tabela.'&acao=deletar&id='.$produto['id'].'">Excluir</a></td>';

			echo '</tr>';

		}

		echo '</table>';

	}	

	#Lista retirantes
	function ListarRetirantes(){
		
		
		$produtos=$this->conexaoDB->PesquisaCampos("id,nome,empresa",$this->tabela);
		
		echo '<h1>Listando '.$this->tabela.'</h1><br><br>';
		echo '<table align="center">';
		echo '<td class="header">Nome</td><td class="header">Empresa</td><td class="header"  align="center" colspan="2"> Ações</td></tr>';
		while($produto=mysql_fetch_array($produtos)){

			

			echo '<tr>';

			echo '<td>'.$produto['nome'].'</td>';
			echo '<td>'.$produto['empresa'].'</td>';	
			echo '<td><a href="main.php?url='.$this->tabela.'&acao=formeditar&id='.$produto['id'].'">Editar</a></td>';

			echo '<td><a href="main.php?url='.$this->tabela.'&acao=deletar&id='.$produto['id'].'">Excluir</a></td>';

			echo '</tr>';

		}

		echo '</table>';

	}	

	function Entrada($produto,$quantidade){
		
		$dados=$_POST;
		foreach ($dados as $campo=>$valor){
		if($campo=='data'){
			$valor="NOW()";	
		}
			$campos.=$campo."='$valor' ,";			
		}
		
		$campos=strip_tags($campos);
		$campos=substr($campos,0,-2);
		$campos=str_replace("'NOW()'","NOW()",$campos);
		$id=mysql_insert_id($this->conexaoDB->ExecutaQuery("Insert into entrada SET $campos"));
		$this->conexaoDB->ExecutaQuery("Update $this->tabela set estoque_atual=estoque_atual+$quantidade where id=$produto");
		header("Location:main.php");
				
		
	}
	
	function Saida($produto,$quantidade){
		
		if($this->quantidadeatual<$quantidade){
		echo 'A quantidade a Ser Retirada é maior do que a existente no estoque';
		exit();
		}
		else
		{
		$dados=$_POST;
		foreach ($dados as $campo=>$valor){
			if($campo=='data'){
			$valor="NOW()";	
		}	
			$campos.=$campo."='$valor' ,";			
		}
		
		$campos=strip_tags($campos);
		$campos=substr($campos,0,-2);
		$campos=str_replace("'NOW()'","NOW()",$campos);
		$id=mysql_insert_id($this->conexaoDB->ExecutaQuery("Insert into saida SET $campos"));
		$this->conexaoDB->ExecutaQuery("Update $this->tabela set estoque_atual=estoque_atual-$quantidade where id=$produto");
		}
		header("Location:main.php");
	}
	
	function EstoqueMinimo(){
		
	$sql="Select nome from $this->tabela where estoque_atual<=estoque_minimo";	
		
	$produtos=$this->conexaoDB->ExecutaQuery($sql);
	$nlinhas=$this->conexaoDB->Nlinhas($produtos);
		
	if($nlinhas>0){
		echo '<img src="img/alert.png"><br />';	
	    while($produto=mysql_fetch_array($produtos)) {

		   echo $produto['nome']." chegou ao estoque mínimo<br>";
	}
		
	}
	else {
		echo '<img src="img/ok.png" class="img">';
		echo 'Nenhum alerta para hoje';
	}
	}
}

?>
