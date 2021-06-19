1. Trip : is a journey that Driver makes to a particular place ( Customer's location ).
2. Collection : is the driver basket that inlcudes set of orders. 


1. Driver 
3. Restaurant 
2. Order and Order Item

![alt text](https://user-images.githubusercontent.com/4533327/122641086-ae0f0f00-d10b-11eb-856c-94ef6ba983da.png)
![alt text](https://user-images.githubusercontent.com/4533327/122641090-b36c5980-d10b-11eb-8a9f-32b7c3b3b0d4.png)
![alt text](https://user-images.githubusercontent.com/4533327/122641091-b5361d00-d10b-11eb-80e7-9d129778117c.png)



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


