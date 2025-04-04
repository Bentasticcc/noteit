<?php
session_start();
include "db.php";

// Add note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $dot = $conn->real_escape_string($_POST['dot']);
    $current_date = date('Y-m-d'); 
    $sql = "INSERT INTO notes (title, content, dot, date) VALUES ('$title', '$content', '$dot', '$current_date')";
    $conn->query($sql);
    header("Location: code.php");
    exit;
}

// Update note - Add this new section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_note'])) {
    $id = intval($_POST['note_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $dot = $conn->real_escape_string($_POST['dot']);
    
    $sql = "UPDATE notes SET title = '$title', content = '$content', dot = '$dot' WHERE id = $id";
    $conn->query($sql);
    header("Location: code.php");
    exit;
}

// Delete note
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM notes WHERE id = $id");
    header("Location: code.php");
    exit;
}

// Archive note
if (isset($_GET['archive'])) {
    $id = intval($_GET['archive']);
    $conn->query("UPDATE notes SET archived = 1 WHERE id = $id");
    header("Location: code.php");
    exit;
}

// Unarchive note
if (isset($_GET['unarchive'])) {
    $id = intval($_GET['unarchive']);
    $conn->query("UPDATE notes SET archived = 0 WHERE id = $id");
    header("Location: code.php");
    exit;
}

// Favorite/unfavorite note - Add this new section
if (isset($_GET['toggle_favorite'])) {
    $id = intval($_GET['toggle_favorite']);
    $sql = "UPDATE notes SET favorite = NOT favorite WHERE id = $id";
    $conn->query($sql);
    header("Location: code.php");
    exit;
}

// Pagination & Search for regular notes
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 9;
$offset = ($page - 1) * $limit;
$like = "%$search%";

// Check if we're in favorites view
$view = $_GET['view'] ?? 'all';
$whereClause = "archived = 0";
$pageTitle = "All Notes";

if ($view === 'favorites') {
    $whereClause .= " AND favorite = 1";
    $pageTitle = "Favorite Notes";
}

// Query for regular (unarchived) notes
$stmt = $conn->prepare("SELECT * FROM notes WHERE $whereClause AND (title LIKE ? OR content LIKE ?) ORDER BY date DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ssii", $like, $like, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Count for pagination
$total = $conn->prepare("SELECT COUNT(*) as total FROM notes WHERE $whereClause AND (title LIKE ? OR content LIKE ?)");
$total->bind_param("ss", $like, $like);
$total->execute();
$totalResult = $total->get_result()->fetch_assoc();
$totalPages = ceil($totalResult['total'] / $limit);

// Query for archived notes (to show in the archive modal)
$archivedStmt = $conn->prepare("SELECT * FROM notes WHERE archived = 1 ORDER BY date DESC");
$archivedStmt->execute();
$archivedResult = $archivedStmt->get_result();


// Default view filter
$filterSql = "WHERE archived = 0";

// Apply filters
if (isset($_GET['view']) && $_GET['view'] === 'favorites') {
    $filterSql = "WHERE favorite = 1 AND archived = 0";
}

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $filterSql .= " AND (title LIKE '%$search%' OR content LIKE '%$search%')";
}

// Pagination
$limit = 9; // notes per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Count total for pagination
$countQuery = "SELECT COUNT(*) as total FROM notes $filterSql";
$countResult = $conn->query($countQuery);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Main query
$sql = "SELECT * FROM notes $filterSql ORDER BY id ASC LIMIT $start, $limit";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Note It!</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <style>
    /* Reset and base styles */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.6;
      color: #333;
      background-color: #f5f5f5;
      overflow-x: hidden;
    }
    
    /* Typography */
    h1, h2, h3 {
      margin-bottom: 0.5rem;
    }

    a {
      text-decoration: none;
      color: inherit;
    }
    
    /* Layout */
    .container {
      display: flex;
      min-height: 100vh;
      position: relative;
    }
    
    /* Sidebar - Updated with new color */
    .sidebar {
      width: 250px;
      background-color: #019472;
      color: #fff;
      padding: 0;
      transition: transform 0.3s ease;
      z-index: 100;
      flex-shrink: 0;
      box-shadow: 2px 0 5px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      height: 130vh;
    }
    
    .logo {
        padding: 25px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.2);
        text-align: center;
    }
    
    .logo h1 {
        font-size: 32px;
        font-weight: 800;
        color: #fff;
        letter-spacing: 1px;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
    }
    
    .logo span {
        color: #00bfa6;
        font-style: italic;
    }
    
    /* Sidebar Navigation - Updated for better contrast with new background */
    .vertical-nav {
        list-style: none;
        padding: 25px 0;
        flex-grow: 1;
    }
    
    .vertical-nav li {
        padding: 14px 20px;
        transition: background 0.2s, transform 0.1s;
        margin: 5px 0;
    }
    
    .vertical-nav li:hover {
        background-color: rgba(255,255,255,0.15);
        transform: translateX(5px);
    }
    
    .vertical-nav a {
        display: flex;
        align-items: center;
        color: #fff;
        font-weight: 600;
        font-size: 15px;
    }
    
    .vertical-nav i {
        margin-right: 12px;
        width: 20px;
        text-align: center;
        color: #fff;
        font-size: 16px;
    }
    
    /* User info - positioned at bottom - Updated for new sidebar color */
    .user {
      padding: 15px 20px;
      border-top: 1px solid rgba(255,255,255,0.1);
      display: flex;
      align-items: center;
      background-color: rgba(0,0,0,0.1);
      margin-top: auto;
    }
    
    .user .status {
      width: 10px;
      height: 10px;
      background-color: #fff;
      border-radius: 50%;
      margin-right: 10px;
    }
    
    .bold-text {
      font-weight: 600;
      color: #fff;
    }
    
    /* Main Content */
    .main-content {
      flex: 1;
      padding: 25px;
      overflow-y: auto;
      max-width: 100%;
    }
    
    /* Header and search */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    
    .search {
      display: flex;
      align-items: center;
    }
    
    .search-container {
      display: flex;
      align-items: center;
      border: 1px solid #ddd;
      border-radius: 20px;
      padding: 6px 12px;
      background-color: #fff;
      width: 300px;
      max-width: 100%;
    }
    
    .search-container input {
      flex: 1;
      border: none;
      outline: none;
      font-size: 14px;
      background: transparent;
      padding: 6px;
    }
    
    .search-icon {
      background: none;
      border: none;
      color: #777;
      cursor: pointer;
    }
    
    .add-note-btn {
      margin-left: 10px;
      padding: 8px 16px;
      background-color: #019472;
      color: #fff;
      border: none;
      border-radius: 20px;
      font-size: 14px;
      cursor: pointer;
      transition: background 0.2s;
    }
    
    .add-note-btn:hover {
      background-color: #017a5e;
    }
    
    .archived-btn {
      margin-left: 10px;
      padding: 8px 16px;
      background-color: #3498db;
      color: #fff;
      border: none;
      border-radius: 20px;
      font-size: 14px;
      cursor: pointer;
      transition: background 0.2s;
    }
    
    .archived-btn:hover {
      background-color: #2980b9;
    }
    
    /* Notes Grid */
    .notes-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .note-card {
      position: relative;
      padding: 18px;
      padding-bottom: 50px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      overflow: hidden;
    }
    
    .note-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .note-card h3 {
      margin-bottom: 10px;
      padding-bottom: 8px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
    }
    
    .right-align {
      color: #777;
      cursor: pointer;
    }
    
    .right-align:hover {
      color: #333;
    }
    
    .note-card p {
      margin-bottom: 15px;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 5;
      -webkit-box-orient: vertical;
    }
    
    .note-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: #777;
      font-size: 13px;
    }
    
    .dot {
      display: inline-block;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin-right: 5px;
    }
    
    .red { background-color: #e74c3c; }
    .green { background-color: #2ecc71; }
    .blue { background-color: #3498db; }
    .yellow { background-color: #f1c40f; }
    .purple { background-color: #9b59b6; }
    
    .note-date {
      font-style: italic;
    }
    
    .note-actions {
      position: absolute;
      right: 10px;
      bottom: 10px;
      display: flex;
    }
    
    .note-actions a {
      margin-left: 5px;
      color: #fff;
      padding: 5px 10px;
      font-size: 12px;
      border-radius: 4px;
      transition: opacity 0.2s;
    }
    
    .note-actions a:hover {
      opacity: 0.9;
    }
    
    .delete-btn { background-color: #e74c3c; }
    .archive-btn { background-color: #f39c12; }
    .unarchive-btn { background-color: #3498db; }
    .favorite-btn { background-color: #9b59b6; }
    .unfavorite-btn { background-color: #95a5a6; }
    
    /* Pagination */
    .pagination {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      margin: 25px 0;
      gap: 5px;
    }
    
    .pagination a {
      display: inline-block;
      padding: 8px 12px;
      background: #eee;
      border-radius: 4px;
      transition: background 0.2s;
    }
    
    .pagination a:hover {
      background: #ddd;
    }
    
    .pagination a.active {
      background: #019472;
      color: white;
    }
    
    /* Modal */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.6);
      z-index: 1000;
      overflow-y: auto;
      padding: 20px;
      display: flex;
      justify-content: center;
      align-items: flex-start;
    }
    
    .modal-content {
      background: #fff;
      padding: 25px;
      width: 90%;
      max-width: 450px;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
      margin: 50px auto;
    }
    
    /* Archive Modal */
    .archive-modal-content {
      max-width: 800px;
      width: 90%;
    }
    
    .modal-title {
      font-size: 20px;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }
    
    .form-group {
      margin-bottom: 15px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }
    
    .form-control {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
      transition: border 0.2s;
    }
    
    .form-control:focus {
      border-color: #019472;
      outline: none;
    }
    
    textarea.form-control {
      min-height: 120px;
      resize: vertical;
    }
    
    .btn-group {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }
    
    .btn {
      padding: 10px 15px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
      transition: background 0.2s;
    }
    
    .btn-primary {
      background-color: #019472;
      color: white;
    }
    
    .btn-primary:hover {
      background-color: #017a5e;
    }
    
    .btn-secondary {
      background-color: #e0e0e0;
      color: #333;
    }
    
    .btn-secondary:hover {
      background-color: #d0d0d0;
    }
    
    /* Hamburger menu for mobile */
    .menu-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 1.5rem;
      color: #333;
      cursor: pointer;
      position: absolute;
      top: 15px;
      right: 15px;
      z-index: 101;
    }
    
    /* Empty state */
    .empty-state {
      text-align: center;
      padding: 40px 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .empty-state p {
      color: #777;
      font-size: 16px;
    }
    
    /* Responsive styles */
    @media (max-width: 992px) {
      .notes-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      }
      
      .search-container {
        width: 250px;
      }
    }
    
    @media (max-width: 768px) {
      .menu-toggle {
        display: block;
      }
      
      .container {
        display: block;
      }
      
      .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        transform: translateX(-100%);
      }
      
      .sidebar.active {
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0;
        padding: 15px;
      }
      
      .header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .search {
        margin-top: 15px;
        width: 100%;
      }
      
      .search-container {
        width: 100%;
      }
      
      .add-note-btn {
        margin-left: auto;
      }
      
      .notes-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      }
    }
    
    @media (max-width: 480px) {
      .notes-grid {
        grid-template-columns: 1fr;
      }
      
      .modal-content {
        padding: 15px;
      }
      
      .header h2 {
        font-size: 1.5rem;
      }
      
      .add-note-btn {
        padding: 6px 12px;
        font-size: 13px;
      }
      
      .note-actions {
        flex-direction: column;
        gap: 5px;
      }
      
      .note-actions a {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <button class="menu-toggle" id="menuToggle">
      <i class="fas fa-bars"></i>
    </button>
    
    <div class="sidebar" id="sidebar">
      <div class="logo">
        <h1>Note<span>It!</span></h1>
      </div>
      <nav>
        <ul class="vertical-nav">
          <li><a href="code.php"><i class="fas fa-sticky-note"></i> All Notes</a></li>
          <li><a href="code.php?view=favorites"><i class="fas fa-heart"></i> Favorites</a></li>
          <li><a href="#" id="showArchivedLink"><i class="fas fa-box"></i> Archives</a></li>
          <li><a href="sign-in.php"><i class="fas fa-power-off"></i> Logout</a></li>
        </ul>
      </nav>
      <div class="user">
        <div class="status"></div>
        <p class="bold-text">Hi <?php echo $_SESSION["username"] ?? "Guest"; ?>!<br />Welcome back.</p>
      </div>
    </div>

    <div class="main-content">
      <header class="header">
        <h2><?php echo $pageTitle; ?></h2>
        <div class="search">
          <form method="GET" class="search-container">
            <?php if ($view === 'favorites'): ?>
              <input type="hidden" name="view" value="favorites">
            <?php endif; ?>
            <input type="text" name="search" placeholder="Search notes..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="search-icon"><i class="fas fa-search"></i></button>
          </form>
          <button type="button" class="add-note-btn" id="addNoteBtn">Add Note</button>
          <button type="button" class="archived-btn" id="showArchivedBtn">Archived</button>
        </div>
      </header>

      <div class="notes-grid">
        <?php if ($result->num_rows > 0): ?>
          <?php while ($note = $result->fetch_assoc()): ?>
            <div class="note-card">
              <h3>
                <?php echo htmlspecialchars($note["title"]); ?>
                <span class="right-align" data-id="<?php echo $note['id']; ?>" data-title="<?php echo htmlspecialchars($note['title']); ?>" data-content="<?php echo htmlspecialchars($note['content']); ?>" data-dot="<?php echo htmlspecialchars($note['dot']); ?>"><i class="fas fa-ellipsis-h"></i></span>
              </h3>
              <p><?php echo nl2br(htmlspecialchars($note["content"])); ?></p>
              <div class="note-footer">
                <div>
                  <?php if (!empty($note["dot"])): ?>
                    <span class="dot <?php echo htmlspecialchars($note["dot"]); ?>"></span>
                  <?php endif; ?>
                </div>
                <span class="note-date"><?php echo htmlspecialchars($note["date"]); ?></span>
              </div>
              <div class="note-actions">
                <a href="?delete=<?php echo $note['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this note?')">Delete</a>
                <a href="?archive=<?php echo $note['id']; ?>" class="archive-btn">Archive</a>
                <?php if ($note['favorite'] ?? false): ?>
                  <a href="?toggle_favorite=<?php echo $note['id']; ?>" class="unfavorite-btn">Unfavorite</a>
                <?php else: ?>
                  <a href="?toggle_favorite=<?php echo $note['id']; ?>" class="favorite-btn">Favorite</a>
                <?php endif; ?>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="empty-state">
            <p>No notes found. Start by adding a new note!</p>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?<?php echo !empty($view) ? "view=$view&" : ""; ?>search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>">
              <?php echo $i; ?>
            </a>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Add Note Modal -->
  <div id="addModal" class="modal" style="display: none;">
    <div class="modal-content">
      <h3 class="modal-title">Add New Note</h3>
      <form method="POST">
        <div class="form-group">
          <label for="title">Title</label>
          <input type="text" id="title" name="title" class="form-control" placeholder="Note title" required>
        </div>
        <div class="form-group">
          <label for="content">Content</label>
          <textarea id="content" name="content" class="form-control" placeholder="Note content" required></textarea>
        </div>
        <div class="form-group">
          <label for="dot">Color Tag</label>
          <select id="dot" name="dot" class="form-control">
            <option value="">None</option>
            <option value="red">Red</option>
            <option value="green">Green</option>
            <option value="blue">Blue</option>
            <option value="yellow">Yellow</option>
            <option value="purple">Purple</option>
          </select>
        </div>
        <div class="btn-group">
          <button type="submit" name="add_note" class="btn btn-primary">Add Note</button>
          <button type="button" id="cancelBtn" class="btn btn-secondary">Cancel</button>
        </div>
      </form>
    </div>
  </div>
 
  <!-- Edit Note Modal -->
  <div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
      <h3 class="modal-title">Edit Note</h3>
      <form method="POST">
        <input type="hidden" id="edit_note_id" name="note_id">
        <div class="form-group">
          <label for="edit_title">Title</label>
          <input type="text" id="edit_title" name="title" class="form-control" placeholder="Note title" required>
        </div>
        <div class="form-group">
          <label for="edit_content">Content</label>
          <textarea id="edit_content" name="content" class="form-control" placeholder="Note content" required></textarea>
        </div>
        <div class="form-group">
          <label for="edit_dot">Color Tag</label>
          <select id="edit_dot" name="dot" class="form-control">
            <option value="">None</option>
            <option value="red">Red</option>
            <option value="green">Green</option>
            <option value="blue">Blue</option>
            <option value="yellow">Yellow</option>
            <option value="purple">Purple</option>
          </select>
        </div>
        <div class="btn-group">
          <button type="submit" name="update_note" class="btn btn-primary">Update Note</button>
          <button type="button" id="cancelEditBtn" class="btn btn-secondary">Cancel</button>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Archived Notes Modal -->
  <div id="archivedModal" class="modal" style="display: none;">
    <div class="modal-content archive-modal-content">
      <h3 class="modal-title">Archived Notes</h3>
      
      <?php if ($archivedResult->num_rows > 0): ?>
        <div class="notes-grid">
          <?php while ($note = $archivedResult->fetch_assoc()): ?>
            <div class="note-card">
              <h3>
                <?php echo htmlspecialchars($note["title"]); ?>
                <span class="right-align" data-id="<?php echo $note['id']; ?>" data-title="<?php echo htmlspecialchars($note['title']); ?>" data-content="<?php echo htmlspecialchars($note['content']); ?>" data-dot="<?php echo htmlspecialchars($note['dot']); ?>"><i class="fas fa-ellipsis-h"></i></span>
              </h3>
              <p><?php echo nl2br(htmlspecialchars($note["content"])); ?></p>
              <div class="note-footer">
                <div>
                  <?php if (!empty($note["dot"])): ?>
                    <span class="dot <?php echo htmlspecialchars($note["dot"]); ?>"></span>
                  <?php endif; ?>
                </div>
                <span class="note-date"><?php echo htmlspecialchars($note["date"]); ?></span>
              </div>
              <div class="note-actions">
                <a href="?delete=<?php echo $note['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this note?')">Delete</a>
                <a href="?unarchive=<?php echo $note['id']; ?>" class="unarchive-btn">Unarchive</a>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <p>No archived notes found.</p>
        </div>
      <?php endif; ?>
      
      <div class="btn-group">
        <button type="button" id="closeArchiveBtn" class="btn btn-secondary">Close</button>
      </div>
    </div>
  </div>
  
  <!-- JavaScript -->
  <script>
    // Toggle sidebar
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    
    menuToggle.addEventListener('click', function() {
      sidebar.classList.toggle('active');
    });
    
    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
      if (window.innerWidth <= 768 && 
          sidebar.classList.contains('active') &&
          !sidebar.contains(event.target) && 
          event.target !== menuToggle) {
        sidebar.classList.remove('active');
      }
    });
    
    // Add Note Modal handling
    const addModal = document.getElementById('addModal');
    const addNoteBtn = document.getElementById('addNoteBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    
    addNoteBtn.addEventListener('click', function() {
      addModal.style.display = 'flex';
    });
    
    cancelBtn.addEventListener('click', function() {
      addModal.style.display = 'none';
    });

    // Edit Note Modal handling
    const editModal = document.getElementById('editModal');
    const editForm = document.querySelector('#editModal form');
    const editNoteId = document.getElementById('edit_note_id');
    const editTitle = document.getElementById('edit_title');
    const editContent = document.getElementById('edit_content');
    const editDot = document.getElementById('edit_dot');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    
    // Attach click event to all edit icons using event delegation
    document.addEventListener('click', function(event) {
      const clickedIcon = event.target.closest('.right-align');
      if (clickedIcon) {
        const id = clickedIcon.dataset.id;
        const title = clickedIcon.dataset.title;
        const content = clickedIcon.dataset.content;
        const dot = clickedIcon.dataset.dot;
        
        // Populate the edit form
        editNoteId.value = id;
        editTitle.value = title;
        editContent.value = content;
        editDot.value = dot;
        
        // Show the edit modal
        editModal.style.display = 'flex';
      }
    });
    
    cancelEditBtn.addEventListener('click', function() {
      editModal.style.display = 'none';
    });
    
    // Archived Notes Modal handling
    const archivedModal = document.getElementById('archivedModal');
    const showArchivedBtn = document.getElementById('showArchivedBtn');
    const showArchivedLink = document.getElementById('showArchivedLink');
    const closeArchiveBtn = document.getElementById('closeArchiveBtn');
    
    showArchivedBtn.addEventListener('click', function() {
      archivedModal.style.display = 'flex';
    });
    
    showArchivedLink.addEventListener('click', function(e) {
      e.preventDefault();
      archivedModal.style.display = 'flex';
    });
    
    closeArchiveBtn.addEventListener('click', function() {
      archivedModal.style.display = 'none';
    });
    
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
      if (event.target === addModal) {
        addModal.style.display = 'none';
      }
      if (event.target === editModal) {
        editModal.style.display = 'none';
      }
      if (event.target === archivedModal) {
        archivedModal.style.display = 'none';
      }
    });
    
    // Escape key to close modals
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        addModal.style.display = 'none';
        editModal.style.display = 'none';
        archivedModal.style.display = 'none';
      }
    });
  </script>
</body>
</html>