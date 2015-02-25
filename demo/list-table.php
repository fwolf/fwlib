<?php
$pathToRoot = '../';
require __DIR__ . "/{$pathToRoot}config.default.php";

use Fwlib\Bridge\Smarty;
use Fwlib\Config\GlobalConfig;
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
$globalConfig = GlobalConfig::getInstance();
$tpl = new Smarty;
$tpl->compile_dir = $globalConfig->get('smarty.compileDir');
$tpl->template_dir = __DIR__ . "/{$pathToRoot}Fwlib/Html/";
$tpl->cache_dir = $globalConfig->get('smarty.cacheDir');

$configs = [
    'pageSize'  => 3,
    'tdAdd'     => [
        'title'     => 'nowrap="nowrap"',
        'joindate'  => 'nowrap="nowrap"',
    ],
];
$listTable = new ListTable($tpl, $configs);
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
$ref->invokeArgs(null, [$db]);

$bm->mark('Db prepared and test table created');


/***************************************
 * Use person from phpcredits() as fake name
 **************************************/
ob_start();
phpcredits();
$credits = ob_get_contents();
ob_end_clean();

$name = [];
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
$name = array_merge($name, []);
$nameCount = count($name);

$bm->mark('Fake name grabbed');


/***************************************
 * Prepare dummy data, write to db
 **************************************/
$title = [
    'uuid'     => 'UUID',
    'title'    => 'Name',
    'age'      => 'Age',
    'credit'   => 'Money',
    'joindate' => 'Join Date',
];
$data = [];
$rows = $nameCount;
// Casual algorithm, but solid result
$seed = 42;
for ($j = 0; $j < $rows; $j++) {
    $seed = round((100 + $seed) / 100);
    $seed = 101 + $seed * ($j + 2);
    $data[$j] = [
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
    ];
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
    'orderByColumn',
    [
        ['age', 'DESC'],
        ['credit', 'ASC'],
    ]
);
// Set current sort order
//$listTable->setOrderBy(2, 'ASC');
//$listTable->setOrderBy(2);

// Query data from db
$config = [
    'SELECT'    => [
        'uuid', 'title', 'age', 'credit', 'joindate',
    ],
    'FROM'      => $tableUser,
    'WHERE'     => [
        'age > 30',
    ],
];

// Updata totalRows
$listTable->setTotalRows(
    $db->execute(
        array_merge($config, ['SELECT' => 'COUNT(1) as c'])
    )->fields['c']
);

// Fetch real data and set
$config = array_merge($config, $listTable->getSqlConfig(true));
$rs = $db->execute($config);
$listTable->setData($rs->GetArray(), $title);

$html2 = $listTable->getHtml();
$bm->mark('List2 generated');


/***************************************
 * Show list table 3, Use inner db query
 **************************************/
$config = [
    'SELECT'    => [
        'uuid', 'title', 'age', 'credit', 'joindate',
    ],
    'FROM'      => $tableUser,
    'WHERE'     => [
        'age > 30',
    ],
];

$listTable->setId(3)
->setConfig(
    'orderByColumn',
    [
        ['age', 'ASC'],
        ['credit', 'DESC'],
    ]
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
$ref->invokeArgs(null, [$db]);

$bm->mark('Cleanup, test table dropped');
?>

<!DOCTYPE HTML>
<html lang='en'>
<head>
  <meta charset='utf-8' />
  <title>ListTable Demo</title>

  <link rel='stylesheet' href='<?php echo $pathToRoot; ?>css/reset.css'
    type='text/css' media='all' />
  <link rel='stylesheet' href='<?php echo $pathToRoot; ?>css/default.css'
    type='text/css' media='all' />

  <style type='text/css' media='all'>
  /*<![CDATA[*/
  /* Write CSS below */
  .list-table {
    border: 0px solid red;
    margin: auto;
    width: 70%;
  }
  .list-table form {
    display: inline-block;
  }
  .list-table table {
    margin: auto;
    width: 100%;
  }
  .list-table table, .list-table td, .list-table th {
    border: 1px solid black;
    border-collapse: collapse;
  }
  pre {
    text-align: left;
  }
  /*]]>*/
  </style>


  <script type="text/javascript"
    src="<?php echo $globalConfig->get('lib.path.jquery'); ?>">
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
  $(".list-table table").css("table-layout", "fixed");
  // * include th & td here
  $(".list-table tr > td:nth-child(2)").css("background-color", "green");
  $(".list-table tr > *:nth-child(2)").css("width", "20em");
  //$(".list-table tr > *:nth-child(2)").css("width", "3em");

  // If "table-layout: fixed;" is not assigned,
  // width limit will work, but overflow content
  // may make width raise.
  $("#list-table__2 tr > *:nth-child(2)").css("width", "30%");

  //--><!]]>
  </script>


</body>
</html>
