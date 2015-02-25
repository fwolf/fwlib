<?php
require __DIR__ . '/../config.default.php';

use Fwlib\Html\TextDocument\DocumentView;
use Fwlib\Html\TextDocument\Markdown;
use Fwlib\Html\TextDocument\Restructuredtext;
use Fwlib\Html\TextDocument\UnknownMarkup;

$dv = new DocumentView(
    [
        'className' => 'document-view article',
        'dir'       => __DIR__ . '/',
        'exclude'   => ['^\.*', 'index.php'],
        'rawView'   => true,
        'showFileSize'  => true,
    ]
);
$dv->setInstance(new Markdown, 'Markdown');
// Disable css from rst2html
$rest = new Restructuredtext;
$rest->cmdOption[] = 'stylesheet=""';
$dv->setInstance($rest, 'Restructuredtext');
$dv->setInstance(new UnknownMarkup, 'UnknownMarkup');
$html = $dv->display(true);
$title = $dv->title;
?>

<!DOCTYPE HTML>
<html lang='en'>
<head>
  <meta charset='utf-8' />
  <title>
<?php
echo $title;
if ('Index' != $dv->currentDocumentType) {
    echo ' - ' . $dv->config['titleTail'];
}
?>
  </title>

  <link rel='stylesheet' href='../css/reset.css'
    type='text/css' media='all' />
  <link rel='stylesheet' href='../css/default.css'
    type='text/css' media='all' />
  <link rel='stylesheet' href='../css/document-view.css'
    type='text/css' media='all' />

  <style type='text/css' media='all'>>
  /*<![CDATA[*/
  /* Write CSS below */
  /*]]>*/
  </style>
</head>
<body>

<?php
if ('Index' == $dv->currentDocumentType || 'Unknown' == $dv->currentDocumentType) {
    echo "
  <h1>$title</h1>

";
}

echo $html;
?>

<footer>
  <hr />
  <div id='copyright'>
    <p>Copyright 2009-2013 Fwolf, All Rights Reserved.</p>
    <p>Distributed under the
      <a href='http://opensource.org/licenses/mit-license'>MIT License</a>.
  </p>
  </div>
</footer>

</body>
</html>
