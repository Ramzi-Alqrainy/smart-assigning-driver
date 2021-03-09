PRAGMA foreign_keys=1;

INSERT INTO restaurants (id, name, lat, lon, prepare_tolerence_minutes, collection_tolerence_minutes, max_agg_orders, max_agg_order_items, item_handling_seconds, order_handling_seconds) VALUES (1, 'rest-one', 31.9741012, 35.8520097, 5, 15, 4, 100, 5, 30);

INSERT INTO menu_items (id, name, rid, prepare_minutes, delay_tolerence_minutes) VALUES
  (1, 'burger sandwich', 1, 12, 10),
  (2, 'burger meal', 1, 12, 10),
  (3, 'fajita sandwich', 1, 15, 10),
  (4, 'fajita sandwich', 1, 15, 10),
  (5, 'salad', 1, 5, 30);


