<?php
require_once __DIR__ . '/../../Core/Config.php';
use Core\Config;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'DataSnap'; ?></title>
    <link rel="shortcut icon" href="<?php echo Config::getIconUrl('logo-solo.svg'); ?>">
    <?php if (isset($cssFiles)): ?>
        <?php foreach ($cssFiles as $cssFile): ?>
            <link rel="stylesheet" href="<?php echo Config::getCssUrl($cssFile); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <?php echo $content ?? ''; ?>
    
    <?php if (isset($jsFiles)): ?>
        <?php foreach ($jsFiles as $jsFile): ?>
            <script src="<?php echo Config::getJsUrl($jsFile); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>