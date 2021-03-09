```
$ rm db.sq3 ; cat init.sql data.sql | sqlite3 db.sq3
$ php order.php 1 31.9539 35.9106 1 2 3
$ php order.php 1 31.9539 35.9106 1 2 3
$ php order.php 1 31.9539 35.9106 1 2 3
$ php order.php 1 31.9539 35.9106 1 2 3
$ php order.php 1 31.9539 35.9106 1 2 3
$ php mark_prepared.php 1 3
$ php mark_prepared.php 2
 ** new agg aid=1
$ php mark_prepared.php 4 5 6
 ** joining agg aid=1
$ php mark_prepared.php 7 8 9
 ** joining agg aid=1
$ php mark_prepared.php 10 11 12 13
 ** joining agg aid=1
$ php mark_prepared.php 14 15
 ** new agg aid=2
 $ sqlite3 --header db.sq3 "select id, is_closed from aggregations"
id|is_closed
2|0
1|1
$ sqlite3 --header db.sq3 "select id, agg_id from orders"
id|agg_id
1|1
2|1
3|1
4|1
5|2
```


