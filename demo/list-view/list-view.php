<?php
$pathToRoot = '../../';
require __DIR__ . '/' . $pathToRoot . 'config.default.php';
require __DIR__ . '/DemoRetriever.php';

use Fwlib\Config\GlobalConfig;
use Fwlib\Html\ListView\ListView;
use Fwlib\Test\Benchmark\Benchmark;
use FwlibDemo\ListView\DemoRetriever;

/***************************************
 * Prepare benchmark
 **************************************/
$bm = new Benchmark();
$bm->start('ListView Benchmark');


/***************************************
 * Prepare ListView instance
 **************************************/
$listView = new ListView();

$configs = [
    'pageSize'     => 3,
    'showTopPager' => true,
    'tdAppend'     => [
        'title'    => 'nowrap="nowrap"',
        'joindate' => 'nowrap="nowrap"',
    ],
];
$listView->setConfigs($configs);
$bm->mark('ListTable object prepared');


/***************************************
 * Prepare db and test table
 **************************************/
$db = require __DIR__ . '/inc/prepare-db-table.php';
$bm->mark('Db prepared and test table created');


/***************************************
 * Use person from phpcredits() as fake name
 **************************************/
$names = require __DIR__ . '/inc/get-php-credit-names.php';
$nameCount = count($names);
$bm->mark('Fake name grabbed');


/***************************************
 * Prepare dummy data, write to db
 **************************************/
$headers = [
    'uuid'     => 'ID',
    'title'    => 'Name',
    'age'      => 'Age',
    'credit'   => 'Money',
    'joindate' => 'Join Date',
];
$data = [];
// Casual algorithm, but solid result
$seed = 42;
for ($j = 0; $j < $nameCount; $j++) {
    $seed = round((100 + $seed) / 100);
    $seed = 101 + $seed * ($j + 2);
    $data[$j] = [
        'uuid'  => $j,
        'title' => $names[$j],
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
 * Show list 1, head and body are all manual set.
 **************************************/
$listView->setHead($headers)
    ->setRowCount($nameCount);

$page = $listView->getRequest()->getPage();
$pageSize = $configs['pageSize'];
$rows = array_slice($data, ($page - 1) * $pageSize, $pageSize);
$listView->setBody($rows);

$html1 = $listView->getHtml();
$bm->mark('List1 generated');


/***************************************
 * Show list 2, data are query from db with Retriever
 **************************************/
$listView->reset()
    ->setHead($headers)
    ->setId(2)
    ->setConfig(
        'orderBy',
        [
            'age'    => 'DESC',
            'credit' => 'ASC',
        ]
    );

$retriever = new DemoRetriever();
$retriever->setDb($db)
    ->setTable($tableUser);
$listView->setRetriever($retriever);

// Head need not set again

$html2 = $listView->getHtml();
$bm->mark('List2 generated');


/***************************************
 * Show list 3, query with Retriever and apply RowDecorator
 **************************************/
$listView->reset()
    ->setHead($headers)
    ->setId(3)
    ->setConfig(
        'orderBy',
        [
            'age'    => 'ASC',
            'credit' => 'DESC',
        ]
    );

// Head need not set again

// Retriever need not set again

$listView->setRowDecorator(function($row) {
    $row['credit'] = number_format(round($row['credit']));

    return $row;
});

$html3 = $listView->getHtml();
$bm->mark('List3 generated');


/***************************************
 * Cleanup test db
 **************************************/
require __DIR__ . '/inc/clean-db-table.php';
$bm->mark('Cleanup, test table dropped');

// Required to grab js lib path
$globalConfig = GlobalConfig::getInstance();

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
  <link rel='stylesheet' href='css/list-view.css'
    type='text/css' media='all' />

  <style type='text/css' media='all'>
  /* Write CSS below */
  .fwlib-benchmark {
      margin: auto;
      width: 60%;
  }
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


echo "<h2>Query data with Retriever</h2>\n";
echo $html2;


echo "<hr />\n";


echo "<h2>Query data with Retriever and apply RowDecorator</h2>\n";
echo $html3;


echo "<hr />\n";

$bm->display();
?>


  <!-- Below js MUST place after html of list table -->
  <script type="text/javascript">
    // Demo of change list view with js
    (function() {
      // Control of single column
      $(".list-view tr > td:nth-child(2)").css("background-color", "green");
      $(".list-view tr > *:nth-child(1)").css("width", "2em");
      $(".list-view tr > *:nth-child(2)").css("width", "15em");

      // If "table-layout: fixed;" is assigned, all td width will be equal,
      // except manual assigned. Auto wrap will apply to long text, but overflow
      // part is visible.
      $(".list-view table").css("table-layout", "fixed");

      // If "table-layout: fixed;" is not assigned, width limit works only if
      // in limit of content length.
      var list2 = $("#list-view-2");
      list2.find("table").css("table-layout", "auto");
      list2.find("tr > *:nth-child(2)").css("width", "5%");
    }) ();
  </script>


</body>
</html>
