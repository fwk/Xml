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

function debug($txt, $lang = "php") {
    echo '<pre><code class="'. $lang .'" style="padding-bottom:0">' . $txt . '<code></pre>' ."\n";
}

/**
 * Indents a flat JSON string to make it more human-readable.
 *
 * @param string $json The original JSON string to process.
 *
 * @return string Indented version of the original JSON string.
 */
function indentJson($json) {

    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }

    return $result;
}

function debug_json($txt) {
    
    debug(indentJson($txt), "json");
}

use Fwk\Xml\Path;

$xml = new Fwk\Xml\XmlFile(__DIR__ .'/build/codesniffer.xml');
$map = new Fwk\Xml\Map();

$map->add(Path::factory('/checkstyle', 'checkstyle')->attribute('version'));
$map->add(Path::factory('/checkstyle/file', 'files')
        ->loop(true, '@name')
        ->addChildren(
            Path::factory('error', 'lines')
            ->loop(true)
            ->attribute('line')
            ->attribute('column')
            ->attribute('severity')
            ->attribute('message')
            ->attribute('source')
        )
);


// $xml = new Fwk\Xml\XmlFile(__DIR__ .'/Tests/rss-techcrunch.xml');
// $map = new Fwk\Xml\Maps\Rss();
$res = $map->execute($xml);

var_dump($res);
?>

    </body>
</html>