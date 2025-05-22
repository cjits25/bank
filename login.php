<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isCustomerLoggedIn()) {
    header("Location: customer/dashboard.php");
    exit();
} elseif (isManagerLoggedIn()) {
    header("Location: manager/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    if ($user_type == 'customer') {
        // Customer login
        $sql = "SELECT * FROM customers WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            $customer = $result->fetch_assoc();
            
            if (password_verify($password, $customer['password'])) {
                if ($customer['status'] == 'active') {
                    $_SESSION['customer_id'] = $customer['id'];
                    $_SESSION['customer_name'] = $customer['name'];
                    $_SESSION['account_number'] = $customer['account_number'];
                    header("Location: customer/dashboard.php");
                    exit();
                } elseif ($customer['status'] == 'pending') {
                    $_SESSION['error'] = "Your account is pending approval. Please contact the bank.";
                } elseif ($customer['status'] == 'suspended') {
                    $_SESSION['error'] = "Your account has been suspended. Please contact the bank.";
                } else {
                    $_SESSION['error'] = "Your account has been rejected. Please contact the bank.";
                }
            } else {
                $_SESSION['error'] = "Invalid email or password";
            }
        } else {
            $_SESSION['error'] = "Invalid email or password";
        }
    } elseif ($user_type == 'manager') {
        // Manager login
        $sql = "SELECT * FROM managers WHERE username = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            $manager = $result->fetch_assoc();
            
            if (password_verify($password, $manager['password'])) {
                $_SESSION['manager_id'] = $manager['id'];
                $_SESSION['manager_username'] = $manager['username'];
                $_SESSION['manager_role'] = $manager['role'];
                header("Location: manager/dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid username or password";
            }
        } else {
            $_SESSION['error'] = "Invalid username or password";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Login</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="mb-3">
                        <label for="user_type" class="form-label">Login As</label>
                        <select class="form-select" id="user_type" name="user_type" required>
                            <option value="customer">Customer</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label" id="emailLabel">Email Address</label>
                        <input type="text" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
                <hr>
                <div class="text-center">
                    <p>Don't have an account? <a href="register.php">Register as Customer</a></p>
                    <?php if (isset($_SESSION['manager_id']) && $_SESSION['manager_role'] === 'admin'): ?>
                        <p>Admin? <a href="manager/register.php">Register New Manager</a></p>
                    <?php endif; ?>
                    <p><a href="forgot-password.php">Forgot Password?</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('user_type').addEventListener('change', function() {
    const label = document.getElementById('emailLabel');
    if (this.value === 'manager') {
        label.textContent = 'Username';
    } else {
        label.textContent = 'Email Address';
    }
});
</script>

<?php include 'includes/footer.php'; ?>