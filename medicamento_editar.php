<?php
require_once __DIR__ . '/inc/functions.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
$medicamentos = load_medicamentos();
$index = find_medicamento_index_by_id($medicamentos, $id);

if ($index === null) {
    flash_set('error', 'Medicamento nao encontrado.');
    redirect('medicamentos.php');
}

$error = '';
$form = normalize_medicamento($medicamentos[$index]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'delete') {
        $medicamentos = array_values(array_filter(
            $medicamentos,
            static fn(array $item): bool => (int)$item['id'] !== $id
        ));
        save_medicamentos($medicamentos);
        flash_set('success', 'Medicamento excluido com sucesso.');
        redirect('medicamentos.php');
    }

    $form = sanitize_medicamento_input($_POST, $id, $form);

    if ($form['nome'] === '' || $form['principio_ativo'] === '') {
        $error = 'Nome e principio ativo sao obrigatorios.';
    } else {
        $medicamentos[$index] = $form;
        save_medicamentos($medicamentos);
        flash_set('success', 'Cadastro atualizado com sucesso.');
        redirect('medicamento.php?id=' . $id);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar medicamento | <?= APP_NAME ?></title>
<link rel="stylesheet" href="<?= asset_path('style.css') ?>">
<script src="<?= asset_path('app.js') ?>" defer></script>
</head>
<body class="app-page">
<header class="topbar">
    <div class="container">
        <div class="brand">
            <div class="brand-mark"><?= APP_MARK ?></div>
            <div>
                <span class="brand-eyebrow">Central de medicamentos</span>
                <strong><?= APP_NAME ?></strong>
            </div>
        </div>

        <div class="user-strip">
            <a class="btn btn-ghost" href="medicamento.php?id=<?= e($id) ?>">Ficha</a>
            <a class="btn btn-ghost" href="medicamentos.php">Central</a>
            <a class="btn btn-ghost" href="logout.php">Sair</a>
        </div>
    </div>
</header>

<main class="page-wrap">
    <div class="container narrow-container">
        <section class="card">
            <div class="card-header">
                <div>
                    <span class="section-eyebrow">Edicao de cadastro</span>
                    <h1>Editar medicamento</h1>
                    <p class="card-text">
                        Atualize as informacoes do medicamento para manter o estoque, a validade e o cadastro institucional corretos.
                    </p>
                </div>
                <span class="badge badge-soft">Ultima atualizacao em <?= e(format_datetime_br($form['updated_at'])) ?></span>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="form-grid" data-medicamento-form>
                <label class="field">
                    <span>Nome *</span>
                    <input name="nome" required value="<?= e($form['nome']) ?>">
                </label>

                <label class="field">
                    <span>Categoria</span>
                    <input name="categoria" value="<?= e($form['categoria']) ?>">
                </label>

                <label class="field">
                    <span>Principio ativo *</span>
                    <input name="principio_ativo" required value="<?= e($form['principio_ativo']) ?>">
                </label>

                <label class="field">
                    <span>Dosagem</span>
                    <input name="dosagem" value="<?= e($form['dosagem']) ?>">
                </label>

                <label class="field">
                    <span>Apresentacao</span>
                    <input name="apresentacao" value="<?= e($form['apresentacao']) ?>">
                </label>

                <label class="field">
                    <span>Fabricante</span>
                    <input name="fabricante" value="<?= e($form['fabricante']) ?>">
                </label>

                <label class="field">
                    <span>Fornecedor</span>
                    <input name="fornecedor" value="<?= e($form['fornecedor']) ?>">
                </label>

                <label class="field">
                    <span>Lote</span>
                    <input name="lote" value="<?= e($form['lote']) ?>">
                </label>

                <label class="field">
                    <span>Registro ANVISA</span>
                    <input name="registro_anvisa" value="<?= e($form['registro_anvisa']) ?>">
                </label>

                <label class="field">
                    <span>Local de armazenamento</span>
                    <input name="localizacao" value="<?= e($form['localizacao']) ?>">
                </label>

                <label class="field">
                    <span>Validade</span>
                    <input type="date" name="validade" value="<?= e($form['validade']) ?>">
                </label>

                <label class="field">
                    <span>Status</span>
                    <select name="status">
                        <?php foreach (medicamento_status_options() as $status): ?>
                            <option value="<?= e($status) ?>" <?= $form['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="field">
                    <span>Estoque atual</span>
                    <input type="number" min="0" name="estoque" value="<?= e($form['estoque']) ?>" data-stock-input>
                </label>

                <label class="field">
                    <span>Estoque minimo</span>
                    <input type="number" min="0" name="estoque_minimo" value="<?= e($form['estoque_minimo']) ?>" data-min-stock-input>
                </label>

                <label class="field">
                    <span>Preco unitario</span>
                    <input name="preco" value="<?= e(number_format((float)$form['preco'], 2, ',', '')) ?>">
                </label>

                <label class="field checkbox-field">
                    <input type="checkbox" name="controlado" value="1" <?= $form['controlado'] ? 'checked' : '' ?>>
                    <span>Medicamento controlado</span>
                </label>

                <div class="stock-preview" data-stock-preview>
                    Informe o estoque atual e o estoque minimo para acompanhar a situacao do item.
                </div>

                <label class="field field-full">
                    <span>Observacoes</span>
                    <textarea name="descricao" rows="4"><?= e($form['descricao']) ?></textarea>
                </label>

                <div class="form-actions field-full">
                    <button class="btn btn-primary" type="submit">Salvar alteracoes</button>
                    <a class="btn btn-secondary" href="medicamento.php?id=<?= e($id) ?>">Cancelar</a>
                    <button class="btn btn-danger" type="submit" name="action" value="delete" onclick='return confirmDelete(<?= json_encode($form['nome'], JSON_UNESCAPED_UNICODE) ?>);'>Excluir</button>
                </div>
            </form>
        </section>
    </div>
</main>
</body>
</html>
