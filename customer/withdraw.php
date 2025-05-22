<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn('customer');

$account_number = $_SESSION['account_number'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $description = $conn->real_escape_string($_POST['description']);
    
    if ($amount <= 0) {
        $_SESSION['error'] = "Amount must be greater than zero";
    } else {
        // Check sufficient balance
        $sql = "SELECT balance FROM customers WHERE account_number = '$account_number'";
        $result = $conn->query($sql);
        $customer = $result->fetch_assoc();
        
        if ($customer['balance'] < $amount) {
            $_SESSION['error'] = "Insufficient balance";
        } else {
            // Update balance
            $sql = "UPDATE customers SET balance = balance - $amount WHERE account_number = '$account_number'";
            if ($conn->query($sql) === TRUE) {
                // Record transaction
                $new_balance = $customer['balance'] - $amount;
                
                $sql = "INSERT INTO transactions (account_number, type, amount, balance, description) 
                        VALUES ('$account_number', 'withdrawal', $amount, $new_balance, '$description')";
                $conn->query($sql);
                
                $_SESSION['success'] = "Withdrawal successful. New balance: $" . number_format($new_balance, 2);
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Error: " . $conn->error;
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Withdraw Money</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="description" name="description" required>
                        </div>
                        <button type="submit" class="btn btn-warning">Withdraw</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php include '../includes/footer.php'; ?>