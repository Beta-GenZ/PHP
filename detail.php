<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// ดึงค่า ID จาก URL
$stuID = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : '';

// คำสั่ง SQL เพื่อดึงข้อมูลนักศึกษา
$sql = "SELECT s.*, d.Department, c.CityName, y.YearName, sub.SubjectName
        FROM tbl_Student s
        JOIN tbl_Department d ON s.DepID = d.DepID
        JOIN tbl_City c ON s.CityID = c.CityID
        JOIN tbl_Year y ON s.YearID = y.YearID
        JOIN tbl_Subject sub ON s.SubjectID = sub.SubjectID
        WHERE s.StuID = '$stuID'";
$result = $conn->query($sql);

// ตรวจสอบว่ามีผลลัพธ์หรือไม่
$row = $result->num_rows > 0 ? $result->fetch_assoc() : null;

// คำสั่ง SQL เพื่อดึงความชอบทั้งหมดของนักเรียน
$hobbySql = "SELECT h.HobbyName 
             FROM tbl_Hobby h
             JOIN tbl_Student_Hobby sh ON h.HobbyID = sh.HobbyID
             WHERE sh.StuID = '$stuID'";
$hobbyResult = $conn->query($hobbySql);

// เตรียมอาเรย์เพื่อเก็บชื่อความชอบ
$hobbyNames = [];
if ($hobbyResult) {
    while ($hobbyRow = $hobbyResult->fetch_assoc()) {
        $hobbyNames[] = $hobbyRow['HobbyName'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Detail</title>
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

        table {
            width: 60%;
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

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .error-message {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Student Details</h1>
        <?php if ($row): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <td><?php echo htmlspecialchars($row['StuID']); ?></td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td><?php echo htmlspecialchars($row['Prefix'] . " " . $row['StudentName'] . " " . $row['StudentLastName']); ?></td>
                </tr>
                <tr>
                    <th>English Name</th>
                    <td><?php echo htmlspecialchars($row['StudentNameEng'] . " " . $row['StudentLastNameEng']); ?></td>
                </tr>
                <tr>
                    <th>Age</th>
                    <td><?php echo htmlspecialchars($row['Age']); ?></td>
                </tr>
                <tr>
                    <th>Department</th>
                    <td><?php echo htmlspecialchars($row['Department']); ?></td>
                </tr>
                <tr>
                    <th>City</th>
                    <td><?php echo htmlspecialchars($row['CityName']); ?></td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td><?php echo htmlspecialchars($row['Address']); ?></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><?php echo htmlspecialchars($row['PhoneNumber']); ?></td>
                </tr>
                <tr>
                    <th>Subject</th>
                    <td><?php echo htmlspecialchars($row['SubjectName']); ?></td>
                </tr>
                <tr>
                    <th>Year</th>
                    <td><?php echo htmlspecialchars($row['YearName']); ?></td>
                </tr>
                <tr>
                    <th>Hobby</th>
                    <td>
                        <?php if (!empty($hobbyNames)): ?>
                            <ul>
                                <?php foreach ($hobbyNames as $hobby): ?>
                                    <li><?php echo htmlspecialchars($hobby); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <span>No hobbies found.</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        <?php else: ?>
            <p class="error-message">No records found for this student.</p>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="master.php"><button>Back to Student List</button></a>
        </div>
    </div>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
