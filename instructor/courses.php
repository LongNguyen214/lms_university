<?php
require_once '../config/database.php';
requireLogin();

if (!hasRole('instructor')) {
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
        // Add course
        $title = trim($_POST['title']);
        $course_code = trim($_POST['course_code']);
        $semester = trim($_POST['semester']);
        $year = trim($_POST['year']);
        $status = $_POST['status'];
        $instructor_id = $_SESSION['user_id'];
        $query = "INSERT INTO courses (title, course_code, semester, year, status, instructor_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$title, $course_code, $semester, $year, $status, $instructor_id])) {
            $message = 'Course added successfully!';
        } else {
            $message = 'Error adding course.';
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        // Edit course
        $id = $_POST['id'];
        $title = trim($_POST['title']);
        $course_code = trim($_POST['course_code']);
        $semester = trim($_POST['semester']);
        $year = trim($_POST['year']);
        $status = $_POST['status'];
        $query = "UPDATE courses SET title=?, course_code=?, semester=?, year=?, status=? WHERE id=? AND instructor_id=?";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$title, $course_code, $semester, $year, $status, $id, $_SESSION['user_id']])) {
            $message = 'Course updated successfully!';
        } else {
            $message = 'Error updating course.';
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM courses WHERE id=? AND instructor_id=?";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$id, $_SESSION['user_id']])) {
        $message = 'Course deleted successfully!';
    } else {
        $message = 'Error deleting course.';
    }
}

// Get all courses for instructor
$stmt = $db->prepare("SELECT * FROM courses WHERE instructor_id=? ORDER BY id DESC");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If editing, get course info
$edit_course = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $db->prepare("SELECT * FROM courses WHERE id=? AND instructor_id=?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $edit_course = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/instructor_navbar.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/instructor_sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h2>Course Management</h2>
                <a href="courses.php?action=add" class="btn btn-success">Add Course</a>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-info"> <?php echo $message; ?> </div>
            <?php endif; ?>
            <?php if ($action === 'add' || ($action === 'edit' && $edit_course)): ?>
                <div class="card mb-4">
                    <div class="card-header"> <?php echo $action === 'add' ? 'Add Course' : 'Edit Course'; ?> </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_course['id']; ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required value="<?php echo $edit_course['title'] ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label>Course Code</label>
                                <input type="text" name="course_code" class="form-control" required value="<?php echo $edit_course['course_code'] ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label>Semester</label>
                                <input type="text" name="semester" class="form-control" required value="<?php echo $edit_course['semester'] ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label>Year</label>
                                <input type="number" name="year" class="form-control" required value="<?php echo $edit_course['year'] ?? date('Y'); ?>">
                            </div>
                            <div class="mb-3">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="active" <?php if (($edit_course['status'] ?? '') === 'active') echo 'selected'; ?>>Active</option>
                                    <option value="inactive" <?php if (($edit_course['status'] ?? '') === 'inactive') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="courses.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-header">Course List</div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Code</th>
                                <th>Semester</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo $course['id']; ?></td>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['semester']); ?></td>
                                <td><?php echo htmlspecialchars($course['year']); ?></td>
                                <td>
                                    <span class="badge <?php echo $course['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo ucfirst($course['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="courses.php?action=edit&id=<?php echo $course['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                    <a href="courses.php?action=delete&id=<?php echo $course['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this course?');">Delete</a>
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
