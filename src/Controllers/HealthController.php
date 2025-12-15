<?php

declare(strict_types=1);

namespace MyClubHub\Controllers;

use MyClubHub\Core\Database;
use MyClubHub\Core\Request;
use MyClubHub\Core\Response;
use PDO;
use Throwable;
use function htmlspecialchars;

final class HealthController
{
    public function index(Request $request): Response
    {
        $config = Database::getConfig();
        $dbName = (string)($config['database'] ?? '');
        $status = 'FAILED';
        $version = 'Unknown';
        $tables = [];
        $error = null;

        try {
            $pdo = Database::connect();
            $status = 'CONNECTED';

            $versionStmt = $pdo->query('SELECT VERSION() AS version');
            $versionRow = $versionStmt !== false ? $versionStmt->fetch(PDO::FETCH_ASSOC) : false;
            if ($versionRow && isset($versionRow['version'])) {
                $version = (string)$versionRow['version'];
            }

            $tablesStmt = $pdo->query('SHOW TABLES');
            if ($tablesStmt !== false) {
                $tableNames = $tablesStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
                $tableNames = array_slice($tableNames, 0, 5);

                foreach ($tableNames as $tableName) {
                    $safeName = str_replace('`', '``', (string)$tableName);
                    $countStmt = $pdo->query(sprintf('SELECT COUNT(*) AS cnt FROM `%s`', $safeName));
                    $countRow = $countStmt !== false ? $countStmt->fetch(PDO::FETCH_ASSOC) : null;
                    $tables[] = [
                        'name' => (string)$tableName,
                        'count' => isset($countRow['cnt']) ? (int)$countRow['cnt'] : 0,
                    ];
                }
            }
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }

        $body = $this->render($status, $dbName, $version, $tables, $error);
        $statusCode = $status === 'CONNECTED' ? 200 : 500;

        return Response::html($body, $statusCode);
    }

    /**
     * @param array<int, array{name: string, count: int}> $tables
     */
    private function render(string $status, string $dbName, string $version, array $tables, ?string $error): string
    {
        $esc = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Health Check</title>
            <style>
                body { font-family: Arial, sans-serif; background-color: #0d1117; color: #f0f6fc; margin: 0; padding: 2rem; }
                h1 { margin-top: 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
                th, td { border: 1px solid #30363d; padding: 0.5rem; text-align: left; }
                th { background-color: #161b22; }
                .status { font-weight: bold; }
                .failed { color: #ff7b72; }
                .connected { color: #3fb950; }
                .error { margin-top: 1rem; color: #ff7b72; }
            </style>
        </head>
        <body>
            <h1>Health Check</h1>
            <p class="status">
                DB Status: <span class="<?= $status === 'CONNECTED' ? 'connected' : 'failed'; ?>"><?= $esc($status); ?></span>
            </p>
            <p>Database: <?= $esc($dbName !== '' ? $dbName : 'Not configured'); ?></p>
            <p>MariaDB Version: <?= $esc($version); ?></p>
            <?php if ($tables !== []): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Table</th>
                            <th>Row Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $table): ?>
                            <tr>
                                <td><?= $esc($table['name']); ?></td>
                                <td><?= $esc((string)$table['count']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <?php if ($error !== null): ?>
                <div class="error">
                    <strong>Error:</strong> <?= $esc($error); ?>
                </div>
            <?php endif; ?>
        </body>
        </html>
        <?php

        return (string)ob_get_clean();
    }
}
