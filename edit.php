<?php
include 'config.php';
$id = $_GET['id'];

$sql = "SELECT username FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
?>

<form method="post" action="editUsers.php">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="text" name="username" value="<?php echo $username; ?>" required>
    <button type="submit">Update</button>
</form>
