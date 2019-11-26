$.fn.serializeObject = function() {
  var o = {};
  var a = this.serializeArray();
  $.each(a, function() {
    if (o[this.name]) {
      if (!o[this.name].push) {
        o[this.name] = [o[this.name]];
      }
      o[this.name].push(this.value || '');
    } else {
      o[this.name] = this.value || '';
    }
  });
  this.find('input[type=checkbox]:not(:checked)').each(function() {
    o[this.name] = false
  })
  return o;
};
$.fn.selectText = function(){
  var doc = document;
  var element = this[0];
  if (doc.body.createTextRange) {
    var range = document.body.createTextRange();
    range.moveToElementText(element);
    range.select();
  } else if (window.getSelection) {
    var selection = window.getSelection();
    var range = document.createRange();
    range.selectNodeContents(element);
    selection.removeAllRanges();
    selection.addRange(range);
  }
};

(function ($) {
  "use strict";
  var nav = $('nav');
  var navHeight = nav.outerHeight();

  $('.navbar-toggler').on('click', function() {
    if( ! $('#mainNav').hasClass('navbar-reduce')) {
      $('#mainNav').addClass('navbar-reduce');
    }
  })

  // Preloader
  $(window).on('load', function () {
    if ($('#preloader').length) {
      $('#preloader').delay(100).fadeOut('slow', function () {
        $(this).remove();
      });
    }
  });

  // Back to top button
  $(window).scroll(function() {
    if ($(this).scrollTop() > 100) {
      $('.back-to-top').fadeIn('slow').css("display","inline-block");
    } else {
      $('.back-to-top').fadeOut('slow');
    }
  });
  $('.back-to-top').click(function(){
    $('html, body').animate({scrollTop : 0},1500, 'easeInOutExpo');
    return false;
  });

  /*--/ Star ScrollTop /--*/
  $('.scrolltop-mf').on("click", function () {
    $('html, body').animate({
      scrollTop: 0
    }, 1000);
  });

  /*--/ Star Counter /--*/
  $('.counter').counterUp({
    delay: 15,
    time: 2000
  });

  /*--/ Star Scrolling nav /--*/
  $('a.js-scroll[href*="#"]:not([href="#"])').on("click", function () {
    if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
      if (target.length) {
        $('html, body').animate({
          scrollTop: (target.offset().top - navHeight + 5)
        }, 1000, "easeInOutExpo");
        return false;
      }
    }
  });

  // Closes responsive menu when a scroll trigger link is clicked
  $('.js-scroll').on("click", function () {
    $('.navbar-collapse').collapse('hide');
  });

  // Activate scrollspy to add active class to navbar items on scroll
  /*$('body').scrollspy({
    target: '#mainNav',
    offset: navHeight
  });*/
  /*--/ End Scrolling nav /--*/

  /*--/ Navbar Menu Reduce /--*/
  $(window).trigger('scroll');
  $(window).on('scroll', function () {
    var pixels = 50;
    var top = 1200;
    if ($(window).scrollTop() > pixels) {
      $('.navbar-expand-md').addClass('navbar-reduce');
      $('.navbar-expand-md').removeClass('navbar-trans');
    } else {
      $('.navbar-expand-md').addClass('navbar-trans');
      $('.navbar-expand-md').removeClass('navbar-reduce');
    }
    if ($(window).scrollTop() > top) {
      $('.scrolltop-mf').fadeIn(1000, "easeInOutExpo");
    } else {
      $('.scrolltop-mf').fadeOut(1000, "easeInOutExpo");
    }
  });

  /*--/ Star Typed /--*/
  if ($('.text-slider').length == 1) {
    var typed_strings = $('.text-slider-items').text();
    var typed = new Typed('.text-slider', {
      strings: typed_strings.split(','),
      typeSpeed: 80,
      loop: true,
      backDelay: 1100,
      backSpeed: 30
    });
  }

  /*--/ Testimonials owl /--*/
  $('#testimonial-mf').owlCarousel({
    margin: 20,
    autoplay: true,
    autoplayTimeout: 4000,
    autoplayHoverPause: true,
    responsive: {
      0: {
        items: 1,
      }
    }
  });

  // lazy load
  yall({
    observeChanges: true
  })

  $('[data-toggle="tooltip"]').tooltip()
  $('[data-toggle="popover"]').popover()

  const register_info = $('#register .info').text()

  const onError = (form, error) => {
    form.find('button[type="submit"]')
      .removeClass('btn-primary')
      .addClass('btn-danger')
    form.find('.info').text(error)
    form.find('input').prop('disabled', true)
    setTimeout(() => {
      $('#register').modal('hide')
    }, 5000)
  }

  $('#register').on('hide.bs.modal', e => {
    const form = $(e.target).find('form')
    form.find('button[type="submit"]').removeClass('btn-success btn-danger').addClass('btn-primary')
    form.find('input').val('').prop('disabled', false)
    form.find('.info').text(register_info)
  })

  $('#register form').on('submit', e => {
    e.preventDefault()
    const form = $(e.target).closest('form')
    const url = form.attr('action')
    const data = form.serialize()
    const method = form.attr('method')
    fetch(url, {
      method: method,
      body: data,
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      }
    }).then(response => response.text())
      .then(response => {
        if (response === 'ok') {
          form.find('button[type="submit"]')
            .removeClass('btn-primary')
            .addClass('btn-success')
          form.find('.info').text('Kasutaja lisamine õnnestus. Logi välja ja uue kasutajaga sisse.')
          form.find('input').prop('disabled', true)
          setTimeout(() => {
            $('#register').modal('hide')
          }, 5000)
        } else {
          onError(form, response)
        }
      })
      .catch(error => {
        onError(form, error.toString())
      })
  })

})(jQuery);
