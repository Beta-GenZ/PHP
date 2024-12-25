<?php
// ข้อมูลสำหรับเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "u299560388_651227";
$password = "LK3508Hk";
$dbname = "u299560388_651227";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบการลบข้อมูลนักเรียน
if (isset($_POST['delete'])) {
    $stu_id = $_POST['StuID'];

    // ตรวจสอบว่า StuID ไม่ว่าง
    if (empty($stu_id)) {
        echo "<script>alert('StuID ไม่ถูกต้อง');</script>";
    } else {
        // ลบข้อมูลใน tbl_Student_Hobby ก่อน
        $delete_hobby_stmt = $conn->prepare("DELETE FROM tbl_Student_Hobby WHERE StuID = ?");
        $delete_hobby_stmt->bind_param("i", $stu_id);
        $delete_hobby_stmt->execute();
        $delete_hobby_stmt->close();

        // ลบข้อมูลใน tbl_Student
        $delete_stmt = $conn->prepare("DELETE FROM tbl_Student WHERE StuID = ?");
        $delete_stmt->bind_param("i", $stu_id);

        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                echo "<script>alert('ลบข้อมูลนักเรียนเรียบร้อยแล้ว');</script>";
            } else {
                echo "<script>alert('ไม่พบข้อมูลนักเรียนที่ต้องการลบ');</script>";
            }
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการลบข้อมูลนักเรียน: " . $delete_stmt->error . "');</script>";
        }

        $delete_stmt->close();
    }
}

// ดึงข้อมูลนักศึกษา โดยเรียงจาก StuID น้อยไปมาก
$sql = "SELECT s.StuID, s.Prefix, s.StudentName, s.StudentLastName, s.StudentNameEng, s.StudentLastNameEng, s.Age, d.Department, y.YearName
        FROM tbl_Student s
        JOIN tbl_Department d ON s.DepID = d.DepID
        JOIN tbl_Year y ON s.YearID = y.YearID
        ORDER BY s.StuID ASC"; // เรียงตาม StuID น้อยไปมาก
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        /* Navigation Bar Styles */
        .nav-bar {
            display: flex; /* Change to flex for better alignment */
            justify-content: center; /* Center the links */
            background-color: #3498db;
            border-radius: 30px; /* Capsule shape */
            padding: 5px; /* Adjust padding */
            margin: 0 auto 20px; /* Center the navbar */
            width: fit-content; /* Adjust width to fit the content */
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 15px; /* Adjust padding to fit the text */
            border-radius: 20px; /* Capsule shape for links */
            transition: background-color 0.3s;
            margin: 0 5px; /* Reduce space between links */
        }

        .nav-link:hover {
            background-color: #2980b9; /* Background color on hover */
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background-color: #2980b9;
        }

        .delete-button {
            background-color: #e74c3c;
        }

        .delete-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="nav-bar">
        <a href="master.php" class="nav-link">Home</a>
        <a href="register.php" class="nav-link">Add Information</a>
    </div>

    <h1>Student List</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>English Name</th>
                <th>Age</th>
                <th>Department</th>
                <th>Year</th>
                <th>Details</th>
                <th>Edit</th>
                <th>Delete</th> <!-- เพิ่มคอลัมน์สำหรับปุ่ม Delete -->
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['StuID']); ?></td>
                        <td><?php echo htmlspecialchars($row['Prefix'] . " " . $row['StudentName'] . " " . $row['StudentLastName']); ?></td>
                        <td><?php echo htmlspecialchars($row['StudentNameEng'] . " " . $row['StudentLastNameEng']); ?></td>
                        <td><?php echo htmlspecialchars($row['Age']); ?></td>
                        <td><?php echo htmlspecialchars($row['Department']); ?></td>
                        <td><?php echo htmlspecialchars($row['YearName']); ?></td>
                        <td><a href="detail.php?id=<?php echo htmlspecialchars($row['StuID']); ?>"><button>View Details</button></a></td>
                        <td><a href="edit.php?StuID=<?php echo htmlspecialchars($row['StuID']); ?>"><button>Edit</button></a></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="StuID" value="<?php echo htmlspecialchars($row['StuID']); ?>">
                                <button type="submit" name="delete" class="delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No records found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
    // ปิดการเชื่อมต่อฐานข้อมูล
    $conn->close();
    ?>
</body>
</html>
