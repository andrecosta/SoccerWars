<?php
include("template.php");

if (isset($_POST['email'])) {
    $username = mysqli_real_escape_string($connection, $_POST['username']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password_hash = sha1(mysqli_real_escape_string($connection, $_POST['password']).$email);

    if (mysqli_query($connection, "INSERT INTO User (email, pw_hash, Username, Status) VALUES ('$email', '$password_hash', '$username', 'active')")) {
        // TODO: Send confirmation email to "activate" account
        //header("Location: index.php");
        echo "<div class='debug-message'>Your Password: <em>$_POST[password]</em> (shown only for test purposes)<br></div>";
    } else {
        echo "<div class='debug-message'>There was an error creating your account</div>";
    }
}

template_header("register");
?>

<section class="wide">
    <div class="title">Create Account</div>
    <div class="content">
        <form method="post" action="">
            <input type="text" name="username" placeholder="Your User Name" required>
            <input type="email" name="email" placeholder="Your Email Address" required>
            <input type="password" name="password" placeholder="Enter a Password" required>
            <input type="submit" value="Create">
        </form>
    </div>
</section>

<?php
template_footer();
?>