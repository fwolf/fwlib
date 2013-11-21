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

$listTable = new ListTable($tpl);


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
$config = array(
    'page_size' => 3,
);
$listTable->setConfig($config);

$listTable->setData($data, $title);
//$listTable->setId('');
$listTable->setPager();

$html1 = $listTable->getHtml();


// Another table in same page
$listTable->setId('lt1');
// Data is trimmed, need re-make
$listTable->setData($data, $title);
// set sort
$listTable->setOrderby(0, 'asc');
// MUST refresh pager
$listTable->setPager();

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
  .fl_lt, .fl_lt_lt1 {
    border: 0px solid red;
    margin: auto;
    width: 70%;
  }
  .fl_lt form, .fl_lt_lt1 form {
    display: inline-block;
  }
  .fl_lt_pager, .fl_lt_lt1_pager {
    text-align: right;
  }
  .fl_lt table, .fl_lt_lt1 table {
    margin: auto;
    width: 100%;
  }
  .fl_lt table, .fl_lt td, .fl_lt th {
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
echo "<h2>Common Style</h2>\n";
echo $html1;

echo "<hr />\n";

echo "<h2>With header sort</h2>\n";
echo $html2;


echo "<hr />\n";

echo '<pre>
$listTable::getSqlInfoFromUrl()
' . var_export($listTable->getSqlInfoFromUrl(), true) . '

$listTable::getSqlInfo()
' . var_export($listTable->getSqlInfo(), true) . '
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
  $(".fl_lt table").css("table-layout", "fixed");
  // * include th & td here
  $(".fl_lt tr > *:nth-child(2)").css("background-color", "green");
  $(".fl_lt tr > *:nth-child(2)").css("width", "6em");
  //$(".fl_lt tr > *:nth-child(2)").css("width", "3em");

  // If "table-layout: fixed;" is not assigned,
  // width limit will work, but overflow content
  // may make width raise.
  $(".fl_lt_lt1 tr > *:nth-child(2)").css("width", "30%");

  //--><!]]>
  </script>


</body>
</html>
