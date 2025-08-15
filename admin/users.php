<?php
require_once '../config/database.php';
requireLogin();

if (!hasRole('admin')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle add, edit, delete actions
$action = $_GET['action'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        // Add user
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $role = $_POST['role'];
        $query = "INSERT INTO users (username, email, password, first_name, last_name, role, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$username, $email, $password, $first_name, $last_name, $role])) {
            $message = 'User added successfully!';
        } else {
            $message = 'Error adding user.';
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        // Edit user
        $id = $_POST['id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $role = $_POST['role'];
        $query = "UPDATE users SET username=?, email=?, first_name=?, last_name=?, role=? WHERE id=?";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$username, $email, $first_name, $last_name, $role, $id])) {
            $message = 'User updated successfully!';
        } else {
            $message = 'Error updating user.';
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM users WHERE id=?";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$id])) {
        $message = 'User deleted successfully!';
    } else {
        $message = 'Error deleting user.';
    }
}

// Get all users
$stmt = $db->query("SELECT id, username, email, first_name, last_name, role FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If editing, get user info
$edit_user = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/admin_navbar.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/admin_sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h2>User Management</h2>
                <a href="users.php?action=add" class="btn btn-success">Add User</a>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-info"> <?php echo $message; ?> </div>
            <?php endif; ?>
            <?php if ($action === 'add' || ($action === 'edit' && $edit_user)): ?>
                <div class="card mb-4">
                    <div class="card-header"> <?php echo $action === 'add' ? 'Add User' : 'Edit User'; ?> </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required value="<?php echo $edit_user['username'] ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required value="<?php echo $edit_user['email'] ?? ''; ?>">
                            </div>
                            <?php if ($action === 'add'): ?>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="form-control" required value="<?php echo $edit_user['first_name'] ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="form-control" required value="<?php echo $edit_user['last_name'] ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label>Role</label>
                                <select name="role" class="form-control" required>
                                    <option value="admin" <?php if (($edit_user['role'] ?? '') === 'admin') echo 'selected'; ?>>Admin</option>
                                    <option value="instructor" <?php if (($edit_user['role'] ?? '') === 'instructor') echo 'selected'; ?>>Instructor</option>
                                    <option value="student" <?php if (($edit_user['role'] ?? '') === 'student') echo 'selected'; ?>>Student</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="users.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-header">User List</div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td>
                                    <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                    <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
