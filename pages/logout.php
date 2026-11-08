<?php
session_start();

// 1. Svuota tutte le variabili di sessione
$_SESSION = [];

// 2. Distrugge la sessione sul server
session_destroy();

// 3. Reindirizza l'utente alla Home Page
header("Location: index.php");
exit;