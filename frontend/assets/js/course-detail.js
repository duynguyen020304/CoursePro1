
  // Toggle individual syllabus section open/close
  function toggleSyllabusSection(button) {
    const expanded = button.getAttribute('aria-expanded') === 'true';
    button.setAttribute('aria-expanded', !expanded);
    const contentId = button.getAttribute('aria-controls');
    const content = document.getElementById(contentId);
    if (content) {
      if (expanded) {
        content.classList.remove('open');
      } else {
        content.classList.add('open');
      }
    }
  }

  // Expand all / collapse all sections toggle
  document.querySelector('.course-content-expand').addEventListener('click', function(e) {
    e.preventDefault();
    const expandAll = this.getAttribute('aria-expanded') === 'true';
    this.setAttribute('aria-expanded', !expandAll);
    this.textContent = expandAll ? 'Expand all sections' : 'Collapse all sections';

    const toggles = document.querySelectorAll('.course-section-toggle');
    toggles.forEach(toggle => {
      toggle.setAttribute('aria-expanded', !expandAll);
      const contentId = toggle.getAttribute('aria-controls');
      const content = document.getElementById(contentId);
      if (content) {
        if (expandAll) {
          content.classList.remove('open');
        } else {
          content.classList.add('open');
        }
      }
    });
  });

  // Initialize first section open
  document.addEventListener('DOMContentLoaded', () => {
    const firstToggle = document.querySelector('.course-section-toggle');
    if (firstToggle) {
      firstToggle.setAttribute('aria-expanded', 'true');
      const contentId = firstToggle.getAttribute('aria-controls');
      const content = document.getElementById(contentId);
      if (content) {
        content.classList.add('open');
      }
    }
  });

  // Toggle description show more/less
  function toggleDescription(link) {
    const expanded = link.getAttribute('aria-expanded') === 'true';
    link.setAttribute('aria-expanded', !expanded);
    link.textContent = expanded ? 'Show more' : 'Show less';
    const descMore = document.getElementById('description-more');
    if (descMore) {
      descMore.hidden = expanded;
    }
  }

