CREATE DATABASE weekly_planner;
USE weekly_planner;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT,
    name VARCHAR(50),
    day1 INT DEFAULT 0,
    day2 INT DEFAULT 0,
    day3 INT DEFAULT 0,
    day4 INT DEFAULT 0,
    day5 INT DEFAULT 0,
    day6 INT DEFAULT 0,
    day7 INT DEFAULT 0,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);




<?php
// ==================== DATABASE CONNECTION ====================
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "weekly_planner";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// ==================== DELETE EMPLOYEE ====================
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM employees WHERE id = $id");
    header("Location: index.php");
    exit;
}

// ==================== ADD EMPLOYEE ====================
if (isset($_POST['add_employee'])) {
    $dept_id = $_POST['department_id'];
    $name = $_POST['name'];
    $days = [];
    for ($i = 1; $i <= 7; $i++) {
        $days[$i] = intval($_POST["day$i"]);
    }
    $sql = "INSERT INTO employees (department_id, name, day1, day2, day3, day4, day5, day6, day7)
            VALUES ($dept_id, '$name', {$days[1]}, {$days[2]}, {$days[3]}, {$days[4]}, {$days[5]}, {$days[6]}, {$days[7]})";
    $conn->query($sql);
    header("Location: index.php");
    exit;
}

// ==================== FETCH DATA ====================
$departments = $conn->query("SELECT * FROM departments");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weekly Planner</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #999; padding: 8px; text-align: center; }
        th { background-color: #e0b3ff; }
        h2 { background-color: #f0e6ff; padding: 10px; border-radius: 8px; }
        form { margin-bottom: 20px; background: #f9f9f9; padding: 15px; border-radius: 8px; }
        input[type="number"] { width: 60px; }
        .delete { color: red; text-decoration: none; font-weight: bold; }
        .delete:hover { text-decoration: underline; }
    </style>
</head>
<body>

<h1>Weekly Planner (Week 1)</h1>

<!-- ADD EMPLOYEE FORM -->
<form method="POST">
    <label>Department:</label>
    <select name="department_id" required>
        <option value="">Select Department</option>
        <?php while ($d = $departments->fetch_assoc()) { ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
        <?php } ?>
    </select>
    <label>Employee Name:</label>
    <input type="text" name="name" required>
    <?php for ($i = 1; $i <= 7; $i++) { ?>
        <label>Day <?= $i ?>:</label>
        <input type="number" name="day<?= $i ?>" required>
    <?php } ?>
    <button type="submit" name="add_employee">Add Employee</button>
</form>

<?php
// Reset departments pointer
$departments->data_seek(0);

$grand_total = 0;

while ($dept = $departments->fetch_assoc()) {
    $dept_id = $dept['id'];
    $employees = $conn->query("SELECT * FROM employees WHERE department_id = $dept_id");
    if ($employees->num_rows > 0) {
        echo "<h2>{$dept['name']}</h2>";
        echo "<table>
            <tr>
                <th>Employee</th>
                <th>Day1</th><th>Day2</th><th>Day3</th>
                <th>Day4</th><th>Day5</th><th>Day6</th><th>Day7</th>
                <th>Total</th>
                <th>Action</th>
            </tr>";
        $dept_total = 0;

        while ($emp = $employees->fetch_assoc()) {
            $emp_total = $emp['day1'] + $emp['day2'] + $emp['day3'] + $emp['day4'] + $emp['day5'] + $emp['day6'] + $emp['day7'];
            $dept_total += $emp_total;
            echo "<tr>
                <td>{$emp['name']}</td>
                <td>{$emp['day1']}</td>
                <td>{$emp['day2']}</td>
                <td>{$emp['day3']}</td>
                <td>{$emp['day4']}</td>
                <td>{$emp['day5']}</td>
                <td>{$emp['day6']}</td>
                <td>{$emp['day7']}</td>
                <td><b>$emp_total</b></td>
                <td><a href='?delete={$emp['id']}' class='delete' onclick='return confirm(\"Delete this employee?\")'>Delete</a></td>
            </tr>";
        }

        echo "<tr style='background:#f3e6ff; font-weight:bold;'>
                <td colspan='8' align='right'>Department Total</td>
                <td colspan='2'>$dept_total</td>
              </tr>";
        echo "</table>";
        $grand_total += $dept_total;
    }
}

echo "<h3 style='background:#d8b9ff; padding:10px; border-radius:8px;'>Grand Total of All Departments: $grand_total</h3>";
?>

</body>
</html>
