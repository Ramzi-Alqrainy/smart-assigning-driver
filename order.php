<?php
require_once(__DIR__.'/utils.php');

$SPEED=40; // km per hour

if (count($argv)<5) {
echo "order RID LAT LON ITEM1 ITEM2 ITEM3...\n";
echo "example: order 1 31.9539 35.9106 1 2 3\n";
die;
}
list($_, $rid, $lat, $lon) = array_slice($argv, 0, 4);
$items = array_slice($argv, 4);
$items = array_map(function($i){return (int)($i);}, $items);

echo " * getting postcode: ...\n";
$postcode = get_postcode($lat, $lon);
echo " * got postcode: [$postcode] OK\n";
$dsn = 'sqlite:db.sq3';
$db = new PDO($dsn);

$query = $db->query('SELECT * FROM restaurants WHERE id=:rid limit 1');
if (!$query) {
  var_dump($db->errorInfo());
  die;
}
$query->execute([':rid'=>$rid]);
$rest = $query->fetch();
if ($rest===false) {
  echo "restaurant NOT found\n";
  die;
}
$rest_lat = $rest['lat'];
$rest_lon = $rest['lon'];
$km = distance($rest_lat, $rest_lon, $lat, $lon);
$trip_minutes = ceil(60.0 * $km / $SPEED);
echo " * ordering from {$rest['name']} which is $km Km away (that is $trip_minutes minutes).\n";
$mids=implode(",", $items);
$query = $db->query("SELECT * FROM menu_items WHERE id IN ($mids)");
if (!$query) {
  var_dump($db->errorInfo());
  die;
}
$query->execute();
$menu_items = $query->fetchAll();
$by_mid = [];
foreach($menu_items as $menu_item) {
  echo " ** ordering {$menu_item['name']}\n";
  if ($menu_item['rid']!=$rid) {
    echo " ** EE: item does not belong to restaurants\n";
    die;
  }
  $by_mid[$menu_item['id']] = $menu_item;
}
// ----------------
$now = time();
$h_seconds = $rest['item_handling_seconds']*count($items);
echo " ** order handling seconds = item_handling_seconds * items_count = $h_seconds\n";
// NOTE: need to know how to handle quantity/capacity (ex. grill capacity is 10, so 25 is 3 bulks each of parallel 10)
// NOTE: for now assume they are in parallel starting from order time
// sequential
// $prep_seconds = 90*array_sum(array_map(function($i) use ($by_mid) {return $by_mid[$i]['prepare_minutes'];}, $items));
// parallel
$prep_seconds = 60*max(array_map(function($i) use ($by_mid) {return $by_mid[$i]['prepare_minutes'];}, $items));
echo " ** prep seconds = $prep_seconds\n";
$row = [
  'rid' => $rid,
  'lat' => $lat,
  'lon' => $lon,
  'created_at' => $now,
  'st_items_count' => count($items),
  'st_pending_count' => count($items),
  'est_prepared_at' => $now+$h_seconds+$prep_seconds,
  'est_direct_eta' => $now+$h_seconds+$prep_seconds+$trip_minutes*60,
  'heuristic_distance' => $km,
  'heuristic_id' => "ZIP:$postcode",
];
$oid = insert_one($db, 'orders', $row);
echo " *** order id oid=$oid\n";
foreach($items as $mid) {
  $order_item_id = insert_one($db, 'order_items', [
    'created_at'=>$now, 'est_prepared_at'=>$now+$by_mid[$mid]['prepare_minutes']*60, 'rid'=>$rid, 'mid'=>$mid, 'oid'=>$oid]);
  echo " *** order item id $order_item_id\n";
}

