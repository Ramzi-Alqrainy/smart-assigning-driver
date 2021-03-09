<?php

function insert_one($db, $table, $row) {
  $cols = implode(",", array_keys($row));
  $colcol = ':'.implode(",:", array_keys($row));
  $params = [];
  foreach($row as $k=>$v) {
    $params[":{$k}"]=$v;
  }
  // var_dump($params);
  $sql = "INSERT INTO $table ($cols) VALUES ($colcol)";
  // echo "$sql\n";
  $query = $db->prepare($sql);
  if (!$query) {
    var_dump($db->errorInfo());
    die;
  }
  if (!$query->execute($params)) {
    var_dump($db->errorInfo());
    die;
  }
  return $db->lastInsertId();
}


function execute_sql($db, $sql, $params=null) {
  $query = $db->prepare($sql);
  if (!$query) {
    var_dump($db->errorInfo());
    die;
  }
  if ($params) {
    $success = $query->execute($params);
  } else {
    $success = $query->execute();
  }
  if ($success) {
    $count = $query->rowCount();
  } else {
    var_dump($db->errorInfo());
    $count = false;
  }
  return $count;
}


function distance($lat1, $lon1, $lat2, $lon2) {
  if (($lat1 == $lat2) && ($lon1 == $lon2)) {
    return 0;
  }
  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  return  $dist * 60 * 1.1515 * 1.609344;
}

function http_get($url) {
  $ua="Mozilla/5.0 (X11; Fedora; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36";
  $s = curl_init();
  curl_setopt($s,CURLOPT_URL,$url);
  curl_setopt($s,CURLOPT_HTTPHEADER, [
    'Expect:',
    "User-Agent: $ua",
    'accept: application/json',
    'accept-language: en-US,en;q=1.0',
  ]);
  curl_setopt($s,CURLOPT_TIMEOUT, 10);
  curl_setopt($s,CURLOPT_MAXREDIRS, 2);
  curl_setopt($s,CURLOPT_RETURNTRANSFER, true);
  curl_setopt($s,CURLOPT_FOLLOWLOCATION, true);
  $res = curl_exec($s);
  $status = curl_getinfo($s, CURLINFO_HTTP_CODE);
  return [$status, $res];
}

function get_postcode($lat, $lon) {
  list($status, $res) = http_get("https://nominatim.openstreetmap.org/reverse.php?lat=$lat&lon=$lon&zoom=18&format=jsonv2");
  var_dump($status);
  return json_decode($res)->address->postcode;
}



function get_order_rest($db, $oid) {
  $query = $db->query('SELECT * FROM orders WHERE id=:oid limit 1');
  if (!$query) {
    var_dump($db->errorInfo());
    die;
  }
  $query->execute([':oid'=>$oid]);
  $order = $query->fetch();
  $rid = $order['rid'];
  
  $query = $db->query('SELECT * FROM restaurants WHERE id=:rid limit 1');
  if (!$query) {
    var_dump($db->errorInfo());
    die;
  }
  $query->execute([':rid'=>$rid]);
  $rest = $query->fetch();
  return [$order, $rest];
}

function order_prep_trigger($db, $oid) {
  list($order, $rest) = get_order_rest($db, $oid);
  $now = time();
  $rid = $order['rid'];
  $heuristic_id = $order['heuristic_id']; // Postal Code
  // if status is not fully prep
  if ($order['status']<2 && $order['st_pending_count']==0) {
    $sql = "
UPDATE orders
SET
  prepared_at=:prepared_at,
  collection_deadline=:collection_deadline,
  preperation_drift_seconds=:preperation_drift_seconds,
  status=2
WHERE
  id=:oid
";
    // TODO: for collection_deadline consider min with menu_item.delay_tolerence_minutes
    // NOTE: menu_item.delay_tolerence_minutes can be denorm into order_item
    execute_sql($db, $sql, [
      ':oid' => $oid,
      ':prepared_at' => $now,
      ':collection_deadline' => $now + 60*$rest['collection_tolerence_minutes'],
      ':preperation_drift_seconds' => $now-$order['est_prepared_at'],
    ]);
    // update order array, to avoid run the query again
    $order['status'] = 2;
    $order['prepared_at'] = $now;
    $order['collection_deadline'] = $now + 60*$rest['collection_tolerence_minutes'];
    $order['preperation_drift_seconds'] = $now-$order['est_prepared_at'];
  }
  // order ready, assign it to be aggregated
  if ($order['status']!=2 || $order['is_agg']) return;
  // find an existing oldest agg
  $sql="
SELECT id
FROM aggregations
WHERE
  rid = :rid
  AND heuristic_id = :heuristic_id
  AND created_at >= :created_at
  AND open_items >= :items
  AND is_closed=0
  AND open_orders >= 1
ORDER BY created_at ASC
LIMIT 1
";
  $query = $db->query($sql);
  if (!$query) {
    var_dump($db->errorInfo());
    die;
  }
  $query->execute([
    ':rid' => $rid,
    ':heuristic_id' => $heuristic_id,
    ':created_at' => $now-60*$rest['collection_tolerence_minutes'],
    ':items' => $order['st_items_count'],
  ]);
  $agg = $query->fetch();
  if ($agg) {
    $aid = $agg['id'];
    echo " ** joining agg aid=$aid\n";
    execute_sql($db, "
UPDATE aggregations
SET
  st_items_count = st_items_count + :items,
  st_orders_count = st_orders_count + 1,
  open_items = open_items - :items,
  open_orders = open_orders - 1,
  min_est_prepared_at = MIN(min_est_prepared_at, :est_prepared_at),
  max_est_prepared_at = MAX(max_est_prepared_at, :est_prepared_at),
  min_est_direct_eta = MIN(min_est_direct_eta, :est_direct_eta),
  max_est_direct_eta = MAX(max_est_direct_eta, :est_direct_eta),
  min_prepared_at = MIN(min_prepared_at, :prepared_at),
  max_prepared_at = MAX(max_prepared_at, :prepared_at),
  min_preperation_drift_seconds = MIN(min_preperation_drift_seconds, :preperation_drift_seconds),
  max_preperation_drift_seconds = MAX(max_preperation_drift_seconds, :preperation_drift_seconds)
WHERE id=:aid", [
      ':aid' => $aid,
      ':items' => $order['st_items_count'],
      ':est_prepared_at' => $order['est_prepared_at'],
      ':est_direct_eta' => $order['est_direct_eta'],
      ':prepared_at' => $order['prepared_at'],
      ':preperation_drift_seconds' => $order['preperation_drift_seconds'],
    ]);
    // is_closed = IIF(collection_deadline<now OR open_items=0 OR open_orders=0, 1, 0)
  } else {
    // create new agg
    $aid = insert_one($db, 'aggregations', [
      'rid' => $order['rid'],
      'heuristic_id' => $order['heuristic_id'],
      'created_at' => $now,
      'collection_deadline' => $now + 60*$rest['collection_tolerence_minutes'],
      'st_items_count' => $order['st_items_count'],
      'st_orders_count' => 1,
      'rest_max_items' => $rest['max_agg_order_items'],
      'rest_max_orders' => $rest['max_agg_orders'],
      'open_items' => $rest['max_agg_order_items']-$order['st_items_count'],
      'open_orders' => $rest['max_agg_orders']-1,
      'min_est_prepared_at' => $order['est_prepared_at'],
      'max_est_prepared_at' => $order['est_prepared_at'],
      'min_est_direct_eta' => $order['est_direct_eta'],
      'max_est_direct_eta' => $order['est_direct_eta'],
      'min_prepared_at' => $order['prepared_at'],
      'max_prepared_at' => $order['prepared_at'],
      'min_preperation_drift_seconds' => $order['preperation_drift_seconds'],
      'max_preperation_drift_seconds' => $order['preperation_drift_seconds'],
      // 'is_closed' => ($rest['max_agg_order_items']==$order['st_items_count'] || $rest['max_agg_orders']==1)?1:0,
    ]);
    echo " ** new agg aid=$aid\n";
  }
  // update order and order_items
  execute_sql($db, "UPDATE orders SET is_agg = 1, agg_id=:aid WHERE oid=:oid", [':oid'=>$oid, ':aid'=>$aid]);
  execute_sql($db, "UPDATE order_items SET aid=:aid WHERE oid=:oid", [':oid'=>$oid, ':aid'=>$aid]);
  // check if agg is closed
  execute_sql($db, "
UPDATE aggregations
SET is_closed=1
WHERE id=:aid AND (collection_deadline<:now OR open_items=0 OR open_orders=0)
  ", [':aid'=>$aid, ':now'=>time()]);
}

