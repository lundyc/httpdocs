<?php

declare(strict_types=1);

$title = $title ?? 'MyClubHub';
$message = $message ?? '';

$renderHeader = static function (string $pageTitle): void {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #0d1117; color: #f0f6fc; margin: 0; }
            header, footer { background-color: #161b22; padding: 1.5rem; text-align: center; }
            main { padding: 3rem 1.5rem; max-width: 720px; margin: 0 auto; }
            h1 { margin-top: 0; font-size: 2.5rem; }
            p { font-size: 1.125rem; line-height: 1.6; }
        </style>
    </head>
    <body>
        <header>
            <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        </header>
    <?php
};

$renderFooter = static function (): void {
    ?>
        <footer>
            &copy; <?= date('Y'); ?> MyClubHub
        </footer>
    </body>
    </html>
    <?php
};

$renderHeader($title);
?>
<main>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
</main>
<?php $renderFooter();
