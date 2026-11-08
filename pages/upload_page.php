<?php
session_start();

// Se l'utente non è loggato, reindirizza al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

// Gestisce l'invio del form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $database = "communitydb";

    $title        = trim($_POST["title"] ?? "");
    $introduction = trim($_POST["introduction"] ?? "");
    $type         = $_POST["type"] ?? "discussion";
    $tagsInput    = trim($_POST["tags"] ?? "");
    $content      = trim($_POST["content"] ?? "");
    $userId       = $_SESSION["user_id"];

    if (empty($title) || empty($content)) {
        $message = "Title and Content are required!";
    } else {
        try {
            $pdo = new PDO(
                "mysql:host=db;dbname=" . $database . ";charset=utf8mb4",
                getenv("DB_USER"),
                getenv("DB_PASSWORD")
            );

            // 1. Add the article in articles
            $sql = "INSERT INTO articles (user_id, title, introduction, content, type) 
                    VALUES (?, ?, ?, ?, ?)";
            $query = $pdo->prepare($sql);
            $query->execute([$userId, $title, $introduction, $content, $type]);

            // Return the id of the article
            $articleId = $pdo->lastInsertId();

            // 2. tag managment
            if (!empty($tagsInput)) {
                // Divide la stringa separata da virgole in un array
                $tags = explode(",", $tagsInput);

                foreach ($tags as $tag) {
                    $tagName = strtolower(trim($tag));
                    if (empty($tagName)) {
                        continue;
                    }

                    // A) Search for exisiting tags 
                    $stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
                    $stmt->execute([$tagName]);
                    $existingTag = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existingTag) {
                        $tagId = $existingTag["id"];
                    } else {
                        // Se non esiste, lo inserisce
                        $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
                        $stmt->execute([$tagName]);
                        $tagId = $pdo->lastInsertId();
                    }

                    // B) Collega l'articolo e il tag nella tabella ponte article_tags
                    $stmt = $pdo->prepare("INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (?, ?)");
                    $stmt->execute([$articleId, $tagId]);
                }
            }

            $message = "Article uploaded successfully!";
            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            echo $message;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload articol</title>
</head>
<body>
    <h1>Upload an articol about WOLF related stuff!</h1>
    <div class="container">
        <a href="index.php" class="torna-home">← Torna alla Home</a>

        <div class="autore-info">
            You are uploading as: <?= htmlspecialchars($_SESSION["username"]) ?>
        </div>

        <!-- Upload form -->
        <form method="post" action="">

            <!--Title -->
            <div class="form-group">
                <label for="title">Title of the article:</label>
                <br>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    maxlength="255" 
                    required 
                >
                <br>

                <label for="content">Introduction of the article:</label>
                <br>
                <input 
                    id="introduction" 
                    name="introduction"
                    style=" width: 500px;"
                    type="text"
                    maxlength="255"
                    placeholder="Write here the Introduction, is non required but it helps on the preview of the articol."
                ></input>
            </div>

            <!-- Type (ENUM) -->
            <div class="form-group">
                <label for="type">Type of article:</label>
                <select id="type" name="type" required>
                    <option value="" disabled selected>Choose the type</option>
                    <option value="discussion">Discussion</option>
                    <option value="tutorial">Tutorial</option>
                    <option value="project">Project</option>
                    <option value="release">Official release</option>
                </select>
                <br>
                <span class="suggestion">Help the community to search specific topics through tags!</span>
            </div>

            <!-- Tags -->
            <div class="form-group">
                <label for="tags">Tags:</label>
                <input 
                    type="text" 
                    id="tags" 
                    name="tags" 
                    placeholder="E.g. lib, docker, backend, sql"
                ><br>
                <span class="suggestion">Use the comma (,) to separate the tags.</span>
            </div>

            <!-- 4. content (Markdown) -->
            <div class="form-group">
                <label for="content">Content of the article:</label>
                <br>
                <textarea 
                    id="content" 
                    name="content" 
                    rows="14" 
                    required 
                    placeholder="Write here you article... Use Markdown for formatting (# per i titoli, **bold**, *italic*, etc.)"
                ></textarea>
            </div>

            <!-- Upload button -->
            <button type="submit">Upload article!</button>

        </form>
    </div>
</body>
</html>