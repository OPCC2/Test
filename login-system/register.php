<?php
require_once "connect.php";
$message = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    if ($username === "" || $password === "") $message = "กรุณากรอกข้อมูลให้ครบ";
    elseif ($password !== $confirm_password) $message = "รหัสผ่านไม่ตรงกัน";
    elseif (strlen($password) < 6) $message = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();
        if ($result->num_rows > 0) $message = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
        else {
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $password);
            if ($stmt->execute()) { $success = "สมัครสมาชิกสำเร็จ!"; header("refresh:2;url=index.php"); }
            else $message = "เกิดข้อผิดพลาด กรุณาลองใหม่";
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="th"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>สมัครสมาชิก</title>
<style>*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;background:#f4f6f8;display:flex;justify-content:center;align-items:center;min-height:100vh}.register-box{width:350px;background:#fff;padding:30px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,.12)}h2{text-align:center;margin-top:0;margin-bottom:25px}label{display:block;margin-top:12px;margin-bottom:6px}input{width:100%;padding:12px;border:1px solid #ccc;border-radius:6px;font-size:15px}button{width:100%;padding:12px;margin-top:20px;border:0;border-radius:6px;background:#007bff;color:#fff;font-size:16px;cursor:pointer}.error{color:#dc3545;text-align:center;margin-bottom:15px}.success{color:#198754;text-align:center;margin-bottom:15px}.login-link{text-align:center;margin-top:20px;padding-top:20px;border-top:1px solid #eee}.login-link a{color:#007bff;text-decoration:none}</style>
</head><body><div class="register-box"><h2>สมัครสมาชิก</h2>
<?php if ($message): ?><div class="error"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<form method="POST"><label>ชื่อผู้ใช้</label><input type="text" name="username" placeholder="กรอกชื่อผู้ใช้" required><label>รหัสผ่าน</label><input type="password" name="password" placeholder="กรอกรหัสผ่าน" required><label>ยืนยันรหัสผ่าน</label><input type="password" name="confirm_password" placeholder="กรอกรหัสผ่านอีกครั้ง" required><button type="submit">สมัครสมาชิก</button></form>
<div class="login-link">มีบัญชีอยู่แล้ว? <a href="index.php">เข้าสู่ระบบ</a></div></div></body></html>
