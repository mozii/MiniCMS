<?php
require 'head.php';

$post_id      = '';
$post_state   = '';
$post_title   = '';
$post_content = '';
$post_tags    = array();
$error_msg    = '';
$succeed      = false;
  
if (isset($_POST['_IS_POST_BACK_'])) {
  $post_id      = $_POST['id'];
  $post_state   = $_POST['state'];
  $post_title   = trim($_POST['title']);
  $post_content = trim($_POST['content']);
  $post_tags    = explode(',', trim($_POST['tags']));
  $post_date    = date("Y-m-d");
  $post_time    = date("H:i:s");
  
  if ($post_title == '') {
    $error_msg = '文章标题不能为空';
  }
  else {
    if ($post_id == '') {
      $file_names = shorturl($post_title);
      
      foreach ($file_names as $file_name) {
        $file_path = '../mc-files/posts/data/'.$file_name.'.dat';
        
        if (!is_file($file_path)) {
          $post_id = $file_name;
          break;
        }
      }
    }
    else {
      $file_path = '../mc-files/posts/data/'.$post_id.'.dat';
  
      $data = unserialize(file_get_contents($file_path));
      
      $post_old_state = $data['state'];
      
      if ($post_old_state != $post_state) {
        $index_file = '../mc-files/posts/index/'.$post_old_state.'.php';
        
        require $index_file;
        
        unset($mc_posts[$post_id]);
        
        file_put_contents($index_file,
          "<?php\n\$mc_posts=".var_export($mc_posts, true)."\n?>"
        );
      }
    }
    
    $data = array(
      'id'    => $post_id,
      'state' => $post_state,
      'title' => $post_title,
      'tags'  => $post_tags,
      'date'  => $post_date,
      'time'  => $post_time,
    );
    
    $index_file = '../mc-files/posts/index/'.$post_state.'.php';
    
    require $index_file;
    
    $mc_posts[$post_id] = $data;
    
    file_put_contents($index_file,
      "<?php\n\$mc_posts=".var_export($mc_posts, true)."\n?>"
    );
    
    $data['content'] = $post_content;
    
    file_put_contents($file_path, serialize($data));
  }
} else if (isset($_GET['id'])) {
  $file_path = '../mc-files/posts/data/'.$_GET['id'].'.dat';
  
  $data = unserialize(file_get_contents($file_path));
  
  $post_id      = $data['id'];
  $post_state   = $data['state'];
  $post_title   = $data['title'];
  $post_content = $data['content'];
  $post_tags    = $data['tags'];
}
?>
<script type="text/javascript">
function empty_textbox_focus(target){
  if (target.temp_value != undefined && target.value != target.temp_value)
    return;
  
  target.temp_value = target.value;
  target.value='';
  target.style.color='#000';
}

function empty_textbox_blur(target) {
  if (target.value == '') {
    target.style.color='#888';
    target.value = target.temp_value;
  }
}
</script>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
  <input type="hidden" name="_IS_POST_BACK_" value=""/>
  <?php if ($succeed) { ?>
  <div class="updated">文章已保存！ <a href="#">查看</a></div>
  <?php } ?>
  <div class="admin_page_name">
  <?php if ($post_id == '') echo "撰写文章"; else echo "编辑文章"; ?>
  </div>
  <div style="margin-bottom:20px;">
    <input name="title" type="text" class="edit_textbox" value="<?php
    if ($post_title == "") {
      echo '在此输入标题" " style="color:#888;" onfocus="empty_textbox_focus(this)" onblur="empty_textbox_blur(this)';
    }
    else {
      echo htmlspecialchars($post_title);
    }
    ?>"/>
  </div>
  <div style="margin-bottom:20px;">
    <textarea name="content" class="edit_textarea"><?php echo htmlspecialchars($post_content); ?></textarea>
  </div>
  <div style="margin-bottom:20px;">
    <input name="tags" type="text" class="edit_textbox" value="<?php
    if (count($post_tags) == 0) {
      echo '在此输入标签，多个标签用英语逗号(,)分隔" " style="color:#888;" onfocus="empty_textbox_focus(this)" onblur="empty_textbox_blur(this)';
    }
    else {
      echo htmlspecialchars(implode(',', $post_tags));
    }
    ?>"/>
  </div>
  <div style="margin-bottom:20px;text-align:right">
    状态：
    <select name="state" style="width:100px;">
      <option value="draft" <?php if ($post_state == 'draft') echo 'selected="selected"'; ?>>草稿</option>
      <option value="publish" <?php if ($post_state == 'publish') echo 'selected="selected"'; ?>>发布</option>
    </select>
  </div>
  <div style="text-align:right">
    <input type="hidden" name="id" value="<?php echo $post_id; ?>"/>
    <input type="submit" name="save" value="保存" style="padding:6px 20px;"/>
  </div>
</form>
<?php require 'foot.php' ?>