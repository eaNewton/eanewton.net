var lightOn = "light-bulb-on.svg";

function andThenThereWasLight() {
  if (lightOn == "light-bulb-on.svg") {
    document.images["mobile-menu-icon"].src = "/wp-content/themes/eanewton/images/light-bulb-on.svg";
    document.images["mobile-menu-icon"].alt = "Lights on!";
    lightOn = "light-bulb-off.svg";
  } else {
    document.images["mobile-menu-icon"].src = "/wp-content/themes/eanewton/images/light-bulb-off.svg";
    document.images["mobile-menu-icon"].alt = "Lights off!";
    lightOn = "light-bulb-on.svg";
  }
}

window.onload = function () {
  var mobileMenuTrigger = document.querySelector(".mobile-menu-trigger");

  if (mobileMenuTrigger) {
    mobileMenuTrigger.addEventListener('click', function (e) {
      e.preventDefault();
      this.classList.toggle('active');
      this.nextElementSibling.classList.toggle('open');
    })
  }
}
