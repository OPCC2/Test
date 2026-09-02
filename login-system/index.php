<?php
session_start();
require_once "connect.php";
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    if ($username === "" || $password === "") {
        $message = "กรุณากรอกข้อมูลให้ครบ";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($password === $user["password"]) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                header("Location: home.php");
                exit;
            } else { $message = "รหัสผ่านไม่ถูกต้อง"; }
        } else { $message = "ไม่พบชื่อผู้ใช้"; }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ</title>
<style>
*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;background:#f4f6f8;display:flex;justify-content:center;align-items:center;min-height:100vh}.login-box{width:350px;background:#fff;padding:30px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,.12)}h2{text-align:center;margin-top:0;margin-bottom:25px}label{display:block;margin-top:12px;margin-bottom:6px}input{width:100%;padding:12px;border:1px solid #ccc;border-radius:6px;font-size:15px}.login-btn{width:100%;padding:12px;margin-top:20px;border:0;border-radius:6px;background:#007bff;color:#fff;font-size:16px;cursor:pointer}.error{color:#dc3545;text-align:center;margin-bottom:15px}.register{text-align:center;margin-top:20px;padding-top:20px;border-top:1px solid #eee}.register a{display:inline-block;margin-top:10px;width:100%;padding:10px;border:1px solid #007bff;border-radius:6px;color:#007bff;text-decoration:none}.register a:hover{background:#007bff;color:#fff}
</style>
</head>
<body>
<div class="login-box">
<h2>เข้าสู่ระบบ</h2>
<?php if ($message): ?><div class="error"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<form method="POST">
<label>ชื่อผู้ใช้</label><input type="text" name="username" placeholder="กรอกชื่อผู้ใช้" required>
<label>รหัสผ่าน</label><input type="password" name="password" placeholder="กรอกรหัสผ่าน" required>
<button class="login-btn" type="submit">เข้าสู่ระบบ</button>
</form>
<div class="register">ยังไม่มีบัญชี?<a href="register.php">สมัครสมาชิก</a></div>
</div>
</body>
</html>
