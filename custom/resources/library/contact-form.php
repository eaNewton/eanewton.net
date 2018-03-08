<?php
//Vars
$nameError = $emailError = "";
$name = $email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["name"])) {
    $nameError = "Your name is required";
  } else {
    $name = test_input($_POST["name"]);
    //Make sure name only contains letters & whitespace
    if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
      $nameError = "Only letters + spaces";
    }
  }

  if (empty($_POST["email"])) {
    $emailError = "Your email is required";
  } else {
    $email = test_input($_POST["email"]);
    //Make sure email is a valid email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $emailError = "Invalid email";
    }
  }

  if (empty($_POST["message"])) {
    $comment = "";
  } else {
    $comment = test_input($_POST["message"]);
  }
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

?>

<div class="form-container">
  <form id="contact-form" class="send-down" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <div class="col-3 input-effect">
      <input class="effect-22" type="text" placeholder="">
        <label>Name</label>
        <span class="focus-bg"></span>

      <!-- <input type="text" name="name" value="<?php echo $name; ?>" required></input>
      <label for="name">name</label> -->
    </div>
    <div class="col-3 input-effect">
      <input class="effect-22" type="email" name="email" placeholder="">
        <label>Email</label>
        <span class="focus-bg"></span>

      <!-- <input class="effect-22" type="email" name="email" value="<?php echo $email; ?>" required></input>
      <label for="email">email</label> -->
    </div>
    <div class="form_field col-3 input-effect" id="message_field">
      <textarea class="effect-22" rows="5" columns="40"></textarea>
        <label>Message</label>
        <span class="focus-bg"></span>

      <!-- <textarea name="message" rows="5" columns="40" required></textarea>
      <label for="message">message</label> -->
    </div>
    <div class="form_field" id="submit_field">
      <input id="submit-button" type="submit" name="submit" value="submit"></input>
    </div>
  </form>
</div>

<?php

echo $name;
echo $email;

?>
