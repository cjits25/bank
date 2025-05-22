<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn('manager');

// Handle account status change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_status'])) {
    $account_number = $conn->real_escape_string($_POST['account_number']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $sql = "UPDATE customers SET status = '$status' WHERE account_number = '$account_number'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Account status updated successfully";
    } else {
        $_SESSION['error'] = "Error updating account status: " . $conn->error;
    }
}

// Get all customers
$sql = "SELECT * FROM customers ORDER BY created_at DESC";
$customers = $conn->query($sql);
?>

<?php include '../includes/header.php'; ?>
    <h2>Manager Dashboard</h2>
    
    <?php if ($_SESSION['manager_role'] === 'admin'): ?>
    <div class="mb-3">
        <a href="register.php" class="btn btn-success">Register New Manager</a>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5>Customer Accounts</h5>
        </div>
        <div class="card-body">
            <?php if ($customers->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Account Number</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($customer = $customers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $customer['account_number']; ?></td>
                                    <td><?php echo $customer['name']; ?></td>
                                    <td><?php echo $customer['email']; ?></td>
                                    <td>$<?php echo number_format($customer['balance'], 2); ?></td>
                                    <td><?php echo ucfirst($customer['status']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="account_number" value="<?php echo $customer['account_number']; ?>">
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $customer['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="active" <?php echo $customer['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="rejected" <?php echo $customer['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                <option value="suspended" <?php echo $customer['status'] == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                            </select>
                                            <input type="hidden" name="change_status" value="1">
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No customer accounts found.</p>
            <?php endif; ?>
        </div>
    </div>
<?php include '../includes/footer.php'; ?>