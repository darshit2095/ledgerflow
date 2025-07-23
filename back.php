<?php
// ---------- Database Connection ----------
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ledgerflow";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// ---------- Routing Actions ----------
$action = $_GET['action'] ?? '';

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $invoice_no = $_POST['invoice_no'];
  $date = $_POST['date'];
  $city = $_POST['city'];
  $type = $_POST['type'];
  $category = $_POST['category'];
  $total = $_POST['total'];

  $sql = "INSERT INTO transactions (name, invoice_no, date, city, type, category, total)
          VALUES (?, ?, ?, ?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssssd", $name, $invoice_no, $date, $city, $type, $category, $total);
  echo $stmt->execute() ? "Invoice saved successfully!" : "Error: " . $stmt->error;
  exit;
}

if ($action === 'fetch') {
  $result = $conn->query("SELECT * FROM transactions ORDER BY date DESC");
  $data = [];
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}
?>

<!-- ---------- HTML UI Below ---------- -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>LedgerFlow - Invoicing</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { background: #f8f9fa; padding: 20px; }
    .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
  </style>
</head>
<body>

<div class="container mb-5">
  <h3 class="text-primary mb-4">LedgerFlow - Create Invoice</h3>
  <form id="invoiceForm">
    <div class="row mb-3">
      <div class="col-md-4"><label>Name</label><input name="name" required class="form-control" /></div>
      <div class="col-md-4"><label>Invoice No</label><input name="invoice_no" id="invoice_no" class="form-control" readonly /></div>
      <div class="col-md-4"><label>Date</label><input name="date" type="date" required class="form-control" /></div>
    </div>
    <div class="row mb-3">
      <div class="col-md-4"><label>City</label><input name="city" class="form-control" /></div>
      <div class="col-md-4"><label>Type</label><select name="type" class="form-select"><option>Cash</option><option>Debit</option></select></div>
      <div class="col-md-4"><label>Category</label><input name="category" class="form-control" /></div>
    </div>
    <div class="mb-3"><label>Total Amount</label><input name="total" type="number" step="0.01" required class="form-control" /></div>
    <button class="btn btn-success" type="submit">Save Invoice</button>
    <button class="btn btn-secondary" type="button" onclick="loadTransactions()">Show Transactions</button>
  </form>
</div>

<div class="container" id="transactionTable" style="display:none;">
  <h3 class="text-primary mb-4">All Transactions</h3>
  <div class="table-responsive">
    <table class="table table-bordered table-hover text-center">
      <thead class="table-light">
        <tr><th>Date</th><th>Name</th><th>Type</th><th>Category</th><th>Invoice No</th><th>City</th><th>Total (₹)</th></tr>
      </thead>
      <tbody id="transactionsBody"></tbody>
    </table>
  </div>
</div>

<script>
  document.getElementById('invoice_no').value = 'INV-' + Date.now();
  document.querySelector('input[name="date"]').valueAsDate = new Date();

  document.getElementById('invoiceForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const res = await fetch('?action=save', { method: 'POST', body: formData });
    const text = await res.text();
    alert(text);
    this.reset();
    document.getElementById('invoice_no').value = 'INV-' + Date.now();
  });

  async function loadTransactions() {
    const res = await fetch('?action=fetch');
    const data = await res.json();
    document.getElementById('transactionTable').style.display = 'block';
    const tbody = document.getElementById('transactionsBody');
    tbody.innerHTML = data.map(tx => `
      <tr>
        <td>${tx.date}</td>
        <td>${tx.name}</td>
        <td><span class="badge bg-${tx.type === 'Cash' ? 'success' : 'warning'}">${tx.type}</span></td>
        <td>${tx.category}</td>
        <td>${tx.invoice_no}</td>
        <td>${tx.city}</td>
        <td><strong>₹${parseFloat(tx.total).toFixed(2)}</strong></td>
      </tr>
    `).join('');
  }
</script>

</body>
</html>
