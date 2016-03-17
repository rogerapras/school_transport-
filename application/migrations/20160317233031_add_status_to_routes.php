<?php

class Migration_Add_status_to_routes extends CI_Migration {

  public function up() {
    $fields = array(
      'status' => array('type' => 'INT', 'constraint' => 2, 'default' => 0)
    );

    $this->dbforge->add_column('routes', $fields);
  }

  public function down() {
    $this->dbforge->remove_column('routes', 'status');
  }
}

?>
