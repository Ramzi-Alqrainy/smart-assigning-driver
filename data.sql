PRAGMA foreign_keys=1;

-- prepare_tolerence_minutes = how many minutes you are allowed to wait between orders
-- collection_tolerence_minutes = how many minutes you are allowed to wait the whole collection before you leave the rest
-- max_agg_orders = Max orders per collection to get from rest before you leave. 
-- max_agg_order_items = Max order items per collection to get from rest before you leave. 
-- here you take the minmum between max_agg_orders and max_agg_order_items. 
-- item_handling_seconds and order_handling_seconds = handling time per order and item order
INSERT INTO restaurants (id, name, lat, lon, prepare_tolerence_minutes, collection_tolerence_minutes, max_agg_orders, max_agg_order_items, item_handling_seconds, order_handling_seconds) VALUES (1, 'rest-one', 31.9741012, 35.8520097, 5, 15, 4, 100, 5, 30);


-- prepare_minutes = prepartion time for one order
-- delay_tolerence_minutes = some order like cold order can be delayed not like hot item. 
INSERT INTO menu_items (id, name, rid, prepare_minutes, delay_tolerence_minutes) VALUES
  (1, 'burger sandwich', 1, 12, 10),
  (2, 'burger meal', 1, 12, 10),
  (3, 'fajita sandwich', 1, 15, 10),
  (4, 'fajita sandwich', 1, 15, 10),
  (5, 'salad', 1, 5, 30);


