<?php
require __DIR__ . '/../../../config.default.php';

use Fwlib\Bridge\Smarty;
use Fwlib\Config\ConfigGlobal;
use Fwlib\Html\ListTable;

// Prepare object
$tpl = new Smarty;
$tpl->compile_dir = ConfigGlobal::get('smarty.compileDir');
$tpl->template_dir = __DIR__ . '/../';
$tpl->cache_dir = ConfigGlobal::get('smarty.cacheDir');

$config = array(
    'pageSize' => 3,
);
$listTable = new ListTable($tpl, $config);


// Prepare dummy data
$title = array();
$data = array();
$cols = 6;
$rows = 50;
for ($i = 0; $i < $cols; $i++) {
    $title[$i] = 'Head ' . $i;
}
for ($j = 0; $j < $rows; $j++) {
    for ($i = 0; $i < $cols; $i++) {
        $data[$j][$i] = "Row $j Col $i";
    }
}


// Show list table
$listTable->setData($data, $title);

$html1 = $listTable->getHtml();


// Another table in same page
$listTable->setId(2);
// Data is trimmed, need re-make
$listTable->setData($data, $title);
// set sort
$listTable->setOrderby(0, 'asc');

$html2 = $listTable->getHtml();
?>

<!DOCTYPE HTML>
<html lang='en'>
<head>
  <meta charset='utf-8' />
  <title>ListTable Demo</title>

  <link rel='stylesheet' href='../../../css/reset.css'
    type='text/css' media='all' />
  <link rel='stylesheet' href='../../../css/default.css'
    type='text/css' media='all' />

  <style type='text/css' media='all'>
  /*<![CDATA[*/
  /* Write CSS below */
  .ListTable {
    border: 0px solid red;
    margin: auto;
    width: 70%;
  }
  .ListTable form {
    display: inline-block;
  }
  .ListTable table {
    margin: auto;
    width: 100%;
  }
  .ListTable table, .ListTable td, .ListTable th {
    border: 1px solid black;
    border-collapse: collapse;
  }
  pre {
    text-align: left;
  }
  /*]]>*/
  </style>


<script type="text/javascript" src="/js/jquery.js">
</script>


</head>
<body>

<?php
echo "<h2>Common style</h2>\n";
echo $html1;

echo "<hr />\n";

echo "<h2>With header sort</h2>\n";
echo $html2;


echo "<hr />\n";

echo '<pre>
$listTable::getSqlConfig()
' . var_export($listTable->getSqlConfig(), true) . '
</pre>
';
?>


  <!-- Below js MUST place after html of list table -->
  <script type="text/javascript">
  <!--//--><![CDATA[//>
  <!--
  // Assign width for col n

  // If "table-layout: fixed;" is assigned also,
  // then td width is assigned + fixed_for_left,
  // content width exceed limit will auto wrap,
  // but overflow content can also been seen.
  $(".ListTable table").css("table-layout", "fixed");
  // * include th & td here
  $(".ListTable tr > *:nth-child(2)").css("background-color", "green");
  $(".ListTable tr > *:nth-child(2)").css("width", "9em");
  //$(".ListTable tr > *:nth-child(2)").css("width", "3em");

  // If "table-layout: fixed;" is not assigned,
  // width limit will work, but overflow content
  // may make width raise.
  $("#ListTable-2 tr > *:nth-child(2)").css("width", "30%");

  //--><!]]>
  </script>


</body>
</html>
