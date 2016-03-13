<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_more_fields_to_users extends CI_Migration {

  public function up() {
    $fields = array(
      'password' => array('type' => 'VARCHAR', 'constraint' => 50, 'default' => ""),
      'phone' => array('type' => 'VARCHAR', 'constraint' => 15, 'default' => ""),
      'allowed' => array('type' => 'BOOLEAN', 'default' => true),
      'attending' => array('type' => 'BOOLEAN', 'default' => true),
      'token' => array('type' => 'VARCHAR', 'constraint' => 255, 'default' => "")
    );

    if($this->dbforge->add_column('users', $fields)) {
      echo '<br />Added Phone(String), Allowed(Bool), Attending(Bool) field to users table <br />';
    }
  }

  public function down() {
    $this->dbforge->drop_column('users', 'attending');
    $this->dbforge->drop_column('users', 'allowed');
    $this->dbforge->drop_column('users', 'phone');
    $this->dbforge->drop_column('users', 'token');
    $this->dbforge->drop_column('users', 'password');
  }

}
