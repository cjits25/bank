<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn('customer');

$account_number = $_SESSION['account_number'];

// Get customer info
$sql = "SELECT * FROM customers WHERE account_number = '$account_number'";
$result = $conn->query($sql);
$customer = $result->fetch_assoc();

// Get transactions
$sql = "SELECT * FROM transactions 
        WHERE account_number = '$account_number' 
        ORDER BY created_at DESC";
$transactions = $conn->query($sql);
?>

<?php include '../includes/header.php'; ?>
    <h2>Account Statement</h2>
    <div class="card mt-4">
        <div class="card-header">
            <h5>Account Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Account Number:</strong> <?php echo $customer['account_number']; ?></p>
                    <p><strong>Account Holder:</strong> <?php echo $customer['name']; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Current Balance:</strong> $<?php echo number_format($customer['balance'], 2); ?></p>
                    <p><strong>Account Status:</strong> <?php echo ucfirst($customer['status']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5>Transaction History</h5>
        </div>
        <div class="card-body">
            <?php if ($transactions->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $transactions->fetch_assoc()): ?>
                                <tr>
                                    <?php
                                    $type_class = '';
                                    if ($row['type'] == 'deposit' || $row['type'] == 'transfer_in') {
                                        $type_class = 'text-success';
                                    } elseif ($row['type'] == 'withdrawal' || $row['type'] == 'transfer_out') {
                                        $type_class = 'text-danger';
                                    }
                                    
                                    $sign = '';
                                    if ($row['type'] == 'deposit' || $row['type'] == 'transfer_in') {
                                        $sign = '+';
                                    } elseif ($row['type'] == 'withdrawal' || $row['type'] == 'transfer_out') {
                                        $sign = '-';
                                    }
                                    ?>
                                    <td><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></td>
                                    <td><?php echo ucfirst($row['type']); ?></td>
                                    <td><?php echo $row['description']; ?></td>
                                    <td class="<?php echo $row['type'] == 'deposit' || $row['type'] == 'transfer_in' ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo ($row['type'] == 'deposit' || $row['type'] == 'transfer_in' ? '+' : '-') . '$' . number_format($row['amount'], 2); ?>
                                    </td>
                                    <td>$<?php echo number_format($row['balance'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No transactions found.</p>
            <?php endif; ?>
        </div>
    </div>
<?php include '../includes/footer.php'; ?>