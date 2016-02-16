<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_type_to_users extends CI_Migration {

  public function up() {
    $fields = array(
      'type' => array('type' => 'VARCHAR', 'constraint' => 20, 'default' => "")
    );
    if($this->dbforge->add_column('users', $fields)) {
      echo '<br /> Added "type" field to users table.<br />';
    }
  }

  public function down() {
    $this->dbforge->drop_column('users', 'type');
  }
}
