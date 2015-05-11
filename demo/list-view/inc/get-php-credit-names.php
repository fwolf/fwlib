<?php
ob_start();
phpcredits();
$credits = ob_get_contents();
ob_end_clean();

$names = [];
// Part1, name take a full row(3: PHP Group, Language design, QA)
preg_match_all('/<tr><td class="e">([^<]+)<\/td><\/tr>/', $credits, $ar);
foreach ($ar[1] as $v) {
    $names = array_merge($names, explode(',', $v));
}
// Part2, name take right column of output table
// 1 special line is excluded, which is describe end with '. '
preg_match_all('/<td class="v">([^<\(]+\w {0,2})<\/td>/', $credits, $ar);
foreach ($ar[1] as $v) {
    $names = array_merge($names, explode(',', $v));
}

// Clean fake name array
$names = array_map('trim', $names);
$names = array_unique($names);

// Reorder index
$names = array_merge($names, []);

return $names;
