<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!is_admin()) {
    header("Location: ../../login.php");
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', 'Article deleted successfully.');
    } catch (Exception $e) {
        set_flash('danger', 'Failed to delete article: ' . $e->getMessage());
    }
}

header("Location: index.php");
exit;

