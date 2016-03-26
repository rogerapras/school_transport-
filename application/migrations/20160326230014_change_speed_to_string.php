<?php

class Migration_Change_speed_to_string extends CI_Migration {

  public function up() {
    $this->dbforge->modify_column('locations', array('speed' => array('name' => 'speed', 'type' => 'VARCHAR', 'constraint' => 255)));
  }

  public function down() {
    $this->dbforge->modify_column('locations', array('speed' => array('name' => 'speed', 'type' => 'INT', 'constraint' => 11)));
  }
}

?>
