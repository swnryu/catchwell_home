<?php
// 디렉토리 탐색기 함수
function listFilesAndDirectories($dir) {
    $items = array();
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != "..") {
                    $items[] = $file;
                }
            }
            closedir($dh);
        }
    }
    return $items;
}

$currentDir = isset($_GET['dir']) ? $_GET['dir'] : '.';
$items = listFilesAndDirectories($currentDir);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP File Explorer</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .directory, .file { margin: 10px 0; }
        .directory a { font-weight: bold; }
    </style>
</head>
<body>
    <h1>PHP File Explorer</h1>
    <h2>Directory: <?php echo realpath($currentDir); ?></h2>
    <ul>
        <?php
        if ($currentDir != '.') {
            $parentDir = dirname($currentDir);
            echo '<li class="directory"><a href="?dir=' . urlencode($parentDir) . '">.. (Parent Directory)</a></li>';
        }
        foreach ($items as $item) {
            $itemPath = $currentDir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($itemPath)) {
                echo '<li class="directory"><a href="?dir=' . urlencode($itemPath) . '">' . htmlspecialchars($item) . '</a></li>';
            } else {
                echo '<li class="file"><a href="open_file.php?file=' . urlencode($itemPath) . '" target="_blank">' . htmlspecialchars($item) . '</a></li>';
            }
        }
        ?>
    </ul>
</body>
</html>
