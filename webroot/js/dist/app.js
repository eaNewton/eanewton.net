"use strict";

var screenHeight = screen.height;

var sections = document.querySelectorAll("section");
for (var i = 0; i < sections.length; i++) {
  sections[i].style.height = screenHeight + "px";
}

var navigation = document.getElementById("navigation");
navigation.style.height = screenHeight + "px";