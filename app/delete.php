<?php
// delete.php - Exclusão de registro (a confirmação é feita via JavaScript no index.php)
require 'db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM tarefas WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

header('Location: index.php?msg=Tarefa excluída com sucesso!');
exit;
