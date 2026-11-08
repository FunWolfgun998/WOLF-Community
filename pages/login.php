<?php
session_start();
//Catch the form submit.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $TABLE = "users";
    $DATABASE = "communitydb";

    // PHP Data Objects allows to connect to any type of database. It needs the database selection
    $pdo = new PDO(
        "mysql:host=db;dbname=".$DATABASE.";charset=utf8mb4",
        getenv("DB_USER"),
        getenv("DB_PASSWORD")
    );
    if($_POST["action"]=="signup"){
        // Read the data from the form submition. in this case inside the _POST we find the values named after the field name="..."
        $username = trim($_POST["username"]) ?? ""; // the ?? is like if is null use thi value
        $email = trim($_POST["email"]) ?? "";
        $password = trim($_POST["password"]) ?? "";



        // Genera un hash per non salvare la password in chiaro.
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            //Create the full query
            $sql = "INSERT INTO " . $TABLE . " (username, email, password) VALUES (?, ?, ?)";
            //Return a statement object
            $query = $pdo->prepare($sql);
            // Inserts the data instead of the ? before executing the query.
            $query->execute([$username, $email, $passwordHash]);

            $_SESSION["user_id"]  = $pdo->lastInsertId();
            $_SESSION["username"] = $username;
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            // Error 23000 indicates the violation of an integrity constraint (UNIQUE)
            if ($e->getCode() == 23000) {
                if (str_contains($e->getMessage(), 'username')) {
                    $errore = "Username '$username' already used. Choose another one please.";
                } elseif (str_contains($e->getMessage(), 'email')) {
                    $errore = "Email address '$email' already registered. Try logging in!";
                } else {
                    $errore = "Username o Email già utilizzati!";
                }
            } else {
                $errore = "Errore imprevisto del DATABASE: " . $e->getMessage();
            }
            echo $errore;
            exit;
        }
    }
    else if($_POST["action"]=="login"){
        $access = trim($_POST["access"]) ?? "";
        $password = trim($_POST["password"]) ?? "";

        $sql = "SELECT id, username, password FROM ".$TABLE." WHERE email = ? OR username = ?";
        $query = $pdo->prepare($sql);
        $query->execute([$access, $access]);
        $user = $query->fetch(PDO::FETCH_ASSOC); 
        
        if($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"]  = $user["id"];
            $_SESSION["username"] = $user["username"];
            header("Location: index.php");
                exit;
        }
        else 
        {
            $error = "Credenzials not valid. Retry!";
        }
        echo $error;
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Sign up!</title>
</head>

<body>


    <h1>Sign up to WOLF community!</h1>

        <div class="contenitore">

        <!-- ================= (LOGIN) ================= -->
        <div class="box">
            <h2>Login back with you username or email</h2>
            
            <?php if (!empty($errore_login)): ?>
                <p class="errore"><?= htmlspecialchars($errore_login) ?></p>
            <?php endif; ?>

            <form method="post" action="">
                <!-- hidden field used to know where is from the data -->
                <input type="hidden" name="action" value="login">

                <label for="login-access">Email or username:</label>
                <input type="text" id="login-access" name="access" required>

                <label for="login-password">Password:</label>
                <input type="password" id="login-password" name="password" required>

                <button type="submit">Log in!</button>
            </form>
        </div>

        <!-- ================= (SIGN UP) ================= -->
        <div class="box">
            <h2>Sign up with a new account!</h2>

            <?php if (!empty($errore_signup)): ?>
                <p class="errore"><?= htmlspecialchars($errore_signup) ?></p>
            <?php endif; ?>

            <form method="post" action="">
                <!-- hidden field used to know where is from the data -->
                <input type="hidden" name="action" value="signup">

                <label for="signup-username">Username:</label>
                <input type="text" id="signup-username" name="username" required minlength="3">

                <label for="signup-email">Email:</label>
                <input type="email" id="signup-email" name="email" required>

                <label for="signup-password">Password:</label>
                <input type="password" id="signup-password" name="password" required minlength="8">

                <button type="submit">Sign up</button>
            </form>
        </div>

    </div>
</body>
</html>