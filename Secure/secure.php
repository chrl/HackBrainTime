<?php

	/**
	 * Class secure
	 * Отлов возможных атак
	 */
	class Secure{
		/**
		 * тип атаки (определяется через метод setAttackType)
		 * @var string
		 */
		protected $attackType='unidentified';
		/**
		 * Конечная часть названия файла.
		 * @var string
		 */
		protected $postfix='.log';
		/**
		 * Абсолютный путь до места сохранения логов
		 * @var string
		 */
		protected $absPath='';
		/**
		 * Типы отлавливаемых входящих данных get,post,file
		 * @var string
		 */
		protected $dataTypes='';

		/**
		 * secure constructor.
		 *
		 * @param string $dataTypes Типы отлавливаемых входящих данных get,post,file
		 * @param string $absPath Абсолютный путь до места сохранения логов
		 * @param string $postfix Конечная часть названия файла
		 */
		function __construct($dataTypes='get,post,files',$absPath='',$postfix='.log'){
			$this->setVars($dataTypes,$absPath,$postfix);
			$this->fireTrigger();
		}

		//region Простые операции по назначению атрибутов классов
		/**
		 * Назначение абсолютного пути до места сохранения логов
		 * @param $absPath
		 */
		protected function setPath($absPath){
			if(!$absPath){
				$absPath=__DIR__;
			}
			$absPath=trim($absPath);
			$absPath=rtrim($absPath,'/');
			$this->absPath=$absPath;
		}

		/**
		 * Назначение атрибутов классов
		 * @param $dataTypes
		 * @param $absPath
		 * @param $postfix
		 */
		protected function setVars($dataTypes,$absPath,$postfix){
			$this->setPath($absPath);
			$this->setAttackType();
			if(!$dataTypes){
				// TODO: Лучше потом переделать в исключение
				print 'undefined var $types';
				exit;
			}
			$this->dataTypes=explode(',',$dataTypes);
			if(!$dataTypes){
				// TODO: Лучше потом переделать в исключение
				print 'undefined var $dataTypes';
				exit;
			}
			$this->postfix=$postfix;
		}
		//endregion

		//region Определение типа атаки
		/**
		 * Есть ли попвытка пролезть через админку
		 * @return bool
		 */
		protected function _attackType_wpadmin(){
			$request=$_SERVER['REQUEST_URI'];
			return (strpos('wp_admin',$request)!==false);
		}

		/**
		 * Есть ли попытка пролезть через главную страницу
		 * @return bool
		 */
		protected function _attackType_index(){
			$request=$_SERVER['REQUEST_URI'];
			return (strpos('index.php',$request)!==false);
		}

		/**
		 * Назначение типа
		 */
		protected function setAttackType(){
			if($this->_attackType_wpadmin()){
				$this->attackType='wpadmin';
			}elseif($this->_attackType_index()){
				$this->attackType='index';
			}
		}
		//endregion

		//region Работа с файлом и мсодержимым лога
		/**
		 * Формирование строки лога
		 * @param string $separator
		 *
		 * @return string
		 */
		protected function logString($separator="\t"){
			$data=array();
			$data[]=time();
			$data[]='POST';
			$data[]=json_encode($_POST);
			$data[]='GET';
			$data[]=json_encode($_GET);
			$data[]='PHPSELF';
			$data[]=$_SERVER ['PHP_SELF'];
			return implode($separator,$data);
		}

		/**
		 * Сохранение лога
		 */
		protected function saveLog(){
			$file=$this->absPath.'/'.$this->attackType.$this->postfix;
			file_put_contents($file,$this->logString()."\r\n",FILE_APPEND);
		}
		//endregion

		/**
		 * Вызвать событие по типу входящих данных
		 */
		protected function fireTrigger(){
			// TODO: Не гуд что повторы. Можно оптимизировать. Не тратил время на обдумывание
			if(in_array('get',$this->dataTypes)&&!empty($_GET)){
				$this->saveLog();
			}elseif(in_array('files',$this->dataTypes)&&!empty($_FILES)){
				$this->saveLog();
			}elseif(in_array('post',$this->dataTypes)&&!empty($_POST)){
				$this->saveLog();
			}
		}
	}
