<?php

class Chess extends CI_Controller{

	private $chess_validator;

	public function __construct(){
	
		parent::__construct();
		$this->chess_validator = new ChessValidator;
	
	}
	
	public function index(){
	
		//black knight is making an illegal move
		$piece = 'BP';
		$old_position = array(2,7); //actual XY coordinate
		$new_position = array(2,6);
		$move_count = 1;
		
		//positions array from database (WHITE IS ON TOP)	
		$positions = array( //(Y co-ordinate goes DOWN, X co-ordinate goes the right
		1 =>array(1=>'WR', 'WN', 'WB', 'WQ', 'WK', 'WB', 'WN', 'WR'),
			array(1=>'WP', 'WP', 'WP', 'WP', 'WP', 'WP', 'WP', 'WP'),
			array(1=>'',    '',   '',   '',   '',   '',   '',   ''),
			array(1=>'',    '',   '',   '',   '',   '',   '',   ''),
			array(1=>'',    '',   '',   '',   '',   '',   '',   ''),
			array(1=>'',    'BP',   '',   '',   '',   '',   '',   ''),
			array(1=>'BP', 'BP', 'BP', 'BP', 'BP', 'BP', 'BP', 'BP'),
			array(1=>'BR', 'BN', 'BB', 'BQ', 'BK', 'BB', 'BN', 'BR'),
		);
		
		$this->chess_validator->setup_board($positions);
		
		$valid_move = $this->chess_validator->validate($piece, $old_position, $new_position, $move_count);
		
		if(!$valid_move){
			var_dump($this->chess_validator->get_errors());
		}else{
			var_dump($valid_move);
		}
	
	}

}