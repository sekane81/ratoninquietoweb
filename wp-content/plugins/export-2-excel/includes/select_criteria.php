<?php
  ini_set('memory_limit', '256M');
  ini_set('max_execution_time', 300); //300 seconds = 5 minutes
  $post_field_criteria = array( 'Name' => 'Name', 'Description' => 'Description', 'Url' => 'Url', 'Created on' => 'Created on', 'Author' => 'Author', 'Categories' => 'Categories', 'Tags' => 'Tags', 'Status' => 'Status', 'Custom Fields' => 'Custom Fields');
  $page_field_criteria = array( 'Name' => 'Name', 'Description' => 'Description', 'Url' => 'Url', 'Created on' => 'Created on', 'Author' => 'Author', 'Status' => 'Status', 'Custom Fields' => 'Custom Fields');
  $comment_field_criteria = array( 'Commenter' => 'Commenter', 'Email Address' => 'Email Address', 'Url' => 'Url', 'Created on' => 'Created on', 'Ip Address' => 'IP Address', 'Comments' => 'Comments');
  $extensions = array('xls' => '.xls', 'xlsx' => '.xlsx');
  $args = array (
      'public'   => true
  );
  $output = 'objects';
  $post_types = get_post_types($args, $output);


  if ( isset($_POST['Submit']) ) {
    if (empty($_POST) || !check_admin_referer('e2e_export_data' )  ) {
      wp_die('Sorry, your nonce did not verify.');
    } elseif (!isset($_POST['ext']) || !array_key_exists($_POST['ext'], $extensions)  ) {
      wp_die('Please select a valid extension.');
    }  elseif (!isset($_POST['e2e_post_type']) || ( !array_key_exists($_POST['e2e_post_type'], $post_types ) && $_POST['e2e_post_type'] != 'comment_authors'   && $_POST['e2e_post_type'] != 'attachment') ) {
      wp_die('Please select a post type.');
    }  elseif (!isset($_POST['post_fld']) && ($_POST['e2e_post_type'] == 'post')) { 
      wp_die('Please select fields.');
    } elseif ( !isset($_POST['page_fld']) && ($_POST['e2e_post_type'] == 'page')) {
      wp_die('Please select fields.');
    } elseif (!isset($_POST['comment_authors_fld']) && ($_POST['e2e_post_type'] == 'comment_authors')) {
      wp_die('Please select fields.');
    } else {
      $post_type = $_POST['e2e_post_type'];
      $ext = $_POST['ext'];
      $str = '';
      if ( is_multisite() && $network_admin ) {
        $blog_info = get_blog_list(0, 'all');
        foreach ($blog_info as $blog) {
          switch_to_blog($blog['blog_id']);
          include('loop.php');
          restore_current_blog();
        }
      } else {
        include('loop.php');
      }
      $filename = sanitize_file_name(get_bloginfo('name') ) . '.' . $ext;
      if ( $ext == 'xls' ) {
        header("Content-type: application/vnd.ms-excel;");
      } elseif( $ext == 'xlsx') {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, charset=utf-8;");
      }
      header("Content-Disposition: attachment; filename=" . $filename);
       print $str;//$str variable is used in loop.php
      exit();
    }
  } else { ?>
    <form name="export" action="<?php echo $form_action; ?>" method="post" onsubmit="return validate_form();">
      <div class="selection_criteria" >
        <div class="popupmain" style="float:left;">
          <p class="req_head"><?php echo 'Choose your criteria';?></p>
          <div class="formfield">
            <p class="row1" id="slctn_crt">
              <label><?php echo 'Selection Criteria:';?></label>
              <em>

              <?php
                $count = 0;
                foreach ($post_types  as $key => $post_type ) {
                  $divisor = 3;
                  if ($count % $divisor == 0) { ?>
                    <kbd> <?php
                  }
                  if ( $post_type->name != 'attachment' ) { ?>
                    <i>
                      <input type="radio" class="post_type" name="e2e_post_type" value="<?php echo $post_type->name; ?>"  />
                    </i>
                    <small>
                      <?php echo $post_type->label; ?>
                    </small> <?php
                  }
                  $count++;
                  if( $count == count($post_types) ){?>
                     <i>
                      <input type="radio" class="post_type" name="e2e_post_type" value="comment_authors"  />
                     </i>
                     <small>
                       Comments Authors
                     </small>
                  <?php
                  }
                  if ($count % $divisor == 0 ) { ?>
                    </kbd> <?php
                  }
                } ?>
              </em>
            </p>
            <p class="row1" id="post_fld_row">
              <label><?php echo 'Select Fields: '; ?></label>
              <em> <?php
                e2e_display_multiselect($post_field_criteria, 'post_fld', '9'); ?>
              </em>
            </p>
            <p class="row1" id="page_fld_row">
              <label><?php echo 'Select Fields: '; ?></label>
              <em> <?php
                e2e_display_multiselect($page_field_criteria, 'page_fld', '7'); ?>
              </em>
            </p>
            <p class="row1" id="comment_authors_fld_row">
              <label><?php echo 'Select Fields: '; ?></label>
              <em> <?php
                e2e_display_multiselect($comment_field_criteria, 'comment_authors_fld', '6'); ?>
              </em>
            </p>
            <p class="row1">
              <label><?php echo 'Select extension:'; ?></label>
              <em> <?php
                e2e_display_radio_buttons($extensions, 'ext'); ?>
              </em>
            </p>
            <?php wp_nonce_field('e2e_export_data'); ?>

            <p class="row1">
              <label>&nbsp;</label>
              <em>
                <input type="submit" class="button-primary" name="Submit" value="Submit" />
              </em>
            </p>
          </div>
        </div>
      </div>
    </form><?php
  }