<html>
    <head>
        <title>FwkXml Test</title>
        <link rel="stylesheet" href="./hljs/styles/monokai.css">
        <script src="./hljs/highlight.pack.js"></script>
        <script>hljs.initHighlightingOnLoad();</script>
    </head>
    <body>
<?php
require_once __DIR__ .'/Tests/bootstrap.php';

function debug($txt) {
    echo '<pre><code class="php" style="padding-bottom:0">' . $txt . '<code></pre>' ."\n";
}



$xml = new Fwk\Xml\XmlFile(__DIR__ .'/build.xml');
$map = new Fwk\Xml\Map();
$path = Fwk\Xml\Path::factory('/project/target', 'targets')
        ->loop(true, '@name')
        ->attribute('name', 'name')
        ->attribute('description', 'description')
        ->attribute('depends', 'depends')
        ->addChildren(
            Fwk\Xml\Path::factory('exec/arg', 'arg')
            ->attribute('line', 'line')
        );

$map->add($path);
$map->add(Fwk\Xml\Path::factory('/project/description', 'description'));

$res = $map->execute($xml);
var_dump($res['targets']);

debug('echo hello($world);');
?>

    </body>
</html>