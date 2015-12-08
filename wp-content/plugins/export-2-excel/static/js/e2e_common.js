/**
  * This function is used to validate form data
  */
  function validate_form() {
    var post_type = jQuery("input[name=e2e_post_type]:checked").val();
    if (typeof(post_type) == 'undefined') {
      alert ('Please select a selection criteria');
      return false;
    } else if (post_type == 'post') {
      var post_flds = jQuery('select#post_fld').val();
      if (post_flds == null) {
        alert ('Please select fields.');
        return false;
      }
    } else if(post_type == 'page') {
      var page_flds = jQuery('select#page_fld').val();
      if (page_flds == null) {
        alert ('Please select fields.');
        return false;
      }
    } else if(post_type == 'comment_authors') {
      var comments_flds = jQuery('select#comment_authors_fld').val();
      if (comments_flds == null) {
        alert ('Please select fields.');
        return false;
      }
    }
    var ext = jQuery("input[name=ext]:checked").val();
    if (typeof(ext) == 'undefined') {
      alert ('Please select an extension');
      return false;
    }
    return true;
  }
  
  jQuery(document).ready(function() {
    jQuery("#slctn_crt").click(function() {
      var criteria = jQuery('input:radio[name=e2e_post_type]:checked').val();
      if (criteria == 'post') {
        jQuery("#post_fld_row").css({"display":"block"});
        jQuery("#comment_authors_fld_row").css({"display":"none"});
        jQuery("#comment_authors_fld option:selected").removeAttr("selected");
        jQuery("#page_fld_row").css({"display":"none"});
        jQuery("#page_fld_row option:selected").removeAttr("selected");
      } else if (criteria == 'page') {
        jQuery("#page_fld_row").css({"display":"block"});
        jQuery("#comment_authors_fld_row").css({"display":"none"});
        jQuery("#comment_authors_fld option:selected").removeAttr("selected");
        jQuery("#post_fld_row").css({"display":"none"});
        jQuery("#post_fld option:selected").removeAttr("selected");
      } else {
        jQuery("#post_fld_row").css({"display":"none"});
        jQuery("#comment_authors_fld_row").css({"display":"block"});
        jQuery("#post_fld option:selected").removeAttr("selected");
        jQuery("#page_fld_row").css({"display":"none"});
        jQuery("#page_fld_row option:selected").removeAttr("selected");
      }
    });
  });