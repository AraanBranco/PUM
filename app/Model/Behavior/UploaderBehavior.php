<?php

class ImageHandler {
        
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
                if(!file_exists($src)) {
                  throw new Exception('fileNotExists');
                }
                

              // Recupera a Exstensão da Imagem
              $ext = explode(".", $src);
              $extension = $ext[count($ext) -1 ];
              $this->imagemExtensao    =  strtolower($extension);

              //Cria a Imagem com a Devida Extensão
              if($this->imagemExtensao == 'jpg' || $this->imagemExtensao == 'jpeg'){
                $this->imagemAtual  = imagecreatefromjpeg($src);
              } elseif($this->imagemExtensao == 'gif'){
                $this->imagemAtual  = imagecreatefromgif($src);
              } elseif($this->imagemExtensao == 'png'){
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
        public function resize($largura = null, $altura = null, $saveAs = null, $qualidade = 80)
        {   
            
                    if($largura && $altura){

                            $novaImagem = imagecreatetruecolor($largura, $altura);

                    }elseif($largura && !$altura){

                            $altura = intval(($this->alturaOriginal * $largura) / $this->larguraOriginal);
                            $novaImagem = imagecreatetruecolor($largura, $altura);

                    }elseif(!$largura && $altura){

                             $largura = intval(($this->larguraOriginal * $altura) / $this->alturaOriginal);
                             $novaImagem = imagecreatetruecolor($largura, $altura);

                    }

                    if($this->imagemExtensao == 'jpg' || $this->imagemExtensao == 'jpeg'){

                            imagecopyresampled($novaImagem, $this->imagemAtual, 0, 0, 0, 0, $largura, $altura, $this->larguraOriginal, $this->alturaOriginal);
                            imagejpeg($novaImagem, $saveAs, $qualidade);

                   } elseif($this->imagemExtensao == 'gif'){

                            imagecopyresampled($novaImagem, $this->imagemAtual, 0, 0, 0, 0, $largura, $altura, $this->larguraOriginal, $this->alturaOriginal);
                            imagegif($novaImagem, $saveAs);

                    } elseif($this->imagemExtensao == 'png'){

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
        public function crop($largura, $altura, $x, $y,  $saveAs, $qualidade, $pb = false){
            
              $novaImagem = imagecreatetruecolor($largura, $altura);
              
              if($this->imagemExtensao == 'jpg' || $this->imagemExtensao == 'jpeg'){

                  imagecopyresampled($novaImagem, $this->imagemAtual,  0, 0, $x, $y, $largura, $altura, $largura, $altura);
                  if( $pb == true )
                     imagefilter($novaImagem, IMG_FILTER_GRAYSCALE);

                  imagejpeg($novaImagem, $saveAs, $qualidade);

             } elseif($this->imagemExtensao == 'gif'){

                  imagecopyresampled($novaImagem, $this->imagemAtual,  0, 0, $x, $y, $largura, $altura, $largura, $altura);
                  if( $pb == true )
                     imagefilter($novaImagem, IMG_FILTER_GRAYSCALE);

                  imagegif($novaImagem, $saveAs);

              } elseif($this->imagemExtensao == 'png'){

                  imagealphablending($novaImagem, false);
                  imagesavealpha($novaImagem, true);

                  $transparent = imagecolorallocatealpha($novaImagem, 255, 255, 255, 127);
                  
                  imagefilledrectangle($novaImagem, 0, 0, $largura, $altura, $transparent);
                  imagecopyresampled($novaImagem, $this->imagemAtual,  0, 0, $x, $y, $largura, $altura, $largura, $altura);

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

              $y = ( $this->alturaOriginal - $altura) / 2;
              $this->crop( $largura, $altura, 0, $y,  $saveAs, $qualidade, $pb);

          }elseif(  $this->alturaOriginal < $this->larguraOriginal){

              $this->resize( null, $altura, $saveAs, $qualidade );
              $this->load( $saveAs );

              $x = ($this->larguraOriginal - $largura) / 2;
              $this->crop( $largura, $altura, $x, 0,  $saveAs, $qualidade, $pb );

            
          } else {
             $this->resize( $largura, $altura, $saveAs, $qualidade, $pb );
          }
      }
}


class UploaderBehavior extends ModelBehavior {


	public $settings = array();
	public $files    = array();
	public $errors   = array();
	public $info	 = array();
	public $tempDir  = '../tmp/uploader';
	public $stop	= array();
	public $post 	= array();

    public function setup(Model $model, $settings = array()) {

	    $this->settings[$model->alias] = $settings;
	    $this->files = $_FILES;
	    $this->post = $_POST;
	}

	public function addError( $model, $field, $message ){
		$this->errors[$model][$field][] = $message;
	}

	public function validate( Model $model  ){

		if( is_array($this->settings) && count($this->settings) > 0){

			foreach( $this->settings[$model->alias] as $upload ){

				$move = isset($this->files[$model->alias]['tmp_name'][$upload['field']]) ? $this->files[$model->alias]['tmp_name'][$upload['field']] : false;

				//Check required
				if( $move == false ){

					if( isset($this->post['Uploader']['required']) && in_array($upload['field'], $this->post['Uploader']['required'])  )
						$this->addError( $model->alias, $upload['field'],  'Selecione uma arquivo');

					$this->stop[$model->alias][$upload['field']] = true;
				}else{

					$tmp_name  = $this->files[$model->alias]['tmp_name'][$upload['field']];
					$error     = $this->files[$model->alias]['error'][$upload['field']];
					$type 	   = $this->files[$model->alias]['type'][$upload['field']];
					$size 	   = $this->files[$model->alias]['size'][$upload['field']];
					$name 	   = $this->files[$model->alias]['name'][$upload['field']];

					//Check errors
					if( $error > 0 ){

						switch ( $error ) {
							case 1:
								$this->addError( $model->alias, $upload['field'], 'O arquivo no upload é maior do que o limite definido em upload_max_filesize no php.ini.' );
								break;

							case 2:
								$this->addError( $model->alias, $upload['field'], 'O arquivo ultrapassa o limite de tamanho em MAX_FILE_SIZE que foi especificado no formulário HTML. ' );
								break;

							case 3:
								$this->addError( $model->alias, $upload['field'], 'O upload do arquivo foi feito parcialmente. ' );
								break;

							case 4:
								$this->addError( $model->alias, $upload['field'], 'Não foi feito o upload do arquivo. ' );
								break;
						}
					}

					//check size
					$this->info[$model->alias][$upload['field']] = pathinfo( $name );

					//Check extensions
					if( count($upload['allowedExtensions']) > 0 && !in_array( $this->info[$model->alias][$upload['field']]['extension'], $upload['allowedExtensions']) )
						$this->addError( $model->alias, $upload['field'],  'Selecione arquivo somente com as extensões: '.implode( ', ', $upload['allowedExtensions'] ) );
					

					//Valida o tamanho do arquivo
					if( ($size  / 1024) > $upload['maxsize'] )
						$this->addError( $model->alias, $upload['field'], 'Envie um arquivo com até '.number_format($upload['maxsize'] / 1024, '1', '.',',').' Mb');

				}				
			}

		}		

		return $this->errors;
	}

	public function afterValidate(Model $model){

		$errors = $model->validate( $model );
		if( count($errors) > 0 ){
			foreach( $errors[$model->alias] as $field=>$error ){
				$model->validationErrors[$field] = $error;
			}
		}		

		return $model;
	}

	public function beforeSave( Model $model ){
		foreach( $this->settings[$model->alias] as $upload ){

			if( !isset($this->stop[$model->alias][$upload['field']]) ){

				if( !isset($upload['save']) || !is_array( $upload['save'] ) ){
					$model->validationErrors[$upload['field']][] = 'Não há configurações para upload do arquivo';
				}else{

					$tempUploaded = false;

					foreach( $upload['save']  as $index => $save ){

						try{

							if( !is_dir( $save['destination']['folder']) )
								mkdir($save['destination']['folder'], 0755);

							if(! is_writable($save['destination']['folder']) ){
								$model->validationErrors[$upload['field']][] = 'O diretório "'.$save['destination']['folder'].'"" não possui permissão de escrita';
								throw new Exception( 'O diretório "'.$save['destination']['folder'].'"" não possui permissão de escrita');
							}


							$upload['nameFormat'] = isset($upload['nameFormat']) ? $upload['nameFormat'] : null;
							$prefix = isset($save['destination']['prefix']) ? $save['destination']['prefix'] : null;
							$sulfix = isset($save['destination']['sulfix']) ? $save['destination']['sulfix'] : null;
							$upload['fieldSlug'] = isset($upload['fieldSlug']) ? $upload['fieldSlug'] : null;
							
							switch ($upload['nameFormat']) {
								case 'rand':
									$name = $tempUploaded ? $tempUploaded : $prefix.uniqid(rand( 100000, 999999 )).$sulfix.'.'.$this->info[$model->alias][$upload['field']]['extension'];
									$destination = $save['destination']['folder'].'/'.$name;
									break;

								case 'slug':
									$name = $tempUploaded ? $tempUploaded : Inflector::slug(strtolower($model->data[$model->alias][$upload['fieldSlug']]), '-').$sulfix.'.'.$this->info[$model->alias][$upload['field']]['extension'];
									$destination = $save['destination']['folder'].'/'.$name;
									break;
								
								default:
									$name = $tempUploaded ? $tempUploaded : $prefix.$this->info[$model->alias][$upload['field']]['filename'].$sulfix.'.'.$this->info[$model->alias][$upload['field']]['extension'];
									$destination = $save['destination']['folder'].'/'.$name;
									break;
							}

							if( isset( $save['image'] ) ){

								if( $tempUploaded == false ){
									if(move_uploaded_file( $this->files[$model->alias]['tmp_name'][$upload['field']] , $this->tempDir.'/'.$name)){
										$tempUploaded = $name;
									}
								}

								foreach( $save['image'] as $method=>$options ){
									if( $this->handlerImage( $tempUploaded, $method, $options, $destination )){
										$model->data[$model->alias][$upload['field']] = $name;
									}
								}	

							}else{
								if( move_uploaded_file( $this->files[$model->alias]['tmp_name'][$upload['field']] , $destination) ){
									$model->data[$model->alias][$upload['field']] = $name;
						     }
							}
																				
						}catch(Exception $e){
							$model->validationErrors[$upload['field']][] = $e->getMessage();
						}
					
					}

				}

				if($tempUploaded){
					unlink( $this->tempDir.'/'.$tempUploaded );
				}
			}

		}


		if( count($this->errors) == 0  )
			return true;

		return false;
	}

	public function handlerImage( $name, $method, $options, $destination ){

		$image = new ImageHandler();
		$image->load( $this->tempDir.'/'.$name );
		$status = false;

		$grayscale 	 = isset($options['grayscale']) ? $options['grayscale'] : false;
		$width   	 = isset($options['width'])  ? $options['width'] : null;
		$height  	 = isset($options['height']) ? $options['height'] : null;
		$quality 	 = isset($options['quality']) ? $options['quality'] : 80;
		$x			 = isset($options['x']) ? $options['x'] : null;
		$y			 = isset($options['y']) ? $options['y'] : null;
		
		switch ($method) {
			case 'resize':

				$image->resize( $width, $height, $destination, $quality, $grayscale );
				$status = true;
				break;

			case 'crop':
				
				$image->crop( $width, $height, $x, $y, $destination, $quality, $grayscale );
				$status = true;
				break;

			case 'resizeCrop':


				$image->resizeCrop( $width, $height, $destination, $quality, $grayscale );
				$status = true;
				break;
			}

			return $status;

	}


}