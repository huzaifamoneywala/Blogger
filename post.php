<?php
require_once 'db.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { header('Location: index.php'); exit; }

// fetch post
$stmt = $mysqli->prepare("SELECT p.*, c.name as category_name FROM posts p JOIN categories c ON c.id = p.category_id WHERE p.id = ?");
$stmt->bind_param('i',$id); $stmt->execute(); $res = $stmt->get_result();
$post = $res->fetch_assoc();
if (!$post) { echo "Post not found."; exit; }

// fetch comments
$cm = $mysqli->prepare("SELECT name, content, created_at FROM comments WHERE post_id = ? ORDER BY created_at ASC");
$cm->bind_param('i',$id); $cm->execute(); $comments = $cm->get_result()->fetch_all(MYSQLI_ASSOC);

// related posts (same category)
$rp = $mysqli->prepare("SELECT id, title FROM posts WHERE category_id = ? AND id != ? ORDER BY created_at DESC LIMIT 4");
$rp->bind_param('ii', $post['category_id'], $id); $rp->execute(); $related = $rp->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo htmlspecialchars($post['title']); ?> — MiniBlogger</title>
<style>
:root{--bg:#081026; --card:#071021; --accent:#7dd3fc; --muted:#9fb0c6}
body{margin:0; font-family:Inter, system-ui, Arial; background:var(--bg); color:#eaf6ff; -webkit-font-smoothing:antialiased;}
.wrap{max-width:900px;margin:28px auto;padding:20px;}
.top{display:flex;align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px}
.brand{display:flex;align-items:center;gap:10px}
.logo{width:46px;height:46px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#60a5fa);display:flex;align-items:center;justify-content:center;color:#04233a;font-weight:800}
.title{font-size:20px;font-weight:700}
.meta{color:var(--muted); font-size:13px; margin-top:6px}
.card{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:18px; box-shadow:0 8px 30px rgba(2,6,23,0.6); border:1px solid rgba(255,255,255,0.03)}
.content{margin-top:12px; color:#d7ecff; line-height:1.7}
.tools{display:flex;gap:8px;margin-top:18px}
.btn{background:linear-gradient(135deg,#60a5fa,#7dd3fc); color:#04233a; padding:8px 12px; border-radius:8px; border:0; cursor:pointer; font-weight:600}
.secondary{background:transparent;color:var(--muted);border:1px solid rgba(255,255,255,0.03)}
.commentBox{margin-top:16px;padding:12px;border-radius:10px;background:rgba(255,255,255,0.02)}
.comment{border-top:1px dashed rgba(255,255,255,0.03); padding:10px 0; color:#dbeffd}
.related{display:flex;flex-direction:column; gap:8px; margin-top:10px}
@media (max-width:700px){
  .top{flex-direction:column; align-items:flex-start}
}
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <div class="brand">
      <div class="logo">MB</div>
      <div>
        <div class="title"><?php echo htmlspecialchars($post['title']); ?></div>
        <div class="meta"><?php echo htmlspecialchars($post['author']); ?> • <?php echo date('M j, Y', strtotime($post['created_at'])); ?> • <?php echo htmlspecialchars($post['category_name']); ?></div>
      </div>
    </div>
    <div class="tools">
      <a class="btn" href="index.php">Back</a>
      <button class="btn secondary" onclick="location.href='create.php?edit=<?php echo $post['id']; ?>'">Edit</button>
      <button class="btn secondary" onclick="promptDelete(<?php echo $post['id']; ?>)">Delete</button>
    </div>
  </div>

  <div class="card">
    <div class="content"><?php echo $post['content']; ?></div>

    <div style="margin-top:18px; display:flex; gap:18px; align-items:flex-start; justify-content:space-between">
      <div style="flex:1">
        <h4 style="margin-bottom:8px">Comments (<?php echo count($comments); ?>)</h4>
        <div class="commentBox">
          <form id="commentForm" method="post" action="save_comment.php">
            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
            <div style="display:flex; gap:8px; margin-bottom:8px;">
              <input name="name" placeholder="Your name" required style="flex:1;padding:8px;border-radius:8px;border:1px solid rgba(255,255,255,0.03); background:transparent; color:inherit;">
            </div>
            <div>
              <textarea name="content" placeholder="Write a nice comment..." required style="width:100%;min-height:90px;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,0.03);background:transparent;color:inherit"></textarea>
            </div>
            <div style="margin-top:8px"><button class="btn" type="submit">Post Comment</button></div>
          </form>
        </div>

        <div style="margin-top:12px">
          <?php foreach($comments as $c): ?>
            <div class="comment">
              <div style="font-weight:700"><?php echo htmlspecialchars($c['name']); ?> <span style="color:var(--muted); font-weight:400; font-size:12px">• <?php echo date('M j, Y H:i', strtotime($c['created_at'])); ?></span></div>
              <div style="margin-top:6px;"><?php echo nl2br(htmlspecialchars($c['content'])); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <aside style="width:240px">
        <div>
          <h4 style="margin:0 0 8px 0">Related posts</h4>
          <div class="related">
            <?php foreach($related as $r): ?>
              <a href="post.php?id=<?php echo $r['id']; ?>" style="text-decoration:none; color:var(--accent)"><?php echo htmlspecialchars($r['title']); ?></a>
            <?php endforeach; ?>
            <?php if(empty($related)) echo "<div style='color:var(--muted)'>No related posts</div>"; ?>
          </div>
        </div>
      </aside>
    </div>
  </div>
</div>

<script>
function promptDelete(id){
  const pass = prompt('To delete this post enter the post password (set during create).');
  if (!pass) return;
  const params = new URLSearchParams();
  params.set('id', id);
  params.set('password', pass);
  // Redirect via JS to delete_post.php using GET for convenience (server checks)
  location.href = 'delete_post.php?' + params.toString();
}
</script>
</body>
</html>
