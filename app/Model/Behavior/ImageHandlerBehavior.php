<?php
class ImageHandlerBehavior extends ModelBehavior {
        
/**
	* Resource da Imagem Atual Carregada
	* @var <Resource>
	*/
  public $imagemAtual;

/**
	* Extensão da Imagem Atual Carregada
	* @var <String>
	*/
  public $imagemExtensao;

/**
	* Largura Original da imagem atual carregada
	* @var <Integer>
	*/
	public $larguraOriginal;

/**
	* Altura Original da imagem atual carregada
	* @var <Integer>
	*/
	public $alturaOriginal;

/**
	* Tamanho em bytes da imagem atual carregada
	* @var <Integer>
  */
  public $TamanhoArquivo;


/**
	*
	* @param <String> $src = Caminho da Imagem
	*/
	public function load($src){

		//Verifica se a Imagem Existe
		if( !file_exists($src) ) {
		  throw new Exception('fileNotExists');
		}

		// Recupera a Exstensão da Imagem
		$ext = explode(".", $src);
		$extension = $ext[count($ext) -1 ];
		$this->imagemExtensao    =  strtolower($extension);

		//Cria a Imagem com a Devida Extensão
		if( $this->imagemExtensao == 'jpg' || $this->imagemExtensao == 'jpeg' ){
			$this->imagemAtual  = imagecreatefromjpeg($src);
		} elseif( $this->imagemExtensao == 'gif' ){
			$this->imagemAtual  = imagecreatefromgif($src);
		} elseif( $this->imagemExtensao == 'png' ){
			$this->imagemAtual  = imagecreatefrompng($src);
		} else {
			throw new Exception('ExtensionNotSuported');
		}

		//Recupera a Largura Original a Imagem
		$this->larguraOriginal = imagesx($this->imagemAtual);

		//Recupera a Altura Original da Imagem
		$this->alturaOriginal    = imagesy($this->imagemAtual);

		//Recupera o Tamanho Original da Imagem
		$this->TamanhoArquivo    = intval(filesize($src) / 1024);
	}

/**
	* Método para Redimensionar Imagem
	* @param <int> $largura     = Largura da Nova Imagem
	* @param <int> $altura      = Altura da Nova Imagem
	* @param <string> $saveAs   = Pasta de Nome da Imagem Gerada
	* @param <int> $qualidade   = Porcentagem de Qualidade da Imagem (de 0 a 100)
	*/
	public function resize( $largura = null, $altura = null, $saveAs = null, $qualidade = 80 ){   

		if( $largura && $altura ){
			$novaImagem = imagecreatetruecolor($largura, $altura);
		}elseif( $largura && !$altura ){
			$altura = intval(($this->alturaOriginal * $largura) / $this->larguraOriginal);
			$novaImagem = imagecreatetruecolor($largura, $altura);
		}elseif( !$largura && $altura ){
			$largura = intval(($this->larguraOriginal * $altura) / $this->alturaOriginal);
			$novaImagem = imagecreatetruecolor($largura, $altura);
		}

		if( $this->imagemExtensao == 'jpg' || $this->imagemExtensao == 'jpeg' ){
			imagecopyresampled($novaImagem, $this->imagemAtual, 0, 0, 0, 0, $largura, $altura, $this->larguraOriginal, $this->alturaOriginal);
			imagejpeg($novaImagem, $saveAs, $qualidade);
		} elseif( $this->imagemExtensao == 'gif' ){
			imagecopyresampled($novaImagem, $this->imagemAtual, 0, 0, 0, 0, $largura, $altura, $this->larguraOriginal, $this->alturaOriginal);
			imagegif($novaImagem, $saveAs);
		} elseif( $this->imagemExtensao == 'png' ){
			imagealphablending($novaImagem, false);
			imagesavealpha($novaImagem, true);
			$transparent = imagecolorallocatealpha($novaImagem, 255, 255, 255, 127);
			imagefilledrectangle($novaImagem, 0, 0, $largura, $altura, $transparent);
			imagecopyresampled($novaImagem, $this->imagemAtual, 0, 0, 0, 0, $largura, $altura, $this->larguraOriginal, $this->alturaOriginal);
			imagepng($novaImagem, $saveAs, intval ($qualidade / 10.1));
		}
		imagedestroy($novaImagem);
		chmod ($saveAs, 0755);

	}

        
/**
	* Método para Cortar Imagem
	* @param <int> $largura     = Largura da Nova Imagem
	* @param <int> $altura      = Altura da Nova Imagem
	* @param <int> $x           = Coordenadas da Esquerda Para Corte
	* @param <int> $y           = Coordenadas do topo Para Corte
	* @param <string> $saveAs   = Pasta de Nome da Imagem Gerada
	* @param <int> $qualidade   = Porcentagem de Qualidade da Imagem (de 0 a 100)
	*/
	public function crop( $largura, $altura, $x, $y,  $saveAs, $qualidade, $pb = false ){

		$novaImagem = imagecreatetruecolor($largura, $altura);

		if( $this->imagemExtensao == 'jpg' || $this->imagemExtensao == 'jpeg' ){
			imagecopyresampled($novaImagem, $this->imagemAtual,  0, 0, $x, $y, $largura, $altura, $largura, $altura);

			//Transforma em PB
			if( $pb == true )
				imagefilter($novaImagem, IMG_FILTER_GRAYSCALE);

			imagejpeg($novaImagem, $saveAs, $qualidade);
		} elseif( $this->imagemExtensao == 'gif' ){
			imagecopyresampled($novaImagem, $this->imagemAtual,  0, 0, $x, $y, $largura, $altura, $largura, $altura);

			//Transforma em PB
			if( $pb == true )
				imagefilter($novaImagem, IMG_FILTER_GRAYSCALE);

			imagegif($novaImagem, $saveAs);
		} elseif( $this->imagemExtensao == 'png' ){
			imagealphablending($novaImagem, false);
			imagesavealpha($novaImagem, true);

			$transparent = imagecolorallocatealpha($novaImagem, 255, 255, 255, 127);
			imagefilledrectangle($novaImagem, 0, 0, $largura, $altura, $transparent);
			imagecopyresampled($novaImagem, $this->imagemAtual,  0, 0, $x, $y, $largura, $altura, $largura, $altura);

			//Transforma em PB
			if( $pb == true )
				imagefilter($novaImagem, IMG_FILTER_GRAYSCALE);

			$result = imagepng($novaImagem, $saveAs, intval ($qualidade / 10.1));
		}

		chmod ($saveAs, 0755);

	}


	public function resizeCrop( $largura, $altura,  $saveAs, $qualidade, $pb = false ){

		if( $this->alturaOriginal > $this->larguraOriginal ){
			$this->resize( $largura, null, $saveAs, $qualidade );
			$this->load( $saveAs );

			$y = ( $this->alturaOriginal - $altura ) / 2;
			$this->crop( $largura, $altura, 0, $y,  $saveAs, $qualidade, $pb);
		}elseif( $this->alturaOriginal < $this->larguraOriginal ){
			$this->resize( null, $altura, $saveAs, $qualidade );
			$this->load( $saveAs );
			$x = ($this->larguraOriginal - $largura) / 2;

			$this->crop( $largura, $altura, $x, 0,  $saveAs, $qualidade, $pb );
		} else {
			$this->resize( $largura, $altura, $saveAs, $qualidade, $pb );
		}
	}
}
?>