<?php
App::uses('AppModel', 'Model');
/**
 * Phrase Model
 *
 */
class Fake extends AppModel {

	// Exemplo de Upload com UniqID no nome;
	public $actsAs = array(
 		'Uploader' => array(
 			array(
				'nameFormat' => 'rand',
      	'allowedExtensions' => array('jpeg', 'jpg', 'png'),
      	'maxsize' => 5120,
	 			'field' => 'image',
	 			'save' => array(
	 				'cover' => array(
 						'destination' => array('folder' => 'img/teste'),
					),
 				)
			),
 		),
	);

	// Exemplo de Upload com Slug no nome;
	public $actsAs = array(
 		'Uploader' => array(
 			array(
				'nameFormat' => 'slug',
				'fieldSlug' => 'nome', // Nome do campo a ser slug
      	'allowedExtensions' => array('jpeg', 'jpg', 'png'),
      	'maxsize' => 5120,
	 			'field' => 'image',
	 			'save' => array(
	 				'cover' => array(
 						'destination' => array('folder' => 'img/teste')
					),
 				)
			),
 		),
	);

}