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
    'pageSize'  => 3,
    'tdAdd'     => array(
        'title'     => 'nowrap="nowrap"',
        'joindate'  => 'nowrap="nowrap"',
    ),
);
$listTable = new ListTable($tpl, $config);


// Use person from phpcredits() as fake name
ob_start();
phpcredits();
$credits = ob_get_contents();
ob_end_clean();

$name = array();
// Part1, name take a full row(3: PHP Group, Language design, QA)
preg_match_all('/<tr><td class="e">([^<]+)<\/td><\/tr>/', $credits, $ar);
foreach ($ar[1] as $v) {
    $name = array_merge($name, explode(',', $v));
}
// Part1, name take right column of output table
// 1 special line is excluded, which is describe end with '. '
preg_match_all('/<td class="v">([^<\(]+\w {0,2})<\/td>/', $credits, $ar);
foreach ($ar[1] as $v) {
    $name = array_merge($name, explode(',', $v));
}

// Cleanup
$name = array_map('trim', $name);
$name = array_unique($name);
// Reorder index
$name = array_merge($name, array());
$nameCount = count($name);


// Prepare dummy data
$title = array(
    'uuid'     => 'UUID',
    'title'    => 'Name',
    'age'      => 'Age',
    'credit'   => 'Money',
    'joindate' => 'Join Date',
);
$data = array();
$rows = $nameCount;
// Casual algorithm, but solid result
$seed = 42;
for ($j = 0; $j < $rows; $j++) {
    $seed = round((100 + $seed) / 100);
    $seed = 101 + $seed * ($j + 2);
    $data[$j] = array(
        'uuid'  => $j,
        'title' => $name[$j],
        'age'   => $seed % 40 + 20,
        'credit'    => $seed,
        'joindate'  => date(
            'Y-m-d H:i:s',
            strtotime(
                '-' . ($seed % 30) . ' days -' . ($seed % 12) . ' hours'
            )
        )
    );
}


// Show list table
$listTable->setData($data, $title);

$html1 = $listTable->getHtml();


// Another table in same page
$listTable->setId(2);
// Data is trimmed, need re-make
$listTable->setData($data, $title);
// Set sort able column
$listTable->setConfig(
    'orderbyColumn',
    array(
        array('age', 'DESC'),
        array('credit', 'ASC'),
    )
);
// Set current sort order
//$listTable->setOrderby(2, 'ASC');
//$listTable->setOrderby(2);

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
  $(".ListTable tr > td:nth-child(2)").css("background-color", "green");
  $(".ListTable tr > *:nth-child(2)").css("width", "20em");
  //$(".ListTable tr > *:nth-child(2)").css("width", "3em");

  // If "table-layout: fixed;" is not assigned,
  // width limit will work, but overflow content
  // may make width raise.
  $("#ListTable-2 tr > *:nth-child(2)").css("width", "30%");

  //--><!]]>
  </script>


</body>
</html>
