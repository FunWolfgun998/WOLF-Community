<?php
// Esegue il codice quando viene inviato il form.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Legge i dati inseriti nel form.
    $nome = $_POST["nome"] ?? "";
    $cognome = $_POST["cognome"] ?? "";
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    // Si collega al database MySQL.
    $pdo = new PDO(
        "mysql:host=db;dbname=scuola;charset=utf8mb4",
        getenv("DB_USER"),
        getenv("DB_PASSWORD")
    );

    // Genera un hash per non salvare la password in chiaro.
    //$passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Prepara la query di inserimento.
    $sql = "INSERT INTO Studente (nome, cognome, email, password)
            VALUES (?, ?, ?, ?)";

    $query = $pdo->prepare($sql);

    // Inserisce i dati al posto dei quattro punti interrogativi.
    $query->execute([$nome, $cognome, $email, $password]);

    exit;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione utente</title>
</head>
<body>

    <h1>Registrazione .-. utente</h1>

    <form method="post" action="index.php">

        <p>
            <label for="nome">Nome:</label><br>
            <input type="text" id="nome" name="nome" maxlength="100" required>
        </p>

        <p>
            <label for="cognome">Cognome:</label><br>
            <input type="text" id="cognome" name="cognome" maxlength="100" required>
        </p>

        <p>
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" maxlength="255" required>
        </p>

        <p>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" minlength="8" maxlength="72" required>
        </p>

        <button type="submit">Registra utente</button>

    </form>
    <br>
    
    <h2>Utenti registrati</h2>

    <form method="get" action="utenti.php">
        <button type="submit">Mostra tutti gli utenti</button>
    </form>
</body>
</html>