<?php
// index.php - Página de listagem de todas as tarefas cadastradas
require 'db.php';

$stmt = $pdo->query("SELECT * FROM tarefas ORDER BY id DESC");
$tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>CRUD de Tarefas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Lista de Tarefas</h1>
        <a href="create.php" class="btn btn-primary">+ Nova Tarefa</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <table class="table table-striped bg-white shadow-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Descrição</th>
                <th>Data de Cadastro</th>
                <th style="width: 180px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($tarefas) === 0): ?>
                <tr><td colspan="5" class="text-center text-muted">Nenhuma tarefa cadastrada ainda.</td></tr>
            <?php endif; ?>
            <?php foreach ($tarefas as $tarefa): ?>
                <tr>
                    <td><?= $tarefa['id'] ?></td>
                    <td><?= htmlspecialchars($tarefa['titulo']) ?></td>
                    <td><?= htmlspecialchars($tarefa['descricao']) ?></td>
                    <td><?= htmlspecialchars($tarefa['data_cadastro']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $tarefa['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="delete.php?id=<?= $tarefa['id'] ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Tem certeza que deseja excluir esta tarefa?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>