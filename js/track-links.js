(function () {
  /**
   * Finds all <a> elements visible above the fold and captures screen size.
   */
  function getAboveTheFoldLinks() {
    const links = document.querySelectorAll('a');
    const aboveFoldLinks = [];
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;

    links.forEach(link => {
      const rect = link.getBoundingClientRect();
      if (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= viewportHeight &&
        rect.right <= viewportWidth
      ) {
        aboveFoldLinks.push(link.href);
      }
    });

    return {
      screen: {
        width: viewportWidth,
        height: viewportHeight
      },
      links: [...new Set(aboveFoldLinks)] // remove duplicates
    };
  }

  /**
   * Sends the data to the REST API endpoint.
   */
  function sendLinksToServer(payload) {
    fetch('/wp-json/atflt/v1/track', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    }).catch(err => {
      console.error('ATFLT Error:', err);
    });
  }

  // Trigger when page is fully loaded
  window.addEventListener('load', function () {
    const payload = getAboveTheFoldLinks();
    sendLinksToServer(payload);
  });
})();
