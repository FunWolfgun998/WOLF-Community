<?php
// Start session to access logged-in user data
session_start();

$database = "communitydb";
$articles = [];

try {
    // Connect to MySQL database using PDO
    $pdo = new PDO(
        "mysql:host=db;dbname=" . $database . ";charset=utf8mb4",
        getenv("DB_USER"),
        getenv("DB_PASSWORD")
    );
    //Delete request
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "delete") {
        // Ensure user is logged in
        if (isset($_SESSION["user_id"])) {
            $articleIdToDelete = (int)($_POST["article_id"] ?? 0);
            $currentUserId     = (int)$_SESSION["user_id"];

            // Crucial security check: DELETE only if BOTH article id AND author id match!
            $sqlDelete = "DELETE FROM articles WHERE id = ? AND user_id = ?";
            $stmtDelete = $pdo->prepare($sqlDelete);
            $stmtDelete->execute([$articleIdToDelete, $currentUserId]);

            // Refresh the page to show updated list and avoid form re-submission on refresh
            header("Location: index.php");
            exit;
        }
    }
    // Query to fetch articles with author username and associated tags (comma-separated)
    $sql = "SELECT 
                a.id,
                a.user_id,
                a.title, 
                a.introduction, 
                a.type, 
                a.created_at,
                u.username AS author,
                GROUP_CONCAT(t.name SEPARATOR ', ') AS tags
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN article_tags at ON a.id = at.article_id
            LEFT JOIN tags t ON at.tag_id = t.id
            GROUP BY a.id
            ORDER BY a.created_at DESC";

    $stmt = $pdo->query($sql);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Save database error if connection or query fails
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>WOLF Community</title>
</head>
<body>

    <h1>Welcome to the WOLF community</h1>
    <p>
        Here you can find userful articles about WOLF.<br>
    </p>
    <?php if (isset($_SESSION["user_id"])): ?>
        <!-- htmlspecialchars protects for usernames like <script>alert('hacked')</script> making them simple text -->
        <p>Welcome back <?php echo(htmlspecialchars($_SESSION["username"]));?>!<br></p>
        <p>
            Hope you find what you need and if you want to share something <a href="upload_page.php">upload it here!</a>
        </p>
        <a href="logout.php"> Log out!</a>
    <?php else: ?>
        <p>
            You are not logged, if you want to like the posts or post we sugget to do it! <a href="login.php">Log in!</a>
        </p>
    <?php endif; ?>

    <h2>Latest Articles</h2>

    <!-- Display database connection error if any -->
    <?php if (isset($dbError)): ?>
        <p style="color: red;"><strong>Database error:</strong> <?= htmlspecialchars($dbError) ?></p>
    <?php endif; ?>

    <!-- Loop through all articles -->
    <?php if (!empty($articles)): ?>
        <?php foreach ($articles as $article): ?>
            <article class="article-card">
                <h2><?= htmlspecialchars($article["title"]) ?></h2>

                <div class="article-meta">
                    <!-- Category badge -->
                    <span class="article-badge">Category: <?= htmlspecialchars($article["type"]) ?></span>
                    <!-- Author (falls back to "[Deleted user]" if user_id was set to NULL) -->
                    - Written by: <strong><?= htmlspecialchars($article["author"] ?? "[Deleted user]") ?> </strong>
                    - 
                    Published on: <?= date("M d, Y", strtotime($article["created_at"])) ?>
                </div>

                <!-- Introduction preview -->
                <p>
                    <?= htmlspecialchars($article["introduction"] ?: " ") ?>
                </p>

                <!-- Tags list -->
                <?php if (!empty($article["tags"])): ?>
                    <div class="article-tags">
                        <strong>Tags:</strong>
                        <?php foreach (explode(", ", $article["tags"]) as $tag): ?>
                            <span>#<?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Delete link if user is creator of the article-->
                <?php if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] == $article["user_id"]): ?>
                    
                    <button type="button" onclick="document.getElementById('modal-delete-<?= $article['id'] ?>').showModal()">
                        Delete article
                    </button>

                    <dialog id="modal-delete-<?= $article['id'] ?>">
                        <h3>Confirm Deletion</h3>
                        <p>Are you sure you want to permanently delete this article?</p>
                        <p><strong><?= htmlspecialchars($article["title"]) ?></strong></p>

                        <form method="post" action="" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="article_id" value="<?= (int)$article["id"] ?>">
                            <button type="submit">Yes, delete permanently</button>
                        </form>

                        <form method="dialog" style="display: inline;">
                            <button type="submit">Cancel</button>
                        </form>
                    </dialog>

                <?php endif; ?>

                <hr>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No articles published yet. Be the first to <a href="upload_page.php">write one!</a></p>
    <?php endif; ?>

</body>
</html>