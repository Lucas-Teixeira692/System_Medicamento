<?php
require_once __DIR__ . '/inc/functions.php';

require_login();

$medicamentos = load_medicamentos();
$error = '';
$form = medicamento_defaults();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = sanitize_medicamento_input($_POST, next_medicamento_id($medicamentos));

    if ($form['nome'] === '' || $form['principio_ativo'] === '') {
        $error = 'Nome e principio ativo sao obrigatorios.';
    } else {
        $medicamentos[] = $form;
        save_medicamentos($medicamentos);
        flash_set('success', 'Medicamento cadastrado com sucesso.');
        redirect('medicamentos.php');
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Novo medicamento | <?= APP_NAME ?></title>
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
            <a class="btn btn-ghost" href="dashboard.php">Painel</a>
            <a class="btn btn-ghost" href="medicamentos.php">Voltar</a>
            <a class="btn btn-ghost" href="logout.php">Sair</a>
        </div>
    </div>
</header>

<main class="page-wrap">
    <div class="container narrow-container">
        <section class="card">
            <div class="card-header">
                <div>
                    <span class="section-eyebrow">Novo cadastro</span>
                    <h1>Cadastrar medicamento</h1>
                    <p class="card-text">
                        Registre as informacoes principais do medicamento para manter a central da unidade atualizada.
                    </p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="form-grid" data-medicamento-form>
                <label class="field">
                    <span>Nome *</span>
                    <input name="nome" required value="<?= e($form['nome']) ?>" placeholder="Ex.: Ibuprofeno">
                </label>

                <label class="field">
                    <span>Categoria</span>
                    <input name="categoria" value="<?= e($form['categoria']) ?>" placeholder="Ex.: Analgesico">
                </label>

                <label class="field">
                    <span>Principio ativo *</span>
                    <input name="principio_ativo" required value="<?= e($form['principio_ativo']) ?>" placeholder="Ex.: Ibuprofeno">
                </label>

                <label class="field">
                    <span>Dosagem</span>
                    <input name="dosagem" value="<?= e($form['dosagem']) ?>" placeholder="Ex.: 600 mg">
                </label>

                <label class="field">
                    <span>Apresentacao</span>
                    <input name="apresentacao" value="<?= e($form['apresentacao']) ?>" placeholder="Ex.: Comprimido">
                </label>

                <label class="field">
                    <span>Fabricante</span>
                    <input name="fabricante" value="<?= e($form['fabricante']) ?>" placeholder="Laboratorio responsavel">
                </label>

                <label class="field">
                    <span>Fornecedor</span>
                    <input name="fornecedor" value="<?= e($form['fornecedor']) ?>" placeholder="Distribuidor da unidade">
                </label>

                <label class="field">
                    <span>Lote</span>
                    <input name="lote" value="<?= e($form['lote']) ?>" placeholder="Codigo do lote">
                </label>

                <label class="field">
                    <span>Registro ANVISA</span>
                    <input name="registro_anvisa" value="<?= e($form['registro_anvisa']) ?>" placeholder="Numero do registro">
                </label>

                <label class="field">
                    <span>Local de armazenamento</span>
                    <input name="localizacao" value="<?= e($form['localizacao']) ?>" placeholder="Prateleira, armario ou geladeira">
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
                    <input name="preco" value="<?= e(number_format((float)$form['preco'], 2, ',', '')) ?>" placeholder="Ex.: 19,90">
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
                    <textarea name="descricao" rows="4" placeholder="Registre orientacoes internas, observacoes ou detalhes do cadastro"><?= e($form['descricao']) ?></textarea>
                </label>

                <div class="form-actions field-full">
                    <button class="btn btn-primary" type="submit">Salvar cadastro</button>
                    <a class="btn btn-secondary" href="medicamentos.php">Cancelar</a>
                </div>
            </form>
        </section>
    </div>
</main>
</body>
</html>
