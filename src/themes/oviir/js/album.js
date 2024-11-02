function createAlbumDOM (data) {
  data.forEach((item, i) => {
    $('#gallery').append(`
      <figure class="col-lg-3 col-md-4 col-6 thumb p-1">
        <div class="work-box">
          <a href="#${ i + 1 }" class="work-img">
            <img class="img-fluid lazy" 
                src="${ THEME_URL }/img/placeholder.png" 
                data-src="${ item.thumb_url }" alt="${ item.title || item.id}"
                onerror="imgerror(this)" />
          </a>
        </div>
      </figure>
    `)
  })
}

function createSlides (data) {
  const pswpElement = document.querySelectorAll('.pswp')[0]
  const items = data.map(item => {
    return {
      src: item.img_url,
      w: item.img_width,
      h: item.img_height,
      title: item.title || item.id,
      description: item.description,
      datetime: item.datetaken,
    }
  })
  const options = {
    index: null,
    showHideOpacity:true,
    getThumbBoundsFn:false
  }
  const params = parseHash()
  if (params && params.pid) {
    options.index = params.pid
    openPicture(pswpElement, PhotoSwipeUI_Default, items, options)
  }
  $('#gallery').on('click', '.thumb a', e => {
    e.preventDefault()
    options.index = e.currentTarget.hash.substring(1)
    openPicture(pswpElement, PhotoSwipeUI_Default, items, options)
  })
}

function openPicture (pswpElement, PhotoSwipeUI_Default, items, options) {
  options.index = parseInt(options.index, 10) - 1
  const gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options)
  gallery.init()
}

// parse picture index and gallery index from URL (#&pid=1&gid=2)
function parseHash () {
  const hash = window.location.hash.substring(1)
  if (hash.length >= 5) {
    const params = new URLSearchParams(hash)
    if (params.has('pid')) {
      return Object.fromEntries(params)
    }
  }
  return {}
}

function imgerror(e)
{
  setTimeout(reloadImg, 1000, e);
}

function reloadImg(e)
{
  const source = e.src;
  e.removeAttribute('onerror');
  e.src = source;
}


(function($, Picu) {
  const picu = new Picu({
    url: config.url,
    thumbSize: 'SQ320',
    imgSize: 'L1600',
  });
  setTimeout(() => {
    picu.load()
      .then(data => {
        createAlbumDOM(data)
        createSlides(data)
      })
      .catch(e => {
        console.error(e)
      })
  }, 200)

})(jQuery, Picu);
