"use strict";

var screenHeight = screen.height;
console.log(screenHeight);

var sections = document.querySelectorAll("section");
for (var i = 0; i < sections.length; i++) {
  console.log(sections[i]);
  sections[i].style.height = screenHeight + "px";
}

var navigation = document.getElementById("navigation");
navigation.style.height = screenHeight + "px";