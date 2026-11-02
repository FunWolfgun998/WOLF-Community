<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Utenti registrati</title>
</head>
<body>

    <h1>Utenti registrati</h1>
    <?php
    // Connessione al database.
    $pdo = new PDO(
        "mysql:host=db;dbname=scuola;charset=utf8mb4",
        getenv("DB_USER"),
        getenv("DB_PASSWORD")
    );

    // Seleziona tutti gli utenti, senza leggere le password.
    $sql = "SELECT id, nome, cognome, email FROM Studente ORDER BY id";

    $query = $pdo->query($sql);

    // Recupera gli utenti come oggetti.
    $utenti = $query->fetchAll(PDO::FETCH_OBJ);

    if (count($utenti) === 0) {
        echo "<p>Nessun utente registrato.</p>";
    }else{
        echo "<p>ggggg</p>";
    }

    // Stampa i dati di ogni utente.
    foreach ($utenti as $utente) {
        echo "<h5>Utente numero " . $utente->id . "</h5>";
        echo "<p>Nome: " . htmlspecialchars($utente->nome) . "</p>";
        echo "<p>Cognome: " . htmlspecialchars($utente->cognome) . "</p>";
        echo "<p>Email: " . htmlspecialchars($utente->email) . "</p>";
        echo "<hr>";
    }
    ?>

    <a href="index.php">Torna alla registrazione</a>

</body>
</html>