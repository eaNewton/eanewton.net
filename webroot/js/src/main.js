let screenHeight = screen.height;
console.log(screenHeight);

let sections = document.querySelectorAll("section");
for (let i = 0; i < sections.length; i++) {
  console.log(sections[i]);
  sections[i].style.height = screenHeight + "px";
}

let navigation = document.getElementById("navigation");
navigation.style.height = screenHeight + "px";