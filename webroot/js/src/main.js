let screenHeight = screen.height;

let sections = document.querySelectorAll("section");
for (let i = 0; i < sections.length; i++) {
  sections[i].style.height = screenHeight + "px";
}

let navigation = document.getElementById("navigation");
navigation.style.height = screenHeight + "px";