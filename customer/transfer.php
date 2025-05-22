<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn('customer');

$account_number = $_SESSION['account_number'];
$error = '';
$success = '';

// Get current balance
$sql = "SELECT balance FROM customers WHERE account_number = '$account_number'";
$result = $conn->query($sql);
$customer = $result->fetch_assoc();
$current_balance = $customer['balance'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient_account = $conn->real_escape_string($_POST['recipient_account']);
    $amount = floatval($_POST['amount']);
    $description = $conn->real_escape_string($_POST['description']);

    // Validate amount
    if ($amount <= 0) {
        $error = "Amount must be greater than zero";
    } elseif ($amount > $current_balance) {
        $error = "Insufficient balance for transfer";
    } else {
        // Check if recipient account exists and is active
        $sql = "SELECT id, name FROM customers WHERE account_number = '$recipient_account' AND status = 'active'";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            $recipient = $result->fetch_assoc();
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Deduct from sender's account
                $sql = "UPDATE customers SET balance = balance - $amount WHERE account_number = '$account_number'";
                $conn->query($sql);
                
                // Add to recipient's account
                $sql = "UPDATE customers SET balance = balance + $amount WHERE account_number = '$recipient_account'";
                $conn->query($sql);
                
                // Record sender's transaction (transfer out)
                $new_balance = $current_balance - $amount;
                $sql = "INSERT INTO transactions (account_number, type, amount, balance, description) 
                        VALUES ('$account_number', 'transfer_out', $amount, $new_balance, 
                                'Transfer to $recipient_account: $description')";
                $conn->query($sql);
                
                // Record recipient's transaction (transfer in)
                $sql = "SELECT balance FROM customers WHERE account_number = '$recipient_account'";
                $result = $conn->query($sql);
                $recipient_balance = $result->fetch_assoc()['balance'];
                
                $sql = "INSERT INTO transactions (account_number, type, amount, balance, description) 
                        VALUES ('$recipient_account', 'transfer_in', $amount, $recipient_balance, 
                                'Transfer from $account_number: $description')";
                $conn->query($sql);
                
                // Commit transaction
                $conn->commit();
                
                $success = "Transfer successful! New balance: $" . number_format($new_balance, 2);
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $error = "Transfer failed: " . $e->getMessage();
            }
        } else {
            $error = "Recipient account not found or not active";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Transfer Money</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <p><strong>Current Balance:</strong> $<?php echo number_format($current_balance, 2); ?></p>
                </div>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="recipient_account" class="form-label">Recipient Account Number</label>
                        <input type="text" class="form-control" id="recipient_account" name="recipient_account" required>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" step="0.01" min="0.01" max="<?php echo $current_balance; ?>" 
                               class="form-control" id="amount" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Transfer</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>