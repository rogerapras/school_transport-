<?php

class Migration_Add_roll_number_to_users extends CI_Migration {

  public function up() {

    $fields = array(
      'roll_number' => array('type' => 'VARCHAR', 'constraint' => 50, 'default' => '')
    );

    $this->dbforge->add_column('users', $fields);
  }


  public function down() {

    $this->dbforge->drop_column('users', 'roll_number');
  }

}
