<?php
require __DIR__ . '/../../../config.default.php';

use Fwlib\Bridge\Smarty;
use Fwlib\Config\ConfigGlobal;
use Fwlib\Html\ListTable;
use Fwlib\Test\AbstractDbRelateTest;    // Use $tableUser
use Fwlib\Test\Benchmark;
use Fwlib\Test\ServiceContainerTest;

/***************************************
 * Prepare benchmark
 **************************************/
$bm = new Benchmark();
$bm->start('ListTable Benchmark');


/***************************************
 * Prepare ListTable instance
 **************************************/
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
$bm->mark('ListTable object prepared');


/***************************************
 * Prepare db and test table
 **************************************/
$db = ServiceContainerTest::getInstance()->get('db');

$ref = new \ReflectionProperty('Fwlib\Test\AbstractDbRelateTest', 'tableUser');
$ref->setAccessible(true);
$tableUser = $ref->getValue('Fwlib\Test\AbstractDbRelateTest');

$ref = new \ReflectionMethod('Fwlib\Test\AbstractDbRelateTest', 'createTable');
$ref->setAccessible(true);
$ref->invokeArgs(null, array($db));

$bm->mark('Db prepared and test table created');


/***************************************
 * Use person from phpcredits() as fake name
 **************************************/
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
// Part2, name take right column of output table
// 1 special line is excluded, which is describe end with '. '
preg_match_all('/<td class="v">([^<\(]+\w {0,2})<\/td>/', $credits, $ar);
foreach ($ar[1] as $v) {
    $name = array_merge($name, explode(',', $v));
}

// Clean fake name array
$name = array_map('trim', $name);
$name = array_unique($name);

// Reorder index
$name = array_merge($name, array());
$nameCount = count($name);

$bm->mark('Fake name grabbed');


/***************************************
 * Prepare dummy data, write to db
 **************************************/
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

// Write data to db
$db->write($tableUser, $data);
$bm->mark('Fake data written to db');



/***************************************
 * Show list table 1
 **************************************/
$listTable->setData($data, $title, true);

$html1 = $listTable->getHtml();
$bm->mark('List1 generated');


/***************************************
 * Show list table 2, with query data from db
 **************************************/
$listTable->setId(2);
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

// Query data from db
$config = array(
    'SELECT'    => array(
        'uuid', 'title', 'age', 'credit', 'joindate',
    ),
    'FROM'      => $tableUser,
    'WHERE'     => array(
        'age > 30',
    ),
);

// Updata totalRows
$listTable->setTotalRows(
    $db->executeGenSql(
        array_merge($config, array('SELECT' => 'COUNT(1) as c'))
    )->fields['c']
);

// Fetch real data and set
$config = array_merge($config, $listTable->getSqlConfig(true));
$rs = $db->executeGenSql($config);
$listTable->setData($rs->GetArray(), $title);

$html2 = $listTable->getHtml();
$bm->mark('List2 generated');


/***************************************
 * Show list table 3, Use inner db query
 **************************************/
$config = array(
    'SELECT'    => array(
        'uuid', 'title', 'age', 'credit', 'joindate',
    ),
    'FROM'      => $tableUser,
    'WHERE'     => array(
        'age > 30',
    ),
);

$listTable->setId(3)
->setConfig(
    'orderbyColumn',
    array(
        array('age', 'ASC'),
        array('credit', 'DESC'),
    )
)

// Title still need manual set
->setTitle($title)

// Set db query, and set data format closure function
->setDbQuery($db, $config)

// Format list data
->formatData(function (&$row) {
    $row['credit'] = number_format(round($row['credit']));
});

$html3 = $listTable->getHtml();
$bm->mark('List3 generated');


/***************************************
 * Cleanup test db
 **************************************/
$ref = new \ReflectionMethod('Fwlib\Test\AbstractDbRelateTest', 'dropTable');
$ref->setAccessible(true);
$ref->invokeArgs(null, array($db));

$bm->mark('Cleanup, test table dropped');
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


  <script type="text/javascript"
    src="<?php echo ConfigGlobal::get('lib.path.jquery'); ?>">
  </script>


</head>
<body>

<?php
echo "<h2>Simple list</h2>\n";
echo $html1;

echo "<hr />\n";


echo "<h2>Query data from db</h2>\n";
echo $html2;


echo "<hr />\n";


echo "<h2>Use inner db query</h2>\n";
echo $html3;


echo "<hr />\n";

/*
echo '<pre>
$listTable::getSqlConfig()
' . var_export($listTable->getSqlConfig(), true) . '
</pre>
';
 */


$bm->display();
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
