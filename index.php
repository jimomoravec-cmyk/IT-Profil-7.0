<?php
session_start();
$page = $_GET["page"] ?? "home";

function renderPage($page) {
    switch ($page) {
        case "home":
        case "interests":
        case "skills":
        case "portfolio":
            require __DIR__ . "/pages/{$page}.php";
            break;
        default:
            http_response_code(404);
            require __DIR__ . "/pages/not_found.php";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profil Jiri Moravec</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <header>
        <h1>Jiri Moravec - Student IT</h1>
        <nav>
            <a href="?page=home">Domů</a>
            <a href="?page=interests">Zájmy</a>
            <a href="?page=skills">Dovednosti</a>
            <a href="?page=portfolio">Portfolio</a>
        </nav>
    </header>
    <main>
        <?php renderPage($page); ?>
    </main>
    <footer>
        <p>IT Profil 2026</p>
    </footer>
</body>
</html>