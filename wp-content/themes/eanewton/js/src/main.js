function andThenThereWasLight() {

  if (document.getElementById("mobile-menu-icon").src == "/wp-content/themes/eanewton/images/light-bulb-off.svg") {
    document.getElementById("mobile-menu-icon").src = "/wp-content/themes/eanewton/images/light-bulb-on.svg";
  }
  else {
    document.getElementById("mobile-menu-icon").src = "/wp-content/themes/eanewton/images/light-bulb-off.svg";
  }
}
