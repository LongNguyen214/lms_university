<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$error = '';

if ($_POST) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT id, username, email, password, first_name, last_name, role FROM users WHERE username = ? OR email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$username, $username]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header('Location: ../admin/dashboard.php');
                    break;
                case 'instructor':
                    header('Location: ../instructor/dashboard.php');
                    break;
                case 'student':
                    header('Location: ../student/dashboard.php');
                    break;
            }
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #ffe0b2;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(255, 183, 77, 0.15);
            overflow: hidden;
            border: 2px solid #ffe082;
        }
        .login-header {
            background: linear-gradient(135deg, #ffe082 0%, #ffb300 100%);
            color: #ff7043;
            padding: 2rem;
            text-align: center;
        }
        .login-header i {
            color: #67afc3ff;
            text-shadow: 0 2px 8px #fffde7;
        }
        .login-body {
            padding: 2rem;
        }
        .btn-login {
            background: linear-gradient(135deg, #29b6f6 0%, #ffb300 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 4px 12px rgba(41, 182, 246, 0.15);
            transition: background 0.3s;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #ffb300 0%, #29b6f6 100%);
            color: #fffde7;
        }
        .form-control {
            border-radius: 25px;
            padding: 12px 20px;
            border: 2px solid #ffe082;
            background: #fffde7;
        }
        .form-control:focus {
            border-color: #29b6f6;
            box-shadow: 0 0 0 0.2rem rgba(41, 182, 246, 0.15);
        }
        .input-group-text {
            background: #fffde7;
            border: 2px solid #ffe082;
            color: #ffb300;
        }
        .alert-danger {
            background: rgba(255, 112, 67, 0.15);
            color: #d84315;
            border: 1px solid #ffb300;
        }
        .text-muted {
            color: #ffb300 !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-graduation-cap fa-3x mb-3"></i>
                        <h3>University LMS</h3>
                        <p class="mb-0">Learning Management System</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username or Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                Demo Accounts:<br>
                                Admin: admin / 123456<br>
                                Instructor: prof_smith / instructor123<br>
                                Student: student1 / student123
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>