<?php
require_once 'db.php';
$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$post = null;
$editing = false;
if ($editId) {
    $stmt = $mysqli->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param('i',$editId); $stmt->execute(); $res = $stmt->get_result();
    $post = $res->fetch_assoc();
    if ($post) $editing = true;
}
$catRes = $mysqli->query("SELECT id, name FROM categories ORDER BY name");
$categories = $catRes->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo $editing ? 'Edit Post' : 'Create Post'; ?> — MiniBlogger</title>
<style>
:root{--bg:#071022; --panel:#0b1624; --accent:#7dd3fc; --muted:#9fb0c6}
body{margin:0; font-family:Inter, system-ui, Arial; background:linear-gradient(180deg,#071027 0%, #051022 100%); color:#e8f7ff}
.wrap{max-width:980px;margin:24px auto;padding:18px}
.header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
.logo{width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#60a5fa);display:flex;align-items:center;justify-content:center;font-weight:800;color:#04233a}
.card{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 30px rgba(3,8,20,0.6); border:1px solid rgba(255,255,255,0.03)}
.formRow{display:flex;gap:12px; margin-bottom:12px; align-items:center}
.formRow .col{flex:1}
.input, select{width:100%; padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,0.03); background:transparent; color:inherit}
.toolbar{display:flex; gap:8px; margin-bottom:12px}
.toolBtn{padding:8px 10px; border-radius:8px; border:0; cursor:pointer; background:rgba(255,255,255,0.03); color:var(--muted)}
.editor{
  min-height:300px; padding:16px; border-radius:10px; border:1px solid rgba(255,255,255,0.03); background:rgba(255,255,255,0.01); overflow:auto;
  color:#e6f7ff;
}
.note{color:var(--muted); font-size:13px; margin-top:8px}
.actions{display:flex; gap:8px; margin-top:12px}
@media (max-width:760px){ .formRow{flex-direction:column; align-items:stretch} }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div style="display:flex; gap:12px; align-items:center">
      <div class="logo">MB</div>
      <div>
        <div style="font-weight:800; font-size:18px"><?php echo $editing ? 'Edit Post' : 'Create Post'; ?></div>
        <div class="note">Use the editor below to format content — bold, italic, lists, links and images.</div>
      </div>
    </div>
    <div><a class="toolBtn" href="index.php" style="text-decoration:none; color:var(--muted);">Back to Home</a></div>
  </div>

  <div class="card">
    <form id="postForm" method="post" action="save_post.php">
      <input type="hidden" name="id" value="<?php echo $editing ? intval($post['id']) : ''; ?>">
      <div class="formRow">
        <div class="col">
          <input class="input" name="title" placeholder="Post title" required value="<?php echo $editing ? htmlspecialchars($post['title']) : ''; ?>">
        </div>
        <div style="width:220px">
          <select name="category_id" class="input" required>
            <?php foreach($categories as $c): ?>
              <option value="<?php echo $c['id']; ?>" <?php echo ($editing && $post['category_id']==$c['id']) ? 'selected':''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="formRow">
        <div style="width:280px">
          <input class="input" name="author" placeholder="Author name" required value="<?php echo $editing ? htmlspecialchars($post['author']) : ''; ?>">
        </div>
        <div class="col">
          <input class="input" name="excerpt" placeholder="Excerpt (short summary, optional)" value="<?php echo $editing ? htmlspecialchars($post['excerpt']) : ''; ?>">
        </div>
      </div>

      <div>
        <div class="toolbar" role="toolbar">
          <button type="button" class="toolBtn" onclick="cmd('bold')"><b>B</b></button>
          <button type="button" class="toolBtn" onclick="cmd('italic')"><i>I</i></button>
          <button type="button" class="toolBtn" onclick="cmd('insertUnorderedList')">• List</button>
          <button type="button" class="toolBtn" onclick="cmd('createLink', prompt('Enter URL','https://'))">Link</button>
          <button type="button" class="toolBtn" onclick="insertImage()">Image</button>
        </div>

        <div id="editor" class="editor" contenteditable="true" spellcheck="true">
          <?php echo $editing ? $post['content'] : '<p>Write your post here — use the toolbar to format.</p>'; ?>
        </div>
      </div>

      <div style="margin-top:12px">
        <input type="password" name="post_password" placeholder="<?php echo $editing ? 'Enter post password to save edits (leave blank to keep current)' : 'Set a post password (used to edit/delete)'; ?>" class="input">
        <div class="note">Keep your post password safe — you'll need it to edit/delete the post.</div>
      </div>

      <div class="actions">
        <button type="button" class="btn" onclick="submitForm()">Publish</button>
        <button type="button" class="toolBtn" onclick="location.href='index.php'">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
// inline JS: get editor HTML into a hidden field then submit via normal POST (server will redirect using JS)
function cmd(command, value=null){
  document.execCommand(command, false, value);
  document.getElementById('editor').focus();
}

function insertImage(){
  const url = prompt('Image URL (absolute):');
  if(url) document.execCommand('insertImage', false, url);
}

function submitForm(){
  // create a temporary textarea to hold the HTML content
  const form = document.getElementById('postForm');
  let html = document.getElementById('editor').innerHTML;
  // create hidden element
  let input = document.querySelector('input[name="content_html"]');
  if (!input) {
    input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'content_html';
    form.appendChild(input);
  }
  input.value = html;
  form.submit();
}

// if editing but leaving password blank, server will preserve existing password
</script>
</body>
</html>
