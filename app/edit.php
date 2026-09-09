<?php
// edit.php - Formulário de edição, pré-carregado com os dados do registro
require 'db.php';

$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

// Busca a tarefa atual para pré-carregar o formulário
$stmt = $pdo->prepare("SELECT * FROM tarefas WHERE id = :id");
$stmt->execute([':id' => $id]);
$tarefa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarefa) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if ($titulo === '') {
        $erro = 'O campo Título é obrigatório.';
    } else {
        $stmt = $pdo->prepare("UPDATE tarefas SET titulo = :titulo, descricao = :descricao WHERE id = :id");
        $stmt->execute([
            ':titulo' => $titulo,
            ':descricao' => $descricao,
            ':id' => $id,
        ]);

        header('Location: index.php?msg=Tarefa atualizada com sucesso!');
        exit;
    }
    // Mantém os dados digitados na tela em caso de erro de validação
    $tarefa['titulo'] = $titulo;
    $tarefa['descricao'] = $descricao;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Tarefa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 600px;">
    <h1 class="h3 mb-4">Editar Tarefa #<?= htmlspecialchars($id) ?></h1>

    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-4 shadow-sm rounded">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" name="titulo" class="form-control" required value="<?= htmlspecialchars($tarefa['titulo']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" class="form-control" rows="4"><?= htmlspecialchars($tarefa['descricao']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>
