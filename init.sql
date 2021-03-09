
CREATE TABLE restaurants (
   id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
   name TEXT NOT NULL,
   lat REAL NOT NULL, lon REAL NOT NULL, 
   prepare_tolerence_minutes REAL NOT NULL,
   collection_tolerence_minutes REAL NOT NULL,
   max_agg_orders INTEGER NOT NULL,
   max_agg_order_items INTEGER NOT NULL,
   item_handling_seconds INTEGER NOT NULL, -- a per item constant for order
   order_handling_seconds INTEGER NOT NULL -- a per order constant for agg
);

-- meal
CREATE TABLE menu_items (
   id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
   name TEXT NOT NULL,
   rid INTEGER NOT NULL,
   prepare_minutes REAL NOT NULL, -- we can add bulk_size, bulk_prepare_minutes,
   delay_tolerence_minutes REAL NOT NULL,
   CONSTRAINT rf_menu__restaurant FOREIGN KEY (rid) 
      REFERENCES restaurants (id) 
         ON DELETE RESTRICT 
         ON UPDATE CASCADE
);

CREATE INDEX rf_ix_menu__restaurant ON menu_items(rid);

-- Nhoods: nhood, postcode, orders_count, sum_km, sum_h, avg_speed_kmph, correction_factor...

CREATE TABLE aggregations (
   id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
   rid INTEGER NOT NULL,
   heuristic_id TEXT NOT NULL,
   created_at INTEGER NOT NULL,
   collection_deadline INTEGER NOT NULL,
   is_closed INTEGER NOT NULL DEFAULT 0, -- can accept more
   st_items_count INTEGER NOT NULL DEFAULT 0,
   st_orders_count INTEGER NOT NULL DEFAULT 0,
   rest_max_items INTEGER NOT NULL DEFAULT 0,
   rest_max_orders INTEGER NOT NULL DEFAULT 0,
   open_items INTEGER NOT NULL DEFAULT 0,
   open_orders INTEGER NOT NULL DEFAULT 0,
   min_est_prepared_at INTEGER NOT NULL,
   max_est_prepared_at INTEGER NOT NULL,
   min_est_direct_eta INTEGER NOT NULL,
   max_est_direct_eta INTEGER NOT NULL,
   min_prepared_at INTEGER DEFAULT NULL,
   max_prepared_at INTEGER DEFAULT NULL,
   min_delivered_at INTEGER DEFAULT NULL,
   max_delivered_at INTEGER DEFAULT NULL,
   min_preperation_drift_seconds INTEGER DEFAULT NULL,
   min_delivery_drift_seconds INTEGER DEFAULT NULL,
   max_preperation_drift_seconds INTEGER DEFAULT NULL,
   max_delivery_drift_seconds INTEGER DEFAULT NULL
);
CREATE INDEX ix_agg__is_closed ON aggregations(is_closed);
CREATE INDEX ix_agg__created_at ON aggregations(created_at);
CREATE INDEX ix_agg__heuristic_id ON aggregations(heuristic_id);

CREATE TABLE orders (
   id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
   rid INTEGER NOT NULL,
   lat REAL NOT NULL,
   lon REAL NOT NULL,
   created_at INTEGER NOT NULL,
   st_items_count INTEGER NOT NULL DEFAULT 0, -- stats: menu items count
   st_prep_count INTEGER NOT NULL DEFAULT 0, -- stats: prepared menu items count
   st_pending_count INTEGER NOT NULL DEFAULT 0, -- stats: unprepared menu items count
   est_prepared_at INTEGER NOT NULL,
   est_direct_eta INTEGER NOT NULL,
   prepared_at INTEGER DEFAULT NULL,
   collection_deadline INTEGER DEFAULT NULL, -- prepared_at+60*rest.collection_tolerence_minutes
   delivered_at INTEGER DEFAULT NULL,
   preperation_drift_seconds INTEGER DEFAULT NULL,
   delivery_drift_seconds INTEGER DEFAULT NULL,
   heuristic_distance REAL NOT NULL,
   heuristic_id TEXT DEFAULT NULL,
   is_agg INTEGER NOT NULL DEFAULT 0, -- 0: not part of agg, 1 part of agg
   status INTEGER NOT NULL DEFAULT 0, -- 0 not prepared, 1 partly prepared, 2 prepared, 3 collected, 4 delivered
   agg_id INTEGER DEFAULT NULL,
   CONSTRAINT rf_order__restaurant FOREIGN KEY (rid) 
      REFERENCES restaurants (id) 
         ON DELETE RESTRICT 
         ON UPDATE CASCADE,
   CONSTRAINT rf_order__agg FOREIGN KEY (agg_id) 
      REFERENCES aggregations (id) 
         ON DELETE RESTRICT 
         ON UPDATE CASCADE
);
CREATE INDEX rf_ix_order__restaurant ON orders(rid);
CREATE INDEX rf_ix_order__agg ON orders(agg_id);
CREATE INDEX ix_order__created_at ON orders(created_at);
CREATE INDEX ix_order__is_agg ON orders(is_agg);

-- TODO: review indecies based on queries

CREATE TABLE order_items (
   id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
   created_at INTEGER NOT NULL,
   est_prepared_at INTEGER NOT NULL,
   prepared_at INTEGER DEFAULT NULL,
   -- delay_tolerence_minutes REAL NOT NULL, -- denormalized
   rid INTEGER NOT NULL,
   mid INTEGER NOT NULL,
   oid INTEGER NOT NULL,
   aid INTEGER DEFAULT NULL, -- denormalized
   status INTEGER NOT NULL DEFAULT 0, -- 0 not prepared, 2 prepared, 3 collected, 4 delivered
   CONSTRAINT rf_restaurant FOREIGN KEY (rid) 
      REFERENCES restaurants (id) 
         ON DELETE RESTRICT 
         ON UPDATE CASCADE,
   CONSTRAINT rf_menu_item FOREIGN KEY (mid) 
      REFERENCES menu_items (id) 
         ON DELETE RESTRICT 
         ON UPDATE CASCADE,
   CONSTRAINT rf_orders FOREIGN KEY (oid) 
      REFERENCES orders (id) 
         ON DELETE RESTRICT 
         ON UPDATE CASCADE,
   CONSTRAINT rf_agg FOREIGN KEY (aid) 
      REFERENCES aggregations (id) 
         ON DELETE RESTRICT 
         ON UPDATE CASCADE
);

CREATE INDEX rf_ix_order_item__restaurant ON order_items(rid);
CREATE INDEX rf_ix_order_item__menu_item ON order_items(mid);
CREATE INDEX rf_ix_order_item__order ON order_items(oid);
CREATE INDEX rf_ix_order_item__agg ON order_items(aid);
CREATE INDEX ix_order_item_created_at ON order_items(created_at);

