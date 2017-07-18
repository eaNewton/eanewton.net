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
  <form id="contact-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <div class="form_field" id="name_field">
      <label for="name">name</label>
      <input type="text" name="name" value="<?php echo $name; ?>"></input>
    </div>
    <div class="form_field" id="email_field">
      <label for="email">email</label>
      <input type="email" name="email" value="<?php echo $email; ?>"></input>
    </div>
    <div class="form_field" id="message_field">
      <label for="message">message</label>
      <textarea name="message" rows="5" columns="40"></textarea>
    </div>
    <input id="submit-button" type="submit" name="submit" value="submit"></input>
  </form>
</div>

<?php

echo $name;
echo $email;

?>
