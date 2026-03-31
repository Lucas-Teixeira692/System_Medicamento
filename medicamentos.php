<?php
require_once __DIR__ . '/inc/functions.php';

require_login();

$medicamentos = load_medicamentos();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $medicamento = find_medicamento_by_id($medicamentos, $id);

    if ($medicamento) {
        $medicamentos = array_values(array_filter(
            $medicamentos,
            static fn(array $item): bool => (int)$item['id'] !== $id
        ));
        save_medicamentos($medicamentos);
        flash_set('success', 'Medicamento excluido com sucesso.');
    } else {
        flash_set('error', 'Nao foi possivel localizar o medicamento selecionado.');
    }

    redirect('medicamentos.php');
}

$filters = [
    'q' => trim((string)($_GET['q'] ?? '')),
    'status' => trim((string)($_GET['status'] ?? '')),
    'categoria' => trim((string)($_GET['categoria'] ?? '')),
    'risco' => trim((string)($_GET['risco'] ?? '')),
];

$filtered = filter_medicamentos($medicamentos, $filters);
$flash = flash_get();
$user = current_user();
$categories = medicamento_categories($medicamentos);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Central de medicamentos | <?= APP_NAME ?></title>
<link rel="stylesheet" href="<?= asset_path('style.css') ?>">
<script src="<?= asset_path('app.js') ?>" defer></script>
</head>
<body class="app-page">
<header class="topbar">
    <div class="container">
        <div class="brand">
            <div class="brand-mark"><?= APP_MARK ?></div>
            <div>
                <span class="brand-eyebrow">Gestao da unidade</span>
                <strong><?= APP_NAME ?></strong>
            </div>
        </div>

        <div class="user-strip">
            <span class="user-chip"><?= e($user['name']) ?></span>
            <a class="btn btn-ghost" href="dashboard.php">Painel</a>
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
            <div class="card-header">
                <div>
                    <span class="section-eyebrow">Central da unidade</span>
                    <h1>Central de medicamentos</h1>
                    <p class="card-text">
                        Consulte os itens cadastrados, aplique filtros e acompanhe o status de cada medicamento
                        usado pela unidade de saude.
                    </p>
                </div>
                <div class="quick-actions">
                    <a class="btn btn-primary" href="medicamento_novo.php">Cadastrar medicamento</a>
                </div>
            </div>

            <div class="metric-strip">
                <span class="metric-pill">Itens encontrados: <strong data-result-count><?= e(count($filtered)) ?></strong></span>
                <span class="metric-pill">Abaixo do minimo: <strong><?= e(count(array_filter($filtered, 'is_low_stock'))) ?></strong></span>
                <span class="metric-pill">Validade proxima: <strong><?= e(count(array_filter($filtered, static fn(array $item): bool => is_expiring_soon($item['validade'])))) ?></strong></span>
                <span class="metric-pill">Controlados: <strong><?= e(count(array_filter($filtered, static fn(array $item): bool => $item['controlado']))) ?></strong></span>
            </div>

            <form class="filter-grid" method="get" data-filter-form>
                <label class="field">
                    <span>Buscar</span>
                    <input name="q" value="<?= e($filters['q']) ?>" placeholder="Nome, lote, fabricante ou local de armazenamento">
                </label>

                <label class="field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">Todos</option>
                        <?php foreach (medicamento_status_options() as $status): ?>
                            <option value="<?= e($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="field">
                    <span>Categoria</span>
                    <select name="categoria">
                        <option value="">Todas</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category) ?>" <?= $filters['categoria'] === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="field">
                    <span>Situacao</span>
                    <select name="risco">
                        <option value="">Todas</option>
                        <option value="baixo_estoque" <?= $filters['risco'] === 'baixo_estoque' ? 'selected' : '' ?>>Baixo estoque</option>
                        <option value="vencendo" <?= $filters['risco'] === 'vencendo' ? 'selected' : '' ?>>Validade proxima</option>
                        <option value="vencido" <?= $filters['risco'] === 'vencido' ? 'selected' : '' ?>>Vencido</option>
                        <option value="controlado" <?= $filters['risco'] === 'controlado' ? 'selected' : '' ?>>Controlado</option>
                    </select>
                </label>

                <div class="filter-actions">
                    <button class="btn btn-primary" type="submit">Aplicar filtros</button>
                    <a class="btn btn-secondary" href="medicamentos.php">Limpar</a>
                </div>
            </form>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Medicamento</th>
                            <th>Lote / validade</th>
                            <th>Estoque</th>
                            <th>Preco</th>
                            <th>Status</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($filtered)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <strong>Nenhum medicamento foi localizado.</strong>
                                    <p>Revise os filtros aplicados ou registre um novo item na central.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($filtered as $medicamento): ?>
                            <tr data-priority="<?= e(medicamento_priority($medicamento)) ?>" data-med-row>
                                <td>#<?= e($medicamento['id']) ?></td>
                                <td>
                                    <div class="table-title"><?= e($medicamento['nome']) ?></div>
                                    <div class="table-subtitle">
                                        <?= e($medicamento['categoria']) ?> | <?= e($medicamento['principio_ativo']) ?> | <?= e($medicamento['dosagem']) ?>
                                    </div>
                                    <div class="badge-row">
                                        <?php foreach (medicamento_risk_labels($medicamento) as $badge): ?>
                                            <span class="badge <?= e($badge['class']) ?>"><?= e($badge['text']) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-title"><?= e($medicamento['lote']) ?: '--' ?></div>
                                    <div class="table-subtitle">Validade: <?= e(format_date_br($medicamento['validade'])) ?></div>
                                </td>
                                <td>
                                    <div class="table-title"><?= e($medicamento['estoque']) ?> unidades</div>
                                    <div class="table-subtitle">Minimo: <?= e($medicamento['estoque_minimo']) ?></div>
                                </td>
                                <td><?= e(format_currency_br((float)$medicamento['preco'])) ?></td>
                                <td><span class="badge <?= e(medicamento_status_badge($medicamento['status'])) ?>"><?= e($medicamento['status']) ?></span></td>
                                <td>
                                    <div class="action-group">
                                        <a class="btn btn-small btn-secondary" href="medicamento.php?id=<?= e($medicamento['id']) ?>">Ver</a>
                                        <a class="btn btn-small btn-primary" href="medicamento_editar.php?id=<?= e($medicamento['id']) ?>">Editar</a>
                                        <form class="inline-form" method="post" onsubmit='return confirmDelete(<?= json_encode($medicamento['nome'], JSON_UNESCAPED_UNICODE) ?>);'>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= e($medicamento['id']) ?>">
                                            <button class="btn btn-small btn-danger" type="submit">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
</body>
</html>
