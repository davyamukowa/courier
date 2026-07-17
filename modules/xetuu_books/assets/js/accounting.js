document.addEventListener('DOMContentLoaded', function() {
  // Close dropdown when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.acc-nav-item')) {
      document.querySelectorAll('.acc-nav-item').forEach(el => {
        el.classList.remove('open');
      });
    }
  });

  // Toggle on nav item click
  document.querySelectorAll('.acc-nav-item[data-has-dropdown]').forEach(item => {
    item.addEventListener('click', (e) => {
      // Allow links inside the dropdown to be clicked without toggling the menu again
      if (e.target.closest('.acc-dropdown')) {
        return;
      }
      
      e.stopPropagation();
      const isOpen = item.classList.contains('open');
      
      // Close all first
      document.querySelectorAll('.acc-nav-item').forEach(el => {
        el.classList.remove('open');
      });
      
      // Open this one if it was closed
      if (!isOpen) {
        item.classList.add('open');
      }
    });
  });

  // Keyboard: Escape closes all
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.acc-nav-item').forEach(el => {
        el.classList.remove('open');
      });
    }
  });
});
