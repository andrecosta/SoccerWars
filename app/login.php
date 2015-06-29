<?php
include("template.php");

if (isset($_POST['email'])) {
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password_hash = sha1(mysqli_real_escape_string($connection, $_POST['password']).$email);

    if ($user_row = mysqli_query($connection, "SELECT * FROM User WHERE email = '$email' AND pw_hash = '$password_hash' AND Status = 'active'")) {
        $user = mysqli_fetch_array($user_row);
        $_SESSION['uid'] = $user['ID'];
        $_SESSION['username'] = $user['UserName'];
        header("Location: index.php");
    } else {
        echo "<div class='debug-message'>Invalid login credentials</div>";
    }
}

template_header("login");
?>

<section class="wide">
    <div class="title">Login</div>
    <div class="content">
        <form method="post" action="">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login">
        </form>
    </div>
</section>

<?php
template_footer();
?>