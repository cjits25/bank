<?php 
require_once 'includes/config.php';
require_once 'includes/functions.php';

include 'includes/header.php'; ?>
        <h1 class="text-center mb-4">Welcome to Our Bank</h1>
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Banking Services</h5>
                        <p class="card-text">Manage your accounts with our secure online banking system</p>
                        <a href="login.php" class="btn btn-primary">Login</a>
                        <a href="register.php" class="btn btn-outline-primary">Register</a>
                    </div>
                </div>
            </div>
        </div>
<?php include 'includes/footer.php'; ?>