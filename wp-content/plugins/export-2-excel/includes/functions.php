<?php
 /**
  * Displays radio buttons
  */
  function e2e_display_radio_buttons($selection_array, $radio_button_name, $content_type = '') {
    $count = 0;
    $disabled = '';
    $divisor = '';
    if (!array($selection_array)) {
      return false;
    }
    foreach ($selection_array as $sel_key => $sel_val) {
      $divisor = 3;
      if ($count % $divisor == 0) { ?>
        <kbd> <?php
      }
      if ($content_type == $sel_key) {
        $selected = 'checked="true"';
      } else {
        $selected = '';
      } ?>
      <i>
        <input type="radio" <?php echo $selected; ?>  class="<?php echo $radio_button_name; ?>" name="<?php echo $radio_button_name; ?>"  id="<?php echo $sel_key; ?>" value="<?php echo $sel_key; ?>" >
      </i>
      <small><?php echo $sel_val; ?></small> <?php
      $count++;
      if ($count % $divisor == 0 ) { ?>
        </kbd> <?php
      }
    }
  }

  function e2e_display_multiselect($sel_array, $id , $size = '5') {
    if (!array($sel_array)) {
      return false;
    } ?>
    <select name="<?php echo $id . '[]'; ?>" id="<?php echo $id; ?>" multiple="multiple" size="<?php echo $size; ?>"> <?php
      foreach ($sel_array as $key => $val) {
        echo '<option value="' . $key . '">' . $val . '</option>';
      } ?>
    </select><?php
  }