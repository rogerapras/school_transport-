<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_fields_to_users extends CI_Migration {

  public function up() {
    $fields = array(
      'username' => array('type' => 'VARCHAR', 'constraint' => 100),
      'salt' => array('type' => 'VARCHAR', 'constraint' => 100)
    );
    if($this->dbforge->add_column('users', $fields)) {
      echo 'Added username and salt columns to users table';
    }
  }

  public function down() {
    $this->dbforge->drop_column('users', 'username');
    $this->dbforge->drop_column('users', 'salt');
  }
}
