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

// ตรวจสอบว่ามีการส่งค่า StuID มาหรือไม่
if (!isset($_GET['StuID'])) {
    die("กรุณาระบุรหัสนักเรียนที่ต้องการแก้ไข");
}

$stu_id = $_GET['StuID'];

// ดึงข้อมูลนักเรียนจากฐานข้อมูลเพื่อนำไปแสดงในฟอร์ม
$stmt = $conn->prepare("SELECT StuID, Prefix, StudentName, StudentLastName, StudentNameEng, StudentLastNameEng, Age, DepID, CityID, Address, Domicile, PhoneNumber, SubjectID, YearID FROM tbl_Student WHERE StuID = ?");
$stmt->bind_param("i", $stu_id);
$stmt->execute();
$stmt->bind_result($StuID, $Prefix, $StudentName, $StudentLastName, $StudentNameEng, $StudentLastNameEng, $Age, $DepID, $CityID, $Address, $Domicile, $PhoneNumber, $SubjectID, $YearID);
$stmt->fetch();

if (!$StuID) {
    die("ไม่พบนักเรียนที่ต้องการแก้ไข");
}

// เก็บข้อมูลในอาร์เรย์เพื่อนำไปใช้ในฟอร์ม
$student = [
    'StuID' => $StuID,
    'Prefix' => $Prefix,
    'StudentName' => $StudentName,
    'StudentLastName' => $StudentLastName,
    'StudentNameEng' => $StudentNameEng,
    'StudentLastNameEng' => $StudentLastNameEng,
    'Age' => $Age,
    'DepID' => $DepID,
    'CityID' => $CityID,
    'Address' => $Address,
    'Domicile' => $Domicile,
    'PhoneNumber' => $PhoneNumber,
    'SubjectID' => $SubjectID,
    'YearID' => $YearID
];

// ปิด statement
$stmt->close();

// เตรียมข้อมูลสำหรับ select options จากฐานข้อมูล (เหมือนโค้ด insert.php)
$genders = ['นาย', 'นางสาว']; 

// ดึงข้อมูลจากตาราง Department
$departments = [];
$dep_result = $conn->query("SELECT DepID, Department FROM tbl_Department");
while ($row = $dep_result->fetch_assoc()) {
    $departments[$row['DepID']] = $row['Department'];
}

// ดึงข้อมูลจากตาราง City
$cities = [];
$city_result = $conn->query("SELECT CityID, CityName FROM tbl_City");
while ($row = $city_result->fetch_assoc()) {
    $cities[$row['CityID']] = $row['CityName'];
}

// ดึงข้อมูลจากตาราง Subject
$subjects = [];
$subject_result = $conn->query("SELECT SubjectID, SubjectName FROM tbl_Subject");
while ($row = $subject_result->fetch_assoc()) {
    $subjects[$row['SubjectID']] = $row['SubjectName'];
}

// ดึงข้อมูลจากตาราง Year
$years = [];
$year_result = $conn->query("SELECT YearID, YearName FROM tbl_Year");
while ($row = $year_result->fetch_assoc()) {
    $years[$row['YearID']] = $row['YearName'];
}

// ดึงข้อมูลจากตาราง Hobby
$hobbies = [];
$hobby_result = $conn->query("SELECT HobbyID, HobbyName FROM tbl_Hobby");
while ($row = $hobby_result->fetch_assoc()) {
    $hobbies[$row['HobbyID']] = $row['HobbyName'];
}

// ดึงข้อมูลงานอดิเรกของนักเรียน
$student_hobbies = [];
$hobby_stmt = $conn->prepare("SELECT HobbyID FROM tbl_Student_Hobby WHERE StuID = ?");
$hobby_stmt->bind_param("i", $stu_id);
$hobby_stmt->execute();
$hobby_stmt->bind_result($hobby_id);
while ($hobby_stmt->fetch()) {
    $student_hobbies[] = $hobby_id; // เก็บ HobbyID ในอาร์เรย์
}
$hobby_stmt->close();

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        // อัพเดตข้อมูลในตาราง tbl_Student
        $stmt = $conn->prepare("UPDATE tbl_Student SET Prefix = ?, StudentName = ?, StudentLastName = ?, StudentNameEng = ?, StudentLastNameEng = ?, Age = ?, DepID = ?, CityID = ?, Address = ?, Domicile = ?, PhoneNumber = ?, SubjectID = ?, YearID = ? WHERE StuID = ?");
        $stmt->bind_param("sssssiissssiii", $Prefix, $name_th, $surname_th, $name_en, $surname_en, $age, $department, $city, $address, $hometown, $phone, $subject, $year, $stu_id);
        if (!$stmt->execute()) {
            throw new Exception("Error updating student: " . $stmt->error);
        }

        // ลบข้อมูลใน tbl_Student_Hobby เดิมออก
        $conn->query("DELETE FROM tbl_Student_Hobby WHERE StuID = $stu_id");

        // แทรกข้อมูลงานอดิเรกใหม่ลงใน tbl_Student_Hobby
        $stmt_hobby = $conn->prepare("INSERT INTO tbl_Student_Hobby (StuID, HobbyID) VALUES (?, ?)");
        foreach ($hobby_ids as $hobby_id) {
            $stmt_hobby->bind_param("ii", $stu_id, $hobby_id);
            if (!$stmt_hobby->execute()) {
                throw new Exception("Error inserting student hobby: " . $stmt_hobby->error);
            }
        }

        // ยืนยันการทำธุรกรรม
        $conn->commit();
        echo "แก้ไขข้อมูลนักเรียนเรียบร้อยแล้ว";
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
    <title>แก้ไขข้อมูลนักเรียน</title>
</head>
<body>
    <h1>แก้ไขข้อมูลนักเรียน</h1>
    <form method="post">
        <label for="Prefix">คำนำหน้าชื่อ:</label>
        <select id="Prefix" name="Prefix" required>
            <?php foreach ($genders as $gender): ?>
                <option value="<?php echo htmlspecialchars($gender); ?>" <?php echo $gender == $student['Prefix'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($gender); ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="StudentName">ชื่อ:</label>
        <input type="text" id="StudentName" name="StudentName" value="<?php echo htmlspecialchars($student['StudentName']); ?>" required><br>

        <label for="StudentLastName">นามสกุล:</label>
        <input type="text" id="StudentLastName" name="StudentLastName" value="<?php echo htmlspecialchars($student['StudentLastName']); ?>" required><br>

        <label for="StudentNameEng">ชื่อ (ภาษาอังกฤษ):</label>
        <input type="text" id="StudentNameEng" name="StudentNameEng" value="<?php echo htmlspecialchars($student['StudentNameEng']); ?>" required><br>

        <label for="StudentLastNameEng">นามสกุล (ภาษาอังกฤษ):</label>
        <input type="text" id="StudentLastNameEng" name="StudentLastNameEng" value="<?php echo htmlspecialchars($student['StudentLastNameEng']); ?>" required><br>

        <label for="Age">อายุ:</label>
        <input type="number" id="Age" name="Age" value="<?php echo htmlspecialchars($student['Age']); ?>" required><br>

        <label for="DepID">แผนก:</label>
        <select id="DepID" name="DepID" required>
            <?php foreach ($departments as $id => $department): ?>
                <option value="<?php echo htmlspecialchars($id); ?>" <?php echo $id == $student['DepID'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($department); ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="CityID">จังหวัด:</label>
        <select id="CityID" name="CityID" required>
            <?php foreach ($cities as $id => $city): ?>
                <option value="<?php echo htmlspecialchars($id); ?>" <?php echo $id == $student['CityID'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($city); ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="Address">ที่อยู่:</label>
        <input type="text" id="Address" name="Address" value="<?php echo htmlspecialchars($student['Address']); ?>" required><br>

        <label for="Domicile">ภูมิลำเนา:</label>
        <input type="text" id="Domicile" name="Domicile" value="<?php echo htmlspecialchars($student['Domicile']); ?>" required><br>

        <label for="PhoneNumber">เบอร์โทรศัพท์:</label>
        <input type="text" id="PhoneNumber" name="PhoneNumber" value="<?php echo htmlspecialchars($student['PhoneNumber']); ?>" required><br>

        <label for="SubjectID">วิชาเอก:</label>
        <select id="SubjectID" name="SubjectID" required>
            <?php foreach ($subjects as $id => $subject): ?>
                <option value="<?php echo htmlspecialchars($id); ?>" <?php echo $id == $student['SubjectID'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($subject); ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="YearID">ชั้นปี:</label>
        <select id="YearID" name="YearID" required>
            <?php foreach ($years as $id => $year): ?>
                <option value="<?php echo htmlspecialchars($id); ?>" <?php echo $id == $student['YearID'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($year); ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="HobbyID">งานอดิเรก:</label><br>
        <?php foreach ($hobbies as $id => $hobby): ?>
            <input type="checkbox" id="HobbyID_<?php echo htmlspecialchars($id); ?>" name="HobbyID[]" value="<?php echo htmlspecialchars($id); ?>" <?php echo in_array($id, $student_hobbies) ? 'checked' : ''; ?>>
            <label for="HobbyID_<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($hobby); ?></label><br>
        <?php endforeach; ?>

        <input type="submit" value="บันทึกการแก้ไข">
    </form>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
