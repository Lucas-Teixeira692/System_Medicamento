<?php
require_once __DIR__ . '/inc/functions.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
$medicamentos = load_medicamentos();
$medicamento = find_medicamento_by_id($medicamentos, $id);

if (!$medicamento) {
    flash_set('error', 'Medicamento nao encontrado.');
    redirect('medicamentos.php');
}

$flash = flash_get();
$days = days_until($medicamento['validade']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($medicamento['nome']) ?> | <?= APP_NAME ?></title>
<link rel="stylesheet" href="<?= asset_path('style.css') ?>">
<script src="<?= asset_path('app.js') ?>" defer></script>
</head>
<body class="app-page">
<header class="topbar">
    <div class="container">
        <div class="brand">
            <div class="brand-mark"><?= APP_MARK ?></div>
            <div>
                <span class="brand-eyebrow">Ficha do medicamento</span>
                <strong><?= APP_NAME ?></strong>
            </div>
        </div>

        <div class="user-strip">
            <a class="btn btn-ghost" href="dashboard.php">Painel</a>
            <a class="btn btn-ghost" href="medicamentos.php">Central</a>
            <a class="btn btn-ghost" href="logout.php">Sair</a>
        </div>
    </div>
</header>

<main class="page-wrap">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" data-autohide><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <section class="card">
            <div class="medicine-identity">
                <div>
                    <span class="section-eyebrow"><?= e($medicamento['categoria']) ?></span>
                    <h1><?= e($medicamento['nome']) ?></h1>
                    <p class="card-text">
                        <?= e($medicamento['principio_ativo']) ?> | <?= e($medicamento['dosagem']) ?> | <?= e($medicamento['apresentacao']) ?>
                    </p>
                    <div class="badge-row">
                        <span class="badge <?= e(medicamento_status_badge($medicamento['status'])) ?>"><?= e($medicamento['status']) ?></span>
                        <?php foreach (medicamento_risk_labels($medicamento) as $badge): ?>
                            <span class="badge <?= e($badge['class']) ?>"><?= e($badge['text']) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="quick-actions">
                    <a class="btn btn-primary" href="medicamento_editar.php?id=<?= e($medicamento['id']) ?>">Editar cadastro</a>
                    <a class="btn btn-secondary" href="medicamentos.php">Voltar ao estoque</a>
                </div>
            </div>
        </section>

        <section class="info-grid">
            <article class="info-card">
                <span>Estoque atual</span>
                <strong><?= e($medicamento['estoque']) ?> unidades</strong>
                <small>estoque minimo: <?= e($medicamento['estoque_minimo']) ?></small>
            </article>
            <article class="info-card">
                <span>Validade</span>
                <strong><?= e(format_date_br($medicamento['validade'])) ?></strong>
                <small>
                    <?php if ($days === null): ?>
                        validade nao informada
                    <?php elseif ($days < 0): ?>
                        vencido ha <?= e(abs($days)) ?> dias
                    <?php else: ?>
                        vence em <?= e($days) ?> dias
                    <?php endif; ?>
                </small>
            </article>
            <article class="info-card">
                <span>Preco unitario</span>
                <strong><?= e(format_currency_br((float)$medicamento['preco'])) ?></strong>
                <small>valor estimado por unidade</small>
            </article>
            <article class="info-card">
                <span>Local de armazenamento</span>
                <strong><?= e($medicamento['localizacao'] ?: '--') ?></strong>
                <small>referencia interna da unidade</small>
            </article>
        </section>

        <section class="detail-grid">
            <article class="card">
                <div class="card-header">
                    <div>
                        <span class="section-eyebrow">Cadastro</span>
                        <h2>Informacoes do medicamento</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <div><span>Fabricante</span><strong><?= e($medicamento['fabricante'] ?: '--') ?></strong></div>
                    <div><span>Fornecedor</span><strong><?= e($medicamento['fornecedor'] ?: '--') ?></strong></div>
                    <div><span>Lote</span><strong><?= e($medicamento['lote'] ?: '--') ?></strong></div>
                    <div><span>Registro ANVISA</span><strong><?= e($medicamento['registro_anvisa'] ?: '--') ?></strong></div>
                    <div><span>Controlado</span><strong><?= $medicamento['controlado'] ? 'Sim' : 'Nao' ?></strong></div>
                    <div><span>Cadastrado em</span><strong><?= e(format_datetime_br($medicamento['created_at'])) ?></strong></div>
                    <div><span>Atualizado em</span><strong><?= e(format_datetime_br($medicamento['updated_at'])) ?></strong></div>
                </div>
            </article>

            <article class="card">
                <div class="card-header">
                    <div>
                        <span class="section-eyebrow">Observacoes</span>
                        <h2>Registro interno</h2>
                    </div>
                </div>

                <p class="description-box">
                    <?= nl2br(e($medicamento['descricao'] ?: 'Nenhuma observacao foi registrada para este medicamento.')) ?>
                </p>
            </article>
        </section>
    </div>
</main>
</body>
</html>
