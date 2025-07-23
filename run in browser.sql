CREATE TABLE IF NOT EXISTS transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  invoice_no VARCHAR(50),
  date DATE,
  city VARCHAR(100),
  type ENUM('Cash', 'Debit'),
  category VARCHAR(100),
  total DECIMAL(10,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
