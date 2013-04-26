<?php
/**
 * Upload behavior
 *
 * Enables users to easily add file uploading and necessary validation rules
 *
 * PHP versions 4 and 5
 *
 * Copyright 2013, Leonardo Poletto and Araan Branco
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2013, Leonardo Poletto and Araan Branco
 * @package       upload
 * @subpackage    upload.models.behaviors
 * @link          https://github.com/AraanBranco/PUM
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('ImageHandlerBehavior', 'Model/Behavior');
class UploaderBehavior extends ImageHandlerBehavior {

	public $settings = array();
	public $files    = array();
	public $errors   = array();
	public $info	 = array();
	public $tempDir  = '../tmp/uploader';
	public $stop	= array();
	public $post 	= array();

	//Configurações
	public function setup(Model $model, $settings = array()) {

		$this->settings[$model->alias] = $settings;
		$this->files = $_FILES;
		$this->post = $_POST;

	}

	//Classe para adicionar os erros
	public function addError( $model, $field, $message ){
		$this->errors[$model][$field][] = $message;
	}

	//Validação
	public function validate( Model $model  ){

		if( is_array($this->settings) && count($this->settings) > 0){

			foreach( $this->settings[$model->alias] as $upload ){

			$move = isset($this->files[$model->alias]['tmp_name'][$upload['field']]) ? $this->files[$model->alias]['tmp_name'][$upload['field']] : false;

			//Verifica se o campo é requerido ou não
			if( $move == false ){

				if( isset($this->post['Uploader']['required']) && in_array($upload['field'], $this->post['Uploader']['required'])  )
					$this->addError( $model->alias, $upload['field'], 'Selecione uma arquivo' );

				$this->stop[$model->alias][$upload['field']] = true;

			} else {

				$tmp_name  = $this->files[$model->alias]['tmp_name'][$upload['field']];
				$error     = $this->files[$model->alias]['error'][$upload['field']];
				$type 	   = $this->files[$model->alias]['type'][$upload['field']];
				$size 	   = $this->files[$model->alias]['size'][$upload['field']];
				$name 	   = $this->files[$model->alias]['name'][$upload['field']];

				//Verificas os erros
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

				//Checa o tamanho
				$this->info[$model->alias][$upload['field']] = pathinfo( $name );

				//Checa as extenções
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

				if( !isset($upload['save']) || !is_array($upload['save']) ){

					$model->validationErrors[$upload['field']][] = 'Não há configurações para upload do arquivo';

				} else {

					$tempUploaded = false;

					foreach( $upload['save']  as $index => $save ){

						try{

							if( !is_dir( $save['destination']['folder']) )
								mkdir($save['destination']['folder'], 0755);

							if(	!is_writable($save['destination']['folder']) ){

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
									$name = $tempUploaded ? $tempUploaded : $prefix.Inflector::slug(strtolower($model->data[$model->alias][$upload['fieldSlug']]), '-').$sulfix.'.'.$this->info[$model->alias][$upload['field']]['extension'];
									$destination = $save['destination']['folder'].'/'.$name;
									break;
								
								default:
									$name = $tempUploaded ? $tempUploaded : $prefix.$this->info[$model->alias][$upload['field']]['filename'].$sulfix.'.'.$this->info[$model->alias][$upload['field']]['extension'];
									$destination = $save['destination']['folder'].'/'.$name;
									break;
							}

							if( isset($save['image']) ){

								if( $tempUploaded == false ){

									if(move_uploaded_file( $this->files[$model->alias]['tmp_name'][$upload['field']], $this->tempDir.'/'.$name) ){
										$tempUploaded = $name;
									}

								}

								foreach( $save['image'] as $method=>$options ){

									$fileTemp = $save['destination']['folder'].'/'.$name;

									if( file_exists($fileTemp) )
										unlink( $fileTemp );

									if( $this->handlerImage( $tempUploaded, $method, $options, $destination )){
										$model->data[$model->alias][$upload['field']] = $name;
									}

								}	

							} else {

								if( move_uploaded_file( $this->files[$model->alias]['tmp_name'][$upload['field']] , $destination) ){
									$model->data[$model->alias][$upload['field']] = $name;
						    }

							}
																				
						} catch(Exception $e) {
							$model->validationErrors[$upload['field']][] = $e->getMessage();
						}
					
					}

				}

				if($tempUploaded){
					unlink( $this->tempDir.'/'.$tempUploaded );
				}

			}
		}


		if( count($this->errors) == 0 )
			return true;

		return false;
	}

	public function beforeDelete(Model $model, $cascade = true) {

    $data = $model->find('first', array(
			'conditions' => array("{$model->alias}.{$model->primaryKey}" => $model->id),
			'contain' => false,
			'recursive' => -1,
		));
    pr($data);
    exit;

    $image = $save['destination']['folder'].$data[$model->alias][$upload['field']];
	
		if( file_exists( $image ) )
			unlink( $image );
		    	
    return true;
	}

	public function handlerImage( $name, $method, $options, $destination ){

		$image = new ImageHandlerBehavior();
		$image->load( $this->tempDir.'/'.$name );
		$status = false;

		$grayscale 	 	= isset($options['grayscale']) ? $options['grayscale'] : false;
		$width				= isset($options['width'])  ? $options['width'] : null;
		$height  			= isset($options['height']) ? $options['height'] : null;
		$quality 	 		= isset($options['quality']) ? $options['quality'] : 80;
		$x			 			= isset($options['x']) ? $options['x'] : null;
		$y			 			= isset($options['y']) ? $options['y'] : null;
		
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