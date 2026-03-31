<?php
require_once __DIR__ . '/inc/functions.php';

require_login();

$user = current_user();
$flash = flash_get();
$medicamentos = load_medicamentos();
$stats = dashboard_stats($medicamentos);

$vencendo = array_values(array_filter(
    $medicamentos,
    static fn(array $medicamento): bool => is_expiring_soon($medicamento['validade'])
));
$baixoEstoque = array_values(array_filter(
    $medicamentos,
    static fn(array $medicamento): bool => is_low_stock($medicamento)
));

sort_medicamentos($vencendo);
sort_medicamentos($baixoEstoque);

$vencendo = array_slice($vencendo, 0, 5);
$baixoEstoque = array_slice($baixoEstoque, 0, 5);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel | <?= APP_NAME ?></title>
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
            <span class="user-chip"><?= e($user['name']) ?> - <?= e($user['role'] ?? 'Usuario') ?></span>
            <a class="btn btn-ghost" href="medicamentos.php">Estoque</a>
            <a class="btn btn-ghost" href="logout.php">Sair</a>
        </div>
    </div>
</header>

<main class="page-wrap">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" data-autohide><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <section class="hero-card">
            <div>
                <span class="hero-pill">Painel principal</span>
                <h1>Visao central do estoque da unidade.</h1>
                <p class="hero-lead">
                    Acompanhe a disponibilidade dos medicamentos, identifique o que exige reposicao
                    e monitore itens com validade proxima para manter o atendimento organizado.
                </p>
                <div class="quick-actions">
                    <a class="btn btn-light" href="medicamento_novo.php">Cadastrar medicamento</a>
                    <a class="btn btn-outline-light" href="medicamentos.php">Abrir central</a>
                </div>
            </div>

            <div class="hero-summary">
                <div class="summary-item">
                    <span>Unidades em estoque</span>
                    <strong><?= e($stats['estoque_total']) ?></strong>
                </div>
                <div class="summary-item">
                    <span>Valor estimado do estoque</span>
                    <strong><?= e(format_currency_br((float)$stats['valor_estoque'])) ?></strong>
                </div>
                <div class="summary-item">
                    <span>Itens em alerta</span>
                    <strong><?= e($stats['baixo_estoque'] + $stats['vencendo']) ?></strong>
                </div>
            </div>
        </section>

        <section class="kpi-grid">
            <article class="stat-card">
                <span>Medicamentos cadastrados</span>
                <strong><?= e($stats['total']) ?></strong>
                <small>itens registrados na central</small>
            </article>
            <article class="stat-card">
                <span>Disponiveis</span>
                <strong><?= e($stats['ativos']) ?></strong>
                <small>cadastros com status ativo</small>
            </article>
            <article class="stat-card">
                <span>Abaixo do minimo</span>
                <strong><?= e($stats['baixo_estoque']) ?></strong>
                <small>precisam de reposicao</small>
            </article>
            <article class="stat-card">
                <span>Validade proxima</span>
                <strong><?= e($stats['vencendo']) ?></strong>
                <small>vencem em ate 90 dias</small>
            </article>
            <article class="stat-card">
                <span>Controlados</span>
                <strong><?= e($stats['controlados']) ?></strong>
                <small>com acompanhamento especial</small>
            </article>
        </section>

        <section class="section-grid">
            <article class="card">
                <div class="card-header">
                    <div>
                        <span class="section-eyebrow">Reposicao</span>
                        <h2>Itens abaixo do estoque minimo</h2>
                    </div>
                    <a class="btn btn-secondary" href="medicamentos.php?risco=baixo_estoque">Ver no estoque</a>
                </div>

                <?php if (empty($baixoEstoque)): ?>
                    <div class="empty-state">
                        <strong>Nenhum medicamento esta abaixo do estoque minimo.</strong>
                        <p>A central segue sem pendencias de reposicao no momento.</p>
                    </div>
                <?php else: ?>
                    <div class="list-stack">
                        <?php foreach ($baixoEstoque as $medicamento): ?>
                            <a class="list-row" href="medicamento.php?id=<?= e($medicamento['id']) ?>">
                                <div>
                                    <strong><?= e($medicamento['nome']) ?></strong>
                                    <span><?= e($medicamento['categoria']) ?> | <?= e($medicamento['dosagem']) ?></span>
                                </div>
                                <div class="list-row-meta">
                                    <span><?= e($medicamento['estoque']) ?> unidades | minimo <?= e($medicamento['estoque_minimo']) ?></span>
                                    <span class="badge badge-warning">Reposicao</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="card">
                <div class="card-header">
                    <div>
                        <span class="section-eyebrow">Validade</span>
                        <h2>Medicamentos com validade proxima</h2>
                    </div>
                    <a class="btn btn-secondary" href="medicamentos.php?risco=vencendo">Abrir lista</a>
                </div>

                <?php if (empty($vencendo)): ?>
                    <div class="empty-state">
                        <strong>Nenhum medicamento vence nos proximos 90 dias.</strong>
                        <p>O controle de validade da unidade esta regular no momento.</p>
                    </div>
                <?php else: ?>
                    <div class="list-stack">
                        <?php foreach ($vencendo as $medicamento): ?>
                            <?php $days = days_until($medicamento['validade']); ?>
                            <a class="list-row" href="medicamento.php?id=<?= e($medicamento['id']) ?>">
                                <div>
                                    <strong><?= e($medicamento['nome']) ?></strong>
                                    <span>Validade: <?= e(format_date_br($medicamento['validade'])) ?></span>
                                </div>
                                <div class="list-row-meta">
                                    <span><?= $days !== null ? e($days . ' dias') : '--' ?></span>
                                    <span class="badge badge-warning">Validade</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        </section>
    </div>
</main>
</body>
</html>
