<?php
require_once __DIR__ . '/inc/functions.php';

ensure_data_setup();

if (current_user()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $users = load_users();
    $found = null;

    foreach ($users as $user) {
        if (($user['username'] ?? '') === $username && password_verify($password, $user['password'] ?? '')) {
            $found = $user;
            break;
        }
    }

    if ($found) {
        $_SESSION['user'] = [
            'id' => $found['id'],
            'username' => $found['username'],
            'name' => $found['name'],
            'role' => $found['role'] ?? 'Usuario',
        ];

        flash_set('success', 'Acesso realizado com sucesso.');
        redirect('dashboard.php');
    }

    $error = 'Usuario ou senha invalidos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Acesso | <?= APP_NAME ?></title>
<link rel="stylesheet" href="<?= asset_path('style.css') ?>">
<script src="<?= asset_path('app.js') ?>" defer></script>
</head>
<body class="login-page">
<main class="login-shell">
    <section class="login-showcase">
        <div class="login-showcase-top">
            <div class="login-brandbar">
                <div class="login-brandmark"><?= APP_MARK ?></div>
                <div>
                    <span class="hero-pill"><?= APP_NAME ?></span>
                    <h1>Central de medicamentos com visual de sistema, foco institucional e acesso seguro.</h1>
                </div>
            </div>

            <p class="hero-lead">
                Plataforma para acompanhamento do estoque, validade, medicamentos controlados,
                cadastro padronizado e apoio operacional da unidade de saude.
            </p>
        </div>

        <div class="login-highlight-grid">
            <article class="login-highlight-card">
                <strong>Controle do estoque</strong>
                <span>Acompanhe disponibilidade, itens abaixo do minimo e necessidade de reposicao.</span>
            </article>
            <article class="login-highlight-card">
                <strong>Seguranca da assistencia</strong>
                <span>Monitore validade, lote, local de armazenamento e medicamentos controlados.</span>
            </article>
            <article class="login-highlight-card">
                <strong>Padrao institucional</strong>
                <span>Organize a central de medicamentos com um fluxo claro para farmacia e equipe medica.</span>
            </article>
        </div>

        <div class="login-showcase-footer">
            <div class="login-status-card">
                <span class="status-label">Visao da unidade</span>
                <strong>Painel centralizado</strong>
                <p>Uma entrada unica para consulta, cadastro e acompanhamento do estoque assistencial.</p>
            </div>
            <div class="login-status-card">
                <span class="status-label">Uso operacional</span>
                <strong>Equipe medica e farmacia</strong>
                <p>Estrutura pronta para rotina diaria, conferencias internas e consulta rapida das informacoes.</p>
            </div>
        </div>
    </section>

    <section class="login-card">
        <div class="login-card-header">
            <span class="section-eyebrow">Acesso interno</span>
            <h2>Entrar no sistema</h2>
            <p>Informe suas credenciais para acessar a central de medicamentos da unidade.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error" data-autohide><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="form-stack" autocomplete="off">
            <label class="field">
                <span>Usuario</span>
                <input name="username" required autofocus placeholder="Digite seu usuario">
            </label>

            <label class="field">
                <span>Senha</span>
                <div class="password-field">
                    <input type="password" name="password" required placeholder="Digite sua senha">
                    <button type="button" class="password-toggle" data-toggle-password>Mostrar</button>
                </div>
            </label>

            <button type="submit" class="btn btn-primary btn-block">Acessar painel</button>
        </form>

        <div class="login-credentials">
            <div class="login-credential-card">
                <span class="status-label">Perfil administrador</span>
                <strong>Gestao da unidade</strong>
                <p><code>admin</code> / <code>admin123</code></p>
            </div>
            <div class="login-credential-card">
                <span class="status-label">Perfil operacional</span>
                <strong>Equipe da unidade</strong>
                <p><code>farmacia</code> / <code>farmacia123</code></p>
            </div>
        </div>

        <div class="login-footer">
            <div class="mini-stat">
                <strong>Estoque</strong>
                <span>controle centralizado</span>
            </div>
            <div class="mini-stat">
                <strong>Validade</strong>
                <span>acompanhamento continuo</span>
            </div>
            <div class="mini-stat">
                <strong>Cadastro</strong>
                <span>padrao institucional</span>
            </div>
        </div>
    </section>
</main>
</body>
</html>
