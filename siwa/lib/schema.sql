BEGIN;
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL CHECK(role IN ("cashier","admin")),
  full_name TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS consumers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  account_no TEXT UNIQUE NOT NULL,
  name TEXT NOT NULL,
  sitio TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS bills (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  consumer_id INTEGER NOT NULL REFERENCES consumers(id) ON DELETE CASCADE,
  period_start TEXT NOT NULL,
  period_end TEXT NOT NULL,
  current_charges REAL NOT NULL DEFAULT 0,
  unpaid_balance REAL NOT NULL DEFAULT 0,
  penalties REAL NOT NULL DEFAULT 0,
  total_due AS (current_charges + unpaid_balance + penalties) STORED,
  status TEXT NOT NULL DEFAULT "unpaid" CHECK(status IN ("unpaid","paid","partial")),
  due_date TEXT
);
CREATE TABLE IF NOT EXISTS payments (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  bill_id INTEGER NOT NULL REFERENCES bills(id) ON DELETE CASCADE,
  consumer_id INTEGER NOT NULL REFERENCES consumers(id) ON DELETE CASCADE,
  cashier_id INTEGER NOT NULL REFERENCES users(id),
  amount_paid REAL NOT NULL,
  method TEXT NOT NULL,
  receipt_no TEXT UNIQUE NOT NULL,
  created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
  notes TEXT
);
CREATE VIEW IF NOT EXISTS v_consumer_balances AS
SELECT c.id AS consumer_id,
       c.account_no,
       c.name,
       c.sitio,
       SUM(CASE WHEN b.status != "paid" THEN b.total_due ELSE 0 END) AS outstanding
FROM consumers c
LEFT JOIN bills b ON b.consumer_id = c.id
GROUP BY c.id;
COMMIT;
