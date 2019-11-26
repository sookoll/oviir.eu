(function($) {
  setTimeout(() => {
    $('[data-url]').each((i, item) => {
      const api = new Api({ endpoint: $(item).data('url'), hash: config.hash })
      api.load()
        .then(data => {
          $(item).text(data.length || 0)
        })
        .catch(e => {
          console.error(e)
        })
    })
  }, 200)
})(jQuery);
