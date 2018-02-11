<?php
class User {
	public $_id;
	public $_name;
	public $_gender;
	public $_admin;


	public function __construct() {
	}

  public function SetID($id){
    $this->_id = $id;
  }
  public function SetName($name){
    $this->_name = $name;
  }
	public function GetName() {
		return $this->_name;
	}
	public function SetGender($gender){
		$this->_gender = $gender;
	}
	public function GetGender() {
		return $this->_gender;
	}
	public function SetAdmin($admin){
		$this->_admin = $admin;
	}
	public function GetAdmin() {
		return $this->_admin;
	}
}
?>
