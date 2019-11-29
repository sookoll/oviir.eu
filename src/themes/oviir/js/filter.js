(function ($) {

  $('#filter').on('search', e => {
    $('#tags a').removeClass('badge-primary').addClass('badge-secondary')
    const value = $(e.target).val().toLowerCase()
    $(e.target).closest('.container').find('.filter-items > *').filter(function(e) {
      $(this).toggle($(this).find('.filter-text').text().toLowerCase().indexOf(value) > -1)
    });
    $('button[data-target="#filter-collapse"]').css('color', value.length ? 'red' : 'inherit')
  });

  $('#tags a').on('click', e => {
    e.preventDefault()
    $('#filter').val('')
    $('#tags a').not(e.currentTarget).removeClass('badge-primary').addClass('badge-secondary')
    $(e.currentTarget).toggleClass('badge-primary badge-secondary')
    let value = ''
    if ($(e.currentTarget).hasClass('badge-primary')) {
      const value = $(e.currentTarget).text().toLowerCase()
      $(e.target).closest('.container').find('.filter-items > *').filter(function(e) {
        $(this).toggle($(this).attr('data-tags').split(',').map(tag => tag.trim().toLowerCase()).indexOf(value) > -1)
      })
    } else {
      $(e.target).closest('.container').find('.filter-items > *').show()
    }
    $('button[data-target="#filter-collapse"]').css('color', value.length ? 'red' : 'inherit')
  })

})(jQuery);
