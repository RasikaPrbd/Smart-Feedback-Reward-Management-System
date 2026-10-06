<?php
/*
 * -------------------------------------------------------
 * Page    : Landing Page
 *
 * Purpose : Entry point of the application. Redirects visitors to the appropriate login page.
 * -------------------------------------------------------
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customer Feedback & Reward Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
          rel="stylesheet">

    <style>

        body{
            background:#f8f9fa;
        }

        .hero{
            background: linear-gradient(135deg,#0d6efd,#4f46e5);
            color:white;
            padding:80px 20px;
            border-radius:0 0 30px 30px;
        }

        .portal-card{
            transition:0.3s;
            border:none;
            border-radius:20px;
        }

        .portal-card:hover{
            transform:translateY(-6px);
        }

        .icon-box{
            width:80px;
            height:80px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:32px;
            margin:auto;
        }

        footer{
            margin-top:80px;
            background:#212529;
            color:white;
            padding:20px;
        }

    </style>

</head>

<body>

<div class="hero text-center">

    <div class="container">

        <h1 class="fw-bold mb-3">
            Customer Feedback & Reward Management System
        </h1>

        <p class="lead mb-0">
            Collect customer feedback, manage rewards and gain valuable insights.
        </p>

    </div>

</div>

<div class="container my-5">

    <div class="row g-4 justify-content-center">

        <!-- User -->

        <div class="col-md-4">

            <div class="card portal-card shadow h-100">

                <div class="card-body text-center p-4">

                    <div class="icon-box bg-primary text-white mb-4">
                        <i class="bi bi-person"></i>
                    </div>

                    <h4>User Portal</h4>

                    <p class="text-muted">
                        Participate in surveys, earn points and redeem rewards.
                    </p>

                    <a href="user/login.php"
                       class="btn btn-primary w-100">

                        User Login

                    </a>

                </div>

            </div>

        </div>

        <!-- Company -->

        <div class="col-md-4">

            <div class="card portal-card shadow h-100">

                <div class="card-body text-center p-4">

                    <div class="icon-box bg-success text-white mb-4">
                        <i class="bi bi-building"></i>
                    </div>

                    <h4>Company Portal</h4>

                    <p class="text-muted">
                        Create surveys, manage rewards and analyze responses.
                    </p>

                    <a href="company/login.php"
                       class="btn btn-success w-100">

                        Company Login

                    </a>

                </div>

            </div>

        </div>

        <!-- Admin -->

        <div class="col-md-4">

            <div class="card portal-card shadow h-100">

                <div class="card-body text-center p-4">

                    <div class="icon-box bg-dark text-white mb-4">
                        <i class="bi bi-shield-lock"></i>
                    </div>

                    <h4>Admin Portal</h4>

                    <p class="text-muted">
                        Manage users, companies, surveys and rewards.
                    </p>

                    <a href="admin/login.php"
                       class="btn btn-dark w-100">

                        Admin Login

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<footer class="text-center">

    <div class="container">

        <h6 class="mb-2">
            Customer Feedback & Reward Management System
        </h6>
        
    </div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>