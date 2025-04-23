const navbar = document.querySelector('.profile_picture');
const toggleButtons = [
  document.getElementById('profileToggle'),
  document.getElementById('profileToggleImg')
];

toggleButtons.forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    navbar.classList.toggle('active');
  });
});


document.addEventListener('click', function (e) {
  const menu = document.querySelector('.profile_picture');
  const isClickInside = toggleButtons.some(btn => btn.contains(e.target)) || menu.contains(e.target);

  if (!isClickInside) {
    navbar.classList.remove('active');
  }
});
