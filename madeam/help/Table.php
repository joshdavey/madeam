<?php
class Help_Table extends Help_Html {
	 /**
   * Create a table from a model's plural data
   *
   * @param array $data
   * @param array $fields
   * @param array $settings
   * @return string
   */
  public static function magic($model, $data, $controller, $select_fields = array(), $settings = array()) {
    $table = null;

    if (isListFormat($data)) {

      if (!isset($settings['edit'])) { $settings['edit']   = $controller . '/edit'; }
      if (!isset($settings['delete'])) { $settings['delete'] = $controller . '/delete'; }

      // open table
      $table .= '<table class="mad_table">';

      // create model instance
      $modelname = 'Model_' . ucfirst($model);
      $inst = new $modelname(1);

      $setup = $inst->get_setup();

      // get fields
      $fields = $setup['schema'];

      // get label
      $label = $inst->get_label();

      // get list of columns from first row
      $columns = array_keys($data[0]);

      $table .= '<tr>';
        $table .= '<th class="mad_table_edit">edit</th>';
        $table .= '<th class="mad_table_delete">del</th>';
        foreach ($columns as $col) {
          if (empty($select_fields) || in_array($col, $select_fields)) {
            $table .= '<th>' . ucfirst($col) . '</th>';
          }
        }
      $table .= '</tr>';

      foreach ($data as $row) {
        $table .= '<tr>';

          $table .= '<td class="mad_table_edit">' . self::link('edit', $settings['edit'] . '/' . $row[$inst->get_primary_key()]) . '</td>';
          $table .= '<td class="mad_table_delete">' . self::link('del', $settings['delete'] . '/' . $row[$inst->get_primary_key()], array('onclick' => "return confirm('Are you sure you want to delete this?')")) . '</td>';

          foreach ($row as $key => $val) {
            if (empty($select_fields) || in_array($key, $select_fields)) {

							if (is_array($val)) {
								if (isset($settings[$key . '_label'])) {
									$new_key = $settings[$key . '_label'];
									$val = $val[$new_key];
								}
							}

							$table .= '<td>' . $val . '</td>';
            }
          }

        $table .= '</tr>';
      }

      // close table
      $table .= '</table>';

      // retrun table
      return $table;
    } else {
      return null;
    }
  }
}
?>