<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn('customer');

$account_number = $_SESSION['account_number'];
$sql = "SELECT * FROM customers WHERE account_number = '$account_number'";
$result = $conn->query($sql);
$customer = $result->fetch_assoc();
?>

<?php include '../includes/header.php'; ?>
    <h2>Customer Dashboard</h2>
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Account Number</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $customer['account_number']; ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Balance</div>
                <div class="card-body">
                    <h5 class="card-title">$<?php echo number_format($customer['balance'], 2); ?></h5>
                </div>
            </div>
        </div>
        <!-- Add this to your dashboard after the balance cards -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Receive Money via QR Code</h5>
            </div>
            <div class="card-body text-center">
    <?php
    $qrData = json_encode([
        'account_number' => $customer['account_number'],
        'name' => $customer['name'],
        'bank' => 'Our Bank',
        'deposit_url' => 'http://localhost/bank/deposit_via_qr.php'
    ]);
    ?>
    <img src="generate_qr.php?data=<?= urlencode($qrData) ?>" 
         alt="Deposit QR Code" class="img-fluid mb-3" style="max-width: 250px;">
    <p>Scan this QR code to deposit money to this account</p>
    <div class="input-group mb-3">
        <input type="text" class="form-control" id="qrUrl" 
               value="http://localhost/bank/deposit_via_qr.php?data=<?= urlencode($qrData) ?>" readonly>
        <button class="btn btn-outline-secondary" onclick="copyQrUrl()">Copy Link</button>
    </div>
</div>
        </div>
    </div>
</div>

<script>
function copyQrUrl() {
    const copyText = document.getElementById("qrUrl");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("QR code data copied to clipboard!");
}
</script>
</div>
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-header">Account Status</div>
                <div class="card-body">
                    <h5 class="card-title text-capitalize"><?php echo $customer['status']; ?></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Quick Actions</div>
                <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="deposit.php" class="btn btn-success">Deposit Money</a>
                    <a href="withdraw.php" class="btn btn-warning">Withdraw Money</a>
                    <a href="transfer.php" class="btn btn-info">Transfer Money</a>
                    <a href="statement.php" class="btn btn-secondary">View Statement</a>
                </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Recent Transactions</div>
                <div class="card-body">
                    <?php
                    $sql = "SELECT * FROM transactions 
                            WHERE account_number = '$account_number' 
                            ORDER BY created_at DESC LIMIT 5";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        echo '<ul class="list-group">';
                        while ($row = $result->fetch_assoc()) {
                            $type_class = $row['type'] == 'deposit' ? 'text-success' : 'text-danger';
                            echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                            echo '<span>' . ucfirst($row['type']) . ' - ' . $row['description'] . '</span>';
                            echo '<span class="' . $type_class . '">$' . number_format($row['amount'], 2) . '</span>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No transactions found.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php include '../includes/footer.php'; ?>