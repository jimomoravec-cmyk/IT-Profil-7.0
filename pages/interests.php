<?php
require_once __DIR__ . '/../db.php';
$pdo = get_db();

function set_flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="flash flash-' . htmlspecialchars($f['type']) . '">' . htmlspecialchars($f['message']) . '</div>';
        unset($_SESSION['flash']);
    }
}

// POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            set_flash('Zadejte prosím název zájmu.', 'error');
        } else {
            $stmt = $pdo->prepare('INSERT INTO interests (name) VALUES (:name)');
            $stmt->execute(['name' => $name]);
            set_flash('Zájem byl přidán.');
        }
        header('Location: ?page=interests');
        exit;
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($id <= 0 || $name === '') {
            set_flash('Chyba při úpravě zájmu. Zkontrolujte data.', 'error');
        } else {
            $stmt = $pdo->prepare('UPDATE interests SET name = :name WHERE id = :id');
            $stmt->execute(['name' => $name, 'id' => $id]);
            if ($stmt->rowCount() > 0) {
                set_flash('Zájem byl upraven.');
            } else {
                set_flash('Zájem nebyl nalezen.', 'error');
            }
        }
        header('Location: ?page=interests');
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM interests WHERE id = :id');
            $stmt->execute(['id' => $id]);
            set_flash('Zájem byl smazán.');
        } else {
            set_flash('Chyba při mazání zájmu.', 'error');
        }
        header('Location: ?page=interests');
        exit;
    }
}

// GET: show list and forms
$editMode = false;
$editItem = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' ) {
    $editId = (int)($_GET['id'] ?? 0);
    if ($editId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM interests WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $editId]);
        $editItem = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($editItem) {
            $editMode = true;
        }
    }
}

$stmt = $pdo->query('SELECT * FROM interests ORDER BY id DESC');
$interests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section>
    <h2>Zájmy</h2>
    <?php flash(); ?>

    <?php if ($editMode && $editItem): ?>
        <h3>Upravit zájem</h3>
        <form method="post" action="?page=interests">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= htmlspecialchars($editItem['id']) ?>">
            <label for="update_name">Název:</label>
            <input id="update_name" type="text" name="name" value="<?= htmlspecialchars($editItem['name']) ?>" required>
            <button type="submit">Uložit změny</button>
            <a href="?page=interests">Zrušit</a>
        </form>
    <?php else: ?>
        <h3>Přidat zájem</h3>
        <form method="post" action="?page=interests">
            <input type="hidden" name="action" value="add">
            <label for="name">Název:</label>
            <input id="name" type="text" name="name" required>
            <button type="submit">Přidat</button>
        </form>
    <?php endif; ?>

    <h3>Seznam zájmů</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>ID</th><th>Název</th><th>Akce</th></tr>
            </thead>
            <tbody>
                <?php if (count($interests) === 0): ?>
                    <tr><td colspan="3">Žádné zájmy nejsou uloženy.</td></tr>
                <?php else: ?>
                    <?php foreach ($interests as $interest): ?>
                    <tr>
                        <td><?= htmlspecialchars($interest['id']) ?></td>
                        <td><?= htmlspecialchars($interest['name']) ?></td>
                        <td>
                            <a href="?page=interests&action=edit&id=<?= htmlspecialchars($interest['id']) ?>">Upravit</a>
                            <form method="post" action="?page=interests" style="display:inline; margin-left:10px;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($interest['id']) ?>">
                                <button type="submit" onclick="return confirm('Opravdu smazat tento zájem?');">Smazat</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>