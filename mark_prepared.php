<?php
require_once(__DIR__.'/utils.php');

$SPEED=40; // km per hour

if (count($argv)<2) {
echo "prepared order_item_ids...\n";
echo "example: prepared 1\n";
die;
}

$dsn = 'sqlite:db.sq3';
$db = new PDO($dsn);

$now = time();
$ids = array_slice($argv, 1);
$ids = array_map(function($i){return (int)($i);}, $ids);
foreach($ids as $id) {
  $count = execute_sql($db,
    "UPDATE order_items SET prepared_at=:now, status=2 WHERE id=:id AND status=0",
    [':id'=>$id, ':now'=>$now]);
}
$csv = implode(',', $ids);
$query = $db->query("SELECT DISTINCT oid FROM order_items WHERE id IN ($csv)");
$query->execute();
$rows = $query->fetchAll();
$oids = array_map(function($a) {return $a[0];}, $rows);
$csv = implode(',', $oids);
$count = execute_sql($db,
  "UPDATE orders SET st_prep_count = (SELECT count(*) FROM order_items WHERE oid=orders.id AND status>=2) WHERE id IN ($csv)");
$count = execute_sql($db,
  "UPDATE orders SET st_pending_count = st_items_count - st_prep_count, status=MAX(1, status) WHERE id IN ($csv)");
/*
$query = $db->query("SELECT DISTINCT aid FROM order_items WHERE id IN ($csv)");
$query->execute();
$rows = $query->fetchAll();
$aids = array_map(function($a) {return $a[0];}, $rows);
*/
foreach($oids as $oid) {
  order_prep_trigger($db, $oid);
}
// fetch corresponding orders and count of prepared
// mark orders as partially prepared or fully prepared based on count
// work on fully prepared orders
// set prepared_at
// set collection_deadline = prepared_at+60*rest.max_delay_tolerence_minutes


