<?php
  if($post_type != 'comment_authors') {
    query_posts(array('posts_per_page' => -1, 'order'=>'DESC', 'post_type' => $post_type));//-1 is for all posts
    if($post_type == 'post') {
      $selected_fields = $_POST['post_fld'];
    } else {
      $selected_fields = $_POST['page_fld'];
    }
    $str .= '<table>
              <tr>';
    $str .= '<td colspan=' . count($selected_fields) . '>' . get_bloginfo('name') . '</td>';
    $str .= '</tr>';
    if (have_posts()) {
      $sub_string = '';
      $str .= '<tr>';
      foreach($selected_fields as $field) {
        $str .= '<th>' . $field . '</th>';
        switch($field) {
          case 'Name' : $sub_string .= '<td>[NAME]</td>';
          break;
          case 'Description' : $sub_string .= '<td>[DESC]</td>';
          break;
          case 'Url' : $sub_string .= '<td>[URL]</td>';
          break;
          case 'Created on' : $sub_string .= '<td>[CREATED_ON]</td>';
          break;
          case 'Author' : $sub_string .= '<td>[AUTHOR]</td>';
          break;
          case 'Categories' : $sub_string .= '<td>[CATS]</td>';
          break;
          case 'Tags' : $sub_string .= '<td>[TAGS]</td>';
          break;
          case 'Custom Fields' : $sub_string .= '[CUSTOM_FIELDS]';
          break;
          case 'Status' : $sub_string .= '<td>[Status]</td>';
          break;
        }
      }
      $str .= '</tr>';
      while (have_posts()) {
        the_post();

        global $post;
        $all_cats = '';
        $all_tags = '';
        $custom_fields_string = '';
        
        if(in_array("Categories", $selected_fields)) {
          foreach( (get_the_category()) as $category) {
            $all_cats .= $category->cat_name . ', ';
          }
          $all_cats = substr($all_cats, 0, -2);
        }
        if(in_array("Tags", $selected_fields)) {
          $posttags = get_the_tags();
          if ($posttags) {
            foreach( (get_the_tags()) as $tag) {
              $all_tags .= $tag->name . ', ';
            }
            $all_tags = substr($all_tags, 0, -2);
          }
        }
        if(in_array("Custom Fields", $selected_fields)) {
          $post_custom_fields = get_post_custom($post->ID);
          foreach ( $post_custom_fields as $key => $value ) {
            $custom_fields = get_post_meta($post->ID,$key);
            foreach($custom_fields as $k => $v) {
              if (substr($key,0,1) !== '_') {
                $custom_fields_string .= "<td>" . trim($key) . ": " .  mb_convert_encoding($v,"HTML-ENTITIES", "UTF-8") . "</td>";
              }
            }
          }
        }
        $matches = array('[NAME]', '[DESC]', '[URL]', '[CREATED_ON]', '[AUTHOR]', '[CATS]', '[TAGS]', '[CUSTOM_FIELDS]', '[Status]');
        $replace = array(mb_convert_encoding(get_the_title(), "HTML-ENTITIES", "UTF-8"), mb_convert_encoding($post->post_content, "HTML-ENTITIES", "UTF-8"), get_permalink(), get_the_date("Y-m-d", "", "", FALSE), get_the_author(), $all_cats, $all_tags, $custom_fields_string, $post->post_status);
        $str.= '<tr>' . str_replace($matches, $replace, $sub_string) . '</tr>';
      }
      wp_reset_query();
    } else {
      $str .= '<tr colspan="6"><td>No post found.</td></tr>';
    }
    $str.= '</table><br/></br>';
  } else {
    global $wpdb,$table_prefix;
    $selected_fields = $_POST['comment_authors_fld'];
    $sub_string = '';
    $str .= '<table>
            <tr>';
    $str .= '<td colspan=' . count($selected_fields) . '>' . get_bloginfo('name') . '</td>';
    $str .= '</tr><tr>';
    foreach($selected_fields as $field) {
      $str .= '<th>' . $field . '</th>';
      switch($field) {
        case 'Commenter': 
          $sub_string .= '<td>[COMMENTER]</td>';
        break;
        case 'Email Address' : $sub_string .= '<td>[EMAIL]</td>';
        break;
        case 'Url' : $sub_string .= '<td>[URL]</td>';
        break;
        case 'Created on' : $sub_string .= '<td>[CREATED_ON]</td>';
        break;
        case 'Ip Address' : $sub_string .= '<td>[IP]</td>';
        break;
        case 'Comments' : $sub_string .= '<td>[COMMENTS]</td>';
        break;
      }
    }
    $str .= '</tr>';
    $commenters = $wpdb->get_results("SELECT COUNT(comment_ID) AS count, comment_ID, comment_author, comment_author_email, comment_author_url, comment_date, comment_author_IP FROM $table_prefix"."comments WHERE comment_approved = 1 AND comment_type = '' GROUP BY comment_author, comment_author_email ORDER BY comment_author ASC, comment_date DESC;");
    $matches = array('[COMMENTER]', '[EMAIL]', '[URL]', '[CREATED_ON]', '[IP]', '[COMMENTS]');
    foreach ($commenters as $row)
    {
      $str.= '<tr>' . str_replace($matches, array($row->comment_author, $row->comment_author_email, $row->comment_author_url, $row->comment_date, $row->comment_author_IP, $row->count), $sub_string) . '</tr>';
    }
  }