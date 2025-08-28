<?php
// Show errors while testing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$mysqli = new mysqli("localhost", "uyneengacpnz5", "yq8amjwdkzqf", "db3aciew98guol");

// Check connection
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}
?>
<?php
require_once 'db.php';

// handle category filter and search
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// build query
$params = [];
$sql = "SELECT p.id, p.title, p.excerpt, p.author, p.created_at, c.name as category_name
        FROM posts p
        JOIN categories c ON c.id = p.category_id
        ";
$where = [];
if ($category !== '') {
    $where[] = "c.name = ?";
    $params[] = $category;
}
if ($q !== '') {
    $where[] = "(p.title LIKE ? OR p.content LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY p.created_at DESC LIMIT 50";

$stmt = $mysqli->prepare($sql);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);

// categories for sidebar
$catRes = $mysqli->query("SELECT id, name FROM categories ORDER BY name");
$categories = $catRes->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>MiniBlogger — Home</title>
<style>
/* internal CSS: beautiful, responsive */
:root{
  --bg:#0f1724; --card:#0b1220; --muted:#98a0b2; --accent:#7dd3fc;
  --glass: rgba(255,255,255,0.03);
  --maxw:1100px;
  font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
}
*{box-sizing:border-box}
body{
  margin:0; background:linear-gradient(180deg,#081026 0%, #07121b 60%); color:#e6eef6;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
}
.container{max-width:var(--maxw); margin:32px auto; padding:20px;}
.header{
  display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:24px;
}
.brand{display:flex;align-items:center;gap:12px}
.logo{
  width:56px;height:56px;border-radius:12px;background:linear-gradient(135deg,var(--accent),#60a5fa);
  display:flex;align-items:center;justify-content:center;font-weight:700;color:#04233a;font-size:20px; box-shadow:0 6px 18px rgba(7,15,23,0.6);
}
.title{font-size:20px; font-weight:700; letter-spacing:0.3px}
.subtitle{color:var(--muted); font-size:13px; margin-top:4px}

.controls{display:flex; gap:12px; align-items:center}
.searchBox{background:var(--glass); border:1px solid rgba(255,255,255,0.04); padding:10px 12px; border-radius:10px; display:flex; gap:8px; align-items:center; min-width:260px}
.searchBox input{background:transparent;border:0; outline:0; color:inherit; width:100%}
.btn{
  background:linear-gradient(135deg,#60a5fa,#7dd3fc); color:#04233a; padding:10px 14px; border-radius:10px; border:0; cursor:pointer;
  box-shadow:0 6px 18px rgba(96,165,250,0.12); font-weight:600;
}
.layout{display:grid; grid-template-columns: 1fr 320px; gap:20px;}
.main{display:flex; flex-direction:column; gap:18px}
.card{
  background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
  border-radius:14px; padding:18px; box-shadow: 0 6px 20px rgba(2,6,23,0.6);
  border:1px solid rgba(255,255,255,0.03);
}
.postTitle{font-size:18px; margin:0 0 8px 0; color:#eaf6ff}
.meta{color:var(--muted); font-size:13px; margin-bottom:10px}
.excerpt{color:#d6e6f5; opacity:0.9; line-height:1.5}
.sidebar{display:flex; flex-direction:column; gap:14px}
.categoryList button{
  width:100%; text-align:left; padding:10px 12px;border-radius:10px; border:0; background:transparent; color:var(--muted);
  cursor:pointer; font-weight:600;
}
.categoryList button.active{background:rgba(125,211,252,0.08); color:var(--accent);}

.headerSmall{display:none}

/* responsive */
@media (max-width:920px){
  .layout{grid-template-columns: 1fr; }
  .sidebar{order:2}
  .headerSmall{display:block}
  .controls .btn{padding:8px 10px}
}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div class="brand">
      <div class="logo">MB</div>
      <div>
        <div class="title">MiniBlogger</div>
        <div class="subtitle">A tiny blogging platform — beautiful, fast, and simple</div>
      </div>
    </div>

    <div class="controls">
      <div class="searchBox">
        <form id="searchForm" style="display:flex; gap:8px; width:100%">
          <input name="q" placeholder="Search posts..." value="<?php echo htmlspecialchars($q); ?>" />
          <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>" />
          <button type="submit" class="btn" style="padding:8px 10px">Search</button>
        </form>
      </div>
      <button class="btn" id="newPostBtn">Create Post</button>
    </div>
  </div>

  <div class="layout">
    <div class="main">
      <?php if(!$posts): ?>
        <div class="card">No posts found. Click "Create Post" to publish the first article.</div>
      <?php endif; ?>

      <?php foreach($posts as $p): ?>
        <div class="card">
          <a href="post.php?id=<?php echo $p['id']; ?>" style="text-decoration:none">
            <h3 class="postTitle"><?php echo htmlspecialchars($p['title']); ?></h3>
          </a>
          <div class="meta"><?php echo htmlspecialchars($p['author']); ?> • <?php echo date('M j, Y', strtotime($p['created_at'])); ?> • <?php echo htmlspecialchars($p['category_name']); ?></div>
          <div class="excerpt"><?php echo htmlspecialchars($p['excerpt'] ?: (mb_substr(strip_tags($p['content'] ?? ''),0,180).'...')); ?></div>
          <div style="margin-top:12px; display:flex; gap:8px;">
            <a class="btn" href="post.php?id=<?php echo $p['id']; ?>" style="background:transparent; color:var(--accent); border:1px solid rgba(125,211,252,0.08); box-shadow:none; padding:8px 10px">Read</a>
            <button onclick="redirectToEdit(<?php echo $p['id']; ?>)" class="btn" style="background:rgba(255,255,255,0.03); color:#bfefff; box-shadow:none; padding:8px 10px">Edit</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <aside class="sidebar">
      <div class="card">
        <h4 style="margin:0 0 10px 0">Categories</h4>
        <div class="categoryList">
          <form id="categoryForm">
            <input type="hidden" name="q" value="<?php echo htmlspecialchars($q); ?>">
            <button class="<?php echo $category=='' ? 'active' : ''; ?>" onclick="selectCategory(event,'')">All</button>
            <?php foreach($categories as $c): ?>
              <button class="<?php echo ($category===$c['name']) ? 'active':''; ?>" onclick="selectCategory(event, '<?php echo htmlspecialchars($c['name']); ?>')"><?php echo htmlspecialchars($c['name']); ?></button>
            <?php endforeach; ?>
          </form>
        </div>
      </div>

      <div class="card">
        <h4 style="margin:0 0 10px 0">Quick actions</h4>
        <div style="display:flex;flex-direction:column;gap:8px">
          <button class="btn" onclick="location.href='create.php'">New Post</button>
          <button class="btn" onclick="location.href='index.php'">Home</button>
        </div>
      </div>

      <div class="card" style="text-align:center; color:var(--muted); font-size:13px">MiniBlogger • Lightweight demo</div>
    </aside>
  </div>
</div>

<script>
// inline JS for actions (no external JS)
document.getElementById('newPostBtn').addEventListener('click', function(){ location.href='create.php'; });

function selectCategory(e, name){
  e.preventDefault();
  const f = document.getElementById('categoryForm');
  const qInput = f.querySelector('input[name=q]');
  const params = new URLSearchParams();
  if (name) params.set('category', name);
  if (qInput && qInput.value) params.set('q', qInput.value);
  location.href = 'index.php?' + params.toString();
}

function redirectToEdit(id){
  // Use JS to redirect to create.php with edit id — user will be prompted for password in edit mode
  location.href = 'create.php?edit=' + encodeURIComponent(id);
}
</script>
</body>
</html>
