<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function now_iso(): string
{
    return date('c');
}

function seed_users(): array
{
    return [
        [
            'id' => 1,
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'name' => 'Administrador',
            'role' => 'Gestor',
        ],
        [
            'id' => 2,
            'username' => 'farmacia',
            'password' => password_hash('farmacia123', PASSWORD_DEFAULT),
            'name' => 'Equipe da Unidade',
            'role' => 'Atendente',
        ],
    ];
}

function seed_medicamentos(): array
{
    $now = now_iso();

    return [
        [
            'id' => 1,
            'nome' => 'Dipirona',
            'categoria' => 'Analgésico',
            'principio_ativo' => 'Metamizol sodico',
            'dosagem' => '500 mg',
            'apresentacao' => 'Comprimido',
            'fabricante' => 'Neo Quimica',
            'fornecedor' => 'Distribuidora Vida',
            'lote' => 'DIP-2026-001',
            'registro_anvisa' => '123456789001',
            'validade' => '2026-11-30',
            'estoque' => 180,
            'estoque_minimo' => 50,
            'preco' => 12.9,
            'status' => 'ATIVO',
            'controlado' => false,
            'localizacao' => 'Prateleira A1',
            'descricao' => 'Medicamento para controle de dor e febre.',
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'id' => 2,
            'nome' => 'Omeprazol',
            'categoria' => 'Gastro',
            'principio_ativo' => 'Omeprazol',
            'dosagem' => '20 mg',
            'apresentacao' => 'Capsula',
            'fabricante' => 'Medley',
            'fornecedor' => 'Central Pharma',
            'lote' => 'OME-2026-114',
            'registro_anvisa' => '223456789001',
            'validade' => '2026-08-15',
            'estoque' => 64,
            'estoque_minimo' => 30,
            'preco' => 18.5,
            'status' => 'ATIVO',
            'controlado' => false,
            'localizacao' => 'Prateleira B2',
            'descricao' => 'Protecao gastrica e tratamento de refluxo.',
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'id' => 3,
            'nome' => 'Amoxicilina',
            'categoria' => 'Antibiotico',
            'principio_ativo' => 'Amoxicilina tri-hidratada',
            'dosagem' => '500 mg',
            'apresentacao' => 'Capsula',
            'fabricante' => 'EMS',
            'fornecedor' => 'Bio Distribuicao',
            'lote' => 'AMO-2026-332',
            'registro_anvisa' => '323456789001',
            'validade' => '2026-05-21',
            'estoque' => 22,
            'estoque_minimo' => 25,
            'preco' => 27.4,
            'status' => 'EM FALTA',
            'controlado' => false,
            'localizacao' => 'Geladeira Farmaceutica 01',
            'descricao' => 'Antibiotico oral com estoque abaixo do minimo.',
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'id' => 4,
            'nome' => 'Rivotril',
            'categoria' => 'Controlado',
            'principio_ativo' => 'Clonazepam',
            'dosagem' => '2 mg',
            'apresentacao' => 'Comprimido',
            'fabricante' => 'Roche',
            'fornecedor' => 'ControlMed',
            'lote' => 'RIV-2026-090',
            'registro_anvisa' => '423456789001',
            'validade' => '2026-07-12',
            'estoque' => 16,
            'estoque_minimo' => 10,
            'preco' => 32.8,
            'status' => 'ATIVO',
            'controlado' => true,
            'localizacao' => 'Armario Controlados C3',
            'descricao' => 'Medicamento controlado com acesso restrito.',
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'id' => 5,
            'nome' => 'Vitamina C',
            'categoria' => 'Suplemento',
            'principio_ativo' => 'Acido ascorbico',
            'dosagem' => '1 g',
            'apresentacao' => 'Envelope',
            'fabricante' => 'Cimed',
            'fornecedor' => 'Distribuidora Vida',
            'lote' => 'VTC-2026-441',
            'registro_anvisa' => '523456789001',
            'validade' => '2027-02-28',
            'estoque' => 210,
            'estoque_minimo' => 80,
            'preco' => 9.7,
            'status' => 'ATIVO',
            'controlado' => false,
            'localizacao' => 'Prateleira D4',
            'descricao' => 'Suplemento vitaminico de alta rotatividade.',
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ];
}

function ensure_data_setup(): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0777, true);
    }

    if (!file_exists(USERS_FILE)) {
        file_put_contents(
            USERS_FILE,
            json_encode(seed_users(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    if (!file_exists(MEDICAMENTO_FILE)) {
        file_put_contents(
            MEDICAMENTO_FILE,
            json_encode(seed_medicamentos(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}

function medicamento_defaults(): array
{
    $now = now_iso();

    return [
        'id' => 0,
        'nome' => '',
        'categoria' => 'Medicamento Geral',
        'principio_ativo' => '',
        'dosagem' => '',
        'apresentacao' => 'Comprimido',
        'fabricante' => '',
        'fornecedor' => '',
        'lote' => '',
        'registro_anvisa' => '',
        'validade' => '',
        'estoque' => 0,
        'estoque_minimo' => 0,
        'preco' => 0.0,
        'status' => 'ATIVO',
        'controlado' => false,
        'localizacao' => '',
        'descricao' => '',
        'created_at' => $now,
        'updated_at' => $now,
    ];
}

function load_users(): array
{
    ensure_data_setup();
    $raw = file_get_contents(USERS_FILE);
    $users = json_decode($raw ?: '[]', true);

    return is_array($users) ? $users : [];
}

function normalize_medicamento_status(string $status): string
{
    $status = strtoupper(trim($status));
    $valid = medicamento_status_options();

    return in_array($status, $valid, true) ? $status : 'ATIVO';
}

function normalize_medicamento(array $medicamento): array
{
    $defaults = medicamento_defaults();
    $normalized = array_merge($defaults, $medicamento);

    $normalized['id'] = max(0, (int)($normalized['id'] ?? 0));
    $normalized['nome'] = trim((string)($normalized['nome'] ?? ''));
    $normalized['categoria'] = trim((string)($normalized['categoria'] ?? 'Medicamento Geral'));
    $normalized['principio_ativo'] = trim((string)($normalized['principio_ativo'] ?? ''));
    $normalized['dosagem'] = trim((string)($normalized['dosagem'] ?? ''));
    $normalized['apresentacao'] = trim((string)($normalized['apresentacao'] ?? ''));
    $normalized['fabricante'] = trim((string)($normalized['fabricante'] ?? ''));
    $normalized['fornecedor'] = trim((string)($normalized['fornecedor'] ?? ''));
    $normalized['lote'] = trim((string)($normalized['lote'] ?? ''));
    $normalized['registro_anvisa'] = trim((string)($normalized['registro_anvisa'] ?? ''));
    $normalized['validade'] = trim((string)($normalized['validade'] ?? ''));
    $normalized['estoque'] = max(0, (int)($normalized['estoque'] ?? 0));
    $normalized['estoque_minimo'] = max(0, (int)($normalized['estoque_minimo'] ?? 0));
    $normalized['preco'] = max(0, (float)str_replace(',', '.', (string)($normalized['preco'] ?? 0)));
    $normalized['status'] = normalize_medicamento_status((string)($normalized['status'] ?? 'ATIVO'));
    $normalized['controlado'] = filter_var($normalized['controlado'], FILTER_VALIDATE_BOOLEAN);
    $normalized['localizacao'] = trim((string)($normalized['localizacao'] ?? ''));
    $normalized['descricao'] = trim((string)($normalized['descricao'] ?? ''));
    $normalized['created_at'] = trim((string)($normalized['created_at'] ?? now_iso())) ?: now_iso();
    $normalized['updated_at'] = trim((string)($normalized['updated_at'] ?? now_iso())) ?: now_iso();

    return $normalized;
}

function load_medicamentos(): array
{
    ensure_data_setup();
    $raw = file_get_contents(MEDICAMENTO_FILE);
    $decoded = json_decode($raw ?: '[]', true);
    $medicamentos = is_array($decoded) ? $decoded : [];
    $normalized = array_map('normalize_medicamento', $medicamentos);

    if ($normalized !== $medicamentos) {
        save_medicamentos($normalized);
    }

    return $normalized;
}

function save_medicamentos(array $medicamentos): bool
{
    ensure_data_setup();
    $normalized = array_map('normalize_medicamento', array_values($medicamentos));

    return (bool)file_put_contents(
        MEDICAMENTO_FILE,
        json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

function flash_set(string $type, string $message): void
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (!isset($_SESSION['_flash'])) {
        return null;
    }

    $flash = $_SESSION['_flash'];
    unset($_SESSION['_flash']);

    return $flash;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function require_login(): void
{
    if (current_user() === null) {
        redirect('login.php');
    }
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function next_medicamento_id(array $medicamentos): int
{
    $ids = array_column($medicamentos, 'id');

    return empty($ids) ? 1 : max($ids) + 1;
}

function asset_path(string $file): string
{
    return ASSETS_URL . '/' . ltrim($file, '/');
}

function medicamento_status_options(): array
{
    return ['ATIVO', 'EM FALTA', 'INATIVO'];
}

function medicamento_categories(array $medicamentos): array
{
    $categories = array_unique(array_filter(array_map(
        static fn(array $medicamento): string => trim((string)$medicamento['categoria']),
        $medicamentos
    )));

    natcasesort($categories);

    return array_values($categories);
}

function sanitize_medicamento_input(array $input, int $id, ?array $existing = null): array
{
    $base = $existing ? normalize_medicamento($existing) : medicamento_defaults();

    return [
        'id' => $id,
        'nome' => trim((string)($input['nome'] ?? '')),
        'categoria' => trim((string)($input['categoria'] ?? $base['categoria'])) ?: 'Medicamento Geral',
        'principio_ativo' => trim((string)($input['principio_ativo'] ?? '')),
        'dosagem' => trim((string)($input['dosagem'] ?? '')),
        'apresentacao' => trim((string)($input['apresentacao'] ?? $base['apresentacao'])) ?: 'Comprimido',
        'fabricante' => trim((string)($input['fabricante'] ?? '')),
        'fornecedor' => trim((string)($input['fornecedor'] ?? '')),
        'lote' => trim((string)($input['lote'] ?? '')),
        'registro_anvisa' => trim((string)($input['registro_anvisa'] ?? '')),
        'validade' => trim((string)($input['validade'] ?? '')),
        'estoque' => max(0, (int)($input['estoque'] ?? 0)),
        'estoque_minimo' => max(0, (int)($input['estoque_minimo'] ?? 0)),
        'preco' => max(0, (float)str_replace(',', '.', (string)($input['preco'] ?? 0))),
        'status' => normalize_medicamento_status((string)($input['status'] ?? $base['status'])),
        'controlado' => !empty($input['controlado']),
        'localizacao' => trim((string)($input['localizacao'] ?? '')),
        'descricao' => trim((string)($input['descricao'] ?? '')),
        'created_at' => $base['created_at'] ?? now_iso(),
        'updated_at' => now_iso(),
    ];
}

function find_medicamento_by_id(array $medicamentos, int $id): ?array
{
    foreach ($medicamentos as $medicamento) {
        if ((int)$medicamento['id'] === $id) {
            return normalize_medicamento($medicamento);
        }
    }

    return null;
}

function find_medicamento_index_by_id(array $medicamentos, int $id): ?int
{
    foreach ($medicamentos as $index => $medicamento) {
        if ((int)$medicamento['id'] === $id) {
            return $index;
        }
    }

    return null;
}

function format_date_br(?string $date): string
{
    if (!$date) {
        return '--';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y', $timestamp) : (string)$date;
}

function format_datetime_br(?string $date): string
{
    if (!$date) {
        return '--';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y H:i', $timestamp) : (string)$date;
}

function format_currency_br(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function days_until(?string $date): ?int
{
    if (!$date) {
        return null;
    }

    try {
        $today = new DateTimeImmutable('today');
        $target = new DateTimeImmutable($date);
    } catch (Exception $exception) {
        return null;
    }

    return (int)$today->diff($target->setTime(0, 0))->format('%r%a');
}

function is_expired(?string $date): bool
{
    $days = days_until($date);

    return $days !== null && $days < 0;
}

function is_expiring_soon(?string $date, int $days = 90): bool
{
    $remaining = days_until($date);

    return $remaining !== null && $remaining >= 0 && $remaining <= $days;
}

function is_low_stock(array $medicamento): bool
{
    return (int)$medicamento['estoque'] <= (int)$medicamento['estoque_minimo']
        && $medicamento['status'] !== 'INATIVO';
}

function medicamento_priority(array $medicamento): int
{
    if (is_expired($medicamento['validade'])) {
        return 1;
    }

    if (is_low_stock($medicamento)) {
        return 2;
    }

    if (is_expiring_soon($medicamento['validade'])) {
        return 3;
    }

    if ($medicamento['status'] === 'EM FALTA') {
        return 4;
    }

    return 5;
}

function sort_medicamentos(array &$medicamentos): void
{
    usort($medicamentos, static function (array $left, array $right): int {
        $leftPriority = medicamento_priority($left);
        $rightPriority = medicamento_priority($right);

        if ($leftPriority !== $rightPriority) {
            return $leftPriority <=> $rightPriority;
        }

        $leftDate = strtotime($left['validade'] ?: '2999-12-31') ?: strtotime('2999-12-31');
        $rightDate = strtotime($right['validade'] ?: '2999-12-31') ?: strtotime('2999-12-31');

        if ($leftDate !== $rightDate) {
            return $leftDate <=> $rightDate;
        }

        return strcasecmp($left['nome'], $right['nome']);
    });
}

function filter_medicamentos(array $medicamentos, array $filters): array
{
    $search = trim((string)($filters['q'] ?? ''));
    $status = strtoupper(trim((string)($filters['status'] ?? '')));
    $categoria = trim((string)($filters['categoria'] ?? ''));
    $risco = trim((string)($filters['risco'] ?? ''));

    $filtered = array_filter($medicamentos, static function (array $medicamento) use ($search, $status, $categoria, $risco): bool {
        if ($search !== '') {
            $haystack = implode(' ', [
                $medicamento['nome'],
                $medicamento['principio_ativo'],
                $medicamento['categoria'],
                $medicamento['fabricante'],
                $medicamento['fornecedor'],
                $medicamento['lote'],
                $medicamento['localizacao'],
            ]);

            if (stripos($haystack, $search) === false) {
                return false;
            }
        }

        if ($status !== '' && $medicamento['status'] !== $status) {
            return false;
        }

        if ($categoria !== '' && $medicamento['categoria'] !== $categoria) {
            return false;
        }

        if ($risco === 'baixo_estoque' && !is_low_stock($medicamento)) {
            return false;
        }

        if ($risco === 'vencendo' && !is_expiring_soon($medicamento['validade'])) {
            return false;
        }

        if ($risco === 'vencido' && !is_expired($medicamento['validade'])) {
            return false;
        }

        if ($risco === 'controlado' && !$medicamento['controlado']) {
            return false;
        }

        return true;
    });

    $result = array_values($filtered);
    sort_medicamentos($result);

    return $result;
}

function dashboard_stats(array $medicamentos): array
{
    $stats = [
        'total' => count($medicamentos),
        'ativos' => 0,
        'baixo_estoque' => 0,
        'vencendo' => 0,
        'controlados' => 0,
        'estoque_total' => 0,
        'valor_estoque' => 0.0,
    ];

    foreach ($medicamentos as $medicamento) {
        if ($medicamento['status'] === 'ATIVO') {
            $stats['ativos']++;
        }

        if (is_low_stock($medicamento)) {
            $stats['baixo_estoque']++;
        }

        if (is_expiring_soon($medicamento['validade'])) {
            $stats['vencendo']++;
        }

        if ($medicamento['controlado']) {
            $stats['controlados']++;
        }

        $stats['estoque_total'] += (int)$medicamento['estoque'];
        $stats['valor_estoque'] += (float)$medicamento['estoque'] * (float)$medicamento['preco'];
    }

    return $stats;
}

function medicamento_status_badge(string $status): string
{
    return match ($status) {
        'ATIVO' => 'badge-success',
        'EM FALTA' => 'badge-warning',
        'INATIVO' => 'badge-neutral',
        default => 'badge-neutral',
    };
}

function medicamento_risk_labels(array $medicamento): array
{
    $labels = [];
    $days = days_until($medicamento['validade']);

    if (is_expired($medicamento['validade'])) {
        $labels[] = ['class' => 'badge-danger', 'text' => 'Vencido'];
    } elseif (is_expiring_soon($medicamento['validade']) && $days !== null) {
        $labels[] = ['class' => 'badge-warning', 'text' => 'Vence em ' . $days . ' dias'];
    }

    if (is_low_stock($medicamento)) {
        $labels[] = ['class' => 'badge-warning', 'text' => 'Baixo estoque'];
    }

    if ($medicamento['controlado']) {
        $labels[] = ['class' => 'badge-info', 'text' => 'Controlado'];
    }

    if (empty($labels)) {
        $labels[] = ['class' => 'badge-soft', 'text' => 'Sem alerta'];
    }

    return $labels;
}
