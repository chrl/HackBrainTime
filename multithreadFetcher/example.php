<?php
	include_once(__DIR__.'/init.php');
	use ArcheeNic\HackBrainTime;

	class daemon extends HackBrainTime\init{
		protected function _inner(){
			if(file_exists('test.txt')){
				unlink('test.txt');
				echo 'I removed file';
			}
			if(file_exists('stop.txt')){
				$this->stop();
			}
		}

		protected function _beforeStart(){
			ob_implicit_flush(true);
		}

		protected function _afterStop(){
			ob_implicit_flush(false);
		}
	}

	$daemon=new daemon();
	$daemon->start();