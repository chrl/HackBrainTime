<?php
	namespace ArcheeNic\HackBrainTime;
	abstract class init{
		/**
		 * @var string if set status "stop" - daemon break;
		 */
		protected $status='';
		/**
		 * External libs array (dependency injection adapter)
		 * @var array
		 */
		protected $libs;
		public function __construct($libs=array()){
			$this->libs;
			set_time_limit(0);
		}
		abstract protected function _inner();
		abstract protected function _beforeStart();
		abstract protected function _afterStop();
		protected function process(){
			while(true == true){
				if($this->status=='stop'){
					$this->_afterStop();
					break;
				}
				$this->_inner();
			}
		}
		public function start(){
			$this->status='start';
			$this->_beforeStart();
			$this->process();
		}
		public function stop(){
			$this->status='stop';
		}
	}