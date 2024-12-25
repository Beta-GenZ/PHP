<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ข้อมูลสำหรับเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "u299560388_651227"; // เปลี่ยนเป็น username ของคุณ
$password = "LK3508Hk"; // เปลี่ยนเป็น password ของคุณ
$dbname = "u299560388_651227"; // ชื่อฐานข้อมูล

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// เตรียมข้อมูลสำหรับ select options จากฐานข้อมูล
$genders = ['นาย', 'นางสาว']; // ข้อมูลคำนำหน้าชื่อ

// ดึงข้อมูลจากตาราง Department
$departments = [];
$dep_result = $conn->query("SELECT DepID, Department FROM tbl_Department");
if ($dep_result) {
    while ($row = $dep_result->fetch_assoc()) {
        $departments[$row['DepID']] = $row['Department'];
    }
} else {
    die("Error fetching departments: " . $conn->error);
}

// ดึงข้อมูลจากตาราง City
$cities = [];
$city_result = $conn->query("SELECT CityID, CityName FROM tbl_City");
if ($city_result) {
    while ($row = $city_result->fetch_assoc()) {
        $cities[$row['CityID']] = $row['CityName'];
    }
} else {
    die("Error fetching cities: " . $conn->error);
}

// ดึงข้อมูลจากตาราง Subject
$subjects = [];
$subject_result = $conn->query("SELECT SubjectID, SubjectName FROM tbl_Subject");
if ($subject_result) {
    while ($row = $subject_result->fetch_assoc()) {
        $subjects[$row['SubjectID']] = $row['SubjectName'];
    }
} else {
    die("Error fetching subjects: " . $conn->error);
}

// ดึงข้อมูลจากตาราง Year
$years = [];
$year_result = $conn->query("SELECT YearID, YearName FROM tbl_Year");
if ($year_result) {
    while ($row = $year_result->fetch_assoc()) {
        $years[$row['YearID']] = $row['YearName'];
    }
} else {
    die("Error fetching years: " . $conn->error);
}

// ดึงข้อมูลจากตาราง Hobby
$hobbies = [];
$hobby_result = $conn->query("SELECT HobbyID, HobbyName FROM tbl_Hobby");
if ($hobby_result) {
    while ($row = $hobby_result->fetch_assoc()) {
        $hobbies[$row['HobbyID']] = $row['HobbyName'];
    }
} else {
    die("Error fetching hobbies: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $Prefix = $_POST['Prefix'];
    $name_th = $_POST['StudentName'];
    $surname_th = $_POST['StudentLastName'];
    $name_en = $_POST['StudentNameEng'];
    $surname_en = $_POST['StudentLastNameEng'];
    $age = $_POST['Age'];
    $department = $_POST['DepID'];
    $city = $_POST['CityID'];
    $address = $_POST['Address'];
    $hometown = $_POST['Domicile'];
    $phone = $_POST['PhoneNumber'];
    $subject = $_POST['SubjectID'];
    $year = $_POST['YearID'];
    $hobby_ids = isset($_POST['HobbyID']) ? $_POST['HobbyID'] : [];

    if (empty($hobby_ids)) {
        die("กรุณาเลือกงานอดิเรกอย่างน้อยหนึ่งรายการ");
    }

    // เริ่ม transaction
    $conn->begin_transaction();

    try {
        // หาค่า StuID ที่มากที่สุดและเพิ่มขึ้น 1
        $result = $conn->query("SELECT MAX(StuID) AS max_id FROM tbl_Student");
        if (!$result) {
            throw new Exception("Error fetching max StuID: " . $conn->error);
        }
        $row = $result->fetch_assoc();
        $new_stu_id = $row['max_id'] + 1;

        // แทรกข้อมูลลงในตาราง tbl_Student โดยรวม StuID และ HobbyID แรกที่เลือก
        $primary_hobby_id = $hobby_ids[0];
        $stmt = $conn->prepare("INSERT INTO tbl_Student (StuID, Prefix, StudentName, StudentLastName, StudentNameEng, StudentLastNameEng, Age, DepID, CityID, Address, Domicile, PhoneNumber, SubjectID, YearID, HobbyID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error); // เพิ่มการตรวจสอบข้อผิดพลาด
        }
        $stmt->bind_param("isssssissssiiis", $new_stu_id, $Prefix, $name_th, $surname_th, $name_en, $surname_en, $age, $department, $city, $address, $hometown, $phone, $subject, $year, $primary_hobby_id);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting student: " . $stmt->error);
        }
        $stmt->close();

        // แทรกข้อมูลลงในตาราง tbl_Student_Hobby สำหรับแต่ละ Hobby ที่เลือก
        if (!empty($hobby_ids)) {
            $stmt_hobby = $conn->prepare("INSERT INTO tbl_Student_Hobby (StuID, HobbyID) VALUES (?, ?)");
            if (!$stmt_hobby) {
                die("Prepare failed: " . $conn->error); // เพิ่มการตรวจสอบข้อผิดพลาด
            }
            foreach ($hobby_ids as $hobby_id) {
                $stmt_hobby->bind_param("ii", $new_stu_id, $hobby_id);
                if (!$stmt_hobby->execute()) {
                    throw new Exception("Error inserting student hobby: " . $stmt_hobby->error);
                }
            }
            $stmt_hobby->close();
        }

        // ยืนยันการทำธุรกรรม
        $conn->commit();
        echo "บันทึกข้อมูลนักเรียนเรียบร้อยแล้ว";

    } catch (Exception $e) {
        // ยกเลิกการทำธุรกรรมหากมีข้อผิดพลาด
        $conn->rollback();
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ลงทะเบียนนักเรียน</title>
</head>
<body>
    <h1>ลงทะเบียนนักเรียน</h1>
    <form method="post">
        <label for="Prefix">คำนำหน้าชื่อ:</label>
        <select id="Prefix" name="Prefix" required>
            <?php foreach ($genders as $gender): ?>
                <option value="<?php echo htmlspecialchars($gender); ?>"><?php echo htmlspecialchars($gender); ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="StudentName">ชื่อ:</label>
        <input type="text" id="StudentName" name="StudentName" required><br>

        <label for="StudentLastName">นามสกุล:</label>
        <input type="text" id="StudentLastName" name="StudentLastName" required><br>

        <label for="StudentNameEng">ชื่อ (ภาษาอังกฤษ):</label>
        <input type="text" id="StudentNameEng" name="StudentNameEng" required><br>

        <label for="StudentLastNameEng">นามสกุล (ภาษาอังกฤษ):</label>
        <input type="text" id="StudentLastNameEng" name="StudentLastNameEng" required><br>

        <label for="Age">อายุ:</label>
        <input type="number" id="Age" name="Age" required><br>

        <label for="DepID">แผนก:</label>
        <select id="DepID" name="DepID" required>
            <?php foreach ($departments as $id => $department): ?>
                <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($department); ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="CityID">เมือง:</label>
        <select id="CityID" name="CityID" required>
            <?php foreach ($cities as $id => $city): ?>
                <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($city); ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="Address">ที่อยู่:</label>
        <textarea id="Address" name="Address" required></textarea><br>

        <label for="Domicile">ที่อยู่ถิ่นกำเนิด:</label>
        <input type="text" id="Domicile" name="Domicile" required><br>

        <label for="PhoneNumber">หมายเลขโทรศัพท์:</label>
        <input type="tel" id="PhoneNumber" name="PhoneNumber" required><br>

        <label for="SubjectID">วิชา:</label>
        <select id="SubjectID" name="SubjectID" required>
            <?php foreach ($subjects as $id => $subject): ?>
                <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($subject); ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="YearID">ปีการศึกษา:</label>
        <select id="YearID" name="YearID" required>
            <?php foreach ($years as $id => $year): ?>
                <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($year); ?></option>
            <?php endforeach; ?>
        </select><br>

        <label>งานอดิเรก:</label><br>
        <?php foreach ($hobbies as $id => $hobby): ?>
            <input type="checkbox" name="HobbyID[]" value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($hobby); ?><br>
        <?php endforeach; ?>

        <input type="submit" value="บันทึกข้อมูล">
    </form>
</body>
</html>
