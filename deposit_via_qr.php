<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$account_info = [];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['data'])) {
    $data = json_decode(urldecode($_GET['data']), true);
    
    if ($data && isset($data['account_number'])) {
        $account_number = $conn->real_escape_string($data['account_number']);
        $sql = "SELECT name, account_number FROM customers WHERE account_number = '$account_number' AND status = 'active'";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            $account_info = $result->fetch_assoc();
        } else {
            $error = "Invalid account number or account not active";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account_number = $conn->real_escape_string($_POST['account_number']);
    $amount = floatval($_POST['amount']);
    $sender_name = $conn->real_escape_string($_POST['sender_name']);
    $description = $conn->real_escape_string($_POST['description']);
    
    if ($amount <= 0) {
        $error = "Amount must be greater than zero";
    } else {
        // Update balance
        $sql = "UPDATE customers SET balance = balance + $amount WHERE account_number = '$account_number'";
        if ($conn->query($sql)) {
            // Record transaction
            $sql = "SELECT balance FROM customers WHERE account_number = '$account_number'";
            $result = $conn->query($sql);
            $customer = $result->fetch_assoc();
            $new_balance = $customer['balance'];
            
            $desc = "QR Deposit from $sender_name: $description";
            $sql = "INSERT INTO transactions (account_number, type, amount, balance, description) 
                    VALUES ('$account_number', 'deposit', $amount, $new_balance, '$desc')";
            $conn->query($sql);
            
            $success = "Deposit successful!";
            $account_info = []; // Clear form after successful deposit
        } else {
            $error = "Error processing deposit: " . $conn->error;
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Deposit via QR Code</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($account_info)): ?>
                    <div class="alert alert-info">
                        <p>Account Holder: <strong><?php echo $account_info['name']; ?></strong></p>
                        <p>Account Number: <strong><?php echo $account_info['account_number']; ?></strong></p>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="account_number" value="<?php echo $account_info['account_number']; ?>">
                        
                        <div class="mb-3">
                            <label for="sender_name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="sender_name" name="sender_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="description" name="description" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Deposit</button>
                    </form>
                <?php else: ?>
                    <div class="text-center">
                        <p>Scan a recipient's QR code to deposit money to their account</p>
                        <p>or</p>
                        <a href="index.php" class="btn btn-secondary">Go to Homepage</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>