(function ($) {

  $('#filter').on('keyup', e => {
    $('.tags a').removeClass('badge-primary').addClass('badge-secondary')
    const value = $(e.target).val().toLowerCase()
    $(e.target).closest('.container').find('.filter-items > *').filter(function(e) {
      $(this).toggle($(this).find('.s-title a').text().toLowerCase().indexOf(value) > -1)
    });
  });

  $('.tags a').on('click', e => {
    e.preventDefault()
    $('#filter').val('')
    $('.tags a').not(e.currentTarget).removeClass('badge-primary').addClass('badge-secondary')
    $(e.currentTarget).toggleClass('badge-primary badge-secondary')
    if ($(e.currentTarget).hasClass('badge-primary')) {
      value = $(e.currentTarget).text().toLowerCase()
      $(e.target).closest('.container').find('.filter-items > *').filter(function(e) {
        $(this).toggle($(this).data('tags').split(',').map(tag => tag.trim().toLowerCase()).indexOf(value) > -1)
      })
    } else {
      $(e.target).closest('.container').find('.filter-items > *').show()
    }
  })

})(jQuery);
