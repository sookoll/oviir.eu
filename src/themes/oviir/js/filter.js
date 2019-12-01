(function ($) {

  $('#filter').on('input', e => {
    $('#tags a').removeClass('badge-primary').addClass('badge-secondary')
    $(e.target).closest('.container').find('.filter-text').unhighlight()
    const value = $(e.target).val().toLowerCase()
    $(e.target).closest('.container').find('.filter-items > *').filter(function(e) {
      $(this).toggle($(this).find('.filter-text').text().toLowerCase().indexOf(value) > -1)
    });
    $(e.target).closest('.container').find('.filter-text').highlight(value)
    $('button[data-target="#filter-collapse"]').css('color', value.length ? 'red' : 'inherit')
  });

  $('#tags a').on('click', e => {
    e.preventDefault()
    $('#filter').val('')
    $(e.target).closest('.container').find('.filter-text').unhighlight()
    $('#tags a').not(e.currentTarget).removeClass('badge-primary').addClass('badge-secondary')
    $(e.currentTarget).toggleClass('badge-primary badge-secondary')
    let value = ''
    if ($(e.currentTarget).hasClass('badge-primary')) {
      value = $(e.currentTarget).text().toLowerCase()
      $(e.target).closest('.container').find('.filter-items > *').filter(function(e) {
        $(this).toggle($(this).attr('data-tags').split(',').map(tag => tag.trim().toLowerCase()).indexOf(value) > -1)
      })
    } else {
      $(e.target).closest('.container').find('.filter-items > *').show()
    }
    $('button[data-target="#filter-collapse"]').css('color', value.length ? 'red' : 'inherit')
  })

})(jQuery);
